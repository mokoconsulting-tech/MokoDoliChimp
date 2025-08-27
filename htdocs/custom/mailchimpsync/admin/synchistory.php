<?php
/**
 * Mailchimp Sync History Page
 * 
 * @file        synchistory.php
 * @ingroup     mailchimpsync
 * @brief       Sync history and logs page
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
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
require_once '../class/synchistory.class.php';

// Access control
restrictedArea($user, 'mailchimpsync');

// Load translation files required by the page
$langs->loadLangs(array("admin", "mailchimpsync@mailchimpsync"));

// Parameters
$action = GETPOST('action', 'alpha');
$search_entity_type = GETPOST('search_entity_type', 'alpha');
$search_status = GETPOST('search_status', 'alpha');
$search_date_start = dol_mktime(0, 0, 0, GETPOST('search_date_startmonth', 'int'), GETPOST('search_date_startday', 'int'), GETPOST('search_date_startyear', 'int'));
$search_date_end = dol_mktime(23, 59, 59, GETPOST('search_date_endmonth', 'int'), GETPOST('search_date_endday', 'int'), GETPOST('search_date_endyear', 'int'));
$limit = GETPOST('limit', 'int') ? GETPOST('limit', 'int') : $conf->liste_limit;
$sortfield = GETPOST('sortfield', 'alpha');
$sortorder = GETPOST('sortorder', 'alpha');
$page = GETPOST('page', 'int');

if (empty($page) || $page == -1) $page = 0;
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;

if (empty($sortfield)) $sortfield = "t.date_sync";
if (empty($sortorder)) $sortorder = "DESC";

/*
 * Actions
 */

if ($action == 'clear_history') {
    $sync_history = new SyncHistory($db);
    $days = GETPOST('clear_days', 'int');
    
    if ($days > 0) {
        $result = $sync_history->clearOldHistory($days);
        if ($result >= 0) {
            setEventMessages($langs->trans("HistoryCleared"), null, 'mesgs');
        } else {
            setEventMessages($langs->trans("ErrorClearingHistory"), null, 'errors');
        }
    }
}

/*
 * View
 */

$page_name = "SyncHistory";
llxHeader('', $langs->trans($page_name));

$form = new Form($db);
$formother = new FormOther($db);
$sync_history = new SyncHistory($db);

$linkback = '<a href="dashboard.php">'.$langs->trans("BackToDashboard").'</a>';
print load_fiche_titre($langs->trans($page_name), $linkback, 'mailchimpsync@mailchimpsync');

// Search form
print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
print '<input type="hidden" name="action" value="list">';
print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';

print '<table class="noborder centpercent">';
print '<tr class="liste_titre_filter">';

// Entity Type filter
print '<td class="liste_titre">';
$entity_options = array('' => $langs->trans("All"), 'thirdparty' => $langs->trans("ThirdParties"), 'contact' => $langs->trans("Contacts"), 'user' => $langs->trans("Users"));
print $form->selectarray('search_entity_type', $entity_options, $search_entity_type, 0, 0, 0, '', 0, 0, 0, '', 'maxwidth100');
print '</td>';

// Status filter
print '<td class="liste_titre">';
$status_options = array('' => $langs->trans("All"), 'success' => $langs->trans("Success"), 'error' => $langs->trans("Error"), 'pending' => $langs->trans("Pending"));
print $form->selectarray('search_status', $status_options, $search_status, 0, 0, 0, '', 0, 0, 0, '', 'maxwidth100');
print '</td>';

// Date range filter
print '<td class="liste_titre">';
print $form->selectDate($search_date_start, 'search_date_start', 0, 0, 1, '', 1, 0);
print ' - ';
print $form->selectDate($search_date_end, 'search_date_end', 0, 0, 1, '', 1, 0);
print '</td>';

print '<td class="liste_titre"></td>';
print '<td class="liste_titre"></td>';

print '<td class="liste_titre maxwidthsearch">';
$searchpicto = $form->showFilterButtons();
print $searchpicto;
print '</td>';

print '</tr>';

// Column headers
print '<tr class="liste_titre">';
print_liste_field_titre("EntityType", $_SERVER["PHP_SELF"], "t.entity_type", "", "", "", $sortfield, $sortorder);
print_liste_field_titre("Status", $_SERVER["PHP_SELF"], "t.status", "", "", "", $sortfield, $sortorder);
print_liste_field_titre("SyncDate", $_SERVER["PHP_SELF"], "t.date_sync", "", "", "", $sortfield, $sortorder);
print_liste_field_titre("EntityID", $_SERVER["PHP_SELF"], "t.entity_id", "", "", "", $sortfield, $sortorder);
print_liste_field_titre("Message", $_SERVER["PHP_SELF"], "", "", "", "", $sortfield, $sortorder);
print_liste_field_titre("", $_SERVER["PHP_SELF"], "", "", "", "", "", "");
print '</tr>';

// Get history records
$sql = "SELECT t.rowid, t.entity_type, t.entity_id, t.status, t.sync_direction, t.message, t.date_sync";
$sql .= " FROM ".MAIN_DB_PREFIX."mailchimp_sync_history as t";
$sql .= " WHERE 1 = 1";

if ($search_entity_type) {
    $sql .= " AND t.entity_type = '".$db->escape($search_entity_type)."'";
}
if ($search_status) {
    $sql .= " AND t.status = '".$db->escape($search_status)."'";
}
if ($search_date_start) {
    $sql .= " AND t.date_sync >= '".$db->idate($search_date_start)."'";
}
if ($search_date_end) {
    $sql .= " AND t.date_sync <= '".$db->idate($search_date_end)."'";
}

$nbtotalofrecords = '';
if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST)) {
    $result = $db->query($sql);
    $nbtotalofrecords = $db->num_rows($result);
    if (($page * $limit) > $nbtotalofrecords) {
        $page = 0;
        $offset = 0;
    }
}

$sql .= $db->order($sortfield, $sortorder);
$sql .= $db->plimit($limit + 1, $offset);

$resql = $db->query($sql);
if ($resql) {
    $num = $db->num_rows($resql);
    
    $i = 0;
    while ($i < min($num, $limit)) {
        $obj = $db->fetch_object($resql);
        
        print '<tr class="oddeven">';
        
        // Entity Type
        print '<td>';
        $entity_labels = array('thirdparty' => $langs->trans("ThirdParty"), 'contact' => $langs->trans("Contact"), 'user' => $langs->trans("User"));
        print isset($entity_labels[$obj->entity_type]) ? $entity_labels[$obj->entity_type] : $obj->entity_type;
        print '</td>';
        
        // Status
        print '<td>';
        if ($obj->status == 'success') {
            print '<span class="badge badge-status4 badge-status">'.$langs->trans("Success").'</span>';
        } elseif ($obj->status == 'error') {
            print '<span class="badge badge-status8 badge-status">'.$langs->trans("Error").'</span>';
        } elseif ($obj->status == 'pending') {
            print '<span class="badge badge-status3 badge-status">'.$langs->trans("Pending").'</span>';
        } else {
            print '<span class="badge badge-status5 badge-status">'.$obj->status.'</span>';
        }
        print '</td>';
        
        // Sync Date
        print '<td>';
        print dol_print_date($db->jdate($obj->date_sync), 'dayhour');
        print '</td>';
        
        // Entity ID
        print '<td>';
        print $obj->entity_id;
        print '</td>';
        
        // Message
        print '<td>';
        if ($obj->message) {
            $message = dol_escape_htmltag($obj->message);
            if (strlen($message) > 100) {
                print substr($message, 0, 100) . '...';
            } else {
                print $message;
            }
        }
        print '</td>';
        
        // Actions
        print '<td class="center">';
        if ($obj->message) {
            print '<a href="#" onclick="alert(\''.dol_escape_js($obj->message).'\'); return false;" title="'.$langs->trans("ViewFullMessage").'">';
            print img_picto($langs->trans("ViewFullMessage"), 'info');
            print '</a>';
        }
        print '</td>';
        
        print '</tr>';
        $i++;
    }
} else {
    dol_print_error($db);
}

print '</table>';
print '</form>';

// Pagination
if ($nbtotalofrecords > $limit) {
    print_barre_liste('', $page, $_SERVER["PHP_SELF"], '', $sortfield, $sortorder, '', $num, $nbtotalofrecords, '', 0, '', '', $limit);
}

// Clear history section
if ($user->rights->mailchimpsync->write) {
    print '<br>';
    print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
    print '<input type="hidden" name="token" value="'.newToken().'">';
    print '<input type="hidden" name="action" value="clear_history">';
    
    print '<table class="noborder centpercent">';
    print '<tr class="liste_titre">';
    print '<td colspan="2">'.$langs->trans("ClearHistory").'</td>';
    print '</tr>';
    
    print '<tr class="oddeven">';
    print '<td>'.$langs->trans("ClearHistoryOlderThan").'</td>';
    print '<td>';
    print '<input type="number" name="clear_days" value="30" min="1" max="365" size="5"> '.$langs->trans("days");
    print ' <input type="submit" class="button" value="'.$langs->trans("ClearHistory").'" onclick="return confirm(\''.$langs->trans("ConfirmClearHistory").'\')">';
    print '</td>';
    print '</tr>';
    
    print '</table>';
    print '</form>';
}

// End of page
llxFooter();
$db->close();
?>
