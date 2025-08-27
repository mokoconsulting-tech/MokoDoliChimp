<?php
/**
 * Simple Dashboard Demo
 */

// Load Dolibarr environment
$res = 0;
if (!$res && file_exists("../../../main.inc.php")) $res = @include "../../../main.inc.php";
if (!$res) die("Include of main fails");

$page_name = "MailchimpSyncDashboard";
llxHeader('', $langs->trans($page_name));

?>
<div class="mailchimpsync-dashboard">
    <h1>Mailchimp Sync Dashboard</h1>
    
    <div class="mailchimpsync-stats-card">
        <h3>Connection Status</h3>
        <div class="mailchimpsync-stats-row">
            <span class="mailchimpsync-stats-label">Mailchimp API:</span>
            <span class="mailchimpsync-stats-value mailchimpsync-status-connected">Connected</span>
        </div>
        <div class="mailchimpsync-stats-row">
            <span class="mailchimpsync-stats-label">Auto Sync:</span>
            <span class="mailchimpsync-stats-value">Enabled</span>
        </div>
    </div>
    
    <div class="mailchimpsync-stats-card">
        <h3>Sync Statistics (Last 24 Hours)</h3>
        <div class="mailchimpsync-stats-row">
            <span class="mailchimpsync-stats-label">Total Syncs:</span>
            <span class="mailchimpsync-stats-value">125</span>
        </div>
        <div class="mailchimpsync-stats-row">
            <span class="mailchimpsync-stats-label">Successful:</span>
            <span class="mailchimpsync-stats-value">120</span>
        </div>
        <div class="mailchimpsync-stats-row">
            <span class="mailchimpsync-stats-label">Failed:</span>
            <span class="mailchimpsync-stats-value">5</span>
        </div>
        <div class="mailchimpsync-stats-row">
            <span class="mailchimpsync-stats-label">Success Rate:</span>
            <span class="mailchimpsync-stats-value">96%</span>
        </div>
    </div>
    
    <div class="mailchimpsync-stats-card">
        <h3>Pending Syncs</h3>
        <div class="mailchimpsync-stats-row">
            <span class="mailchimpsync-stats-label">Third Parties:</span>
            <span class="mailchimpsync-stats-value">12</span>
        </div>
        <div class="mailchimpsync-stats-row">
            <span class="mailchimpsync-stats-label">Contacts:</span>
            <span class="mailchimpsync-stats-value">8</span>
        </div>
        <div class="mailchimpsync-stats-row">
            <span class="mailchimpsync-stats-label">Users:</span>
            <span class="mailchimpsync-stats-value">3</span>
        </div>
    </div>
    
    <div class="mailchimpsync-actions">
        <a href="setup.php" class="button button-primary">Configuration</a>
        <a href="fieldmapping.php" class="button button-secondary">Field Mapping</a>
        <a href="synchistory.php" class="button button-secondary">Sync History</a>
        <button type="button" class="button button-primary">Run Manual Sync</button>
    </div>
    
    <div class="mailchimpsync-stats-card">
        <h3>Module Information</h3>
        <p><strong>Module:</strong> MokoDoliChimp v1.0.0</p>
        <p><strong>Description:</strong> Bidirectional synchronization between Dolibarr and Mailchimp</p>
        <p><strong>Features:</strong> Third parties, contacts, and users sync with field mapping and real-time triggers</p>
        <p><strong>DOB Support:</strong> Date of birth field mapping included</p>
        <p><strong>Tags & Segments:</strong> Advanced audience targeting capabilities</p>
    </div>
</div>

<?php
llxFooter();
?>