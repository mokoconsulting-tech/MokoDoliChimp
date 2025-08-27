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
 * \file    class/sync_manager.class.php
 * \ingroup mokodolichimp
 * \brief   Sync manager class for handling manual and scheduled synchronization operations
 */

require_once 'mokodolichimp.class.php';

/**
 * Synchronization Manager for MokoDoliChimp
 * Handles both manual and automated sync operations
 */
class MokoDoliChimpSyncManager
{
    private $mokodolichimp;
    private $db;
    private $logger;
    
    /**
     * Constructor
     * @param object $db Database connection
     */
    public function __construct($db)
    {
        $this->db = $db;
        $this->mokodolichimp = new MokoDoliChimp($db);
        $this->logger = new MokoDoliChimpLogger($db);
    }
    
    /**
     * Manual sync trigger - called from admin interface
     * @param string $entity_type Type of entity to sync (thirdparty, contact, user, all)
     * @param string $list_id Mailchimp list ID (optional)
     * @return array Sync results
     */
    public function manualSync($entity_type = 'all', $list_id = null)
    {
        $this->logger->info("Manual sync initiated for entity type: $entity_type");
        
        $start_time = microtime(true);
        $results = [];
        
        try {
            switch ($entity_type) {
                case 'thirdparty':
                    $results['thirdparties'] = $this->mokodolichimp->syncThirdPartiesToMailchimp($list_id);
                    break;
                    
                case 'contact':
                    $results['contacts'] = $this->mokodolichimp->syncContactsToMailchimp($list_id);
                    break;
                    
                case 'user':
                    $results['users'] = $this->mokodolichimp->syncUsersToMailchimp($list_id);
                    break;
                    
                case 'all':
                default:
                    $results['thirdparties'] = $this->mokodolichimp->syncThirdPartiesToMailchimp($list_id);
                    $results['contacts'] = $this->mokodolichimp->syncContactsToMailchimp($list_id);
                    $results['users'] = $this->mokodolichimp->syncUsersToMailchimp($list_id);
                    break;
            }
            
            $execution_time = round(microtime(true) - $start_time, 2);
            $this->logger->info("Manual sync completed in {$execution_time} seconds");
            
            // Log sync operation to database
            $this->logSyncOperation('manual', $entity_type, $results, $execution_time);
            
            return [
                'status' => 'success',
                'execution_time' => $execution_time,
                'results' => $results,
                'message' => 'Manual sync completed successfully'
            ];
            
        } catch (Exception $e) {
            $this->logger->error("Manual sync failed: " . $e->getMessage());
            
            return [
                'status' => 'error',
                'message' => 'Manual sync failed: ' . $e->getMessage(),
                'results' => $results
            ];
        }
    }
    
    /**
     * Scheduled sync - called by Dolibarr cron/scheduled tasks
     * @return array Sync results
     */
    public function scheduledSync()
    {
        $this->logger->info("Scheduled sync initiated");
        
        $start_time = microtime(true);
        
        try {
            // Check if scheduled sync is enabled
            if (!$this->isScheduledSyncEnabled()) {
                return [
                    'status' => 'disabled',
                    'message' => 'Scheduled sync is disabled in configuration'
                ];
            }
            
            // Check sync frequency to avoid over-syncing
            if (!$this->shouldRunScheduledSync()) {
                return [
                    'status' => 'skipped',
                    'message' => 'Sync skipped - frequency limit not reached'
                ];
            }
            
            // Perform full sync
            $results = $this->mokodolichimp->doScheduledSync();
            
            $execution_time = round(microtime(true) - $start_time, 2);
            $this->logger->info("Scheduled sync completed in {$execution_time} seconds");
            
            // Log sync operation
            $this->logSyncOperation('scheduled', 'all', $results, $execution_time);
            
            // Update last sync timestamp
            $this->updateLastSyncTime();
            
            return [
                'status' => 'success',
                'execution_time' => $execution_time,
                'results' => $results,
                'message' => 'Scheduled sync completed successfully'
            ];
            
        } catch (Exception $e) {
            $this->logger->error("Scheduled sync failed: " . $e->getMessage());
            
            return [
                'status' => 'error',
                'message' => 'Scheduled sync failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Bidirectional sync - sync both ways
     * @param string $list_id Mailchimp list ID (optional)
     * @return array Sync results
     */
    public function bidirectionalSync($list_id = null)
    {
        $this->logger->info("Bidirectional sync initiated");
        
        $start_time = microtime(true);
        
        try {
            $results = [];
            
            // Step 1: Sync from Dolibarr to Mailchimp
            $this->logger->info("Step 1: Syncing Dolibarr → Mailchimp");
            $results['dolibarr_to_mailchimp'] = [
                'thirdparties' => $this->mokodolichimp->syncThirdPartiesToMailchimp($list_id),
                'contacts' => $this->mokodolichimp->syncContactsToMailchimp($list_id),
                'users' => $this->mokodolichimp->syncUsersToMailchimp($list_id)
            ];
            
            // Step 2: Sync from Mailchimp to Dolibarr
            $this->logger->info("Step 2: Syncing Mailchimp → Dolibarr");
            $results['mailchimp_to_dolibarr'] = $this->mokodolichimp->syncFromMailchimp($list_id);
            
            $execution_time = round(microtime(true) - $start_time, 2);
            $this->logger->info("Bidirectional sync completed in {$execution_time} seconds");
            
            // Log sync operation
            $this->logSyncOperation('bidirectional', 'all', $results, $execution_time);
            
            return [
                'status' => 'success',
                'execution_time' => $execution_time,
                'results' => $results,
                'message' => 'Bidirectional sync completed successfully'
            ];
            
        } catch (Exception $e) {
            $this->logger->error("Bidirectional sync failed: " . $e->getMessage());
            
            return [
                'status' => 'error',
                'message' => 'Bidirectional sync failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Real-time sync trigger - called when data changes in Dolibarr
     * @param string $entity_type Entity type that changed
     * @param int $entity_id Entity ID that changed
     * @param string $action Action performed (create, update, delete)
     * @return array Sync result
     */
    public function realTimeSync($entity_type, $entity_id, $action = 'update')
    {
        $this->logger->info("Real-time sync triggered: $entity_type $entity_id ($action)");
        
        try {
            // Check if real-time sync is enabled
            if (!$this->isRealTimeSyncEnabled()) {
                return [
                    'status' => 'disabled',
                    'message' => 'Real-time sync is disabled'
                ];
            }
            
            $result = null;
            
            switch ($entity_type) {
                case 'thirdparty':
                    $thirdparty = $this->getThirdPartyById($entity_id);
                    if ($thirdparty && $action !== 'delete') {
                        $result = $this->mokodolichimp->syncThirdPartyToMailchimp($thirdparty, null);
                    }
                    break;
                    
                case 'contact':
                    $contact = $this->getContactById($entity_id);
                    if ($contact && $action !== 'delete') {
                        $result = $this->mokodolichimp->syncContactToMailchimp($contact, null);
                    }
                    break;
                    
                case 'user':
                    $user = $this->getUserById($entity_id);
                    if ($user && $action !== 'delete') {
                        $result = $this->mokodolichimp->syncUserToMailchimp($user, null);
                    }
                    break;
            }
            
            if ($result) {
                $this->logger->info("Real-time sync completed for $entity_type $entity_id");
                return [
                    'status' => 'success',
                    'result' => $result,
                    'message' => 'Real-time sync completed'
                ];
            } else {
                return [
                    'status' => 'skipped',
                    'message' => 'Entity not found or deleted'
                ];
            }
            
        } catch (Exception $e) {
            $this->logger->error("Real-time sync failed: " . $e->getMessage());
            
            return [
                'status' => 'error',
                'message' => 'Real-time sync failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get sync status and statistics
     * @return array Sync status information
     */
    public function getSyncStatus()
    {
        return [
            'scheduled_sync_enabled' => $this->isScheduledSyncEnabled(),
            'real_time_sync_enabled' => $this->isRealTimeSyncEnabled(),
            'last_sync_time' => $this->getLastSyncTime(),
            'next_scheduled_sync' => $this->getNextScheduledSyncTime(),
            'sync_frequency' => $this->getSyncFrequency(),
            'total_syncs_today' => $this->getTotalSyncsToday(),
            'last_sync_results' => $this->getLastSyncResults()
        ];
    }
    
    // ========================================
    // PRIVATE HELPER METHODS
    // ========================================
    
    /**
     * Check if scheduled sync is enabled
     * @return bool
     */
    private function isScheduledSyncEnabled()
    {
        // In real implementation, check Dolibarr configuration
        return true; // Mock return
    }
    
    /**
     * Check if real-time sync is enabled  
     * @return bool
     */
    private function isRealTimeSyncEnabled()
    {
        // In real implementation, check Dolibarr configuration
        return true; // Mock return
    }
    
    /**
     * Check if scheduled sync should run based on frequency
     * @return bool
     */
    private function shouldRunScheduledSync()
    {
        $last_sync = $this->getLastSyncTime();
        $frequency = $this->getSyncFrequency(); // in seconds
        
        if (!$last_sync) {
            return true; // First run
        }
        
        return (time() - strtotime($last_sync)) >= $frequency;
    }
    
    /**
     * Get last sync time
     * @return string|null
     */
    private function getLastSyncTime()
    {
        // In real implementation, query database
        return date('Y-m-d H:i:s', time() - 3600); // Mock: 1 hour ago
    }
    
    /**
     * Update last sync timestamp
     */
    private function updateLastSyncTime()
    {
        // In real implementation, update database
        $this->logger->info("Last sync time updated to: " . date('Y-m-d H:i:s'));
    }
    
    /**
     * Get sync frequency in seconds
     * @return int
     */
    private function getSyncFrequency()
    {
        // In real implementation, get from configuration
        return 3600; // 1 hour default
    }
    
    /**
     * Get next scheduled sync time
     * @return string
     */
    private function getNextScheduledSyncTime()
    {
        $last_sync = $this->getLastSyncTime();
        $frequency = $this->getSyncFrequency();
        
        if ($last_sync) {
            return date('Y-m-d H:i:s', strtotime($last_sync) + $frequency);
        }
        
        return date('Y-m-d H:i:s', time() + $frequency);
    }
    
    /**
     * Get total syncs performed today
     * @return int
     */
    private function getTotalSyncsToday()
    {
        // In real implementation, query sync log table
        return 5; // Mock return
    }
    
    /**
     * Get last sync results
     * @return array|null
     */
    private function getLastSyncResults()
    {
        // In real implementation, query sync log table
        return [
            'sync_type' => 'scheduled',
            'entity_type' => 'all',
            'total_success' => 25,
            'total_errors' => 2,
            'execution_time' => 45.5
        ];
    }
    
    /**
     * Log sync operation to database
     * @param string $sync_type Type of sync (manual, scheduled, bidirectional, realtime)
     * @param string $entity_type Entity type synced
     * @param array $results Sync results
     * @param float $execution_time Execution time in seconds
     */
    private function logSyncOperation($sync_type, $entity_type, $results, $execution_time)
    {
        // In real implementation, insert into sync log table
        $this->logger->info("Sync operation logged: $sync_type sync of $entity_type completed in {$execution_time}s");
        
        // Calculate totals
        $total_success = 0;
        $total_errors = 0;
        
        if (is_array($results)) {
            foreach ($results as $entity_results) {
                if (is_array($entity_results)) {
                    $total_success += $entity_results['success'] ?? 0;
                    $total_errors += $entity_results['errors'] ?? 0;
                }
            }
        }
        
        $this->logger->info("Totals: $total_success successful, $total_errors errors");
    }
    
    /**
     * Get third party by ID
     * @param int $id Third party ID
     * @return array|null
     */
    private function getThirdPartyById($id)
    {
        // In real implementation, query database
        // For mock, return data from the mock method
        $thirdparties = $this->mokodolichimp->getThirdParties();
        foreach ($thirdparties as $tp) {
            if ($tp['id'] == $id) {
                return $tp;
            }
        }
        return null;
    }
    
    /**
     * Get contact by ID
     * @param int $id Contact ID
     * @return array|null
     */
    private function getContactById($id)
    {
        // In real implementation, query database
        // For mock, return data from the mock method
        $contacts = $this->mokodolichimp->getContacts();
        foreach ($contacts as $contact) {
            if ($contact['id'] == $id) {
                return $contact;
            }
        }
        return null;
    }
    
    /**
     * Get user by ID
     * @param int $id User ID
     * @return array|null
     */
    private function getUserById($id)
    {
        // In real implementation, query database
        // For mock, return data from the mock method
        $users = $this->mokodolichimp->getUsers();
        foreach ($users as $user) {
            if ($user['id'] == $id) {
                return $user;
            }
        }
        return null;
    }
}