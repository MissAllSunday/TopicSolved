<?php

/**
 * @package Topic Solved mod
 * @version 1.0
 * @author Suki <missallsunday@simplemachines.org>
 * @copyright 2012 Suki
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

/*
 * Version: MPL 2.0
 *
 * This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0.
 * If a copy of the MPL was not distributed with this file,
 * You can obtain one at http://mozilla.org/MPL/2.0/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 *
 */

if (!defined('SMF'))
	die('No direct access!');

// Use Ohara! manually :(
require_once ($sourcedir .'/ohara/src/Suki/Ohara.php');
require_once ($sourcedir .'/TopicSolvedTools.php');

use Suki\Ohara as Suki;

class TopicSolved extends Suki
{
	public $name = __CLASS__;

	// Define the hooks we are going to use
	protected $_availableHooks = array(
		'credits' => 'integrate_credits',
		'displayTopic' => 'integrate_display_topic',
		'adminArea' => 'integrate_admin_areas',
		'permissions' => 'integrate_load_permissions',
		'helpAdmin' => 'integrate_helpadmin',
	);

	public function __construct()
	{
		$this->_tools = new TopicSolvedTools();
		$this->setRegistry();
	}

	public function addPermissions(&$permissionGroups, &$permissionList)
	{
		loadLanguage($this->name);

		$permissionGroups['membergroup']['simple'] = array($this->name .'_per_simple');
		$permissionGroups['membergroup']['classic'] = array($this->name .'_per_classic');

		$permissionList['membergroup'][$this->name] = array(
			true,
			$this->name .'_per_simple',
			$this->name .'_per_classic');
	}

	public function addAdminArea(&$areas)
	{
		$areas['config']['areas'][$this->name] = array(
			'label' => $this->text('modName'),
			'file' => $this->name .'Admin.php',
			'function' => 'TopicSolvedAdmin::call#',
			'icon' => 'posts',
			'subsections' => array(
				'settings' => array($this->text('modName')),
			),
		);
	}

	public function addDisplayTopic(&$topic_selects, &$topic_tables, &$topic_parameters)
	{
		// If $topic_selects is empty, make sure to add a , at the beginning
		$topic_selects[] = 't.is_solved';
	}

	public function task()
	{

	}
}