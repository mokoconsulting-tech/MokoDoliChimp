<?php
/**
 * Mailchimp Sync Module Setup Page
 * 
 * @file        setup.php
 * @ingroup     mailchimpsync
 * @brief       Configuration setup page
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
require_once '../class/mailchimpapi.class.php';

// Access control
restrictedArea($user, 'mailchimpsync');

// Load translation files required by the page
$langs->loadLangs(array("admin", "mailchimpsync@mailchimpsync"));

// Parameters
$action = GETPOST('action', 'alpha');
$value = GETPOST('value', 'alpha');
$label = GETPOST('label', 'alpha');
$scandir = GETPOST('scandir', 'alpha');

/*
 * Actions
 */

if ($action == 'updateMask') {
    $maskconstname = GETPOST('maskconstname', 'alpha');
    $maskvalue = GETPOST('maskvalue', 'alpha');
    
    if ($maskconstname && preg_match('/^[A-Z_]+$/', $maskconstname)) {
        $res = dolibarr_set_const($db, $maskconstname, $maskvalue, 'chaine', 0, '', $conf->entity);
        if (!$res) {
            $error++;
        }
    }
}

if ($action == 'set') {
    $constname = GETPOST('constname', 'alpha');
    $constvalue = GETPOST('constvalue', 'alpha');
    
    $res = dolibarr_set_const($db, $constname, $constvalue, 'chaine', 0, '', $conf->entity);
    if (!$res) {
        $error++;
    }
}

if ($action == 'del') {
    $constname = GETPOST('constname', 'alpha');
    
    $res = dolibarr_del_const($db, $constname, $conf->entity);
    if (!$res) {
        $error++;
    }
}

if ($action == 'test_connection') {
    $api_key = GETPOST('api_key', 'alpha');
    $server_prefix = GETPOST('server_prefix', 'alpha');
    
    if ($api_key && $server_prefix) {
        $mailchimp = new MailchimpAPI($db);
        $test_result = $mailchimp->testConnection($api_key, $server_prefix);
        
        if ($test_result['success']) {
            setEventMessages($langs->trans("ConnectionTestSuccess"), null, 'mesgs');
        } else {
            setEventMessages($langs->trans("ConnectionTestFailed") . ': ' . $test_result['error'], null, 'errors');
        }
    }
}

/*
 * View
 */

$page_name = "MailchimpSyncSetup";
llxHeader('', $langs->trans($page_name));

$form = new Form($db);
$formadmin = new FormAdmin($db);

$linkback = '<a href="'.DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1">'.$langs->trans("BackToModuleList").'</a>';

print load_fiche_titre($langs->trans($page_name), $linkback, 'title_setup');

print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="action" value="set">';

print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Parameter").'</td>';
print '<td>'.$langs->trans("Value").'</td>';
print '<td>'.$langs->trans("Action").'</td>';
print '</tr>';

// API Key
print '<tr class="oddeven">';
print '<td>'.$langs->trans("MailchimpAPIKey").'</td>';
print '<td>';
print '<input type="password" name="api_key" value="'.dol_escape_htmltag($conf->global->MAILCHIMPSYNC_API_KEY).'" size="40">';
print '</td>';
print '<td>';
print '<input type="hidden" name="constname" value="MAILCHIMPSYNC_API_KEY">';
print '<input type="submit" class="button" value="'.$langs->trans("Save").'">';
print '</td>';
print '</tr>';

// Server Prefix
print '<tr class="oddeven">';
print '<td>'.$langs->trans("MailchimpServerPrefix").'</td>';
print '<td>';
print '<input type="text" name="server_prefix" value="'.dol_escape_htmltag($conf->global->MAILCHIMPSYNC_SERVER_PREFIX).'" size="20" placeholder="us1">';
print '</td>';
print '<td>';
print '<input type="hidden" name="constname" value="MAILCHIMPSYNC_SERVER_PREFIX">';
print '<input type="submit" class="button" value="'.$langs->trans("Save").'">';
print '</td>';
print '</tr>';

// Default List ID
print '<tr class="oddeven">';
print '<td>'.$langs->trans("DefaultListID").'</td>';
print '<td>';
print '<input type="text" name="default_list_id" value="'.dol_escape_htmltag($conf->global->MAILCHIMPSYNC_DEFAULT_LIST_ID).'" size="30">';
print '</td>';
print '<td>';
print '<input type="hidden" name="constname" value="MAILCHIMPSYNC_DEFAULT_LIST_ID">';
print '<input type="submit" class="button" value="'.$langs->trans("Save").'">';
print '</td>';
print '</tr>';

// Auto Sync
print '<tr class="oddeven">';
print '<td>'.$langs->trans("AutoSync").'</td>';
print '<td>';
if ($conf->use_javascript_ajax) {
    print ajax_constantonoff('MAILCHIMPSYNC_AUTO_SYNC');
} else {
    $arrval = array('0' => $langs->trans("No"), '1' => $langs->trans("Yes"));
    print $form->selectarray("auto_sync", $arrval, $conf->global->MAILCHIMPSYNC_AUTO_SYNC);
}
print '</td>';
print '<td>';
if (!$conf->use_javascript_ajax) {
    print '<input type="hidden" name="constname" value="MAILCHIMPSYNC_AUTO_SYNC">';
    print '<input type="submit" class="button" value="'.$langs->trans("Save").'">';
}
print '</td>';
print '</tr>';

// Sync Interval
print '<tr class="oddeven">';
print '<td>'.$langs->trans("SyncInterval").'</td>';
print '<td>';
print '<input type="number" name="sync_interval" value="'.dol_escape_htmltag($conf->global->MAILCHIMPSYNC_SYNC_INTERVAL).'" size="10" min="300"> '.$langs->trans("seconds");
print '</td>';
print '<td>';
print '<input type="hidden" name="constname" value="MAILCHIMPSYNC_SYNC_INTERVAL">';
print '<input type="submit" class="button" value="'.$langs->trans("Save").'">';
print '</td>';
print '</tr>';

print '</table>';
print '</form>';

// Test Connection Section
print '<br>';
print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="action" value="test_connection">';

print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<td colspan="2">'.$langs->trans("TestConnection").'</td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td>'.$langs->trans("TestMailchimpConnection").'</td>';
print '<td>';
print '<input type="hidden" name="api_key" value="'.dol_escape_htmltag($conf->global->MAILCHIMPSYNC_API_KEY).'">';
print '<input type="hidden" name="server_prefix" value="'.dol_escape_htmltag($conf->global->MAILCHIMPSYNC_SERVER_PREFIX).'">';
print '<input type="submit" class="button" value="'.$langs->trans("TestConnection").'">';
print '</td>';
print '</tr>';

print '</table>';
print '</form>';

print '<br>';

// Info section
print info_admin($langs->trans("MailchimpSyncInfo"));

// End of page
llxFooter();
$db->close();
?>
