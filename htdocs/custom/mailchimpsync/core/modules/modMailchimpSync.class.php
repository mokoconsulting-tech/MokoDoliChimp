<?php
/**
 * Dolibarr Mailchimp Sync Module
 * 
 * @file        modMailchimpSync.class.php
 * @ingroup     mailchimpsync
 * @brief       Main module descriptor
 * @author      Moko Consulting <hello@mokoconsulting.tech>
 * @copyright   2025 Moko Consulting
 * @license     GNU General Public License v3.0 or later
 * @warranty    This program comes with ABSOLUTELY NO WARRANTY. This is free software.
 */

include_once DOL_DOCUMENT_ROOT.'/core/modules/DolibarrModules.class.php';

/**
 * Main module class for Mailchimp synchronization
 */
class modMailchimpSync extends DolibarrModules
{
    /**
     * Constructor
     * 
     * @param DoliDB $db Database handler
     */
    public function __construct($db)
    {
        global $langs, $conf;
        
        $this->db = $db;
        
        // Module info
        $this->numero = 999999; // Unique module number
        $this->rights_class = 'mailchimpsync';
        $this->family = "crm";
        $this->module_position = '50';
        $this->name = preg_replace('/^mod/i', '', get_class($this));
        $this->description = "Bidirectional synchronization between Dolibarr and Mailchimp";
        $this->descriptionlong = "Complete synchronization solution for third parties, contacts, and users with Mailchimp including field mapping and real-time sync capabilities";
        
        // Version
        $this->version = '1.0.0';
        $this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
        $this->picto = 'mailchimpsync@mailchimpsync';
        
        // Data directories
        $this->module_parts = array(
            'triggers' => 1,
            'css' => array('/mailchimpsync/css/mailchimpsync.css'),
            'js' => array('/mailchimpsync/js/mailchimpsync.js')
        );
        
        // Config pages
        $this->config_page_url = array("setup.php@mailchimpsync");
        
        // Dependencies
        $this->hidden = false;
        $this->depends = array("modSociete", "modUser");
        $this->requiredby = array();
        $this->conflictwith = array();
        $this->langfiles = array("mailchimpsync@mailchimpsync");
        $this->phpmin = array(7,4);
        $this->need_dolibarr_version = array(13,0);
        
        // Constants
        $this->const = array(
            1 => array('MAILCHIMPSYNC_API_KEY', 'chaine', '', 'Mailchimp API Key', 0),
            2 => array('MAILCHIMPSYNC_SERVER_PREFIX', 'chaine', '', 'Mailchimp Server Prefix', 0),
            3 => array('MAILCHIMPSYNC_DEFAULT_LIST_ID', 'chaine', '', 'Default Mailchimp List ID', 0),
            4 => array('MAILCHIMPSYNC_AUTO_SYNC', 'yesno', '1', 'Enable automatic synchronization', 0),
            5 => array('MAILCHIMPSYNC_SYNC_INTERVAL', 'chaine', '3600', 'Sync interval in seconds', 0),
        );
        
        // Boxes
        $this->boxes = array();
        
        // Cronjobs
        $this->cronjobs = array(
            0 => array(
                'label' => 'MailchimpSyncCron',
                'jobtype' => 'method',
                'class' => '/custom/mailchimpsync/class/syncservice.class.php',
                'objectname' => 'SyncService',
                'method' => 'runScheduledSync',
                'parameters' => '',
                'comment' => 'Scheduled Mailchimp synchronization',
                'frequency' => 1,
                'unitfrequency' => 3600,
                'status' => 0,
                'test' => '$conf->mailchimpsync->enabled'
            )
        );
        
        // Permissions
        $this->rights = array();
        $r = 0;
        
        $this->rights[$r][0] = $this->numero + $r;
        $this->rights[$r][1] = 'Read Mailchimp sync data';
        $this->rights[$r][4] = 'mailchimpsync';
        $this->rights[$r][5] = 'read';
        $r++;
        
        $this->rights[$r][0] = $this->numero + $r;
        $this->rights[$r][1] = 'Create/Update Mailchimp sync data';
        $this->rights[$r][4] = 'mailchimpsync';
        $this->rights[$r][5] = 'write';
        $r++;
        
        $this->rights[$r][0] = $this->numero + $r;
        $this->rights[$r][1] = 'Delete Mailchimp sync data';
        $this->rights[$r][4] = 'mailchimpsync';
        $this->rights[$r][5] = 'delete';
        $r++;
        
        // Menu entries
        $this->menu = array();
        $r = 0;
        
        $this->menu[$r++] = array(
            'fk_menu' => '',
            'type' => 'top',
            'titre' => 'MailchimpSync',
            'mainmenu' => 'mailchimpsync',
            'leftmenu' => '',
            'url' => '/custom/mailchimpsync/admin/dashboard.php',
            'langs' => 'mailchimpsync@mailchimpsync',
            'position' => 1000,
            'enabled' => '$conf->mailchimpsync->enabled',
            'perms' => '$user->rights->mailchimpsync->read',
            'target' => '',
            'user' => 2
        );
        
        $this->menu[$r++] = array(
            'fk_menu' => 'fk_mainmenu=mailchimpsync',
            'type' => 'left',
            'titre' => 'Dashboard',
            'mainmenu' => 'mailchimpsync',
            'leftmenu' => 'mailchimpsync_dashboard',
            'url' => '/custom/mailchimpsync/admin/dashboard.php',
            'langs' => 'mailchimpsync@mailchimpsync',
            'position' => 100,
            'enabled' => '$conf->mailchimpsync->enabled',
            'perms' => '$user->rights->mailchimpsync->read',
            'target' => '',
            'user' => 2
        );
        
        $this->menu[$r++] = array(
            'fk_menu' => 'fk_mainmenu=mailchimpsync',
            'type' => 'left',
            'titre' => 'Configuration',
            'mainmenu' => 'mailchimpsync',
            'leftmenu' => 'mailchimpsync_setup',
            'url' => '/custom/mailchimpsync/admin/setup.php',
            'langs' => 'mailchimpsync@mailchimpsync',
            'position' => 200,
            'enabled' => '$conf->mailchimpsync->enabled',
            'perms' => '$user->rights->mailchimpsync->write',
            'target' => '',
            'user' => 2
        );
        
        $this->menu[$r++] = array(
            'fk_menu' => 'fk_mainmenu=mailchimpsync',
            'type' => 'left',
            'titre' => 'Field Mapping',
            'mainmenu' => 'mailchimpsync',
            'leftmenu' => 'mailchimpsync_mapping',
            'url' => '/custom/mailchimpsync/admin/fieldmapping.php',
            'langs' => 'mailchimpsync@mailchimpsync',
            'position' => 300,
            'enabled' => '$conf->mailchimpsync->enabled',
            'perms' => '$user->rights->mailchimpsync->write',
            'target' => '',
            'user' => 2
        );
        
        $this->menu[$r++] = array(
            'fk_menu' => 'fk_mainmenu=mailchimpsync',
            'type' => 'left',
            'titre' => 'Sync History',
            'mainmenu' => 'mailchimpsync',
            'leftmenu' => 'mailchimpsync_history',
            'url' => '/custom/mailchimpsync/admin/synchistory.php',
            'langs' => 'mailchimpsync@mailchimpsync',
            'position' => 400,
            'enabled' => '$conf->mailchimpsync->enabled',
            'perms' => '$user->rights->mailchimpsync->read',
            'target' => '',
            'user' => 2
        );
    }
    
    /**
     * Function called when module is enabled
     * 
     * @param string $options Options
     * @return int 1 if OK, 0 if KO
     */
    public function init($options = '')
    {
        global $conf, $langs;
        
        $result = $this->_load_tables('/custom/mailchimpsync/sql/');
        if ($result < 0) return -1;
        
        return $this->_init($this->db, $options);
    }
    
    /**
     * Function called when module is disabled
     * 
     * @param string $options Options
     * @return int 1 if OK, 0 if KO
     */
    public function remove($options = '')
    {
        return $this->_remove($options);
    }
}
?>
