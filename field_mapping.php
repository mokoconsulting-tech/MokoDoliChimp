<?php
/* Copyright (C) 2025 MokoDoliChimp Development Team
 *
 * Field mapping configuration page
 */

$action = $_POST['action'] ?? 'view';
$message = '';
$error = 0;

// Default field mappings
$default_mappings = [
    'thirdparty' => [
        'email' => 'EMAIL',
        'name' => 'COMPANY', 
        'firstname' => 'FNAME',
        'lastname' => 'LNAME',
        'phone' => 'PHONE',
        'address' => 'ADDRESS',
        'zip' => 'ZIP',
        'town' => 'CITY',
        'country' => 'COUNTRY'
    ],
    'contact' => [
        'email' => 'EMAIL',
        'firstname' => 'FNAME',
        'lastname' => 'LNAME',
        'phone' => 'PHONE',
        'mobile' => 'MOBILE',
        'title' => 'TITLE',
        'company' => 'COMPANY'
    ],
    'user' => [
        'email' => 'EMAIL',
        'firstname' => 'FNAME',
        'lastname' => 'LNAME',
        'phone' => 'PHONE',
        'office_phone' => 'OFFICE',
        'job' => 'JOB'
    ]
];

if ($action === 'save') {
    // Save field mappings
    $mappings = [
        'thirdparty' => $_POST['thirdparty'] ?? [],
        'contact' => $_POST['contact'] ?? [],
        'user' => $_POST['user'] ?? []
    ];
    
    // In real implementation, save to database
    $message = 'Field mappings saved successfully!';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MokoDoliChimp Field Mapping</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .mapping-section {
            margin-bottom: 40px;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 25px;
            background: #f8f9fa;
        }
        .mapping-section h2 {
            color: #555;
            margin-top: 0;
            display: flex;
            align-items: center;
        }
        .section-icon {
            font-size: 1.5em;
            margin-right: 15px;
        }
        .mapping-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
            align-items: center;
            margin-bottom: 15px;
            padding: 15px;
            background: white;
            border-radius: 6px;
            border: 1px solid #dee2e6;
        }
        .mapping-grid.header {
            background: #007cba;
            color: white;
            font-weight: bold;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            font-weight: 500;
            margin-bottom: 5px;
            color: #333;
        }
        .form-group select,
        .form-group input {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .dolibarr-field {
            font-family: 'Courier New', monospace;
            background: #e9ecef;
            padding: 8px;
            border-radius: 4px;
            font-size: 13px;
        }
        .button {
            background: #007cba;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            margin: 10px 5px;
        }
        .button:hover {
            background: #005c8a;
        }
        .button-secondary {
            background: #6c757d;
        }
        .button-secondary:hover {
            background: #545b62;
        }
        .message {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .help-text {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
            font-style: italic;
        }
        .add-mapping {
            background: #28a745;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        .remove-mapping {
            background: #dc3545;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
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
        .navigation a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="navigation">
            <a href="sync_dashboard.php">← Back to Dashboard</a> |
            <a href="admin/setup.php">Configuration</a> |
            <a href="sync_history.php">Sync History</a>
        </div>

        <h1>🔗 Field Mapping Configuration</h1>
        
        <?php if ($message): ?>
            <div class="message">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="field_mapping.php">
            <input type="hidden" name="action" value="save">

            <!-- Third Party Mapping -->
            <div class="mapping-section">
                <h2><span class="section-icon">🏢</span>Third Party Field Mapping</h2>
                <p>Map Dolibarr third party fields to Mailchimp merge fields.</p>
                
                <div class="mapping-grid header">
                    <div>Dolibarr Field</div>
                    <div>Mailchimp Merge Field</div>
                    <div>Custom Tag</div>
                </div>
                
                <?php foreach ($default_mappings['thirdparty'] as $dolibarr_field => $mailchimp_field): ?>
                    <div class="mapping-grid">
                        <div class="dolibarr-field"><?php echo htmlspecialchars($dolibarr_field); ?></div>
                        <div class="form-group">
                            <select name="thirdparty[<?php echo htmlspecialchars($dolibarr_field); ?>]">
                                <option value="">-- Skip Field --</option>
                                <option value="EMAIL" <?php echo $mailchimp_field === 'EMAIL' ? 'selected' : ''; ?>>EMAIL</option>
                                <option value="FNAME" <?php echo $mailchimp_field === 'FNAME' ? 'selected' : ''; ?>>FNAME</option>
                                <option value="LNAME" <?php echo $mailchimp_field === 'LNAME' ? 'selected' : ''; ?>>LNAME</option>
                                <option value="COMPANY" <?php echo $mailchimp_field === 'COMPANY' ? 'selected' : ''; ?>>COMPANY</option>
                                <option value="PHONE" <?php echo $mailchimp_field === 'PHONE' ? 'selected' : ''; ?>>PHONE</option>
                                <option value="ADDRESS" <?php echo $mailchimp_field === 'ADDRESS' ? 'selected' : ''; ?>>ADDRESS</option>
                                <option value="ZIP" <?php echo $mailchimp_field === 'ZIP' ? 'selected' : ''; ?>>ZIP</option>
                                <option value="CITY" <?php echo $mailchimp_field === 'CITY' ? 'selected' : ''; ?>>CITY</option>
                                <option value="COUNTRY" <?php echo $mailchimp_field === 'COUNTRY' ? 'selected' : ''; ?>>COUNTRY</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <input type="text" placeholder="Custom tag" name="thirdparty_tag[<?php echo htmlspecialchars($dolibarr_field); ?>]">
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="help-text">
                    Third parties represent companies/organizations in Dolibarr. They will be tagged as "dolibarr-thirdparty" in Mailchimp.
                </div>
            </div>

            <!-- Contact Mapping -->
            <div class="mapping-section">
                <h2><span class="section-icon">👤</span>Contact Field Mapping</h2>
                <p>Map Dolibarr contact fields to Mailchimp merge fields.</p>
                
                <div class="mapping-grid header">
                    <div>Dolibarr Field</div>
                    <div>Mailchimp Merge Field</div>
                    <div>Custom Tag</div>
                </div>
                
                <?php foreach ($default_mappings['contact'] as $dolibarr_field => $mailchimp_field): ?>
                    <div class="mapping-grid">
                        <div class="dolibarr-field"><?php echo htmlspecialchars($dolibarr_field); ?></div>
                        <div class="form-group">
                            <select name="contact[<?php echo htmlspecialchars($dolibarr_field); ?>]">
                                <option value="">-- Skip Field --</option>
                                <option value="EMAIL" <?php echo $mailchimp_field === 'EMAIL' ? 'selected' : ''; ?>>EMAIL</option>
                                <option value="FNAME" <?php echo $mailchimp_field === 'FNAME' ? 'selected' : ''; ?>>FNAME</option>
                                <option value="LNAME" <?php echo $mailchimp_field === 'LNAME' ? 'selected' : ''; ?>>LNAME</option>
                                <option value="COMPANY" <?php echo $mailchimp_field === 'COMPANY' ? 'selected' : ''; ?>>COMPANY</option>
                                <option value="PHONE" <?php echo $mailchimp_field === 'PHONE' ? 'selected' : ''; ?>>PHONE</option>
                                <option value="MOBILE" <?php echo $mailchimp_field === 'MOBILE' ? 'selected' : ''; ?>>MOBILE</option>
                                <option value="TITLE" <?php echo $mailchimp_field === 'TITLE' ? 'selected' : ''; ?>>TITLE</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <input type="text" placeholder="Custom tag" name="contact_tag[<?php echo htmlspecialchars($dolibarr_field); ?>]">
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="help-text">
                    Contacts represent individual people associated with third parties. They will be tagged as "dolibarr-contact" in Mailchimp.
                </div>
            </div>

            <!-- User Mapping -->
            <div class="mapping-section">
                <h2><span class="section-icon">👥</span>User Field Mapping</h2>
                <p>Map Dolibarr user fields to Mailchimp merge fields.</p>
                
                <div class="mapping-grid header">
                    <div>Dolibarr Field</div>
                    <div>Mailchimp Merge Field</div>
                    <div>Custom Tag</div>
                </div>
                
                <?php foreach ($default_mappings['user'] as $dolibarr_field => $mailchimp_field): ?>
                    <div class="mapping-grid">
                        <div class="dolibarr-field"><?php echo htmlspecialchars($dolibarr_field); ?></div>
                        <div class="form-group">
                            <select name="user[<?php echo htmlspecialchars($dolibarr_field); ?>]">
                                <option value="">-- Skip Field --</option>
                                <option value="EMAIL" <?php echo $mailchimp_field === 'EMAIL' ? 'selected' : ''; ?>>EMAIL</option>
                                <option value="FNAME" <?php echo $mailchimp_field === 'FNAME' ? 'selected' : ''; ?>>FNAME</option>
                                <option value="LNAME" <?php echo $mailchimp_field === 'LNAME' ? 'selected' : ''; ?>>LNAME</option>
                                <option value="PHONE" <?php echo $mailchimp_field === 'PHONE' ? 'selected' : ''; ?>>PHONE</option>
                                <option value="OFFICE" <?php echo $mailchimp_field === 'OFFICE' ? 'selected' : ''; ?>>OFFICE</option>
                                <option value="JOB" <?php echo $mailchimp_field === 'JOB' ? 'selected' : ''; ?>>JOB</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <input type="text" placeholder="Custom tag" name="user_tag[<?php echo htmlspecialchars($dolibarr_field); ?>]">
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="help-text">
                    Users represent Dolibarr system users (employees, admins). They will be tagged as "dolibarr-user" in Mailchimp.
                </div>
            </div>

            <div style="text-align: center; margin-top: 30px;">
                <button type="submit" class="button">💾 Save Field Mappings</button>
                <button type="button" class="button button-secondary" onclick="location.href='sync_dashboard.php'">Cancel</button>
            </div>
        </form>

        <div class="mapping-section">
            <h2><span class="section-icon">💡</span>Field Mapping Tips</h2>
            <ul>
                <li><strong>EMAIL field is required</strong> - All entities must have an email to sync with Mailchimp</li>
                <li><strong>Standard merge fields</strong> - Use FNAME, LNAME, COMPANY, PHONE for better compatibility</li>
                <li><strong>Custom tags</strong> - Add additional tags to categorize your contacts in Mailchimp</li>
                <li><strong>Skip unused fields</strong> - Select "Skip Field" for data you don't want to sync</li>
                <li><strong>Bidirectional sync</strong> - Mappings work both ways (Dolibarr → Mailchimp and Mailchimp → Dolibarr)</li>
            </ul>
        </div>
    </div>
</body>
</html>