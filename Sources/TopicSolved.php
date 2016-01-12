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

class TopicSolved extends TopicSolvedTools
{
	public $name = __CLASS__;
	public $bootstrapCDN = '//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css';

	// Define the hooks we are going to use
	protected $_availableHooks = array(
		'displayTopic' => 'integrate_display_topic',
		'displayButtons' => 'integrate_display_buttons',
		'messageIndex' => 'integrate_message_index',
		'messageButtons' => 'integrate_messageindex_buttons',
		'adminArea' => 'integrate_admin_areas',
		'permissions' => 'integrate_load_permissions',
		'helpAdmin' => 'integrate_helpadmin',
	);

	public function __construct()
	{
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
		$topic_selects[] = 't.is_solved';

		// Since we're already here...
		loadCSSFile($this->bootstrapCDN, array('external' => true));
		loadCSSFile('topicSolved.css');
	}

	public function addMessageIndex(&$message_index_selects, &$message_index_tables, &$message_index_parameters)
	{
		$message_index_selects[] = 't.is_solved';

		// Since we're already here...
		loadCSSFile($this->bootstrapCDN, array('external' => true));
		loadCSSFile('topicSolved.css');
	}

	public function addMessageButtons()
	{
		global $context;

		if (empty($context['topics']))
			return false;

		// Append the corresponding solved class.
		foreach ($context['topics'] as $id => $topic)
		{
			// Only add any css class if the topic has been marked as solved.
			$isSolved = empty($topic['is_solved']) ? '' : 'solved';

			if (!empty($isSolved) && !empty($this->_topicStatus[$isSolved]))
				$context['topics'][$id]['css_class'] = $context['topics'][$id]['css_class'] . ' '. $isSolved;
		}

		// Create the needed JS stuff!
		$injectJS  = '
	$(function()
	{';

		// Because reasons!
		foreach ($this->_topicStatus as $css => $icon)
			$injectJS  .= '
		$(".'. $css .'").children(".icon").empty().addClass("fa '. $icon .'");';

		// Close the JS
		$injectJS  .= '
	});';

		// Add our lovely JS!
		addInlineJavascript($injectJS, true);
	}

	public function addDisplayButtons()
	{
		global $context, $user_info;

		loadLanguage($this->name);

		// Invert the roles!
		$isSolved = empty($context['topicinfo']['is_solved']) ? 'solved' : 'notsolved';

		$confirmText = $this->parser($this->text('mark_as_solved_sure'), array(
			'status' => $this->text($isSolved)
		));

		if (allowedTo($this->name .'_any') || (allowedTo($this->name .'_own') && $user_info['id'] == $context['topicinfo']['id_member_started']))
			$context['normal_buttons'][$this->name] = array(
				'text' => $this->name .'_mark_as_'. $isSolved,
				'lang' => true,
				'url' => $this->scriptUrl . '?action='. $this->name .';topic=' . $context['current_topic'] . ';'. $isSolved,
				'class' => 'you_sure '. $isSolved,
				'custom' => 'data-confirm="'. $confirmText .'"'
			);
	}
}

/*
* So many fish there in the sea
* I wanted you, you wanted me
* That's just a phase, it's got to pass
* I was a train moving too fast
*/
