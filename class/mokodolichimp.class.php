<?php
/* Copyright (C) 2024 Moko Consulting <hello@mokoconsulting.tech>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    class/mokodolichimp.class.php
 * \ingroup mokodolichimp
 * \brief   Main class file for MokoDoliChimp module - handles Mailchimp API integration
 */

// Check if vendor autoload exists, otherwise use mock implementation
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// Import Mailchimp API classes if available
if (class_exists('MailchimpMarketing\ApiClient')) {
    // Use real Mailchimp SDK
} else {
    // Mock ApiClient for standalone testing
    class ApiClient {
        public function setConfig($config) {}
        public function ping() { return (object)['status' => 'ok']; }
        public $lists;
        public function __construct() {
            $this->lists = new class {
                public function setListMember($listId, $hash, $data) {
                    return (object)['id' => 'mock_' . rand(1000, 9999)];
                }
                public function getAllLists() {
                    return (object)['lists' => []];
                }
                public function getListMembersInfo($listId) {
                    return (object)['members' => []];
                }
            };
            $this->ping = new class {
                public function get() { return (object)['status' => 'ok']; }
            };
        }
    }
}

/**
 * Main MokoDoliChimp class for synchronization functionality
 */
class MokoDoliChimp
{
    private $db;
    private $mailchimp;
    private $config;
    private $logger;

    /**
     * Constructor
     * @param object $db Database connection
     */
    public function __construct($db)
    {
        $this->db = $db;
        $this->loadConfig();
        $this->initMailchimp();
        $this->logger = new MokoDoliChimpLogger($db);
    }

    /**
     * Load module configuration
     */
    private function loadConfig()
    {
        $this->config = [
            'api_key' => $this->getConfigValue('MOKODOLICHIMP_API_KEY'),
            'server_prefix' => $this->getConfigValue('MOKODOLICHIMP_SERVER_PREFIX'),
            'default_list_id' => $this->getConfigValue('MOKODOLICHIMP_DEFAULT_LIST'),
            'sync_enabled' => $this->getConfigValue('MOKODOLICHIMP_SYNC_ENABLED', '1'),
            'auto_sync' => $this->getConfigValue('MOKODOLICHIMP_AUTO_SYNC', '1'),
            'sync_third_parties' => $this->getConfigValue('MOKODOLICHIMP_SYNC_THIRDPARTIES', '1'),
            'sync_contacts' => $this->getConfigValue('MOKODOLICHIMP_SYNC_CONTACTS', '1'),
            'sync_users' => $this->getConfigValue('MOKODOLICHIMP_SYNC_USERS', '1'),
        ];
    }

    /**
     * Initialize Mailchimp API client
     */
    private function initMailchimp()
    {
        if (empty($this->config['api_key']) || empty($this->config['server_prefix'])) {
            $this->logger->error('Mailchimp API credentials not configured');
            return false;
        }

        try {
            $this->mailchimp = new ApiClient();
            $this->mailchimp->setConfig([
                'apiKey' => $this->config['api_key'],
                'server' => $this->config['server_prefix']
            ]);
            
            // Test connection
            $this->mailchimp->ping->get();
            $this->logger->info('Mailchimp connection established successfully');
            return true;
        } catch (Exception $e) {
            $this->logger->error('Failed to initialize Mailchimp: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get configuration value
     * @param string $key Configuration key
     * @param string $default Default value
     * @return string Configuration value
     */
    private function getConfigValue($key, $default = '')
    {
        // In a real Dolibarr module, this would use $conf->global->$key
        // For this implementation, we'll simulate configuration storage
        $configs = [
            'MOKODOLICHIMP_API_KEY' => '',
            'MOKODOLICHIMP_SERVER_PREFIX' => '',
            'MOKODOLICHIMP_DEFAULT_LIST' => '',
            'MOKODOLICHIMP_SYNC_ENABLED' => '1',
            'MOKODOLICHIMP_AUTO_SYNC' => '1',
            'MOKODOLICHIMP_SYNC_THIRDPARTIES' => '1',
            'MOKODOLICHIMP_SYNC_CONTACTS' => '1',
            'MOKODOLICHIMP_SYNC_USERS' => '1',
        ];
        
        return isset($configs[$key]) ? $configs[$key] : $default;
    }

    // ========================================
    // THIRD PARTIES SYNCHRONIZATION FUNCTIONS
    // ========================================

    /**
     * Sync all third parties to Mailchimp
     * @param string $list_id Mailchimp list ID (optional)
     * @return array Sync results
     */
    public function syncThirdPartiesToMailchimp($list_id = null)
    {
        if (!$this->config['sync_third_parties']) {
            return ['status' => 'disabled', 'message' => 'Third parties sync is disabled'];
        }

        $list_id = $list_id ?: $this->config['default_list_id'];
        if (empty($list_id)) {
            return ['status' => 'error', 'message' => 'No Mailchimp list ID specified'];
        }

        $thirdparties = $this->getThirdParties();
        $results = ['success' => 0, 'errors' => 0, 'skipped' => 0, 'details' => []];

        foreach ($thirdparties as $thirdparty) {
            try {
                $result = $this->syncThirdPartyToMailchimp($thirdparty, $list_id);
                if ($result['status'] === 'success') {
                    $results['success']++;
                } else {
                    $results['errors']++;
                }
                $results['details'][] = $result;
            } catch (Exception $e) {
                $results['errors']++;
                $results['details'][] = [
                    'id' => $thirdparty['id'],
                    'email' => $thirdparty['email'],
                    'status' => 'error',
                    'message' => $e->getMessage()
                ];
            }
        }

        $this->logger->info(sprintf(
            'Third parties sync completed: %d success, %d errors, %d skipped',
            $results['success'],
            $results['errors'],
            $results['skipped']
        ));

        return $results;
    }

    /**
     * Sync single third party to Mailchimp
     * @param array $thirdparty Third party data
     * @param string $list_id Mailchimp list ID
     * @return array Sync result
     */
    public function syncThirdPartyToMailchimp($thirdparty, $list_id)
    {
        if (empty($thirdparty['email'])) {
            return ['status' => 'skipped', 'message' => 'No email address'];
        }

        try {
            $email_hash = md5(strtolower($thirdparty['email']));
            
            $member_data = [
                'email_address' => $thirdparty['email'],
                'status_if_new' => 'subscribed',
                'merge_fields' => [
                    'FNAME' => $thirdparty['firstname'] ?? '',
                    'LNAME' => $thirdparty['lastname'] ?? $thirdparty['name'] ?? '',
                    'COMPANY' => $thirdparty['name'] ?? '',
                    'PHONE' => $thirdparty['phone'] ?? '',
                    'ADDRESS' => $thirdparty['address'] ?? '',
                    'ZIP' => $thirdparty['zip'] ?? '',
                    'CITY' => $thirdparty['town'] ?? '',
                    'COUNTRY' => $thirdparty['country'] ?? '',
                    'SIGNUP' => $thirdparty['date_creation'] ?? '', // Creation date as signup date
                ],
                'tags' => ['dolibarr-thirdparty', 'company']
            ];

            $response = $this->mailchimp->lists->setListMember($list_id, $email_hash, $member_data);
            
            // Update sync status in database
            $this->updateSyncStatus('thirdparty', $thirdparty['id'], 'mailchimp', $response->id, 'success');
            
            return [
                'id' => $thirdparty['id'],
                'email' => $thirdparty['email'],
                'status' => 'success',
                'mailchimp_id' => $response->id
            ];
        } catch (Exception $e) {
            $this->updateSyncStatus('thirdparty', $thirdparty['id'], 'mailchimp', null, 'error', $e->getMessage());
            throw $e;
        }
    }

    // ========================================
    // CONTACTS SYNCHRONIZATION FUNCTIONS
    // ========================================

    /**
     * Sync all contacts to Mailchimp
     * @param string $list_id Mailchimp list ID (optional)
     * @return array Sync results
     */
    public function syncContactsToMailchimp($list_id = null)
    {
        if (!$this->config['sync_contacts']) {
            return ['status' => 'disabled', 'message' => 'Contacts sync is disabled'];
        }

        $list_id = $list_id ?: $this->config['default_list_id'];
        if (empty($list_id)) {
            return ['status' => 'error', 'message' => 'No Mailchimp list ID specified'];
        }

        $contacts = $this->getContacts();
        $results = ['success' => 0, 'errors' => 0, 'skipped' => 0, 'details' => []];

        foreach ($contacts as $contact) {
            try {
                $result = $this->syncContactToMailchimp($contact, $list_id);
                if ($result['status'] === 'success') {
                    $results['success']++;
                } else {
                    $results['errors']++;
                }
                $results['details'][] = $result;
            } catch (Exception $e) {
                $results['errors']++;
                $results['details'][] = [
                    'id' => $contact['id'],
                    'email' => $contact['email'],
                    'status' => 'error',
                    'message' => $e->getMessage()
                ];
            }
        }

        $this->logger->info(sprintf(
            'Contacts sync completed: %d success, %d errors, %d skipped',
            $results['success'],
            $results['errors'],
            $results['skipped']
        ));

        return $results;
    }

    /**
     * Sync single contact to Mailchimp
     * @param array $contact Contact data
     * @param string $list_id Mailchimp list ID
     * @return array Sync result
     */
    public function syncContactToMailchimp($contact, $list_id)
    {
        if (empty($contact['email'])) {
            return ['status' => 'skipped', 'message' => 'No email address'];
        }

        try {
            $email_hash = md5(strtolower($contact['email']));
            
            $member_data = [
                'email_address' => $contact['email'],
                'status_if_new' => 'subscribed',
                'merge_fields' => [
                    'FNAME' => $contact['firstname'] ?? '',
                    'LNAME' => $contact['lastname'] ?? '',
                    'PHONE' => $contact['phone'] ?? '',
                    'MOBILE' => $contact['phone_mobile'] ?? '',
                    'COMPANY' => $contact['company'] ?? '',
                    'ADDRESS' => $contact['address'] ?? '',
                    'ZIP' => $contact['zip'] ?? '',
                    'CITY' => $contact['town'] ?? '',
                    'COUNTRY' => $contact['country'] ?? '',
                    'BIRTHDAY' => $contact['birthday'] ?? '', // Date of birth
                    'SIGNUP' => $contact['date_creation'] ?? '',
                ],
                'tags' => ['dolibarr-contact', 'individual']
            ];

            $response = $this->mailchimp->lists->setListMember($list_id, $email_hash, $member_data);
            
            // Update sync status in database
            $this->updateSyncStatus('contact', $contact['id'], 'mailchimp', $response->id, 'success');
            
            return [
                'id' => $contact['id'],
                'email' => $contact['email'],
                'status' => 'success',
                'mailchimp_id' => $response->id
            ];
        } catch (Exception $e) {
            $this->updateSyncStatus('contact', $contact['id'], 'mailchimp', null, 'error', $e->getMessage());
            throw $e;
        }
    }

    // ========================================
    // USERS SYNCHRONIZATION FUNCTIONS
    // ========================================

    /**
     * Sync all users to Mailchimp
     * @param string $list_id Mailchimp list ID (optional)
     * @return array Sync results
     */
    public function syncUsersToMailchimp($list_id = null)
    {
        if (!$this->config['sync_users']) {
            return ['status' => 'disabled', 'message' => 'Users sync is disabled'];
        }

        $list_id = $list_id ?: $this->config['default_list_id'];
        if (empty($list_id)) {
            return ['status' => 'error', 'message' => 'No Mailchimp list ID specified'];
        }

        $users = $this->getUsers();
        $results = ['success' => 0, 'errors' => 0, 'skipped' => 0, 'details' => []];

        foreach ($users as $user) {
            try {
                $result = $this->syncUserToMailchimp($user, $list_id);
                if ($result['status'] === 'success') {
                    $results['success']++;
                } else {
                    $results['errors']++;
                }
                $results['details'][] = $result;
            } catch (Exception $e) {
                $results['errors']++;
                $results['details'][] = [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'status' => 'error',
                    'message' => $e->getMessage()
                ];
            }
        }

        $this->logger->info(sprintf(
            'Users sync completed: %d success, %d errors, %d skipped',
            $results['success'],
            $results['errors'],
            $results['skipped']
        ));

        return $results;
    }

    /**
     * Sync single user to Mailchimp
     * @param array $user User data
     * @param string $list_id Mailchimp list ID
     * @return array Sync result
     */
    public function syncUserToMailchimp($user, $list_id)
    {
        if (empty($user['email'])) {
            return ['status' => 'skipped', 'message' => 'No email address'];
        }

        try {
            $email_hash = md5(strtolower($user['email']));
            
            $member_data = [
                'email_address' => $user['email'],
                'status_if_new' => 'subscribed',
                'merge_fields' => [
                    'FNAME' => $user['firstname'] ?? '',
                    'LNAME' => $user['lastname'] ?? '',
                    'PHONE' => $user['office_phone'] ?? '',
                    'MOBILE' => $user['user_mobile'] ?? '',
                    'JOB' => $user['job'] ?? '',
                    'ADDRESS' => $user['address'] ?? '',
                    'ZIP' => $user['zip'] ?? '',
                    'CITY' => $user['town'] ?? '',
                    'COUNTRY' => $user['country'] ?? '',
                    'BIRTHDAY' => $user['birth'] ?? '', // Date of birth
                    'SIGNUP' => $user['date_creation'] ?? '',
                ],
                'tags' => ['dolibarr-user', 'employee']
            ];

            $response = $this->mailchimp->lists->setListMember($list_id, $email_hash, $member_data);
            
            // Update sync status in database
            $this->updateSyncStatus('user', $user['id'], 'mailchimp', $response->id, 'success');
            
            return [
                'id' => $user['id'],
                'email' => $user['email'],
                'status' => 'success',
                'mailchimp_id' => $response->id
            ];
        } catch (Exception $e) {
            $this->updateSyncStatus('user', $user['id'], 'mailchimp', null, 'error', $e->getMessage());
            throw $e;
        }
    }

    // ========================================
    // REVERSE SYNC FUNCTIONS (MAILCHIMP TO DOLIBARR)
    // ========================================

    /**
     * Sync Mailchimp subscribers back to Dolibarr
     * @param string $list_id Mailchimp list ID
     * @return array Sync results
     */
    public function syncFromMailchimp($list_id = null)
    {
        $list_id = $list_id ?: $this->config['default_list_id'];
        if (empty($list_id)) {
            return ['status' => 'error', 'message' => 'No Mailchimp list ID specified'];
        }

        try {
            $members = $this->getMailchimpMembers($list_id);
            $results = ['success' => 0, 'errors' => 0, 'skipped' => 0, 'details' => []];

            foreach ($members as $member) {
                try {
                    $result = $this->syncMemberToDolibarr($member);
                    if ($result['status'] === 'success') {
                        $results['success']++;
                    } else {
                        $results['skipped']++;
                    }
                    $results['details'][] = $result;
                } catch (Exception $e) {
                    $results['errors']++;
                    $results['details'][] = [
                        'email' => $member->email_address,
                        'status' => 'error',
                        'message' => $e->getMessage()
                    ];
                }
            }

            return $results;
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Sync single Mailchimp member to Dolibarr
     * @param object $member Mailchimp member data
     * @return array Sync result
     */
    private function syncMemberToDolibarr($member)
    {
        // Check if member already exists in Dolibarr
        $existing = $this->findDolibarrContactByEmail($member->email_address);
        
        if ($existing) {
            return ['status' => 'skipped', 'message' => 'Contact already exists in Dolibarr'];
        }

        // Create new contact in Dolibarr
        $contact_data = [
            'email' => $member->email_address,
            'firstname' => $member->merge_fields->FNAME ?? '',
            'lastname' => $member->merge_fields->LNAME ?? '',
            'phone' => $member->merge_fields->PHONE ?? '',
            'company' => $member->merge_fields->COMPANY ?? '',
            'source' => 'mailchimp'
        ];

        $contact_id = $this->createDolibarrContact($contact_data);
        
        if ($contact_id) {
            $this->updateSyncStatus('contact', $contact_id, 'dolibarr', $member->id, 'success');
            return [
                'email' => $member->email_address,
                'status' => 'success',
                'dolibarr_id' => $contact_id
            ];
        } else {
            return ['status' => 'error', 'message' => 'Failed to create contact in Dolibarr'];
        }
    }

    // ========================================
    // SCHEDULED SYNC FUNCTIONS
    // ========================================

    /**
     * Perform scheduled synchronization (called by cron)
     * @return array Sync results
     */
    public function doScheduledSync()
    {
        if (!$this->config['auto_sync'] || !$this->config['sync_enabled']) {
            return ['status' => 'disabled', 'message' => 'Auto sync is disabled'];
        }

        $this->logger->info('Starting scheduled sync');
        
        $results = [
            'thirdparties' => $this->syncThirdPartiesToMailchimp(),
            'contacts' => $this->syncContactsToMailchimp(),
            'users' => $this->syncUsersToMailchimp(),
            'reverse' => $this->syncFromMailchimp()
        ];

        $this->logger->info('Scheduled sync completed');
        
        return $results;
    }

    // ========================================
    // DATA RETRIEVAL FUNCTIONS
    // ========================================

    /**
     * Get all third parties from Dolibarr
     * @return array Third parties data
     */
    private function getThirdParties()
    {
        // In real Dolibarr implementation, this would query:
        // SELECT rowid, nom, email, phone, address, zip, town, country_code, datec, tms
        // FROM llx_societe WHERE entity IN (1) AND status = 1
        
        // Mock data with additional fields including DOB-like creation date
        return [
            [
                'id' => 1,
                'name' => 'ACME Corporation',
                'email' => 'contact@acme.com',
                'firstname' => 'John',
                'lastname' => 'Doe',
                'phone' => '+1234567890',
                'address' => '123 Business Ave',
                'zip' => '10001',
                'town' => 'New York',
                'country' => 'US',
                'date_creation' => '2023-01-15',
                'date_modification' => '2024-12-01'
            ],
            [
                'id' => 2,
                'name' => 'Tech Solutions Ltd',
                'email' => 'info@techsolutions.com',
                'firstname' => 'Jane',
                'lastname' => 'Smith',
                'phone' => '+1987654321',
                'address' => '456 Tech Street',
                'zip' => '90210',
                'town' => 'Los Angeles',
                'country' => 'US',
                'date_creation' => '2023-03-20',
                'date_modification' => '2024-11-15'
            ]
        ];
    }

    /**
     * Get all contacts from Dolibarr
     * @return array Contacts data
     */
    private function getContacts()
    {
        // In real Dolibarr implementation, this would query:
        // SELECT rowid, firstname, lastname, email, phone, phone_perso, phone_mobile, 
        //        birthday, address, zip, town, country, fk_soc, statut, datec, tms
        // FROM llx_socpeople WHERE entity IN (1) AND statut = 1
        
        return [
            [
                'id' => 1,
                'firstname' => 'Alice',
                'lastname' => 'Johnson',
                'email' => 'alice.johnson@company.com',
                'phone' => '+1555123456',
                'phone_mobile' => '+1555123457',
                'company' => 'Johnson & Associates',
                'fk_soc' => 1,
                'birthday' => '1985-06-15',
                'address' => '789 Contact Lane',
                'zip' => '10002',
                'town' => 'New York',
                'country' => 'US',
                'date_creation' => '2023-02-10',
                'date_modification' => '2024-12-01'
            ],
            [
                'id' => 2,
                'firstname' => 'Bob',
                'lastname' => 'Wilson',
                'email' => 'bob.wilson@enterprise.com',
                'phone' => '+1555789012',
                'phone_mobile' => '+1555789013',
                'company' => 'Wilson Enterprises',
                'fk_soc' => 2,
                'birthday' => '1978-11-22',
                'address' => '321 Professional Blvd',
                'zip' => '90211',
                'town' => 'Los Angeles',
                'country' => 'US',
                'date_creation' => '2023-04-05',
                'date_modification' => '2024-11-20'
            ]
        ];
    }

    /**
     * Get all users from Dolibarr
     * @return array Users data
     */
    private function getUsers()
    {
        // In real Dolibarr implementation, this would query:
        // SELECT rowid, login, firstname, lastname, email, office_phone, user_mobile,
        //        birth, job, address, zip, town, country, statut, datec, tms
        // FROM llx_user WHERE entity IN (1) AND statut = 1
        
        return [
            [
                'id' => 1,
                'login' => 'admin',
                'firstname' => 'Admin',
                'lastname' => 'User',
                'email' => 'admin@dolibarr.local',
                'office_phone' => '+1555000000',
                'user_mobile' => '+1555000001',
                'birth' => '1980-01-01',
                'job' => 'System Administrator',
                'address' => '100 Admin Street',
                'zip' => '10003',
                'town' => 'New York',
                'country' => 'US',
                'date_creation' => '2023-01-01',
                'date_modification' => '2024-12-01'
            ],
            [
                'id' => 2,
                'login' => 'sales',
                'firstname' => 'Sales',
                'lastname' => 'Manager',
                'email' => 'sales@dolibarr.local',
                'office_phone' => '+1555111111',
                'user_mobile' => '+1555111112',
                'birth' => '1975-05-10',
                'job' => 'Sales Manager',
                'address' => '200 Sales Plaza',
                'zip' => '90212',
                'town' => 'Los Angeles', 
                'country' => 'US',
                'date_creation' => '2023-01-15',
                'date_modification' => '2024-11-30'
            ]
        ];
    }

    /**
     * Get Mailchimp list members
     * @param string $list_id List ID
     * @return array Members data
     */
    private function getMailchimpMembers($list_id)
    {
        try {
            $response = $this->mailchimp->lists->getListMembersInfo($list_id);
            return $response->members ?? [];
        } catch (Exception $e) {
            $this->logger->error('Failed to get Mailchimp members: ' . $e->getMessage());
            return [];
        }
    }

    // ========================================
    // HELPER FUNCTIONS
    // ========================================

    /**
     * Update sync status in database
     * @param string $entity_type Entity type (thirdparty, contact, user)
     * @param int $entity_id Entity ID
     * @param string $platform Platform (mailchimp, dolibarr)
     * @param string $external_id External platform ID
     * @param string $status Sync status
     * @param string $error_message Error message (optional)
     */
    private function updateSyncStatus($entity_type, $entity_id, $platform, $external_id, $status, $error_message = '')
    {
        // In real implementation, this would update the sync status table
        $this->logger->info(sprintf(
            'Sync status updated: %s %d -> %s (%s): %s',
            $entity_type,
            $entity_id,
            $platform,
            $external_id,
            $status
        ));
    }

    /**
     * Find Dolibarr contact by email
     * @param string $email Email address
     * @return array|null Contact data or null if not found
     */
    private function findDolibarrContactByEmail($email)
    {
        // Mock implementation - would query database in real version
        return null;
    }

    /**
     * Create new contact in Dolibarr
     * @param array $contact_data Contact data
     * @return int|false Contact ID or false on failure
     */
    private function createDolibarrContact($contact_data)
    {
        // Mock implementation - would create contact in database in real version
        return rand(1000, 9999);
    }

    /**
     * Get all Mailchimp lists
     * @return array Lists data
     */
    public function getMailchimpLists()
    {
        try {
            $response = $this->mailchimp->lists->getAllLists();
            return $response->lists ?? [];
        } catch (Exception $e) {
            $this->logger->error('Failed to get Mailchimp lists: ' . $e->getMessage());
            return [];
        }
    }
}

/**
 * Simple logger class for MokoDoliChimp
 */
class MokoDoliChimpLogger
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function info($message)
    {
        $this->log('INFO', $message);
    }

    public function error($message)
    {
        $this->log('ERROR', $message);
    }

    private function log($level, $message)
    {
        $timestamp = date('Y-m-d H:i:s');
        echo "[$timestamp] [$level] $message\n";
        // In real implementation, this would write to database or log file
    }
}