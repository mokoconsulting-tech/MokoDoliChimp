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
 * DEFGROUP: MokoDoliChimp.Core
 * INGROUP: MokoDoliChimp
 * REPO: https://github.com/mokoconsulting-tech/MokoDoliChimp
 * PATH: /core/modules/modMokoDoliChimp.class.php
 * VERSION: 01.00.00
 * BRIEF: Module descriptor and activation file for MokoDoliChimp
 */

include_once DOL_DOCUMENT_ROOT.'/core/modules/DolibarrModules.class.php';

/**
 * Description and activation class for module MokoDoliChimp
 */
class modMokoDoliChimp extends DolibarrModules
{
	/**
	 * Constructor. Define names, constants, directories, boxes, permissions
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		global $langs, $conf;

		$this->db = $db;

		$this->numero = 500100;
		$this->rights_class = 'mokodolichimp';
		$this->family = "technic";
		$this->module_position = '90';
		$this->name = preg_replace('/^mod/i', '', get_class($this));
		$this->description = "Sync Dolibarr contacts and users with Mailchimp";
		$this->descriptionlong = "MokoDoliChimp enables seamless synchronization between Dolibarr contacts/users and Mailchimp subscriber lists. "
			."Automatically sync contact information, manage subscriptions, and keep your mailing lists up-to-date.";
		$this->editor_name = 'Moko Consulting';
		$this->editor_url = 'https://mokoconsulting.tech';
		$this->version = '1.0.0';
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		$this->picto = 'generic';

		$this->module_parts = array(
			'triggers' => 1,
			'hooks' => array(
				'contactcard',
				'usercard',
				'thirdpartycard'
			)
		);

		$this->dirs = array();

		$this->config_page_url = array("setup.php@mokodolichimp");

		$this->hidden = false;
		$this->depends = array();
		$this->requiredby = array();
		$this->conflictwith = array();
		$this->langfiles = array("mokodolichimp@mokodolichimp");
		$this->phpmin = array(7, 0);
		$this->need_dolibarr_version = array(11, 0);
		$this->warnings_activation = array();
		$this->warnings_activation_ext = array();

		$this->const = array();

		if (!isset($conf->mokodolichimp) || !isset($conf->mokodolichimp->enabled)) {
			$conf->mokodolichimp = new stdClass();
			$conf->mokodolichimp->enabled = 0;
		}

		$this->tabs = array();

		$this->dictionaries = array();

		$this->boxes = array();

		$this->cronjobs = array();

		$this->rights = array();
		$r = 0;

		$r++;
		$this->rights[$r][0] = $this->numero + $r;
		$this->rights[$r][1] = 'Read MokoDoliChimp';
		$this->rights[$r][4] = 'read';
		$this->rights[$r][5] = '';

		$r++;
		$this->rights[$r][0] = $this->numero + $r;
		$this->rights[$r][1] = 'Configure MokoDoliChimp';
		$this->rights[$r][4] = 'configure';
		$this->rights[$r][5] = '';

		$r++;
		$this->rights[$r][0] = $this->numero + $r;
		$this->rights[$r][1] = 'Sync contacts with Mailchimp';
		$this->rights[$r][4] = 'sync';
		$this->rights[$r][5] = '';

		$this->menu = array();
	}

	/**
	 * Function called when module is enabled.
	 * The init function adds tabs, constants, boxes, permissions and menus
	 * (defined in constructor) into Dolibarr database.
	 * It also creates data directories
	 *
	 * @param string $options Options when enabling module ('', 'noboxes')
	 * @return int 1 if OK, 0 if KO
	 */
	public function init($options = '')
	{
		global $conf, $langs;

		$result = $this->_load_tables('/mokodolichimp/sql/');
		if ($result < 0) {
			return -1;
		}

		return $this->_init($this->db, $options);
	}

	/**
	 * Function called when module is disabled.
	 * Remove from database constants, boxes and permissions from Dolibarr database.
	 * Data directories are not deleted
	 *
	 * @param string $options Options when disabling module ('', 'noboxes')
	 * @return int 1 if OK, 0 if KO
	 */
	public function remove($options = '')
	{
		return $this->_remove($this->db, $options);
	}
}
