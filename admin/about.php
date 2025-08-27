<?php
/* Copyright (C) 2025 MokoDoliChimp Development Team
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file    admin/about.php
 * \ingroup mokodolichimp
 * \brief   About page for MokoDoliChimp module
 */

// Load Dolibarr environment
$res = 0;
if (!$res && file_exists("../main.inc.php")) {
    $res = include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
    $res = include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
    $res = include "../../../main.inc.php";
}

// Mock Dolibarr environment for standalone testing
if (!$res) {
    $conf = new stdClass();
    $conf->mokodolichimp = new stdClass();
    $conf->mokodolichimp->enabled = true;
    
    $user = new stdClass();
    $user->rights = new stdClass();
    $user->rights->mokodolichimp = new stdClass();
    $user->rights->mokodolichimp->sync = new stdClass();
    $user->rights->mokodolichimp->sync->read = true;
    $user->rights->mokodolichimp->sync->write = true;
    
    $langs = new stdClass();
    $langs->trans = function($key) { return $key; };
}

// Check permissions
if (defined('DOL_DOCUMENT_ROOT') && !$user->rights->mokodolichimp->sync->read) {
    accessforbidden();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MokoDoliChimp - About</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }
        .header h1 {
            color: #333;
            margin: 0 0 10px 0;
        }
        .header .version {
            background: #007cba;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 14px;
            display: inline-block;
        }
        .section {
            margin-bottom: 30px;
        }
        .section h2 {
            color: #333;
            border-bottom: 2px solid #007cba;
            padding-bottom: 10px;
        }
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .feature-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #007cba;
        }
        .feature-card h3 {
            margin: 0 0 10px 0;
            color: #333;
        }
        .feature-card p {
            margin: 0;
            color: #666;
            line-height: 1.5;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .stat-card {
            background: #007cba;
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            display: block;
        }
        .stat-label {
            font-size: 0.9em;
            opacity: 0.9;
        }
        .navigation {
            text-align: center;
            margin-bottom: 20px;
        }
        .navigation a {
            color: #007cba;
            text-decoration: none;
            margin: 0 15px;
        }
        .tech-specs {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .tech-specs table {
            width: 100%;
            border-collapse: collapse;
        }
        .tech-specs th,
        .tech-specs td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .tech-specs th {
            background: #e9ecef;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="navigation">
            <a href="../sync_dashboard.php">← Back to Dashboard</a> |
            <a href="setup.php">Configuration</a> |
            <a href="../field_mapping.php">Field Mapping</a>
        </div>

        <div class="header">
            <h1>🔄 MokoDoliChimp</h1>
            <span class="version">v1.0.0</span>
            <p>Bidirectional Dolibarr ↔ Mailchimp Synchronization Module</p>
        </div>

        <div class="section">
            <h2>📋 Module Information</h2>
            <p>MokoDoliChimp is a comprehensive module that provides seamless bidirectional synchronization between Dolibarr entities and Mailchimp lists. It enables businesses to maintain consistent customer data across both platforms while leveraging Mailchimp's powerful marketing automation capabilities.</p>
        </div>

        <div class="section">
            <h2>🚀 Key Features</h2>
            <div class="feature-grid">
                <div class="feature-card">
                    <h3>Bidirectional Sync</h3>
                    <p>Synchronize data both ways between Dolibarr and Mailchimp to maintain consistency across platforms.</p>
                </div>
                <div class="feature-card">
                    <h3>Real-Time Triggers</h3>
                    <p>Automatic synchronization when entities are modified in Dolibarr, ensuring immediate updates.</p>
                </div>
                <div class="feature-card">
                    <h3>Field Mapping</h3>
                    <p>Flexible mapping of Dolibarr fields to Mailchimp merge fields, including date of birth support.</p>
                </div>
                <div class="feature-card">
                    <h3>Tags & Segments</h3>
                    <p>Advanced audience management with dynamic tag assignment and segment creation based on rules.</p>
                </div>
                <div class="feature-card">
                    <h3>Scheduled Tasks</h3>
                    <p>Automated background synchronization through Dolibarr's cron system.</p>
                </div>
                <div class="feature-card">
                    <h3>Entity Support</h3>
                    <p>Synchronizes third parties (companies), contacts (individuals), and users.</p>
                </div>
            </div>
        </div>

        <div class="section">
            <h2>📊 Module Statistics</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <span class="stat-number">7</span>
                    <span class="stat-label">Core Classes</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number">3</span>
                    <span class="stat-label">Entity Types</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number">15+</span>
                    <span class="stat-label">Field Mappings</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number">5</span>
                    <span class="stat-label">Sync Types</span>
                </div>
            </div>
        </div>

        <div class="section">
            <h2>🔧 Technical Specifications</h2>
            <div class="tech-specs">
                <table>
                    <tr>
                        <th>Component</th>
                        <th>Description</th>
                        <th>Status</th>
                    </tr>
                    <tr>
                        <td>PHP Version</td>
                        <td>7.4 or higher</td>
                        <td>✓ Compatible</td>
                    </tr>
                    <tr>
                        <td>Dolibarr Version</td>
                        <td>13.0 or higher</td>
                        <td>✓ Compatible</td>
                    </tr>
                    <tr>
                        <td>Mailchimp API</td>
                        <td>Marketing API v3.0</td>
                        <td>✓ Integrated</td>
                    </tr>
                    <tr>
                        <td>Database</td>
                        <td>MySQL/MariaDB</td>
                        <td>✓ Supported</td>
                    </tr>
                    <tr>
                        <td>Triggers</td>
                        <td>Real-time entity sync</td>
                        <td>✓ Active</td>
                    </tr>
                    <tr>
                        <td>Cron Jobs</td>
                        <td>Scheduled synchronization</td>
                        <td>✓ Configured</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="section">
            <h2>📝 Module Architecture</h2>
            <div class="feature-grid">
                <div class="feature-card">
                    <h3>Core Module (modMokoDoliChimp.class.php)</h3>
                    <p>Main module descriptor handling installation, permissions, and Dolibarr integration.</p>
                </div>
                <div class="feature-card">
                    <h3>Sync Engine (mokodolichimp.class.php)</h3>
                    <p>Primary synchronization logic with Mailchimp API integration and error handling.</p>
                </div>
                <div class="feature-card">
                    <h3>Sync Manager (sync_manager.class.php)</h3>
                    <p>Coordinates different sync types and manages sync operations workflow.</p>
                </div>
                <div class="feature-card">
                    <h3>Tag Manager (tag_segment_manager.class.php)</h3>
                    <p>Handles Mailchimp tags and audience segments with dynamic rule application.</p>
                </div>
                <div class="feature-card">
                    <h3>Trigger System (MokoDoliChimpTrigger.class.php)</h3>
                    <p>Real-time sync triggers responding to entity modifications in Dolibarr.</p>
                </div>
                <div class="feature-card">
                    <h3>Admin Interface (admin/setup.php)</h3>
                    <p>Configuration interface for API settings and sync preferences.</p>
                </div>
            </div>
        </div>

        <div class="section">
            <h2>🎯 Getting Started</h2>
            <ol>
                <li><strong>Configuration:</strong> Set up your Mailchimp API key and server prefix</li>
                <li><strong>Field Mapping:</strong> Configure how Dolibarr fields map to Mailchimp</li>
                <li><strong>Tag Rules:</strong> Set up automatic tag assignment rules</li>
                <li><strong>Test Sync:</strong> Perform a manual sync to verify configuration</li>
                <li><strong>Enable Auto-Sync:</strong> Activate real-time and scheduled synchronization</li>
            </ol>
        </div>

        <div class="section">
            <h2>📞 Support & Documentation</h2>
            <p>For detailed documentation, configuration guides, and troubleshooting information:</p>
            <ul>
                <li>Module documentation: <a href="../BITE_SIZE_REVIEW.md">BITE_SIZE_REVIEW.md</a></li>
                <li>Configuration guide: Available in the admin interface</li>
                <li>Field mapping help: Interactive mapping interface</li>
                <li>API testing: Built-in connection testing tools</li>
            </ul>
        </div>

        <div class="section">
            <h2>⚡ Performance & Reliability</h2>
            <div class="feature-grid">
                <div class="feature-card">
                    <h3>Error Handling</h3>
                    <p>Comprehensive error logging and retry mechanisms for failed synchronizations.</p>
                </div>
                <div class="feature-card">
                    <h3>Rate Limiting</h3>
                    <p>Respects Mailchimp API rate limits to ensure reliable service.</p>
                </div>
                <div class="feature-card">
                    <h3>Background Processing</h3>
                    <p>Non-blocking sync operations that don't impact Dolibarr performance.</p>
                </div>
                <div class="feature-card">
                    <h3>Data Validation</h3>
                    <p>Validates data before sync to prevent errors and maintain data integrity.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>