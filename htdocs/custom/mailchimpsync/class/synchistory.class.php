<?php
/**
 * Sync History Class
 * 
 * @file        synchistory.class.php
 * @ingroup     mailchimpsync
 * @brief       Synchronization history management class
 * @author      Moko Consulting <hello@mokoconsulting.tech>
 * @copyright   2025 Moko Consulting
 * @license     GNU General Public License v3.0 or later
 * @warranty    This program comes with ABSOLUTELY NO WARRANTY. This is free software.
 */

/**
 * Class to handle synchronization history and logging
 */
class SyncHistory
{
    /** @var DoliDB Database handler */
    public $db;
    
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
    }
    
    /**
     * Log a synchronization event
     * 
     * @param string $entity_type Entity type (thirdparty, contact, user)
     * @param int $entity_id Entity ID
     * @param string $sync_direction Sync direction
     * @param string $status Status (success, error, pending)
     * @param string $message Optional message
     * @return int >0 if success, <=0 if error
     */
    public function logSync($entity_type, $entity_id, $sync_direction, $status, $message = '')
    {
        global $user;
        
        $sql = "INSERT INTO ".MAIN_DB_PREFIX."mailchimp_sync_history";
        $sql .= " (entity_type, entity_id, sync_direction, status, message, date_sync, fk_user_creat)";
        $sql .= " VALUES (";
        $sql .= "'".$this->db->escape($entity_type)."'";
        $sql .= ", ".((int) $entity_id);
        $sql .= ", '".$this->db->escape($sync_direction)."'";
        $sql .= ", '".$this->db->escape($status)."'";
        $sql .= ", '".$this->db->escape($message)."'";
        $sql .= ", '".$this->db->idate(dol_now())."'";
        $sql .= ", ".((int) (is_object($user) ? $user->id : 0));
        $sql .= ")";
        
        $resql = $this->db->query($sql);
        if ($resql) {
            return $this->db->lastinsertid(MAIN_DB_PREFIX."mailchimp_sync_history");
        } else {
            $this->errors[] = "Error logging sync: " . $this->db->lasterror();
            return -1;
        }
    }
    
    /**
     * Get recent sync statistics
     * 
     * @param int $hours Number of hours to look back
     * @return array Statistics
     */
    public function getRecentSyncStats($hours = 24)
    {
        $since = dol_now() - ($hours * 3600);
        
        $stats = array(
            'total' => 0,
            'successful' => 0,
            'failed' => 0,
            'last_success' => null,
            'last_failure' => null
        );
        
        // Get total counts
        $sql = "SELECT status, COUNT(*) as count, MAX(date_sync) as last_sync";
        $sql .= " FROM ".MAIN_DB_PREFIX."mailchimp_sync_history";
        $sql .= " WHERE date_sync >= '".$this->db->idate($since)."'";
        $sql .= " GROUP BY status";
        
        $resql = $this->db->query($sql);
        if ($resql) {
            while ($obj = $this->db->fetch_object($resql)) {
                $stats['total'] += $obj->count;
                
                if ($obj->status == 'success') {
                    $stats['successful'] = $obj->count;
                    $stats['last_success'] = $this->db->jdate($obj->last_sync);
                } elseif ($obj->status == 'error') {
                    $stats['failed'] = $obj->count;
                    $stats['last_failure'] = $this->db->jdate($obj->last_sync);
                }
            }
        }
        
        return $stats;
    }
    
    /**
     * Get pending sync counts by entity type
     * 
     * @return array Pending counts
     */
    public function getPendingSyncCounts()
    {
        $counts = array();
        
        $sql = "SELECT entity_type, COUNT(*) as count";
        $sql .= " FROM ".MAIN_DB_PREFIX."mailchimp_sync_history";
        $sql .= " WHERE status = 'pending'";
        $sql .= " GROUP BY entity_type";
        
        $resql = $this->db->query($sql);
        if ($resql) {
            while ($obj = $this->db->fetch_object($resql)) {
                $counts[$obj->entity_type] = $obj->count;
            }
        }
        
        return $counts;
    }
    
    /**
     * Get last sync for entity type
     * 
     * @param string $entity_type Entity type
     * @return array|null Last sync data
     */
    public function getLastSyncForType($entity_type)
    {
        $sql = "SELECT status, date_sync, message";
        $sql .= " FROM ".MAIN_DB_PREFIX."mailchimp_sync_history";
        $sql .= " WHERE entity_type = '".$this->db->escape($entity_type)."'";
        $sql .= " ORDER BY date_sync DESC";
        $sql .= " LIMIT 1";
        
        $resql = $this->db->query($sql);
        if ($resql && $this->db->num_rows($resql) > 0) {
            $obj = $this->db->fetch_object($resql);
            return array(
                'status' => $obj->status,
                'date_sync' => $this->db->jdate($obj->date_sync),
                'message' => $obj->message
            );
        }
        
        return null;
    }
    
    /**
     * Get sync history for an entity
     * 
     * @param string $entity_type Entity type
     * @param int $entity_id Entity ID
     * @param int $limit Number of records to return
     * @return array Sync history
     */
    public function getEntitySyncHistory($entity_type, $entity_id, $limit = 10)
    {
        $history = array();
        
        $sql = "SELECT sync_direction, status, message, date_sync";
        $sql .= " FROM ".MAIN_DB_PREFIX."mailchimp_sync_history";
        $sql .= " WHERE entity_type = '".$this->db->escape($entity_type)."'";
        $sql .= " AND entity_id = ".((int) $entity_id);
        $sql .= " ORDER BY date_sync DESC";
        $sql .= " LIMIT ".((int) $limit);
        
        $resql = $this->db->query($sql);
        if ($resql) {
            while ($obj = $this->db->fetch_object($resql)) {
                $history[] = array(
                    'sync_direction' => $obj->sync_direction,
                    'status' => $obj->status,
                    'message' => $obj->message,
                    'date_sync' => $this->db->jdate($obj->date_sync)
                );
            }
        }
        
        return $history;
    }
    
    /**
     * Clear old sync history
     * 
     * @param int $days Number of days to keep
     * @return int Number of deleted records, or <0 if error
     */
    public function clearOldHistory($days)
    {
        $cutoff_date = dol_now() - ($days * 24 * 3600);
        
        $sql = "DELETE FROM ".MAIN_DB_PREFIX."mailchimp_sync_history";
        $sql .= " WHERE date_sync < '".$this->db->idate($cutoff_date)."'";
        
        $resql = $this->db->query($sql);
        if ($resql) {
            return $this->db->affected_rows($resql);
        } else {
            $this->errors[] = "Error clearing history: " . $this->db->lasterror();
            return -1;
        }
    }
    
    /**
     * Mark pending syncs as failed after timeout
     * 
     * @param int $timeout_minutes Timeout in minutes
     * @return int Number of updated records, or <0 if error
     */
    public function timeoutPendingSyncs($timeout_minutes = 30)
    {
        $timeout_date = dol_now() - ($timeout_minutes * 60);
        
        $sql = "UPDATE ".MAIN_DB_PREFIX."mailchimp_sync_history";
        $sql .= " SET status = 'error', message = 'Sync timed out'";
        $sql .= " WHERE status = 'pending'";
        $sql .= " AND date_sync < '".$this->db->idate($timeout_date)."'";
        
        $resql = $this->db->query($sql);
        if ($resql) {
            return $this->db->affected_rows($resql);
        } else {
            $this->errors[] = "Error timing out pending syncs: " . $this->db->lasterror();
            return -1;
        }
    }
}
?>
