<?php
/**
 * Mailchimp Sync Trigger Interface
 * 
 * @file        interface_99_modMailchimpSync_MailchimpSyncTrigger.class.php
 * @ingroup     mailchimpsync
 * @brief       Trigger interface for real-time synchronization
 * @author      Moko Consulting <hello@mokoconsulting.tech>
 * @copyright   2025 Moko Consulting
 * @license     GNU General Public License v3.0 or later
 * @warranty    This program comes with ABSOLUTELY NO WARRANTY. This is free software.
 */

require_once DOL_DOCUMENT_ROOT.'/core/triggers/dolibarrtriggers.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/mailchimpsync/class/syncservice.class.php';

/**
 * Trigger class for Mailchimp synchronization
 */
class InterfaceMailchimpSyncTrigger extends DolibarrTriggers
{
    /** @var string Module name */
    public $name = 'InterfaceMailchimpSyncTrigger';
    
    /** @var string Module description */
    public $description = "Trigger for Mailchimp synchronization";
    
    /** @var string Module version */
    public $version = '1.0.0';
    
    /** @var string Module picture */
    public $picto = 'mailchimpsync@mailchimpsync';
    
    /**
     * Constructor
     * 
     * @param DoliDB $db Database handler
     */
    public function __construct($db)
    {
        $this->db = $db;
        
        $this->name = preg_replace('/^Interface/i', '', get_class($this));
        $this->family = "crm";
        $this->description = "Trigger for Mailchimp synchronization";
        $this->version = '1.0.0';
        $this->picto = 'mailchimpsync@mailchimpsync';
    }
    
    /**
     * Function called when a Dolibarr business event occurs
     * 
     * @param string $action Event action code
     * @param CommonObject $object The object the event concerns
     * @param User $user Current user
     * @param Translate $langs Language object
     * @param Conf $conf Configuration object
     * @return int 0 if OK, <0 if KO
     */
    public function runTrigger($action, $object, User $user, Translate $langs, Conf $conf)
    {
        global $conf;
        
        // Check if module is enabled
        if (empty($conf->mailchimpsync->enabled)) {
            return 0;
        }
        
        // Check if auto sync is enabled
        if (empty($conf->global->MAILCHIMPSYNC_AUTO_SYNC)) {
            return 0;
        }
        
        dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        
        // Handle different entity types
        $entity_type = null;
        $sync_required = false;
        
        switch ($action) {
            // Third party events
            case 'COMPANY_CREATE':
            case 'COMPANY_MODIFY':
            case 'COMPANY_DELETE':
                if (get_class($object) == 'Societe' && !empty($object->email)) {
                    $entity_type = 'thirdparty';
                    $sync_required = true;
                }
                break;
                
            // Contact events
            case 'CONTACT_CREATE':
            case 'CONTACT_MODIFY':
            case 'CONTACT_DELETE':
                if (get_class($object) == 'Contact' && !empty($object->email)) {
                    $entity_type = 'contact';
                    $sync_required = true;
                }
                break;
                
            // User events
            case 'USER_CREATE':
            case 'USER_MODIFY':
            case 'USER_DELETE':
                if (get_class($object) == 'User' && !empty($object->email)) {
                    $entity_type = 'user';
                    $sync_required = true;
                }
                break;
        }
        
        // Perform synchronization if required
        if ($sync_required && $entity_type) {
            $this->performAsyncSync($action, $entity_type, $object);
        }
        
        return 0;
    }
    
    /**
     * Perform asynchronous synchronization
     * 
     * @param string $action Trigger action
     * @param string $entity_type Entity type
     * @param CommonObject $object Entity object
     * @return int 0 if OK, <0 if KO
     */
    private function performAsyncSync($action, $entity_type, $object)
    {
        global $conf;
        
        try {
            $sync_service = new SyncService($this->db);
            $sync_history = new SyncHistory($this->db);
            
            // Log pending sync
            $sync_history->logSync(
                $entity_type,
                $object->id,
                'dolibarr_to_mailchimp',
                'pending',
                "Triggered by action: {$action}"
            );
            
            // Handle deletion separately
            if (strpos($action, 'DELETE') !== false) {
                $this->handleEntityDeletion($entity_type, $object);
                return 0;
            }
            
            // Get field mappings
            $field_mapping = new FieldMapping($this->db);
            $mappings = $field_mapping->getMappings($entity_type);
            
            if (empty($mappings)) {
                dol_syslog("No field mappings configured for {$entity_type}", LOG_WARNING);
                return 0;
            }
            
            // Perform sync
            $list_id = $conf->global->MAILCHIMPSYNC_DEFAULT_LIST_ID;
            if (!$list_id) {
                dol_syslog("Default list ID not configured", LOG_WARNING);
                return -1;
            }
            
            $mailchimp_api = new MailchimpAPI($this->db);
            
            // Prepare member data
            $member_data = array(
                'email_address' => $object->email,
                'status' => 'subscribed',
                'merge_fields' => array()
            );
            
            // Apply field mappings
            foreach ($mappings as $dol_field => $mapping) {
                if (empty($mapping['mailchimp_field']) || 
                    !in_array($mapping['sync_direction'], array('dolibarr_to_mailchimp', 'bidirectional'))) {
                    continue;
                }
                
                $value = $this->getEntityFieldValue($object, $dol_field);
                if ($value !== null) {
                    if ($mapping['mailchimp_field'] == 'EMAIL') {
                        $member_data['email_address'] = $value;
                    } else {
                        $member_data['merge_fields'][$mapping['mailchimp_field']] = $value;
                    }
                }
            }
            
            // Add tags based on entity type and action
            $member_data['tags'] = array("dolibarr_{$entity_type}");
            if (strpos($action, 'CREATE') !== false) {
                $member_data['tags'][] = 'dolibarr_new';
            } elseif (strpos($action, 'MODIFY') !== false) {
                $member_data['tags'][] = 'dolibarr_updated';
            }
            
            // Sync to Mailchimp
            $result = $mailchimp_api->addOrUpdateMember($list_id, $member_data);
            
            // Update sync history
            $sync_history->logSync(
                $entity_type,
                $object->id,
                'dolibarr_to_mailchimp',
                $result['success'] ? 'success' : 'error',
                $result['success'] ? 'Synced via trigger' : $result['error']
            );
            
            if (!$result['success']) {
                dol_syslog("Mailchimp sync failed for {$entity_type} {$object->id}: " . $result['error'], LOG_ERR);
                return -1;
            }
            
            dol_syslog("Successfully synced {$entity_type} {$object->id} to Mailchimp", LOG_INFO);
            return 0;
            
        } catch (Exception $e) {
            dol_syslog("Exception in Mailchimp sync trigger: " . $e->getMessage(), LOG_ERR);
            return -1;
        }
    }
    
    /**
     * Handle entity deletion
     * 
     * @param string $entity_type Entity type
     * @param CommonObject $object Entity object
     * @return int 0 if OK, <0 if KO
     */
    private function handleEntityDeletion($entity_type, $object)
    {
        global $conf;
        
        if (empty($object->email)) {
            return 0;
        }
        
        $list_id = $conf->global->MAILCHIMPSYNC_DEFAULT_LIST_ID;
        if (!$list_id) {
            return -1;
        }
        
        $mailchimp_api = new MailchimpAPI($this->db);
        $sync_history = new SyncHistory($this->db);
        
        // Delete from Mailchimp
        $result = $mailchimp_api->deleteMember($list_id, $object->email);
        
        // Log sync history
        $sync_history->logSync(
            $entity_type,
            $object->id,
            'dolibarr_to_mailchimp',
            $result['success'] ? 'success' : 'error',
            $result['success'] ? 'Deleted from Mailchimp' : $result['error']
        );
        
        return $result['success'] ? 0 : -1;
    }
    
    /**
     * Get entity field value
     * 
     * @param CommonObject $entity Entity object
     * @param string $field Field name
     * @return mixed Field value
     */
    private function getEntityFieldValue($entity, $field)
    {
        if (property_exists($entity, $field)) {
            $value = $entity->$field;
            
            // Handle date fields
            if (in_array($field, array('birthday', 'birth')) && !empty($value)) {
                if (is_numeric($value)) {
                    return date('Y-m-d', $value);
                }
            }
            
            return $value;
        }
        return null;
    }
}
?>
