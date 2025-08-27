<?php
/**
 * Minimal Dolibarr Main Include File for Demo
 * 
 * @file        main.inc.php
 * @ingroup     mailchimpsync
 * @brief       Main include file for standalone demo
 * @author      Moko Consulting <hello@mokoconsulting.tech>
 * @copyright   2025 Moko Consulting
 * @license     GNU General Public License v3.0 or later
 * @warranty    This program comes with ABSOLUTELY NO WARRANTY. This is free software.
 */

// Define constants for standalone demo
define('DOL_DOCUMENT_ROOT', __DIR__);
define('DOL_VERSION', '17.0.0');
define('DOL_APPLICATION_TITLE', 'Dolibarr ERP/CRM');
define('DOL_DATA_ROOT', __DIR__.'/data');
define('MAIN_DB_PREFIX', 'llx_');

// Initialize global variables
global $conf, $db, $user, $langs;

// Initialize minimal configuration object
$conf = new stdClass();
$conf->entity = 1;
$conf->global = new stdClass();
$conf->global->MAIN_APPLICATION_TITLE = 'Dolibarr ERP/CRM';
$conf->global->MAILCHIMPSYNC_API_KEY = '';
$conf->global->MAILCHIMPSYNC_SERVER_PREFIX = '';
$conf->global->MAILCHIMPSYNC_DEFAULT_LIST = '';
$conf->global->MAILCHIMPSYNC_AUTO_SYNC = 0;
$conf->liste_limit = 25;

// Define Database class with essential methods
class DoliDB {
    public $connected = true;
    public $type = 'mysql';
    
    public function query($sql) {
        // Return dummy result for demo
        return new stdClass();
    }
    
    public function fetch_object($result) {
        // Return dummy data for demo
        $obj = new stdClass();
        $obj->total_syncs = rand(50, 200);
        $obj->successful_syncs = rand(40, 180);
        $obj->failed_syncs = rand(0, 20);
        $obj->last_success = date('Y-m-d H:i:s', time() - rand(0, 86400));
        $obj->last_failure = date('Y-m-d H:i:s', time() - rand(0, 86400));
        return $obj;
    }
    
    public function num_rows($result) {
        return rand(0, 10);
    }
    
    public function fetch_array($result) {
        return array();
    }
    
    public function idate($timestamp) {
        return date('Y-m-d H:i:s', $timestamp);
    }
    
    public function jdate($timestamp) {
        return is_numeric($timestamp) ? $timestamp : strtotime($timestamp);
    }
    
    public function lastinsertid($table = '') {
        return rand(1, 1000);
    }
    
    public function lasterror() {
        return 'Demo error message';
    }
}

// Initialize database object
$db = new DoliDB();

// Initialize minimal user object
$user = new stdClass();
$user->admin = true;
$user->rights = new stdClass();
$user->rights->mailchimpsync = new stdClass();
$user->rights->mailchimpsync->read = 1;
$user->rights->mailchimpsync->write = 1;

// Define Language class
class Translate {
    public function trans($key) {
        return $key;
    }
    
    public function load($lang) {
        return true;
    }
    
    public function loadLangs($langs) {
        return true;
    }
}

// Initialize language object
$langs = new Translate();

// Define essential functions
if (!function_exists('GETPOST')) {
    function GETPOST($paramname, $check = 'alpha', $method = 0, $filter = null, $options = null, $noreplace = 0) {
        $value = '';
        if ($method == 0 || $method == 3) {
            if (isset($_GET[$paramname])) $value = $_GET[$paramname];
        }
        if ($method == 0 || $method == 2) {
            if (isset($_POST[$paramname])) $value = $_POST[$paramname];
        }
        return $value;
    }
}

if (!function_exists('restrictedArea')) {
    function restrictedArea($user, $features, $objectid = 0, $tableandshare = '', $feature2 = '', $dbt_keyfield = 'fk_soc', $dbt_select = 'rowid', $isdraft = 0, $mode = 0) {
        return true; // Allow all for demo
    }
}

if (!function_exists('llxHeader')) {
    function llxHeader($head = "", $title = "", $help_url = "", $target = "", $disablejs = 0, $disablehead = 0, $arrayofjs = '', $arrayofcss = '', $morehead = '', $morehtml = '', $replacemainareaby = '', $disablenofollow = 0, $disablenoindex = 0) {
        echo "<!DOCTYPE html><html><head><title>$title</title>";
        echo '<link rel="stylesheet" href="../css/mailchimpsync.css">';
        echo '<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>';
        echo '<script src="../js/mailchimpsync.js"></script>';
        echo "</head><body>";
    }
}

if (!function_exists('llxFooter')) {
    function llxFooter($comment = '', $zone = 'private') {
        echo "</body></html>";
    }
}

if (!function_exists('load_fiche_titre')) {
    function load_fiche_titre($titre, $morehtmlright = '', $picto = '', $pictoisfullpath = 0, $id = '', $morecssontable = '', $morehtmlcenter = '') {
        echo "<h1>$titre</h1>";
        return '';
    }
}

if (!function_exists('setEventMessages')) {
    function setEventMessages($mesg, $mesgs = null, $style = 'mesgs', $messagekey = '', $noduplicate = 0) {
        if ($style == 'mesgs') {
            echo '<div class="mailchimpsync-alert mailchimpsync-alert-success">' . $mesg . '</div>';
        } else {
            echo '<div class="mailchimpsync-alert mailchimpsync-alert-error">' . $mesg . '</div>';
        }
    }
}

if (!function_exists('dol_escape_htmltag')) {
    function dol_escape_htmltag($stringtoescape, $keepb = 0, $keepn = 0, $noescapetags = '', $escapeonlyhtmltags = 0, $cleanalsojavascript = 0) {
        return htmlspecialchars($stringtoescape);
    }
}

if (!function_exists('dol_print_date')) {
    function dol_print_date($time, $format = '', $tzoutput = 'auto', $outputlangs = '', $encodetooutput = false) {
        if (empty($time)) return '';
        return date('Y-m-d H:i:s', is_numeric($time) ? $time : strtotime($time));
    }
}

if (!function_exists('newToken')) {
    function newToken() {
        return md5(uniqid(rand(), true));
    }
}

if (!function_exists('dol_syslog')) {
    function dol_syslog($message, $level = LOG_INFO) {
        error_log($message);
    }
}

if (!function_exists('dol_include_once')) {
    function dol_include_once($relpath) {
        $path = DOL_DOCUMENT_ROOT . '/custom' . $relpath;
        if (file_exists($path)) {
            return include_once $path;
        }
        return false;
    }
}

if (!function_exists('dol_mktime')) {
    function dol_mktime($hour, $minute, $second, $month, $day, $year, $gm = 'auto', $check = 1) {
        return mktime($hour, $minute, $second, $month, $day, $year);
    }
}

if (!function_exists('dol_now')) {
    function dol_now($mode = 'auto') {
        return time();
    }
}

// Define Form class
if (!class_exists('Form')) {
    class Form {
        public $db;
        public function __construct($db) {
            $this->db = $db;
        }
        public function selectDate($set_time = '', $prefix = 're', $h = 0, $m = 0, $empty = 0, $form_name = "", $d = 1, $addnowbutton = 0, $nooutput = 0, $disabled = 0, $fullday = '', $addplusone = '', $adddateof = '') {
            return '<input type="date" name="' . $prefix . '" value="' . date('Y-m-d', $set_time) . '">';
        }
    }
}

// Define DolibarrTriggers class
if (!class_exists('DolibarrTriggers')) {
    class DolibarrTriggers {
        public $db;
        public $name;
        public $description;
        public $version;
        public $picto;
        public $family;
        
        public function getName() {
            return $this->name;
        }
        
        public function getDesc() {
            return $this->description;
        }
        
        public function getVersion() {
            return $this->version;
        }
    }
}

// Initialize module enabled status
$conf->mailchimpsync = new stdClass();
$conf->mailchimpsync->enabled = 1;