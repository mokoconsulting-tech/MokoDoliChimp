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
 * \defgroup    mokodolichimp     Module MokoDoliChimp
 * \ingroup     mokodolichimp
 * \brief       MokoDoliChimp module descriptor.
 *
 * Module to synchronize Dolibarr contacts with Mailchimp email lists
 */

// Include Dolibarr base module class
// Note: This would typically include the Dolibarr framework
// For this implementation, we'll create a compatible base class

/**
 * Description and activation class for module MokoDoliChimp
 */
/**
 * Base class for Dolibarr modules - Mock implementation for standalone testing
 */
class DolibarrModules
{
    protected $db;
    public $numero;
    public $rights_class;
    public $family;
    public $module_position;
    public $familyinfo;
    public $name;
    public $description;
    public $descriptionlong;
    public $version;
    public $editor_name;
    public $editor_url;
    public $const_name;
    public $picto;
    public $module_parts;
    public $dirs;
    public $config_page_url;
    public $hidden;
    public $depends;
    public $requiredby;
    public $conflictwith;
    public $langfiles;
    public $phpmin;
    public $need_dolibarr_version;
    public $const;
    public $tabs;
    public $dictionaries;
    public $boxes;
    public $cronjobs;
    public $rights;
    public $menu;
    
    protected function _load_tables($path) { return 1; }
    protected function _init($sql, $options) { return 1; }
    protected function _remove($sql, $options) { return 1; }
}

class modMokoDoliChimp extends DolibarrModules
{
    /**
     * Constructor. Define names, constants, directories, boxes, permissions
     *
     * @param DoliDB $db Database handler
     */
    public function __construct($db)
    {
        global $langs, $conf;
        $this->db = $db;

        // Module unique ID (must be an integer)
        $this->numero = 500001;

        // Key text used to identify module (for permissions, menus, etc...)
        $this->rights_class = 'mokodolichimp';

        // Family can be 'base' (core modules) or 'external' (external modules)
        $this->family = "external";

        // Module position in the family on 2 digits ('01', '10', '20', ...)
        $this->module_position = '90';

        // Gives the possibility for the module, to provide his own family info and position of this family (Overwrite $this->family and $this->module_position)
        $this->familyinfo = array('external' => array('position' => '01', 'label' => $langs->trans("External")));
        
        // Module label (no space allowed), used if translation string 'ModuleMokoDoliChimpName' not found
        $this->name = preg_replace('/^mod/i', '', get_class($this));

        // Module description, used if translation string 'ModuleMokoDoliChimpDesc' not found
        $this->description = "Bidirectional synchronization between Dolibarr contacts and Mailchimp email lists";

        // Used only if file README.md and README-LL.md not found
        $this->descriptionlong = "MokoDoliChimp provides seamless integration between your Dolibarr ERP system and Mailchimp marketing platform. Features include automatic contact synchronization, webhook support for real-time updates, configurable field mapping, and comprehensive sync logging.";

        // Possible values for version are: 'development', 'experimental', 'dolibarr', 'dolibarr_deprecated' or a version string like 'x.y.z'
        $this->version = '1.0.0';

        // Publisher
        $this->editor_name = 'MokoDoliChimp Team';
        $this->editor_url = '';

        // Key used in llx_const table to save module status enabled/disabled
        $this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);

        // Name of image file used for this module
        $this->picto = 'mokodolichimp@mokodolichimp';

        // Define some features supported by module (triggers, login, substitutions, menus, css, etc...)
        $this->module_parts = array(
            'triggers' => 1,                                 // Set to 1 if module has its own trigger directory (core/triggers)
            'login' => 0,                                    // Set to 1 if module has its own login method file (core/login)
            'substitutions' => 0,                            // Set to 1 if module has its own substitution function file (core/substitutions)
            'menus' => 0,                                    // Set to 1 if module has its own menus handler directory (core/menus)
            'theme' => 0,                                    // Set to 1 if module has its own theme directory (theme)
            'tpl' => 0,                                      // Set to 1 if module overwrite template dir (core/tpl)
            'barcode' => 0,                                  // Set to 1 if module has its own barcode directory (core/modules/barcode)
            'models' => 0,                                   // Set to 1 if module has its own models directory (core/modules/xxx)
            'css' => array(),                                // Set to relative path of css file if module has its own css file
            'js' => array(),                                 // Set to relative path of js file if module has its own js file
            'hooks' => array(
                'contactcard',
                'thirdpartycard',
                'globalcard'
            ),
            'moduleforexternal' => 0,                        // Set to 1 if features of module are opened to external users
        );

        // Data directories to create when module is enabled
        $this->dirs = array("/mokodolichimp/temp");

        // Config pages
        $this->config_page_url = array("setup.php@mokodolichimp");

        // Dependencies
        $this->hidden = false; // A condition to hide module
        $this->depends = array(); // List of module class names as string that must be enabled if this module is enabled
        $this->requiredby = array(); // List of module class names as string to disable if this one is disabled
        $this->conflictwith = array(); // List of module class names as string this module is in conflict with
        $this->langfiles = array("mokodolichimp@mokodolichimp");
        $this->phpmin = array(7, 4); // Minimum version of PHP required by module
        $this->need_dolibarr_version = array(11, 0); // Minimum version of Dolibarr required by module

        // Constants
        $this->const = array();

        // Array to add new pages in new tabs
        $this->tabs = array();

        // Dictionaries
        $this->dictionaries = array();

        // Boxes/Widgets
        $this->boxes = array();

        // Cronjobs (format: array('label'=>'', 'jobtype'=>'', 'class'=>'', 'objectname'=>'', 'method'=>'', 'command'=>'', 'parameters'=>'', 'comment'=>'', 'frequency'=>'', 'unitfrequency'=>'', 'status'=>'', 'test'=>'', 'priority'=>''))
        $this->cronjobs = array(
            0 => array(
                'label' => 'MokoDoliChimpSyncJob',
                'jobtype' => 'method',
                'class' => '/mokodolichimp/class/mokodolichimp.class.php',
                'objectname' => 'MokoDoliChimp',
                'method' => 'doScheduledSync',
                'command' => '',
                'parameters' => '',
                'comment' => 'Scheduled sync between Dolibarr and Mailchimp',
                'frequency' => 2,
                'unitfrequency' => 3600,
                'status' => 0,
                'test' => '$conf->mokodolichimp->enabled',
                'priority' => 50,
            ),
        );

        // Menu entries for Dolibarr navigation
        $this->menu = array();
        
        // Main menu entry
        $r = 0;
        $this->menu[$r] = array(
            'fk_menu' => '',
            'type' => 'top',
            'titre' => 'MokoDoliChimp',
            'prefix' => '<i class="fa fa-exchange-alt paddingright"></i>',
            'mainmenu' => 'mokodolichimp',
            'leftmenu' => '',
            'url' => '/mokodolichimp/sync_dashboard.php',
            'langs' => 'mokodolichimp@mokodolichimp',
            'position' => 1000,
            'enabled' => '$conf->mokodolichimp->enabled',
            'perms' => '$user->rights->mokodolichimp->sync->read',
            'target' => '',
            'user' => 2,
        );
        $r++;

        // Submenu entries
        $this->menu[$r] = array(
            'fk_menu' => 'fk_mainmenu=mokodolichimp',
            'type' => 'left',
            'titre' => 'Dashboard',
            'mainmenu' => 'mokodolichimp',
            'leftmenu' => 'mokodolichimp_dashboard',
            'url' => '/mokodolichimp/sync_dashboard.php',
            'langs' => 'mokodolichimp@mokodolichimp',
            'position' => 1001,
            'enabled' => '$conf->mokodolichimp->enabled',
            'perms' => '$user->rights->mokodolichimp->sync->read',
            'target' => '',
            'user' => 2,
        );
        $r++;

        $this->menu[$r] = array(
            'fk_menu' => 'fk_mainmenu=mokodolichimp',
            'type' => 'left',
            'titre' => 'Field Mapping',
            'mainmenu' => 'mokodolichimp',
            'leftmenu' => 'mokodolichimp_mapping',
            'url' => '/mokodolichimp/field_mapping.php',
            'langs' => 'mokodolichimp@mokodolichimp',
            'position' => 1002,
            'enabled' => '$conf->mokodolichimp->enabled',
            'perms' => '$user->rights->mokodolichimp->sync->read',
            'target' => '',
            'user' => 2,
        );
        $r++;

        $this->menu[$r] = array(
            'fk_menu' => 'fk_mainmenu=mokodolichimp',
            'type' => 'left',
            'titre' => 'Tags & Segments',
            'mainmenu' => 'mokodolichimp',
            'leftmenu' => 'mokodolichimp_tags',
            'url' => '/mokodolichimp/tag_segment_config.php',
            'langs' => 'mokodolichimp@mokodolichimp',
            'position' => 1003,
            'enabled' => '$conf->mokodolichimp->enabled',
            'perms' => '$user->rights->mokodolichimp->sync->read',
            'target' => '',
            'user' => 2,
        );
        $r++;

        $this->menu[$r] = array(
            'fk_menu' => 'fk_mainmenu=mokodolichimp',
            'type' => 'left',
            'titre' => 'Configuration',
            'mainmenu' => 'mokodolichimp',
            'leftmenu' => 'mokodolichimp_config',
            'url' => '/mokodolichimp/admin/setup.php',
            'langs' => 'mokodolichimp@mokodolichimp',
            'position' => 1004,
            'enabled' => '$conf->mokodolichimp->enabled',
            'perms' => '$user->rights->mokodolichimp->sync->write',
            'target' => '',
            'user' => 2,
        );

        // Permissions provided by this module
        $this->rights = array();
        $r = 0;
        
        // Add permissions here
        $this->rights[$r][0] = $this->numero.sprintf("%02d", $r + 1); // Permission id (must not be already used)
        $this->rights[$r][1] = 'Read MokoDoliChimp sync data'; // Permission label
        $this->rights[$r][4] = 'sync';
        $this->rights[$r][5] = 'read'; // In php code, permission will be checked by test if ($user->rights->mokodolichimp->sync->read)
        $r++;
        
        $this->rights[$r][0] = $this->numero.sprintf("%02d", $r + 1);
        $this->rights[$r][1] = 'Create/Update MokoDoliChimp sync data';
        $this->rights[$r][4] = 'sync';
        $this->rights[$r][5] = 'write';
        $r++;
        
        $this->rights[$r][0] = $this->numero.sprintf("%02d", $r + 1);
        $this->rights[$r][1] = 'Delete MokoDoliChimp sync data';
        $this->rights[$r][4] = 'sync';
        $this->rights[$r][5] = 'delete';
        $r++;

        // Main menu entries to add
        $this->menu = array();
        $r = 0;

        // Add top menu entry
        $this->menu[$r++] = array(
            'fk_menu'=>'', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
            'type'=>'top', // This is a Top menu entry
            'titre'=>'MokoDoliChimp',
            'mainmenu'=>'mokodolichimp',
            'leftmenu'=>'',
            'url'=>'/mokodolichimp/mokodolichimpindex.php',
            'langs'=>'mokodolichimp@mokodolichimp', // Lang file to use (without .lang) by module
            'position'=>1000 + $r,
            'enabled'=>'$conf->mokodolichimp->enabled', // Define condition to show or hide menu entry
            'perms'=>'$user->rights->mokodolichimp->sync->read', // Use 'perms'=>'$user->rights->module->level1->level2' if you want your menu with a permission rules
            'target'=>'',
            'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
        );

        // Add left menu entries
        $this->menu[$r++] = array(
            'fk_menu'=>'fk_mainmenu=mokodolichimp',
            'type'=>'left',
            'titre'=>'Sync Dashboard',
            'mainmenu'=>'mokodolichimp',
            'leftmenu'=>'mokodolichimp_sync',
            'url'=>'/mokodolichimp/sync_dashboard.php',
            'langs'=>'mokodolichimp@mokodolichimp',
            'position'=>1000 + $r,
            'enabled'=>'$conf->mokodolichimp->enabled',
            'perms'=>'$user->rights->mokodolichimp->sync->read',
            'target'=>'',
            'user'=>2,
        );

        $this->menu[$r++] = array(
            'fk_menu'=>'fk_mainmenu=mokodolichimp',
            'type'=>'left',
            'titre'=>'Configuration',
            'mainmenu'=>'mokodolichimp',
            'leftmenu'=>'mokodolichimp_config',
            'url'=>'/mokodolichimp/admin/setup.php',
            'langs'=>'mokodolichimp@mokodolichimp',
            'position'=>1000 + $r,
            'enabled'=>'$conf->mokodolichimp->enabled',
            'perms'=>'$user->rights->mokodolichimp->sync->write',
            'target'=>'',
            'user'=>2,
        );
    }

    /**
     * Function called when module is enabled.
     * The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
     * It also creates data directories
     *
     * @param string $options Options when enabling module ('', 'noboxes')
     * @return int 1 if OK, 0 if KO
     */
    public function init($options = '')
    {
        global $conf, $langs;

        $result = $this->_load_tables('/mokodolichimp/sql/');
        if ($result < 0) {
            return -1; // Do not activate module if error 'not allowed' returned when loading module SQL queries (the _load_table run sql with run_sql with the error allowed parameter set to 'default')
        }

        // Create extrafields during init
        //include_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
        //$extrafields = new ExtraFields($this->db);
        //$result1=$extrafields->addExtraField('myattr1', "New Attr 1 label", 'boolean', 1,  3, 'thirdparty',   0, 0, '', '', 1, '', 0, 0, '', '', 'mokodolichimp@mokodolichimp', '$conf->mokodolichimp->enabled');
        //$result2=$extrafields->addExtraField('myattr2', "New Attr 2 label", 'varchar', 1, 10, 'contact',      0, 0, '', '', 1, '', 0, 0, '', '', 'mokodolichimp@mokodolichimp', '$conf->mokodolichimp->enabled');
        //$result3=$extrafields->addExtraField('myattr3', "New Attr 3 label", 'varchar', 1, 10, 'categorie',    0, 0, '', '', 1, '', 0, 0, '', '', 'mokodolichimp@mokodolichimp', '$conf->mokodolichimp->enabled');
        //$result4=$extrafields->addExtraField('myattr4', "New Attr 4 label", 'select',  1,  3, 'adherent',     0, 1, '', array('options'=>array('code1'=>'Val1','code2'=>'Val2','code3'=>'Val3')), 1,'', 0, 0, '', '', 'mokodolichimp@mokodolichimp', '$conf->mokodolichimp->enabled');
        //$result5=$extrafields->addExtraField('myattr5', "New Attr 5 label", 'text',    1, 10, 'user',         0, 0, '', '', 1, '', 0, 0, '', '', 'mokodolichimp@mokodolichimp', '$conf->mokodolichimp->enabled');

        // Permissions
        $this->remove($options);

        $sql = array();

        return $this->_init($sql, $options);
    }

    /**
     * Function called when module is disabled.
     * Remove from database constants, boxes and permissions from Dolibarr database.
     * Data directories are not deleted
     *
     * @param string $options Options when enabling module ('', 'noboxes')
     * @return int 1 if OK, 0 if KO
     */
    public function remove($options = '')
    {
        $sql = array();
        return $this->_remove($sql, $options);
    }
}