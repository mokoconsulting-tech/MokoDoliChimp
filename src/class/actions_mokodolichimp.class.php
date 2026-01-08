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
 * DEFGROUP: MokoDoliChimp.Classes
 * INGROUP: MokoDoliChimp
 * REPO: https://github.com/mokoconsulting-tech/MokoDoliChimp
 * PATH: /src/class/actions_mokodolichimp.class.php
 * VERSION: 01.00.00
 * BRIEF: Hook implementations for automatic and manual Mailchimp sync actions
 */

dol_include_once('/mokodolichimp/class/mailchimpclient.class.php');

/**
 * Class ActionsmokoDoliChimp
 */
class ActionsMokoDoliChimp
{
	/**
	 * @var DoliDB Database handler
	 */
	public $db;

	/**
	 * @var array Hook results
	 */
	public $results = array();

	/**
	 * @var string String displayed by executeHook() immediately after return
	 */
	public $resprints;

	/**
	 * @var array Errors
	 */
	public $errors = array();

	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}

	/**
	 * Add sync button to contact card
	 *
	 * @param array $parameters Hook parameters
	 * @param object $object Object
	 * @param string $action Action
	 * @return int 0 on success
	 */
	public function formObjectOptions($parameters, &$object, &$action)
	{
		global $conf, $langs, $user;

		if (empty($conf->mokodolichimp->enabled)) {
			return 0;
		}

		$contexts = explode(':', $parameters['context']);
		
		if (in_array('contactcard', $contexts) || in_array('usercard', $contexts)) {
			$langs->load("mokodolichimp@mokodolichimp");
			
			if ($user->rights->mokodolichimp->sync) {
				$email = '';
				$firstName = '';
				$lastName = '';
				
				if (in_array('contactcard', $contexts) && isset($object->email)) {
					$email = $object->email;
					$firstName = isset($object->firstname) ? $object->firstname : '';
					$lastName = isset($object->lastname) ? $object->lastname : '';
				} elseif (in_array('usercard', $contexts) && isset($object->email)) {
					$email = $object->email;
					$firstName = isset($object->firstname) ? $object->firstname : '';
					$lastName = isset($object->lastname) ? $object->lastname : '';
				}
				
				if ($email) {
					$this->resprints = '<div class="tabsAction">';
					$this->resprints .= '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=sync_mailchimp">';
					$this->resprints .= $langs->trans("SyncToMailchimp");
					$this->resprints .= '</a>';
					$this->resprints .= '</div>';
				}
			}
		}

		return 0;
	}

	/**
	 * Handle sync action
	 *
	 * @param array $parameters Hook parameters
	 * @param object $object Object
	 * @param string $action Action
	 * @return int 0 on success
	 */
	public function doActions($parameters, &$object, &$action)
	{
		global $conf, $langs, $user;

		if (empty($conf->mokodolichimp->enabled)) {
			return 0;
		}

		if ($action == 'sync_mailchimp') {
			$langs->load("mokodolichimp@mokodolichimp");
			
			if ($user->rights->mokodolichimp->sync) {
				$email = '';
				$firstName = '';
				$lastName = '';
				
				$contexts = explode(':', $parameters['context']);
				
				if (in_array('contactcard', $contexts) && isset($object->email)) {
					$email = $object->email;
					$firstName = isset($object->firstname) ? $object->firstname : '';
					$lastName = isset($object->lastname) ? $object->lastname : '';
				} elseif (in_array('usercard', $contexts) && isset($object->email)) {
					$email = $object->email;
					$firstName = isset($object->firstname) ? $object->firstname : '';
					$lastName = isset($object->lastname) ? $object->lastname : '';
				}
				
				if ($email) {
					$mailchimpClient = new MailchimpClient();
					
					if (!$mailchimpClient->isConfigured()) {
						setEventMessages($langs->trans("MailchimpNotConfigured"), null, 'errors');
					} else {
						$result = $mailchimpClient->syncSubscriber($email, $firstName, $lastName);
						
						if ($result) {
							setEventMessages($langs->trans("SyncSuccess"), null, 'mesgs');
						} else {
							$error = $mailchimpClient->getLastError();
							setEventMessages($langs->trans("SyncError", $error), null, 'errors');
						}
					}
				}
			}
			
			$action = '';
		}

		return 0;
	}

	/**
	 * Auto-sync on contact or user save
	 *
	 * @param array $parameters Hook parameters
	 * @param object $object Object
	 * @param string $action Action
	 * @return int 0 on success
	 */
	public function afterObjectCreate($parameters, &$object, &$action)
	{
		return $this->autoSyncToMailchimp($parameters, $object, $action);
	}

	/**
	 * Auto-sync on contact or user update
	 *
	 * @param array $parameters Hook parameters
	 * @param object $object Object
	 * @param string $action Action
	 * @return int 0 on success
	 */
	public function afterObjectUpdate($parameters, &$object, &$action)
	{
		return $this->autoSyncToMailchimp($parameters, $object, $action);
	}

	/**
	 * Auto-sync to Mailchimp if enabled
	 *
	 * @param array $parameters Hook parameters
	 * @param object $object Object
	 * @param string $action Action
	 * @return int 0 on success
	 */
	private function autoSyncToMailchimp($parameters, &$object, &$action)
	{
		global $conf, $langs;

		if (empty($conf->mokodolichimp->enabled)) {
			return 0;
		}

		if (!getDolGlobalString('MOKODOLICHIMP_AUTOSYNC')) {
			return 0;
		}

		$contexts = explode(':', $parameters['context']);
		
		if ((in_array('contactcard', $contexts) || in_array('usercard', $contexts)) && isset($object->email) && $object->email) {
			$email = $object->email;
			$firstName = isset($object->firstname) ? $object->firstname : '';
			$lastName = isset($object->lastname) ? $object->lastname : '';
			
			$mailchimpClient = new MailchimpClient();
			
			if ($mailchimpClient->isConfigured()) {
				$mailchimpClient->syncSubscriber($email, $firstName, $lastName);
			}
		}

		return 0;
	}
}
