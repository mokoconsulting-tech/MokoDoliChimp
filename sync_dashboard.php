<?php
/* Copyright (C) 2025 MokoDoliChimp Development Team
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file       sync_dashboard.php
 * \ingroup    mokodolichimp
 * \brief      MokoDoliChimp sync dashboard page.
 */

require_once 'class/mokodolichimp.class.php';
require_once 'class/sync_manager.class.php';

// Mock logger class for standalone testing
if (!class_exists('MokoDoliChimpLogger')) {
    class MokoDoliChimpLogger {
        private $db;
        public function __construct($db) { $this->db = $db; }
        public function info($message) { error_log("INFO: $message"); }
        public function error($message) { error_log("ERROR: $message"); }
    }
}

// Check if we're in Dolibarr environment
if (defined('DOL_DOCUMENT_ROOT')) {
    // Real Dolibarr environment
    require_once DOL_DOCUMENT_ROOT.'/main.inc.php';
    require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
    
    // Check permissions
    if (!$user->rights->mokodolichimp->sync->read) {
        accessforbidden();
    }
} else {
    // Standalone testing environment
    $conf = new stdClass();
    $conf->mokodolichimp = new stdClass();
    $conf->mokodolichimp->enabled = true;
    
    $user = new stdClass();
    $user->rights = new stdClass();
    $user->rights->mokodolichimp = new stdClass();
    $user->rights->mokodolichimp->sync = new stdClass();
    $user->rights->mokodolichimp->sync->read = true;
    $user->rights->mokodolichimp->sync->write = true;
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'view';
$list_id = $_GET['list_id'] ?? $_POST['list_id'] ?? '';

// Initialize MokoDoliChimp and SyncManager
$mokodolichimp = new MokoDoliChimp(null);
$syncManager = new MokoDoliChimpSyncManager(null);

$message = '';
$error = 0;
$sync_results = [];

// Handle actions
switch ($action) {
    case 'sync_thirdparties':
        $sync_results = $syncManager->manualSync('thirdparty', $list_id);
        $message = sprintf('Third parties sync completed: %s', $sync_results['message'] ?? 'Unknown result');
        break;
        
    case 'sync_contacts':
        $sync_results = $syncManager->manualSync('contact', $list_id);
        $message = sprintf('Contacts sync completed: %s', $sync_results['message'] ?? 'Unknown result');
        break;
        
    case 'sync_users':
        $sync_results = $syncManager->manualSync('user', $list_id);
        $message = sprintf('Users sync completed: %s', $sync_results['message'] ?? 'Unknown result');
        break;
        
    case 'sync_from_mailchimp':
        $sync_results = $mokodolichimp->syncFromMailchimp($list_id);
        $message = sprintf('Mailchimp import completed: %d success, %d errors', 
            $sync_results['success'] ?? 0, $sync_results['errors'] ?? 0);
        break;
        
    case 'full_sync':
        $sync_results = $syncManager->manualSync('all', $list_id);
        $message = sprintf('Full sync completed: %s', $sync_results['message'] ?? 'Unknown result');
        break;
        
    case 'bidirectional_sync':
        $sync_results = $syncManager->bidirectionalSync($list_id);
        $message = sprintf('Bidirectional sync completed: %s', $sync_results['message'] ?? 'Unknown result');
        break;
        
    case 'get_sync_status':
        $sync_results = $syncManager->getSyncStatus();
        $message = 'Sync status retrieved successfully';
        break;
}

// Get available Mailchimp lists
$mailchimp_lists = $mokodolichimp->getMailchimpLists();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MokoDoliChimp Sync Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            color: #333;
            font-size: 2.5em;
            font-weight: 300;
        }
        .header .subtitle {
            color: #666;
            font-size: 1.2em;
            margin-top: 10px;
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card h3 {
            margin: 0 0 20px 0;
            color: #333;
            font-size: 1.4em;
            display: flex;
            align-items: center;
        }
        .card-icon {
            font-size: 1.8em;
            margin-right: 15px;
        }
        .sync-button {
            background: linear-gradient(45deg, #007cba, #00a8e8);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            width: 100%;
            margin: 10px 0;
        }
        .sync-button:hover {
            background: linear-gradient(45deg, #005c8a, #007bb8);
            transform: translateY(-2px);
        }
        .sync-button.full-sync {
            background: linear-gradient(45deg, #28a745, #20c997);
            font-size: 16px;
            padding: 15px 30px;
        }
        .sync-button.danger {
            background: linear-gradient(45deg, #dc3545, #e74c3c);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }
        .message {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .stat-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #007cba;
        }
        .stat-label {
            color: #666;
            font-size: 0.9em;
            margin-top: 5px;
        }
        .sync-details {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-top: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .sync-log {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 15px;
            max-height: 300px;
            overflow-y: auto;
            font-family: 'Courier New', monospace;
            font-size: 13px;
        }
        .progress-bar {
            background: #e9ecef;
            border-radius: 10px;
            height: 20px;
            margin: 10px 0;
            overflow: hidden;
        }
        .progress-fill {
            background: linear-gradient(45deg, #28a745, #20c997);
            height: 100%;
            transition: width 0.3s ease;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🐒 MokoDoliChimp</h1>
            <div class="subtitle">Dolibarr ↔ Mailchimp Synchronization Dashboard</div>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo $error ? 'error' : ''; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="sync_dashboard.php">
            <div class="form-group">
                <label for="list_id">Select Mailchimp List:</label>
                <select name="list_id" id="list_id">
                    <option value="">Default List</option>
                    <?php foreach ($mailchimp_lists as $list): ?>
                        <option value="<?php echo htmlspecialchars($list->id ?? ''); ?>" 
                                <?php echo ($list_id === ($list->id ?? '')) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($list->name ?? 'Unnamed List'); ?> 
                            (<?php echo $list->stats->member_count ?? 0; ?> members)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="dashboard-grid">
                <div class="card">
                    <h3><span class="card-icon">🏢</span>Third Parties</h3>
                    <p>Synchronize company information between Dolibarr and Mailchimp.</p>
                    <button type="submit" name="action" value="sync_thirdparties" class="sync-button">
                        Sync Third Parties → Mailchimp
                    </button>
                </div>

                <div class="card">
                    <h3><span class="card-icon">👤</span>Contacts</h3>
                    <p>Synchronize individual contacts and their details.</p>
                    <button type="submit" name="action" value="sync_contacts" class="sync-button">
                        Sync Contacts → Mailchimp
                    </button>
                </div>

                <div class="card">
                    <h3><span class="card-icon">👥</span>Users</h3>
                    <p>Synchronize Dolibarr users with Mailchimp subscribers.</p>
                    <button type="submit" name="action" value="sync_users" class="sync-button">
                        Sync Users → Mailchimp
                    </button>
                </div>

                <div class="card">
                    <h3><span class="card-icon">📥</span>Import</h3>
                    <p>Import Mailchimp subscribers back into Dolibarr.</p>
                    <button type="submit" name="action" value="sync_from_mailchimp" class="sync-button danger">
                        Import from Mailchimp
                    </button>
                </div>
            </div>

            <div class="card">
                <h3><span class="card-icon">🔄</span>Full Synchronization</h3>
                <p>Perform complete bidirectional synchronization of all entities.</p>
                <button type="submit" name="action" value="full_sync" class="sync-button full-sync">
                    🚀 Run Full Sync
                </button>
            </div>
        </form>

        <?php if (!empty($sync_results)): ?>
            <div class="sync-details">
                <h3>📊 Sync Results</h3>
                
                <?php if (is_array($sync_results) && isset($sync_results['success'])): ?>
                    <!-- Single entity sync results -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $sync_results['success'] ?? 0; ?></div>
                            <div class="stat-label">Successful</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $sync_results['errors'] ?? 0; ?></div>
                            <div class="stat-label">Errors</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $sync_results['skipped'] ?? 0; ?></div>
                            <div class="stat-label">Skipped</div>
                        </div>
                    </div>
                    
                    <?php if (!empty($sync_results['details'])): ?>
                        <h4>Detailed Log:</h4>
                        <div class="sync-log">
                            <?php foreach ($sync_results['details'] as $detail): ?>
                                <div><?php echo htmlspecialchars(json_encode($detail, JSON_PRETTY_PRINT)); ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <!-- Full sync results -->
                    <?php foreach ($sync_results as $entity => $results): ?>
                        <?php if (is_array($results)): ?>
                            <h4><?php echo ucfirst($entity); ?> Sync:</h4>
                            <div class="stats-grid">
                                <div class="stat-card">
                                    <div class="stat-number"><?php echo $results['success'] ?? 0; ?></div>
                                    <div class="stat-label">Success</div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-number"><?php echo $results['errors'] ?? 0; ?></div>
                                    <div class="stat-label">Errors</div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="sync-details">
            <h3>🔧 Quick Actions</h3>
            <div class="dashboard-grid">
                <div class="card">
                    <h4>⚙️ Configuration</h4>
                    <a href="admin/setup.php" class="sync-button">Open Settings</a>
                </div>
                <div class="card">
                    <h4>📋 Field Mapping</h4>
                    <a href="field_mapping.php" class="sync-button">Configure Fields</a>
                </div>
                <div class="card">
                    <h4>📊 Sync History</h4>
                    <a href="sync_history.php" class="sync-button">View Logs</a>
                </div>
                <div class="card">
                    <h4>🔔 Webhooks</h4>
                    <a href="webhook_config.php" class="sync-button">Setup Webhooks</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-refresh for live sync monitoring
        let autoRefresh = false;
        
        function toggleAutoRefresh() {
            autoRefresh = !autoRefresh;
            if (autoRefresh) {
                setTimeout(refreshPage, 30000); // Refresh every 30 seconds
            }
        }
        
        function refreshPage() {
            if (autoRefresh) {
                location.reload();
            }
        }
        
        // Add confirmation for destructive actions
        document.querySelectorAll('.sync-button.danger').forEach(button => {
            button.addEventListener('click', function(e) {
                if (!confirm('This will import data from Mailchimp. Continue?')) {
                    e.preventDefault();
                }
            });
        });
        
        // Add loading state to buttons
        document.querySelectorAll('.sync-button').forEach(button => {
            button.addEventListener('click', function() {
                this.style.opacity = '0.7';
                this.innerHTML = '⏳ Processing...';
            });
        });
    </script>
</body>
</html>