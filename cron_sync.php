<?php
/* Copyright (C) 2025 MokoDoliChimp Development Team
 *
 * Dolibarr Scheduled Task for MokoDoliChimp Synchronization
 * This file is called by Dolibarr's cron/scheduled task system
 */

/**
 * Dolibarr scheduled task entry point for MokoDoliChimp sync
 * 
 * In real Dolibarr implementation, this would be called by:
 * - Dolibarr's built-in cron system
 * - External cron job pointing to this file
 * - Scheduled task in admin interface
 */

// Initialize Dolibarr environment
$res = 0;
if (!$res && file_exists("../main.inc.php")) {
    $res = include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
    $res = include "../../main.inc.php";
}

// For standalone testing, simulate Dolibarr environment
if (!$res) {
    // Mock Dolibarr globals for testing
    $conf = new stdClass();
    $conf->mokodolichimp = new stdClass();
    $conf->mokodolichimp->enabled = true;
    
    $db = null; // Mock database connection
}

require_once 'class/sync_manager.class.php';

/**
 * Main cron function - called by Dolibarr scheduled tasks
 * @param array $parameters Cron parameters from Dolibarr
 * @return int Return code (0 = success, 1 = error)
 */
function doSchedule($parameters = [])
{
    global $db, $conf, $langs;
    
    $error = 0;
    $output = [];
    
    try {
        // Check if module is enabled
        if (empty($conf->mokodolichimp->enabled)) {
            $output[] = "MokoDoliChimp module is not enabled";
            return 1;
        }
        
        // Initialize sync manager
        $syncManager = new MokoDoliChimpSyncManager($db);
        
        // Get sync type from parameters (default: scheduled)
        $sync_type = $parameters['sync_type'] ?? 'scheduled';
        $list_id = $parameters['list_id'] ?? null;
        
        $output[] = "Starting MokoDoliChimp $sync_type sync...";
        
        // Perform sync based on type
        switch ($sync_type) {
            case 'scheduled':
                $result = $syncManager->scheduledSync();
                break;
                
            case 'bidirectional':
                $result = $syncManager->bidirectionalSync($list_id);
                break;
                
            case 'manual':
                $entity_type = $parameters['entity_type'] ?? 'all';
                $result = $syncManager->manualSync($entity_type, $list_id);
                break;
                
            default:
                $result = $syncManager->scheduledSync();
                break;
        }
        
        // Process results
        if ($result['status'] === 'success') {
            $output[] = "Sync completed successfully in {$result['execution_time']}s";
            
            if (isset($result['results'])) {
                foreach ($result['results'] as $entity => $entity_result) {
                    if (is_array($entity_result)) {
                        $success = $entity_result['success'] ?? 0;
                        $errors = $entity_result['errors'] ?? 0;
                        $output[] = "  - $entity: $success success, $errors errors";
                    }
                }
            }
        } else {
            $output[] = "Sync failed: " . $result['message'];
            $error = 1;
        }
        
    } catch (Exception $e) {
        $output[] = "Sync error: " . $e->getMessage();
        $error = 1;
    }
    
    // Output results (for cron logs)
    foreach ($output as $line) {
        echo date('Y-m-d H:i:s') . " - " . $line . "\n";
    }
    
    return $error;
}

/**
 * Standalone execution for testing
 */
function runStandaloneTest() {
    echo "MokoDoliChimp Cron Test\n";
    echo "======================\n\n";
    
    $parameters = [
        'sync_type' => $_GET['sync_type'] ?? 'scheduled',
        'entity_type' => $_GET['entity_type'] ?? 'all',
        'list_id' => $_GET['list_id'] ?? null
    ];
    
    $result = doSchedule($parameters);
    
    echo "\nExit code: $result\n";
    
    if (php_sapi_name() === 'cli') {
        exit($result);
    }
    
    return $result;
}

// Standalone testing
if (php_sapi_name() === 'cli' || !empty($_GET['test'])) {
    echo "MokoDoliChimp Cron Test\n";
    echo "======================\n\n";
    
    $parameters = [
        'sync_type' => $_GET['sync_type'] ?? 'scheduled',
        'entity_type' => $_GET['entity_type'] ?? 'all',
        'list_id' => $_GET['list_id'] ?? null
    ];
    
    $result = doSchedule($parameters);
    
    echo "\nExit code: $result\n";
    
    if (php_sapi_name() === 'cli') {
        exit($result);
    }
}

/**
 * Web interface for manual testing
 */
if (!empty($_GET['manual_test'])) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>MokoDoliChimp Cron Test</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .container { max-width: 800px; margin: 0 auto; }
            .form-group { margin-bottom: 15px; }
            label { display: block; margin-bottom: 5px; font-weight: bold; }
            select, input { padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
            button { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
            .output { background: #f8f9fa; padding: 15px; border-radius: 4px; margin-top: 20px; font-family: monospace; white-space: pre-line; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>MokoDoliChimp Sync Test</h1>
            
            <form method="get">
                <input type="hidden" name="manual_test" value="1">
                
                <div class="form-group">
                    <label for="sync_type">Sync Type:</label>
                    <select name="sync_type" id="sync_type">
                        <option value="scheduled">Scheduled Sync</option>
                        <option value="manual">Manual Sync</option>
                        <option value="bidirectional">Bidirectional Sync</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="entity_type">Entity Type (for manual sync):</label>
                    <select name="entity_type" id="entity_type">
                        <option value="all">All Entities</option>
                        <option value="thirdparty">Third Parties</option>
                        <option value="contact">Contacts</option>
                        <option value="user">Users</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="list_id">Mailchimp List ID (optional):</label>
                    <input type="text" name="list_id" id="list_id" placeholder="Leave empty for default list">
                </div>
                
                <button type="submit" name="run_test" value="1">Run Sync Test</button>
            </form>
            
            <?php if (!empty($_GET['run_test'])): ?>
                <div class="output">
                    <strong>Sync Results:</strong>
                    
                    <?php
                    ob_start();
                    $test_params = [
                        'sync_type' => $_GET['sync_type'],
                        'entity_type' => $_GET['entity_type'],
                        'list_id' => $_GET['list_id']
                    ];
                    
                    $result = doSchedule($test_params);
                    $output = ob_get_clean();
                    echo htmlspecialchars($output);
                    ?>
                </div>
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
    exit;
}
?>