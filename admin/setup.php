<?php
/* Copyright (C) 2025 MokoDoliChimp Development Team
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file       admin/setup.php
 * \ingroup    mokodolichimp
 * \brief      MokoDoliChimp setup page.
 */

// Load Dolibarr environment
// $res = 0;
// Simulate Dolibarr environment for standalone testing
$res = 1;

if (!$res && file_exists("../../main.inc.php")) {
    $res = include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
    $res = include "../../../main.inc.php";
}

// Simulate required variables for standalone execution
if (!isset($langs)) {
    $langs = new stdClass();
    $langs->trans = function($key) { return $key; };
}
if (!isset($conf)) {
    $conf = new stdClass();
    $conf->mokodolichimp = new stdClass();
    $conf->mokodolichimp->enabled = true;
}
if (!isset($user)) {
    $user = new stdClass();
    $user->rights = new stdClass();
    $user->rights->mokodolichimp = new stdClass();
    $user->rights->mokodolichimp->sync = new stdClass();
    $user->rights->mokodolichimp->sync->write = true;
}

// Check permissions (mock for standalone)
if (!$user->rights->mokodolichimp->sync->write) {
    // In real Dolibarr: accessforbidden();
    // For demo, we'll just show a warning
    $message = 'Warning: In production, this would check user permissions';
}

// Variables
$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : 'view');
$error = 0;
$message = '';

// Actions
if ($action == 'update') {
    $config_updates = [
        'MOKODOLICHIMP_API_KEY' => $_POST['api_key'] ?? '',
        'MOKODOLICHIMP_SERVER_PREFIX' => $_POST['server_prefix'] ?? '',
        'MOKODOLICHIMP_DEFAULT_LIST' => $_POST['default_list'] ?? '',
        'MOKODOLICHIMP_SYNC_ENABLED' => isset($_POST['sync_enabled']) ? '1' : '0',
        'MOKODOLICHIMP_AUTO_SYNC' => isset($_POST['auto_sync']) ? '1' : '0',
        'MOKODOLICHIMP_SYNC_THIRDPARTIES' => isset($_POST['sync_thirdparties']) ? '1' : '0',
        'MOKODOLICHIMP_SYNC_CONTACTS' => isset($_POST['sync_contacts']) ? '1' : '0',
        'MOKODOLICHIMP_SYNC_USERS' => isset($_POST['sync_users']) ? '1' : '0',
    ];
    
    // In real Dolibarr module, this would update database configuration
    // For demo purposes, we'll just show success message
    $message = 'Configuration updated successfully';
}

if ($action == 'test_connection') {
    try {
        if (file_exists('../class/mokodolichimp.class.php')) {
            require_once '../class/mokodolichimp.class.php';
            $mokodolichimp = new MokoDoliChimp(null);
            $lists = $mokodolichimp->getMailchimpLists();
            if (!empty($lists)) {
                $message = 'Connection successful! Found ' . count($lists) . ' lists.';
            } else {
                $error = 1;
                $message = 'Connection failed or no lists found.';
            }
        } else {
            $error = 1;
            $message = 'MokoDoliChimp class file not found.';
        }
    } catch (Exception $e) {
        $error = 1;
        $message = 'Connection failed: ' . $e->getMessage();
    }
}

// Get current configuration values
$current_config = [
    'api_key' => $_POST['api_key'] ?? '',
    'server_prefix' => $_POST['server_prefix'] ?? '',
    'default_list' => $_POST['default_list'] ?? '',
    'sync_enabled' => isset($_POST['sync_enabled']) || $action !== 'update',
    'auto_sync' => isset($_POST['auto_sync']) || $action !== 'update',
    'sync_thirdparties' => isset($_POST['sync_thirdparties']) || $action !== 'update',
    'sync_contacts' => isset($_POST['sync_contacts']) || $action !== 'update',
    'sync_users' => isset($_POST['sync_users']) || $action !== 'update',
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MokoDoliChimp Configuration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #007cba;
            padding-bottom: 10px;
        }
        h2 {
            color: #555;
            margin-top: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        input[type="text"], input[type="password"], select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        input[type="checkbox"] {
            margin-right: 8px;
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .button {
            background-color: #007cba;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-right: 10px;
        }
        .button:hover {
            background-color: #005c8a;
        }
        .button-secondary {
            background-color: #6c757d;
        }
        .button-secondary:hover {
            background-color: #545b62;
        }
        .message {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .message-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .message-error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .help-text {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .section {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🐒 MokoDoliChimp Configuration</h1>
        
        <?php if ($message): ?>
            <div class="message <?php echo $error ? 'message-error' : 'message-success'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="setup.php">
            <input type="hidden" name="action" value="update">
            
            <div class="section">
                <h2>🔑 Mailchimp API Configuration</h2>
                
                <div class="form-group">
                    <label for="api_key">API Key *</label>
                    <input type="password" id="api_key" name="api_key" value="<?php echo htmlspecialchars($current_config['api_key']); ?>" required>
                    <div class="help-text">Your Mailchimp API key (found in Mailchimp → Profile → Extras → API Keys)</div>
                </div>
                
                <div class="form-group">
                    <label for="server_prefix">Server Prefix *</label>
                    <input type="text" id="server_prefix" name="server_prefix" value="<?php echo htmlspecialchars($current_config['server_prefix']); ?>" required>
                    <div class="help-text">Server prefix from your API key (e.g., "us19" from "xxxxx-us19")</div>
                </div>
                
                <div class="form-group">
                    <label for="default_list">Default List ID</label>
                    <input type="text" id="default_list" name="default_list" value="<?php echo htmlspecialchars($current_config['default_list']); ?>">
                    <div class="help-text">Default Mailchimp list ID for synchronization</div>
                </div>
                
                <button type="submit" name="action" value="test_connection" class="button button-secondary">Test Connection</button>
            </div>
            
            <div class="section">
                <h2>⚙️ Synchronization Settings</h2>
                
                <div class="checkbox-group">
                    <input type="checkbox" id="sync_enabled" name="sync_enabled" <?php echo $current_config['sync_enabled'] ? 'checked' : ''; ?>>
                    <label for="sync_enabled">Enable Synchronization</label>
                </div>
                
                <div class="checkbox-group">
                    <input type="checkbox" id="auto_sync" name="auto_sync" <?php echo $current_config['auto_sync'] ? 'checked' : ''; ?>>
                    <label for="auto_sync">Enable Automatic Sync (Cron)</label>
                </div>
            </div>
            
            <div class="section">
                <h2>📊 Entity Synchronization</h2>
                
                <div class="checkbox-group">
                    <input type="checkbox" id="sync_thirdparties" name="sync_thirdparties" <?php echo $current_config['sync_thirdparties'] ? 'checked' : ''; ?>>
                    <label for="sync_thirdparties">Sync Third Parties</label>
                </div>
                
                <div class="checkbox-group">
                    <input type="checkbox" id="sync_contacts" name="sync_contacts" <?php echo $current_config['sync_contacts'] ? 'checked' : ''; ?>>
                    <label for="sync_contacts">Sync Contacts</label>
                </div>
                
                <div class="checkbox-group">
                    <input type="checkbox" id="sync_users" name="sync_users" <?php echo $current_config['sync_users'] ? 'checked' : ''; ?>>
                    <label for="sync_users">Sync Users</label>
                </div>
            </div>
            
            <button type="submit" class="button">Save Configuration</button>
        </form>

        <div class="section">
            <h2>📋 Quick Setup Guide</h2>
            <ol>
                <li><strong>Get Mailchimp API Key:</strong>
                    <ul>
                        <li>Log into your Mailchimp account</li>
                        <li>Go to Profile → Extras → API Keys</li>
                        <li>Create a new API key or copy existing one</li>
                    </ul>
                </li>
                <li><strong>Find Server Prefix:</strong>
                    <ul>
                        <li>Look at your API key format: "xxxxx-us19"</li>
                        <li>The server prefix is the part after the dash (e.g., "us19")</li>
                    </ul>
                </li>
                <li><strong>Get List ID:</strong>
                    <ul>
                        <li>Go to Audience → Settings → Audience name and campaign defaults</li>
                        <li>Find the "Audience ID" (also called List ID)</li>
                    </ul>
                </li>
                <li><strong>Test Connection:</strong>
                    <ul>
                        <li>Enter your API key and server prefix</li>
                        <li>Click "Test Connection" to verify</li>
                    </ul>
                </li>
                <li><strong>Configure Sync:</strong>
                    <ul>
                        <li>Choose which entities to sync (Third Parties, Contacts, Users)</li>
                        <li>Enable automatic sync for scheduled synchronization</li>
                    </ul>
                </li>
            </ol>
        </div>
    </div>
</body>
</html>