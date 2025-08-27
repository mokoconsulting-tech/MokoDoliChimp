<?php
/**
 * Mailchimp Sync Module Dashboard
 * 
 * @file        dashboard.php
 * @ingroup     mailchimpsync
 * @brief       Main dashboard page
 * @author      Moko Consulting <hello@mokoconsulting.tech>
 * @copyright   2025 Moko Consulting
 * @license     GNU General Public License v3.0 or later
 * @warranty    This program comes with ABSOLUTELY NO WARRANTY. This is free software.
 */

// Load Dolibarr environment
$res = 0;
if (!$res && file_exists("../../../main.inc.php")) $res = @include "../../../main.inc.php";
if (!$res && file_exists("../../../../main.inc.php")) $res = @include "../../../../main.inc.php";
if (!$res) die("Include of main fails");

require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
require_once '../class/syncservice.class.php';
require_once '../class/synchistory.class.php';
require_once '../class/mailchimpapi.class.php';

// Access control  
restrictedArea($user, 'mailchimpsync');

// Load translation files required by the page
$langs->loadLangs(array("admin", "mailchimpsync@mailchimpsync"));

// Parameters
$action = GETPOST('action', 'alpha');

/*
 * Actions
 */

if ($action == 'manual_sync') {
    $sync_service = new SyncService($db);
    $result = $sync_service->runManualSync();
    
    if ($result['success']) {
        setEventMessages($langs->trans("ManualSyncCompleted"), null, 'mesgs');
    } else {
        setEventMessages($langs->trans("ManualSyncFailed") . ': ' . $result['error'], null, 'errors');
    }
}

if ($action == 'full_sync') {
    $sync_service = new SyncService($db);
    $result = $sync_service->runFullSync();
    
    if ($result['success']) {
        setEventMessages($langs->trans("FullSyncCompleted"), null, 'mesgs');
    } else {
        setEventMessages($langs->trans("FullSyncFailed") . ': ' . $result['error'], null, 'errors');
    }
}

/*
 * View
 */

$page_name = "MailchimpSyncDashboard";
llxHeader('', $langs->trans($page_name));

$form = new Form($db);

print load_fiche_titre($langs->trans($page_name), '', 'mailchimpsync@mailchimpsync');

// Status Overview
$sync_history = new SyncHistory($db);
$mailchimp_api = new MailchimpAPI($db);

// Get connection status
$connection_status = $mailchimp_api->checkConnection();

// Get recent sync stats
$recent_syncs = $sync_history->getRecentSyncStats(24); // Last 24 hours

print '<div class="fichecenter">';

// Connection Status Card
print '<div class="div-table-responsive-no-min">';
print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<td colspan="2">'.$langs->trans("ConnectionStatus").'</td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td>'.$langs->trans("MailchimpConnection").'</td>';
print '<td>';
if ($connection_status['connected']) {
    print '<span class="badge badge-status4 badge-status">'.$langs->trans("Connected").'</span>';
    if (!empty($connection_status['account_name'])) {
        print ' - ' . dol_escape_htmltag($connection_status['account_name']);
    }
} else {
    print '<span class="badge badge-status8 badge-status">'.$langs->trans("Disconnected").'</span>';
    if (!empty($connection_status['error'])) {
        print '<br><small class="error">' . dol_escape_htmltag($connection_status['error']) . '</small>';
    }
}
print '</td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td>'.$langs->trans("AutoSyncStatus").'</td>';
print '<td>';
if ($conf->global->MAILCHIMPSYNC_AUTO_SYNC) {
    print '<span class="badge badge-status4 badge-status">'.$langs->trans("Enabled").'</span>';
    print ' - ' . $langs->trans("Interval") . ': ' . $conf->global->MAILCHIMPSYNC_SYNC_INTERVAL . ' ' . $langs->trans("seconds");
} else {
    print '<span class="badge badge-status5 badge-status">'.$langs->trans("Disabled").'</span>';
}
print '</td>';
print '</tr>';

print '</table>';
print '</div>';

// Sync Statistics
print '<br>';
print '<div class="div-table-responsive-no-min">';
print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<td colspan="3">'.$langs->trans("SyncStatistics").' ('.$langs->trans("Last24Hours").')</td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td>'.$langs->trans("TotalSyncs").'</td>';
print '<td class="center">'.$recent_syncs['total'].'</td>';
print '<td class="center">';
if ($recent_syncs['total'] > 0) {
    $success_rate = round(($recent_syncs['successful'] / $recent_syncs['total']) * 100, 1);
    print $success_rate . '% ' . $langs->trans("SuccessRate");
} else {
    print '-';
}
print '</td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td>'.$langs->trans("SuccessfulSyncs").'</td>';
print '<td class="center"><span class="badge badge-status4">'.$recent_syncs['successful'].'</span></td>';
print '<td class="center">';
if ($recent_syncs['successful'] > 0) {
    print $langs->trans("LastSuccess") . ': ' . dol_print_date($recent_syncs['last_success'], 'dayhour');
}
print '</td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td>'.$langs->trans("FailedSyncs").'</td>';
print '<td class="center"><span class="badge badge-status8">'.$recent_syncs['failed'].'</span></td>';
print '<td class="center">';
if ($recent_syncs['failed'] > 0) {
    print $langs->trans("LastFailure") . ': ' . dol_print_date($recent_syncs['last_failure'], 'dayhour');
}
print '</td>';
print '</tr>';

print '</table>';
print '</div>';

// Entity Sync Status
print '<br>';
print '<div class="div-table-responsive-no-min">';
print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("EntityType").'</td>';
print '<td class="center">'.$langs->trans("PendingSync").'</td>';
print '<td class="center">'.$langs->trans("LastSync").'</td>';
print '<td class="center">'.$langs->trans("Status").'</td>';
print '</tr>';

// Get pending sync counts
$pending_counts = $sync_history->getPendingSyncCounts();

$entity_types = array(
    'thirdparty' => $langs->trans("ThirdParties"),
    'contact' => $langs->trans("Contacts"),
    'user' => $langs->trans("Users")
);

foreach ($entity_types as $type => $label) {
    $last_sync = $sync_history->getLastSyncForType($type);
    $pending = isset($pending_counts[$type]) ? $pending_counts[$type] : 0;
    
    print '<tr class="oddeven">';
    print '<td>'.$label.'</td>';
    print '<td class="center">';
    if ($pending > 0) {
        print '<span class="badge badge-status3">'.$pending.'</span>';
    } else {
        print '<span class="badge badge-status4">0</span>';
    }
    print '</td>';
    print '<td class="center">';
    if ($last_sync) {
        print dol_print_date($last_sync['date_sync'], 'dayhour');
    } else {
        print $langs->trans("Never");
    }
    print '</td>';
    print '<td class="center">';
    if ($last_sync) {
        if ($last_sync['status'] == 'success') {
            print '<span class="badge badge-status4 badge-status">'.$langs->trans("Success").'</span>';
        } else {
            print '<span class="badge badge-status8 badge-status">'.$langs->trans("Error").'</span>';
        }
    } else {
        print '<span class="badge badge-status5 badge-status">'.$langs->trans("NotSynced").'</span>';
    }
    print '</td>';
    print '</tr>';
}

print '</table>';
print '</div>';

// Quick Actions
print '<br>';
print '<div class="tabsAction">';

if ($user->rights->mailchimpsync->write) {
    // Manual Sync Button
    print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'" style="display: inline-block;">';
    print '<input type="hidden" name="token" value="'.newToken().'">';
    print '<input type="hidden" name="action" value="manual_sync">';
    print '<input type="submit" class="butAction" value="'.$langs->trans("RunManualSync").'">';
    print '</form>';
    
    // Full Sync Button
    print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'" style="display: inline-block;">';
    print '<input type="hidden" name="token" value="'.newToken().'">';
    print '<input type="hidden" name="action" value="full_sync">';
    print '<input type="submit" class="butAction" value="'.$langs->trans("RunFullSync").'" onclick="return confirm(\''.$langs->trans("ConfirmFullSync").'\')">';
    print '</form>';
}

// View History Button
print '<a class="butAction" href="synchistory.php">'.$langs->trans("ViewSyncHistory").'</a>';

// Configuration Button
if ($user->rights->mailchimpsync->write) {
    print '<a class="butAction" href="setup.php">'.$langs->trans("Configuration").'</a>';
    print '<a class="butAction" href="fieldmapping.php">'.$langs->trans("FieldMapping").'</a>';
}

print '</div>';

print '</div>';

// End of page
llxFooter();
$db->close();
?>
