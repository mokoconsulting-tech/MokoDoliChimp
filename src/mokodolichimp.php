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
 * DEFGROUP: MokoDoliChimp.Pages
 * INGROUP: MokoDoliChimp
 * REPO: https://github.com/mokoconsulting-tech/MokoDoliChimp
 * PATH: /mokodolichimp.php
 * VERSION: 01.00.00
 * BRIEF: Main page for MokoDoliChimp module displaying configuration status
 */

$res = 0;
if (!$res && file_exists("../main.inc.php")) {
	$res = @include '../main.inc.php';
}
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include '../../main.inc.php';
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include '../../../main.inc.php';
}
if (!$res) {
	die("Main include file not found");
}

require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
dol_include_once('/mokodolichimp/class/mailchimpclient.class.php');

global $langs, $user, $db, $conf;

$langs->loadLangs(array("mokodolichimp@mokodolichimp"));

if (!$user->rights->mokodolichimp->read) {
	accessforbidden();
}

llxHeader('', $langs->trans("Module500100Name"));

print load_fiche_titre($langs->trans("Module500100Name"), '', 'object_mokodolichimp@mokodolichimp');

print '<div class="fichecenter">';

print '<div class="fichethirdleft">';
print '<div class="div-table-responsive-no-min">';

$mailchimpClient = new MailchimpClient();

if (!$mailchimpClient->isConfigured()) {
	print '<div class="error">';
	print $langs->trans("MailchimpNotConfigured");
	print ' <a href="'.DOL_URL_ROOT.'/custom/mokodolichimp/admin/setup.php">'.$langs->trans("Settings").'</a>';
	print '</div>';
} else {
	print '<div class="success">';
	print $langs->trans("Module500100Desc");
	print '</div>';
	
	print '<br>';
	
	print '<table class="noborder centpercent">';
	print '<tr class="liste_titre">';
	print '<th>'.$langs->trans("Settings").'</th>';
	print '<th></th>';
	print '</tr>';
	
	print '<tr class="oddeven">';
	print '<td>'.$langs->trans("MailchimpAPIKey").'</td>';
	print '<td>'.(getDolGlobalString('MOKODOLICHIMP_APIKEY') ? '***************' : $langs->trans("NotConfigured")).'</td>';
	print '</tr>';
	
	print '<tr class="oddeven">';
	print '<td>'.$langs->trans("MailchimpListID").'</td>';
	print '<td>'.getDolGlobalString('MOKODOLICHIMP_LISTID').'</td>';
	print '</tr>';
	
	print '<tr class="oddeven">';
	print '<td>'.$langs->trans("AutoSyncOnSave").'</td>';
	print '<td>'.(getDolGlobalString('MOKODOLICHIMP_AUTOSYNC') ? $langs->trans("Yes") : $langs->trans("No")).'</td>';
	print '</tr>';
	
	print '<tr class="oddeven">';
	print '<td>'.$langs->trans("SyncStatus").'</td>';
	print '<td>'.getDolGlobalString('MOKODOLICHIMP_STATUS', 'subscribed').'</td>';
	print '</tr>';
	
	print '</table>';
}

print '</div>';
print '</div>';

print '</div>';

llxFooter();
$db->close();
