#!/usr/bin/env php
<?php
/**
 * Mailchimp Sync Cron Script
 * 
 * @file        cron_sync.php
 * @ingroup     mailchimpsync
 * @brief       Cron script for scheduled synchronization
 * @author      Moko Consulting <hello@mokoconsulting.tech>
 * @copyright   2025 Moko Consulting
 * @license     GNU General Public License v3.0 or later
 * @warranty    This program comes with ABSOLUTELY NO WARRANTY. This is free software.
 */

// Check if script is run from command line
if (!isset($_SERVER['REQUEST_METHOD'])) {
    define('ISLOADEDBYSTEELSHEET', 1);
    
    // Set paths
    $sapi_type = php_sapi_name();
    $script_file = basename(__FILE__);
    $path = __DIR__.'/';
    
    // Include Dolibarr main
    require_once $path.'../../../main.inc.php';
    require_once DOL_DOCUMENT_ROOT.'/core/lib/cron.lib.php';
    require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
    dol_include_once('/mailchimpsync/class/syncservice.class.php');
    
    // Global variables
    global $db, $conf, $user, $langs;
    
    // Language
    $langs->load("mailchimpsync@mailchimpsync");
    
    // Check if module is enabled
    if (empty($conf->mailchimpsync->enabled)) {
        print "Mailchimp Sync module is not enabled\n";
        exit(1);
    }
    
    // Check if auto sync is enabled
    if (empty($conf->global->MAILCHIMPSYNC_AUTO_SYNC)) {
        print "Auto sync is disabled\n";
        exit(0);
    }
    
    // Initialize logging
    dol_syslog("Starting Mailchimp sync cron job", LOG_INFO);
    
    try {
        // Create sync service
        $sync_service = new SyncService($db);
        
        // Run scheduled sync
        $result = $sync_service->runScheduledSync();
        
        if ($result == 0) {
            print "Mailchimp sync completed successfully\n";
            dol_syslog("Mailchimp sync cron job completed successfully", LOG_INFO);
            exit(0);
        } else {
            print "Mailchimp sync completed with errors\n";
            dol_syslog("Mailchimp sync cron job completed with errors", LOG_ERR);
            exit(1);
        }
        
    } catch (Exception $e) {
        print "Error during Mailchimp sync: " . $e->getMessage() . "\n";
        dol_syslog("Exception in Mailchimp sync cron: " . $e->getMessage(), LOG_ERR);
        exit(1);
    }
    
} else {
    // Web access - not allowed
    print "This script must be run from command line\n";
    exit(1);
}
?>
