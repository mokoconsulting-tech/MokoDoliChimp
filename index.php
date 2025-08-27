<?php
/* Copyright (C) 2025 MokoDoliChimp Development Team
 *
 * Main Entry Point for MokoDoliChimp Module Demo
 */

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MokoDoliChimp - Dolibarr ↔ Mailchimp Sync Module</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .header {
            background: white;
            border-radius: 12px;
            padding: 40px;
            text-align: center;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .header h1 {
            color: #333;
            margin: 0 0 10px 0;
            font-size: 2.5em;
        }
        .header p {
            color: #666;
            font-size: 1.2em;
            margin: 0;
        }
        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .module-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .module-card:hover {
            transform: translateY(-5px);
        }
        .module-card h3 {
            color: #333;
            margin: 0 0 15px 0;
            font-size: 1.4em;
        }
        .module-card p {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        .module-card .features {
            list-style: none;
            padding: 0;
            margin: 0 0 20px 0;
        }
        .module-card .features li {
            color: #555;
            margin-bottom: 8px;
            padding-left: 20px;
            position: relative;
        }
        .module-card .features li:before {
            content: "✓";
            color: #28a745;
            font-weight: bold;
            position: absolute;
            left: 0;
        }
        .btn {
            display: inline-block;
            background: #007cba;
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: 500;
            transition: background 0.3s ease;
        }
        .btn:hover {
            background: #005c8a;
        }
        .btn-secondary {
            background: #6c757d;
        }
        .btn-secondary:hover {
            background: #545b62;
        }
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .status-item {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .status-icon {
            font-size: 2em;
            margin-bottom: 10px;
            display: block;
        }
        .status-success { color: #28a745; }
        .status-warning { color: #ffc107; }
        .status-info { color: #17a2b8; }
        .footer {
            text-align: center;
            margin-top: 40px;
            color: white;
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔄 MokoDoliChimp</h1>
            <p>Bidirectional Dolibarr ↔ Mailchimp Synchronization Module</p>
        </div>

        <div class="modules-grid">
            <div class="module-card">
                <h3>📊 Sync Dashboard</h3>
                <p>Monitor and control synchronization between Dolibarr and Mailchimp</p>
                <ul class="features">
                    <li>Manual sync triggers</li>
                    <li>Real-time sync monitoring</li>
                    <li>Visual progress feedback</li>
                    <li>Detailed sync results</li>
                </ul>
                <a href="sync_dashboard.php" class="btn">Open Dashboard</a>
            </div>

            <div class="module-card">
                <h3>⚙️ Configuration</h3>
                <p>Set up Mailchimp API credentials and sync preferences</p>
                <ul class="features">
                    <li>API credentials setup</li>
                    <li>Connection testing</li>
                    <li>Sync frequency settings</li>
                    <li>Entity preferences</li>
                </ul>
                <a href="admin/setup.php" class="btn">Configure</a>
            </div>

            <div class="module-card">
                <h3>🔗 Field Mapping</h3>
                <p>Configure how Dolibarr fields map to Mailchimp merge fields</p>
                <ul class="features">
                    <li>Third party mapping</li>
                    <li>Contact field mapping</li>
                    <li>User data mapping</li>
                    <li>DOB field support</li>
                </ul>
                <a href="field_mapping.php" class="btn">Setup Mapping</a>
            </div>

            <div class="module-card">
                <h3>🏷️ Tags & Segments</h3>
                <p>Manage Mailchimp tags and audience segments for advanced targeting</p>
                <ul class="features">
                    <li>Dynamic tag assignment</li>
                    <li>Audience segments</li>
                    <li>Mapping rules</li>
                    <li>Smart suggestions</li>
                </ul>
                <a href="tag_segment_config.php" class="btn">Manage Tags</a>
            </div>

            <div class="module-card">
                <h3>⏰ Scheduled Tasks</h3>
                <p>Test and monitor automated synchronization tasks</p>
                <ul class="features">
                    <li>Cron job testing</li>
                    <li>Scheduled sync types</li>
                    <li>Background processing</li>
                    <li>Task monitoring</li>
                </ul>
                <a href="cron_sync.php?manual_test=1" class="btn btn-secondary">Test Cron</a>
            </div>

            <div class="module-card">
                <h3>📋 Review Guide</h3>
                <p>Bite-size component review and implementation status</p>
                <ul class="features">
                    <li>Implementation status</li>
                    <li>Component breakdown</li>
                    <li>Review questions</li>
                    <li>Next steps</li>
                </ul>
                <a href="BITE_SIZE_REVIEW.md" class="btn btn-secondary">View Guide</a>
            </div>
        </div>

        <div class="status-grid">
            <div class="status-item">
                <span class="status-icon status-success">✅</span>
                <strong>Core Engine</strong><br>
                Sync logic implemented
            </div>
            <div class="status-item">
                <span class="status-icon status-success">✅</span>
                <strong>Real-Time Triggers</strong><br>
                Auto sync on changes
            </div>
            <div class="status-item">
                <span class="status-icon status-success">✅</span>
                <strong>Tag Management</strong><br>
                Advanced targeting
            </div>
            <div class="status-item">
                <span class="status-icon status-warning">⚠️</span>
                <strong>API Connection</strong><br>
                Requires setup
            </div>
        </div>

        <div class="footer">
            <p>MokoDoliChimp v1.0.0 - A comprehensive Dolibarr module for Mailchimp integration</p>
            <p>Features real-time sync, advanced field mapping, and intelligent audience management</p>
        </div>
    </div>
</body>
</html>