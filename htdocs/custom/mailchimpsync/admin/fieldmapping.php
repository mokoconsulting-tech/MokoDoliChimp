<?php
/**
 * Mailchimp Sync Field Mapping Configuration
 * 
 * @file        fieldmapping.php
 * @ingroup     mailchimpsync
 * @brief       Field mapping configuration page
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
require_once '../class/fieldmapping.class.php';
require_once '../class/mailchimpapi.class.php';

// Access control
restrictedArea($user, 'mailchimpsync');

// Load translation files required by the page
$langs->loadLangs(array("admin", "mailchimpsync@mailchimpsync"));

// Parameters
$action = GETPOST('action', 'alpha');
$entity_type = GETPOST('entity_type', 'alpha');

/*
 * Actions
 */

if ($action == 'save_mapping') {
    $field_mapping = new FieldMapping($db);
    $entity_type = GETPOST('entity_type', 'alpha');
    $mappings = GETPOST('mappings', 'array');
    
    $result = $field_mapping->saveMappings($entity_type, $mappings);
    
    if ($result > 0) {
        setEventMessages($langs->trans("MappingsSaved"), null, 'mesgs');
    } else {
        setEventMessages($langs->trans("ErrorSavingMappings"), null, 'errors');
    }
}

if ($action == 'reset_mapping') {
    $field_mapping = new FieldMapping($db);
    $entity_type = GETPOST('entity_type', 'alpha');
    
    $result = $field_mapping->resetMappings($entity_type);
    
    if ($result >= 0) {
        setEventMessages($langs->trans("MappingsReset"), null, 'mesgs');
    } else {
        setEventMessages($langs->trans("ErrorResettingMappings"), null, 'errors');
    }
}

/*
 * View
 */

$page_name = "FieldMapping";
llxHeader('', $langs->trans($page_name));

$form = new Form($db);
$field_mapping = new FieldMapping($db);
$mailchimp_api = new MailchimpAPI($db);

$linkback = '<a href="dashboard.php">'.$langs->trans("BackToDashboard").'</a>';
print load_fiche_titre($langs->trans($page_name), $linkback, 'mailchimpsync@mailchimpsync');

// Entity type selector
if (empty($entity_type)) $entity_type = 'thirdparty';

print '<form method="GET" action="'.$_SERVER['PHP_SELF'].'">';
print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("SelectEntityType").'</td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td>';
$entity_options = array(
    'thirdparty' => $langs->trans("ThirdParties"),
    'contact' => $langs->trans("Contacts"),
    'user' => $langs->trans("Users")
);
print $form->selectarray("entity_type", $entity_options, $entity_type, 0, 0, 0, '', 0, 0, 0, '', 'onchange="this.form.submit()"');
print '</td>';
print '</tr>';
print '</table>';
print '</form>';

print '<br>';

// Get available fields
$dolibarr_fields = $field_mapping->getDolibarrFields($entity_type);
$mailchimp_fields = $mailchimp_api->getAvailableFields();
$current_mappings = $field_mapping->getMappings($entity_type);

// Field mapping form
print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="action" value="save_mapping">';
print '<input type="hidden" name="entity_type" value="'.$entity_type.'">';

print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("DolibarrField").'</td>';
print '<td>'.$langs->trans("MailchimpField").'</td>';
print '<td>'.$langs->trans("Required").'</td>';
print '<td>'.$langs->trans("SyncDirection").'</td>';
print '</tr>';

foreach ($dolibarr_fields as $dol_field => $field_info) {
    $current_mapping = isset($current_mappings[$dol_field]) ? $current_mappings[$dol_field] : array();
    
    print '<tr class="oddeven">';
    
    // Dolibarr field
    print '<td>';
    print '<strong>' . $langs->trans($field_info['label']) . '</strong>';
    if (!empty($field_info['help'])) {
        print '<br><small class="opacitymedium">' . $field_info['help'] . '</small>';
    }
    print '</td>';
    
    // Mailchimp field
    print '<td>';
    $mailchimp_options = array('' => $langs->trans("NotMapped"));
    foreach ($mailchimp_fields as $mc_field => $mc_info) {
        $mailchimp_options[$mc_field] = $mc_info['name'] . ' (' . $mc_info['type'] . ')';
    }
    $selected_mc_field = isset($current_mapping['mailchimp_field']) ? $current_mapping['mailchimp_field'] : '';
    print $form->selectarray("mappings[$dol_field][mailchimp_field]", $mailchimp_options, $selected_mc_field);
    print '</td>';
    
    // Required
    print '<td class="center">';
    if ($field_info['required']) {
        print '<span class="badge badge-status8">'.$langs->trans("Yes").'</span>';
    } else {
        print '<span class="opacitymedium">'.$langs->trans("No").'</span>';
    }
    print '</td>';
    
    // Sync direction
    print '<td>';
    $direction_options = array(
        '' => $langs->trans("NoSync"),
        'dolibarr_to_mailchimp' => $langs->trans("DolibarrToMailchimp"),
        'mailchimp_to_dolibarr' => $langs->trans("MailchimpToDolibarr"),
        'bidirectional' => $langs->trans("Bidirectional")
    );
    $selected_direction = isset($current_mapping['sync_direction']) ? $current_mapping['sync_direction'] : '';
    print $form->selectarray("mappings[$dol_field][sync_direction]", $direction_options, $selected_direction);
    print '</td>';
    
    print '</tr>';
}

print '</table>';

// Save/Reset buttons
print '<div class="tabsAction">';
print '<input type="submit" class="butAction" value="'.$langs->trans("SaveMappings").'">';
print '</form>';

print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'" style="display: inline-block;">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="action" value="reset_mapping">';
print '<input type="hidden" name="entity_type" value="'.$entity_type.'">';
print '<input type="submit" class="butActionDelete" value="'.$langs->trans("ResetMappings").'" onclick="return confirm(\''.$langs->trans("ConfirmResetMappings").'\')">';
print '</form>';
print '</div>';

// Mapping tips
print '<br>';
print '<div class="info">';
print '<strong>'.$langs->trans("MappingTips").':</strong><br>';
print '• ' . $langs->trans("MappingTip1") . '<br>';
print '• ' . $langs->trans("MappingTip2") . '<br>';
print '• ' . $langs->trans("MappingTip3") . '<br>';
print '• ' . $langs->trans("MappingTip4");
print '</div>';

// End of page
llxFooter();
$db->close();
?>
