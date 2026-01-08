<?php
/* Copyright (C) 2025 Moko Consulting <hello@mokoconsulting.tech>
 *
 * This file is part of a Moko Consulting project.
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * FILE INFORMATION
 * DEFGROUP: MokoDoliChimp.Admin
 * INGROUP: MokoDoliChimp
 * REPO: https://github.com/mokoconsulting-tech/MokoDoliChimp
 * PATH: /src/admin/setup.php
 * VERSION: 01.00.00
 * BRIEF: Setup and configuration page for MokoDoliChimp module
 */

$res = 0;
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include '../../main.inc.php';
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include '../../../main.inc.php';
}
if (!$res && file_exists("../../../../main.inc.php")) {
	$res = @include '../../../../main.inc.php';
}
if (!$res) {
	die("Main include file not found");
}

require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';

global $langs, $user, $db, $conf;

$langs->loadLangs(array("admin", "mokodolichimp@mokodolichimp"));

if (!$user->admin) {
	accessforbidden();
}

$action = GETPOST('action', 'aZ09');

if ($action == 'save') {
	$apikey = GETPOST('MOKODOLICHIMP_APIKEY', 'alpha');
	$listid = GETPOST('MOKODOLICHIMP_LISTID', 'alpha');
	$autosync = GETPOST('MOKODOLICHIMP_AUTOSYNC', 'int');
	$status = GETPOST('MOKODOLICHIMP_STATUS', 'alpha');
	
	if ($apikey) {
		$serverPrefix = '';
		if (strpos($apikey, '-') !== false) {
			$parts = explode('-', $apikey);
			$serverPrefix = end($parts);
		}
		dolibarr_set_const($db, 'MOKODOLICHIMP_APIKEY', $apikey, 'chaine', 0, '', $conf->entity);
		dolibarr_set_const($db, 'MOKODOLICHIMP_SERVER_PREFIX', $serverPrefix, 'chaine', 0, '', $conf->entity);
	}
	
	if ($listid) {
		dolibarr_set_const($db, 'MOKODOLICHIMP_LISTID', $listid, 'chaine', 0, '', $conf->entity);
	}
	
	dolibarr_set_const($db, 'MOKODOLICHIMP_AUTOSYNC', $autosync ? '1' : '0', 'chaine', 0, '', $conf->entity);
	dolibarr_set_const($db, 'MOKODOLICHIMP_STATUS', $status ? $status : 'subscribed', 'chaine', 0, '', $conf->entity);
	
	setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
	header("Location: ".$_SERVER["PHP_SELF"]);
	exit;
}

llxHeader('', $langs->trans("MokoDoliChimpSetup"));

$linkback = '<a href="'.DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1">'.$langs->trans("BackToModuleList").'</a>';
print load_fiche_titre($langs->trans("MokoDoliChimpSetup"), $linkback, 'title_setup');

print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="action" value="save">';

print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Settings").'</td>';
print '<td></td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td>'.$langs->trans("MailchimpAPIKey").'</td>';
print '<td><input type="text" name="MOKODOLICHIMP_APIKEY" size="60" value="'.getDolGlobalString('MOKODOLICHIMP_APIKEY').'">';
print '<br><span class="opacitymedium">'.$langs->trans("MailchimpAPIKeyHelp").'</span>';
print '</td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td>'.$langs->trans("MailchimpListID").'</td>';
print '<td><input type="text" name="MOKODOLICHIMP_LISTID" size="40" value="'.getDolGlobalString('MOKODOLICHIMP_LISTID').'">';
print '<br><span class="opacitymedium">'.$langs->trans("MailchimpListIDHelp").'</span>';
print '</td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td>'.$langs->trans("AutoSyncOnSave").'</td>';
print '<td>';
print '<input type="checkbox" name="MOKODOLICHIMP_AUTOSYNC" value="1"'.(getDolGlobalString('MOKODOLICHIMP_AUTOSYNC') ? ' checked' : '').'>';
print '<br><span class="opacitymedium">'.$langs->trans("AutoSyncOnSaveHelp").'</span>';
print '</td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td>'.$langs->trans("SyncStatus").'</td>';
print '<td>';
print '<select name="MOKODOLICHIMP_STATUS">';
print '<option value="subscribed"'.(getDolGlobalString('MOKODOLICHIMP_STATUS') == 'subscribed' ? ' selected' : '').'>'.$langs->trans("Subscribed").'</option>';
print '<option value="pending"'.(getDolGlobalString('MOKODOLICHIMP_STATUS') == 'pending' ? ' selected' : '').'>'.$langs->trans("Pending").'</option>';
print '</select>';
print '<br><span class="opacitymedium">'.$langs->trans("SyncStatusHelp").'</span>';
print '</td>';
print '</tr>';

print '</table>';

print '<div class="center"><input type="submit" class="button" value="'.$langs->trans("Save").'"></div>';
print '</form>';

llxFooter();
$db->close();
