<?php
/**
 * Field Mapping Class
 * 
 * @file        fieldmapping.class.php
 * @ingroup     mailchimpsync
 * @brief       Field mapping management class
 * @author      Moko Consulting <hello@mokoconsulting.tech>
 * @copyright   2025 Moko Consulting
 * @license     GNU General Public License v3.0 or later
 * @warranty    This program comes with ABSOLUTELY NO WARRANTY. This is free software.
 */

/**
 * Class to handle field mappings between Dolibarr and Mailchimp
 */
class FieldMapping
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
     * Get field mappings for an entity type
     * 
     * @param string $entity_type Entity type (thirdparty, contact, user)
     * @return array Field mappings
     */
    public function getMappings($entity_type)
    {
        $mappings = array();
        
        $sql = "SELECT dolibarr_field, mailchimp_field, sync_direction, is_required";
        $sql .= " FROM ".MAIN_DB_PREFIX."mailchimp_field_mapping";
        $sql .= " WHERE entity_type = '".$this->db->escape($entity_type)."'";
        $sql .= " AND active = 1";
        
        $resql = $this->db->query($sql);
        if ($resql) {
            while ($obj = $this->db->fetch_object($resql)) {
                $mappings[$obj->dolibarr_field] = array(
                    'mailchimp_field' => $obj->mailchimp_field,
                    'sync_direction' => $obj->sync_direction,
                    'is_required' => $obj->is_required
                );
            }
        }
        
        return $mappings;
    }
    
    /**
     * Save field mappings for an entity type
     * 
     * @param string $entity_type Entity type
     * @param array $mappings Field mappings
     * @return int >0 if success, <=0 if error
     */
    public function saveMappings($entity_type, $mappings)
    {
        $this->db->begin();
        
        // First delete existing mappings
        $sql = "DELETE FROM ".MAIN_DB_PREFIX."mailchimp_field_mapping";
        $sql .= " WHERE entity_type = '".$this->db->escape($entity_type)."'";
        
        $resql = $this->db->query($sql);
        if (!$resql) {
            $this->db->rollback();
            $this->errors[] = "Error deleting existing mappings: " . $this->db->lasterror();
            return -1;
        }
        
        // Insert new mappings
        foreach ($mappings as $dol_field => $mapping) {
            if (empty($mapping['mailchimp_field']) || empty($mapping['sync_direction'])) {
                continue; // Skip empty mappings
            }
            
            $sql = "INSERT INTO ".MAIN_DB_PREFIX."mailchimp_field_mapping";
            $sql .= " (entity_type, dolibarr_field, mailchimp_field, sync_direction, is_required, active, date_creation)";
            $sql .= " VALUES (";
            $sql .= "'".$this->db->escape($entity_type)."'";
            $sql .= ", '".$this->db->escape($dol_field)."'";
            $sql .= ", '".$this->db->escape($mapping['mailchimp_field'])."'";
            $sql .= ", '".$this->db->escape($mapping['sync_direction'])."'";
            $sql .= ", 0"; // is_required will be determined by field definition
            $sql .= ", 1"; // active
            $sql .= ", '".$this->db->idate(dol_now())."'";
            $sql .= ")";
            
            $resql = $this->db->query($sql);
            if (!$resql) {
                $this->db->rollback();
                $this->errors[] = "Error inserting mapping for {$dol_field}: " . $this->db->lasterror();
                return -1;
            }
        }
        
        $this->db->commit();
        return 1;
    }
    
    /**
     * Reset mappings for an entity type
     * 
     * @param string $entity_type Entity type
     * @return int >=0 if success, <0 if error
     */
    public function resetMappings($entity_type)
    {
        $sql = "DELETE FROM ".MAIN_DB_PREFIX."mailchimp_field_mapping";
        $sql .= " WHERE entity_type = '".$this->db->escape($entity_type)."'";
        
        $resql = $this->db->query($sql);
        if ($resql) {
            return $this->db->affected_rows($resql);
        } else {
            $this->errors[] = "Error resetting mappings: " . $this->db->lasterror();
            return -1;
        }
    }
    
    /**
     * Get available Dolibarr fields for an entity type
     * 
     * @param string $entity_type Entity type
     * @return array Available fields with labels and metadata
     */
    public function getDolibarrFields($entity_type)
    {
        $fields = array();
        
        switch ($entity_type) {
            case 'thirdparty':
                $fields = array(
                    'email' => array('label' => 'Email', 'required' => true, 'help' => 'Company email address'),
                    'nom' => array('label' => 'CompanyName', 'required' => false, 'help' => 'Company name'),
                    'phone' => array('label' => 'Phone', 'required' => false, 'help' => 'Company phone number'),
                    'fax' => array('label' => 'Fax', 'required' => false, 'help' => 'Company fax number'),
                    'address' => array('label' => 'Address', 'required' => false, 'help' => 'Company address'),
                    'zip' => array('label' => 'Zip', 'required' => false, 'help' => 'Postal code'),
                    'town' => array('label' => 'Town', 'required' => false, 'help' => 'City'),
                    'country_code' => array('label' => 'Country', 'required' => false, 'help' => 'Country code'),
                    'client' => array('label' => 'Customer', 'required' => false, 'help' => 'Customer status'),
                    'fournisseur' => array('label' => 'Supplier', 'required' => false, 'help' => 'Supplier status')
                );
                break;
                
            case 'contact':
                $fields = array(
                    'email' => array('label' => 'Email', 'required' => true, 'help' => 'Contact email address'),
                    'firstname' => array('label' => 'Firstname', 'required' => false, 'help' => 'First name'),
                    'lastname' => array('label' => 'Lastname', 'required' => false, 'help' => 'Last name'),
                    'phone' => array('label' => 'Phone', 'required' => false, 'help' => 'Professional phone'),
                    'phone_perso' => array('label' => 'PhonePerso', 'required' => false, 'help' => 'Personal phone'),
                    'phone_mobile' => array('label' => 'PhoneMobile', 'required' => false, 'help' => 'Mobile phone'),
                    'address' => array('label' => 'Address', 'required' => false, 'help' => 'Contact address'),
                    'zip' => array('label' => 'Zip', 'required' => false, 'help' => 'Postal code'),
                    'town' => array('label' => 'Town', 'required' => false, 'help' => 'City'),
                    'country_code' => array('label' => 'Country', 'required' => false, 'help' => 'Country code'),
                    'birthday' => array('label' => 'Birthday', 'required' => false, 'help' => 'Date of birth'),
                    'poste' => array('label' => 'PostOrFunction', 'required' => false, 'help' => 'Job title')
                );
                break;
                
            case 'user':
                $fields = array(
                    'email' => array('label' => 'Email', 'required' => true, 'help' => 'User email address'),
                    'firstname' => array('label' => 'Firstname', 'required' => false, 'help' => 'First name'),
                    'lastname' => array('label' => 'Lastname', 'required' => false, 'help' => 'Last name'),
                    'office_phone' => array('label' => 'PhoneOffice', 'required' => false, 'help' => 'Office phone'),
                    'user_mobile' => array('label' => 'PhoneMobile', 'required' => false, 'help' => 'Mobile phone'),
                    'address' => array('label' => 'Address', 'required' => false, 'help' => 'User address'),
                    'zip' => array('label' => 'Zip', 'required' => false, 'help' => 'Postal code'),
                    'town' => array('label' => 'Town', 'required' => false, 'help' => 'City'),
                    'country_code' => array('label' => 'Country', 'required' => false, 'help' => 'Country code'),
                    'birth' => array('label' => 'Birthday', 'required' => false, 'help' => 'Date of birth'),
                    'job' => array('label' => 'PostOrFunction', 'required' => false, 'help' => 'Job title')
                );
                break;
        }
        
        return $fields;
    }
    
    /**
     * Get default field mappings for an entity type
     * 
     * @param string $entity_type Entity type
     * @return array Default mappings
     */
    public function getDefaultMappings($entity_type)
    {
        $defaults = array();
        
        switch ($entity_type) {
            case 'thirdparty':
                $defaults = array(
                    'email' => array('mailchimp_field' => 'EMAIL', 'sync_direction' => 'bidirectional'),
                    'nom' => array('mailchimp_field' => 'COMPANY', 'sync_direction' => 'dolibarr_to_mailchimp'),
                    'phone' => array('mailchimp_field' => 'PHONE', 'sync_direction' => 'dolibarr_to_mailchimp')
                );
                break;
                
            case 'contact':
                $defaults = array(
                    'email' => array('mailchimp_field' => 'EMAIL', 'sync_direction' => 'bidirectional'),
                    'firstname' => array('mailchimp_field' => 'FNAME', 'sync_direction' => 'bidirectional'),
                    'lastname' => array('mailchimp_field' => 'LNAME', 'sync_direction' => 'bidirectional'),
                    'phone' => array('mailchimp_field' => 'PHONE', 'sync_direction' => 'dolibarr_to_mailchimp'),
                    'birthday' => array('mailchimp_field' => 'BIRTHDAY', 'sync_direction' => 'dolibarr_to_mailchimp')
                );
                break;
                
            case 'user':
                $defaults = array(
                    'email' => array('mailchimp_field' => 'EMAIL', 'sync_direction' => 'bidirectional'),
                    'firstname' => array('mailchimp_field' => 'FNAME', 'sync_direction' => 'bidirectional'),
                    'lastname' => array('mailchimp_field' => 'LNAME', 'sync_direction' => 'bidirectional'),
                    'office_phone' => array('mailchimp_field' => 'PHONE', 'sync_direction' => 'dolibarr_to_mailchimp')
                );
                break;
        }
        
        return $defaults;
    }
    
    /**
     * Create default mappings for an entity type
     * 
     * @param string $entity_type Entity type
     * @return int >0 if success, <=0 if error
     */
    public function createDefaultMappings($entity_type)
    {
        $defaults = $this->getDefaultMappings($entity_type);
        return $this->saveMappings($entity_type, $defaults);
    }
}
?>
