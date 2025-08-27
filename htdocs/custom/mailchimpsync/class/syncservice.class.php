<?php
/**
 * Synchronization Service Class
 * 
 * @file        syncservice.class.php
 * @ingroup     mailchimpsync
 * @brief       Main synchronization service
 * @author      Moko Consulting <hello@mokoconsulting.tech>
 * @copyright   2025 Moko Consulting
 * @license     GNU General Public License v3.0 or later
 * @warranty    This program comes with ABSOLUTELY NO WARRANTY. This is free software.
 */

require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';
dol_include_once('/mailchimpsync/class/mailchimpapi.class.php');
dol_include_once('/mailchimpsync/class/fieldmapping.class.php');
dol_include_once('/mailchimpsync/class/synchistory.class.php');

/**
 * Class to handle synchronization between Dolibarr and Mailchimp
 */
class SyncService
{
    /** @var DoliDB Database handler */
    public $db;
    
    /** @var MailchimpAPI Mailchimp API instance */
    private $mailchimp_api;
    
    /** @var FieldMapping Field mapping instance */
    private $field_mapping;
    
    /** @var SyncHistory Sync history instance */
    private $sync_history;
    
    /** @var array Error container */
    public $errors = array();
    
    /**
     * Constructor
     * 
     * @param DoliDB $db Database handler
     */
    public function __construct($db)
    {
        $this->db = $db;
        $this->mailchimp_api = new MailchimpAPI($db);
        $this->field_mapping = new FieldMapping($db);
        $this->sync_history = new SyncHistory($db);
    }
    
    /**
     * Run manual synchronization
     * 
     * @return array Result array
     */
    public function runManualSync()
    {
        global $conf;
        
        dol_syslog(get_class($this)."::runManualSync", LOG_DEBUG);
        
        $results = array(
            'thirdparty' => 0,
            'contact' => 0,
            'user' => 0,
            'errors' => 0
        );
        
        // Sync third parties
        $thirdparty_result = $this->syncThirdParties();
        $results['thirdparty'] = $thirdparty_result['synced'];
        $results['errors'] += $thirdparty_result['errors'];
        
        // Sync contacts
        $contact_result = $this->syncContacts();
        $results['contact'] = $contact_result['synced'];
        $results['errors'] += $contact_result['errors'];
        
        // Sync users
        $user_result = $this->syncUsers();
        $results['user'] = $user_result['synced'];
        $results['errors'] += $user_result['errors'];
        
        $total_synced = $results['thirdparty'] + $results['contact'] + $results['user'];
        
        if ($results['errors'] == 0) {
            return array('success' => true, 'message' => "Synced {$total_synced} records successfully", 'details' => $results);
        } else {
            return array('success' => false, 'error' => "Sync completed with {$results['errors']} errors. {$total_synced} records synced.", 'details' => $results);
        }
    }
    
    /**
     * Run full synchronization
     * 
     * @return array Result array
     */
    public function runFullSync()
    {
        global $conf;
        
        dol_syslog(get_class($this)."::runFullSync", LOG_DEBUG);
        
        // Run manual sync first
        $manual_result = $this->runManualSync();
        
        // Then sync from Mailchimp to Dolibarr
        $mailchimp_result = $this->syncFromMailchimp();
        
        $total_errors = ($manual_result['success'] ? 0 : 1) + ($mailchimp_result['success'] ? 0 : 1);
        
        if ($total_errors == 0) {
            return array('success' => true, 'message' => 'Full synchronization completed successfully');
        } else {
            return array('success' => false, 'error' => 'Full synchronization completed with errors');
        }
    }
    
    /**
     * Run scheduled synchronization (called by cron)
     * 
     * @return int 0 if success, negative if error
     */
    public function runScheduledSync()
    {
        global $conf;
        
        if (!$conf->global->MAILCHIMPSYNC_AUTO_SYNC) {
            dol_syslog(get_class($this)."::runScheduledSync Auto sync is disabled", LOG_INFO);
            return 0;
        }
        
        dol_syslog(get_class($this)."::runScheduledSync", LOG_DEBUG);
        
        $result = $this->runManualSync();
        
        return $result['success'] ? 0 : -1;
    }
    
    /**
     * Synchronize third parties to Mailchimp
     * 
     * @return array Result with synced count and errors
     */
    public function syncThirdParties()
    {
        global $conf;
        
        $synced = 0;
        $errors = 0;
        
        $list_id = $conf->global->MAILCHIMPSYNC_DEFAULT_LIST_ID;
        if (!$list_id) {
            $this->errors[] = "Default list ID not configured";
            return array('synced' => 0, 'errors' => 1);
        }
        
        // Get mappings for third parties
        $mappings = $this->field_mapping->getMappings('thirdparty');
        if (empty($mappings)) {
            dol_syslog(get_class($this)."::syncThirdParties No field mappings configured", LOG_WARNING);
            return array('synced' => 0, 'errors' => 0);
        }
        
        // Get third parties to sync
        $sql = "SELECT s.rowid, s.nom as name, s.email, s.phone, s.fax, s.address, s.zip, s.town, s.country_code";
        $sql .= ", s.date_creation, s.date_modification";
        $sql .= " FROM ".MAIN_DB_PREFIX."societe as s";
        $sql .= " WHERE s.entity IN (".getEntity('societe').")";
        $sql .= " AND s.email IS NOT NULL AND s.email != ''";
        $sql .= " AND s.status = 1"; // Active companies only
        
        $resql = $this->db->query($sql);
        if ($resql) {
            while ($obj = $this->db->fetch_object($resql)) {
                $societe = new Societe($this->db);
                $societe->fetch($obj->rowid);
                
                $result = $this->syncEntityToMailchimp('thirdparty', $societe, $list_id, $mappings);
                if ($result['success']) {
                    $synced++;
                } else {
                    $errors++;
                    $this->errors[] = "Third party {$obj->rowid}: " . $result['error'];
                }
            }
        } else {
            $this->errors[] = "Database error: " . $this->db->lasterror();
            $errors++;
        }
        
        return array('synced' => $synced, 'errors' => $errors);
    }
    
    /**
     * Synchronize contacts to Mailchimp
     * 
     * @return array Result with synced count and errors
     */
    public function syncContacts()
    {
        global $conf;
        
        $synced = 0;
        $errors = 0;
        
        $list_id = $conf->global->MAILCHIMPSYNC_DEFAULT_LIST_ID;
        if (!$list_id) {
            $this->errors[] = "Default list ID not configured";
            return array('synced' => 0, 'errors' => 1);
        }
        
        // Get mappings for contacts
        $mappings = $this->field_mapping->getMappings('contact');
        if (empty($mappings)) {
            dol_syslog(get_class($this)."::syncContacts No field mappings configured", LOG_WARNING);
            return array('synced' => 0, 'errors' => 0);
        }
        
        // Get contacts to sync
        $sql = "SELECT c.rowid, c.lastname, c.firstname, c.email, c.phone, c.phone_perso, c.phone_mobile";
        $sql .= ", c.address, c.zip, c.town, c.country_code, c.birthday";
        $sql .= ", c.datec, c.tms";
        $sql .= " FROM ".MAIN_DB_PREFIX."socpeople as c";
        $sql .= " WHERE c.entity IN (".getEntity('contact').")";
        $sql .= " AND c.email IS NOT NULL AND c.email != ''";
        $sql .= " AND c.statut = 1"; // Active contacts only
        
        $resql = $this->db->query($sql);
        if ($resql) {
            while ($obj = $this->db->fetch_object($resql)) {
                $contact = new Contact($this->db);
                $contact->fetch($obj->rowid);
                
                $result = $this->syncEntityToMailchimp('contact', $contact, $list_id, $mappings);
                if ($result['success']) {
                    $synced++;
                } else {
                    $errors++;
                    $this->errors[] = "Contact {$obj->rowid}: " . $result['error'];
                }
            }
        } else {
            $this->errors[] = "Database error: " . $this->db->lasterror();
            $errors++;
        }
        
        return array('synced' => $synced, 'errors' => $errors);
    }
    
    /**
     * Synchronize users to Mailchimp
     * 
     * @return array Result with synced count and errors
     */
    public function syncUsers()
    {
        global $conf;
        
        $synced = 0;
        $errors = 0;
        
        $list_id = $conf->global->MAILCHIMPSYNC_DEFAULT_LIST_ID;
        if (!$list_id) {
            $this->errors[] = "Default list ID not configured";
            return array('synced' => 0, 'errors' => 1);
        }
        
        // Get mappings for users
        $mappings = $this->field_mapping->getMappings('user');
        if (empty($mappings)) {
            dol_syslog(get_class($this)."::syncUsers No field mappings configured", LOG_WARNING);
            return array('synced' => 0, 'errors' => 0);
        }
        
        // Get users to sync
        $sql = "SELECT u.rowid, u.lastname, u.firstname, u.email, u.office_phone, u.user_mobile";
        $sql .= ", u.address, u.zip, u.town, u.country_code, u.birth";
        $sql .= ", u.datec, u.tms";
        $sql .= " FROM ".MAIN_DB_PREFIX."user as u";
        $sql .= " WHERE u.entity IN (".getEntity('user').")";
        $sql .= " AND u.email IS NOT NULL AND u.email != ''";
        $sql .= " AND u.statut = 1"; // Active users only
        
        $resql = $this->db->query($sql);
        if ($resql) {
            while ($obj = $this->db->fetch_object($resql)) {
                $user = new User($this->db);
                $user->fetch($obj->rowid);
                
                $result = $this->syncEntityToMailchimp('user', $user, $list_id, $mappings);
                if ($result['success']) {
                    $synced++;
                } else {
                    $errors++;
                    $this->errors[] = "User {$obj->rowid}: " . $result['error'];
                }
            }
        } else {
            $this->errors[] = "Database error: " . $this->db->lasterror();
            $errors++;
        }
        
        return array('synced' => $synced, 'errors' => $errors);
    }
    
    /**
     * Sync from Mailchimp to Dolibarr
     * 
     * @return array Result array
     */
    public function syncFromMailchimp()
    {
        global $conf;
        
        $list_id = $conf->global->MAILCHIMPSYNC_DEFAULT_LIST_ID;
        if (!$list_id) {
            return array('success' => false, 'error' => 'Default list ID not configured');
        }
        
        // Get all members from Mailchimp
        $members_result = $this->mailchimp_api->getListMembers($list_id, 1000);
        if (!$members_result['success']) {
            return array('success' => false, 'error' => $members_result['error']);
        }
        
        $synced = 0;
        $errors = 0;
        
        foreach ($members_result['members'] as $member) {
            // Try to find matching entity in Dolibarr by email
            $entity = $this->findEntityByEmail($member['email_address']);
            
            if ($entity) {
                $result = $this->syncEntityFromMailchimp($entity['type'], $entity['object'], $member);
                if ($result['success']) {
                    $synced++;
                } else {
                    $errors++;
                }
            }
        }
        
        if ($errors == 0) {
            return array('success' => true, 'message' => "Synced {$synced} records from Mailchimp");
        } else {
            return array('success' => false, 'error' => "Sync from Mailchimp completed with {$errors} errors. {$synced} records synced.");
        }
    }
    
    /**
     * Sync entity to Mailchimp
     * 
     * @param string $entity_type Entity type (thirdparty, contact, user)
     * @param object $entity Entity object
     * @param string $list_id Mailchimp list ID
     * @param array $mappings Field mappings
     * @return array Result array
     */
    private function syncEntityToMailchimp($entity_type, $entity, $list_id, $mappings)
    {
        // Prepare member data
        $member_data = array(
            'email_address' => $entity->email,
            'status' => 'subscribed',
            'merge_fields' => array()
        );
        
        // Apply field mappings
        foreach ($mappings as $dol_field => $mapping) {
            if (empty($mapping['mailchimp_field']) || 
                !in_array($mapping['sync_direction'], array('dolibarr_to_mailchimp', 'bidirectional'))) {
                continue;
            }
            
            $value = $this->getEntityFieldValue($entity, $dol_field);
            if ($value !== null) {
                if ($mapping['mailchimp_field'] == 'EMAIL') {
                    $member_data['email_address'] = $value;
                } else {
                    $member_data['merge_fields'][$mapping['mailchimp_field']] = $value;
                }
            }
        }
        
        // Add tags based on entity type
        $member_data['tags'] = array("dolibarr_{$entity_type}");
        
        // Sync to Mailchimp
        $result = $this->mailchimp_api->addOrUpdateMember($list_id, $member_data);
        
        // Log sync history
        $this->sync_history->logSync(
            $entity_type,
            $entity->id,
            'dolibarr_to_mailchimp',
            $result['success'] ? 'success' : 'error',
            $result['success'] ? 'Synced successfully' : $result['error']
        );
        
        return $result;
    }
    
    /**
     * Sync entity from Mailchimp
     * 
     * @param string $entity_type Entity type
     * @param object $entity Entity object
     * @param array $member Mailchimp member data
     * @return array Result array
     */
    private function syncEntityFromMailchimp($entity_type, $entity, $member)
    {
        $mappings = $this->field_mapping->getMappings($entity_type);
        $updated = false;
        
        // Apply reverse field mappings
        foreach ($mappings as $dol_field => $mapping) {
            if (empty($mapping['mailchimp_field']) || 
                !in_array($mapping['sync_direction'], array('mailchimp_to_dolibarr', 'bidirectional'))) {
                continue;
            }
            
            $mc_value = null;
            if ($mapping['mailchimp_field'] == 'EMAIL') {
                $mc_value = $member['email_address'];
            } elseif (isset($member['merge_fields'][$mapping['mailchimp_field']])) {
                $mc_value = $member['merge_fields'][$mapping['mailchimp_field']];
            }
            
            if ($mc_value !== null) {
                $current_value = $this->getEntityFieldValue($entity, $dol_field);
                if ($current_value != $mc_value) {
                    $this->setEntityFieldValue($entity, $dol_field, $mc_value);
                    $updated = true;
                }
            }
        }
        
        // Update entity if changes were made
        if ($updated) {
            $result = $entity->update($entity->id, $entity);
            if ($result > 0) {
                $this->sync_history->logSync(
                    $entity_type,
                    $entity->id,
                    'mailchimp_to_dolibarr',
                    'success',
                    'Updated from Mailchimp'
                );
                return array('success' => true);
            } else {
                $this->sync_history->logSync(
                    $entity_type,
                    $entity->id,
                    'mailchimp_to_dolibarr',
                    'error',
                    'Failed to update entity'
                );
                return array('success' => false, 'error' => 'Failed to update entity');
            }
        }
        
        return array('success' => true);
    }
    
    /**
     * Get entity field value
     * 
     * @param object $entity Entity object
     * @param string $field Field name
     * @return mixed Field value
     */
    private function getEntityFieldValue($entity, $field)
    {
        if (property_exists($entity, $field)) {
            return $entity->$field;
        }
        return null;
    }
    
    /**
     * Set entity field value
     * 
     * @param object $entity Entity object
     * @param string $field Field name
     * @param mixed $value Field value
     */
    private function setEntityFieldValue($entity, $field, $value)
    {
        if (property_exists($entity, $field)) {
            $entity->$field = $value;
        }
    }
    
    /**
     * Find entity by email address
     * 
     * @param string $email Email address
     * @return array|null Entity info or null if not found
     */
    private function findEntityByEmail($email)
    {
        // Check third parties
        $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."societe WHERE email = '".$this->db->escape($email)."' AND entity IN (".getEntity('societe').")";
        $resql = $this->db->query($sql);
        if ($resql && $this->db->num_rows($resql) > 0) {
            $obj = $this->db->fetch_object($resql);
            $societe = new Societe($this->db);
            $societe->fetch($obj->rowid);
            return array('type' => 'thirdparty', 'object' => $societe);
        }
        
        // Check contacts
        $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."socpeople WHERE email = '".$this->db->escape($email)."' AND entity IN (".getEntity('contact').")";
        $resql = $this->db->query($sql);
        if ($resql && $this->db->num_rows($resql) > 0) {
            $obj = $this->db->fetch_object($resql);
            $contact = new Contact($this->db);
            $contact->fetch($obj->rowid);
            return array('type' => 'contact', 'object' => $contact);
        }
        
        // Check users
        $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."user WHERE email = '".$this->db->escape($email)."' AND entity IN (".getEntity('user').")";
        $resql = $this->db->query($sql);
        if ($resql && $this->db->num_rows($resql) > 0) {
            $obj = $this->db->fetch_object($resql);
            $user = new User($this->db);
            $user->fetch($obj->rowid);
            return array('type' => 'user', 'object' => $user);
        }
        
        return null;
    }
}
?>
