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
		'actions' => 'integrate_actions',
		'displayTopic' => 'integrate_display_topic',
		'displayButtons' => 'integrate_display_buttons',
		'messageIndex' => 'integrate_message_index',
		'messageButtons' => 'integrate_messageindex_buttons',
		'helpAdmin' => 'integrate_helpadmin',
	);

	public function __construct()
	{
		$this->setRegistry();
	}

	public function addDisplayTopic(&$topic_selects, &$topic_tables, &$topic_parameters)
	{
		$topic_selects[] = $this->_dbField;

		// Since we're already here...
		loadCSSFile($this->bootstrapCDN, array('external' => true));
		loadCSSFile('TopicSolved.css');
	}

	public function addMessageIndex(&$message_index_selects, &$message_index_tables, &$message_index_parameters)
	{
		$message_index_selects[] = $this->_dbField;

		// Since we're already here...
		loadCSSFile($this->bootstrapCDN, array('external' => true));
		loadCSSFile('TopicSolved.css');
	}

	public function addMessageButtons()
	{
		global $context;

		if (empty($context['topics']))
			return false;

		// Append the corresponding solved class.
		foreach ($context['topics'] as $id => $topic)
		{
			// Only add any css class if the topic has been marked.
			if (empty($topic['is_solved']))
				continue;

			$context['topics'][$id]['css_class'] = $context['topics'][$id]['css_class'] . ' '. $this->_statusFields[$topic['is_solved']];
		}

		// Create the needed JS stuff!
		$injectJS  = '
	$(function()
	{';

		// Because reasons!
		foreach ($this->getStatus() as $class => $icon)
			$injectJS  .= '
		$(".'. $class .'").children(".icon").empty().addClass("'. $icon .'");';

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

		$context['topicinfo']['is_solved'] = (int) $context['topicinfo']['is_solved'];

		// Invert the roles!
		$inverted = ($context['topicinfo']['is_solved'] != 2) ? 2 : 1;

		$confirmText = $this->parser($this->text('mark_as_solved_sure'), array(
			'status' => $this->text($this->_statusFields[$inverted])
		));

		if (allowedTo($this->name .'_any') || (allowedTo($this->name .'_own') && $user_info['id'] == $context['topicinfo']['id_member_started']))
			$context['normal_buttons'][$this->name] = array(
				'text' => $this->name .'_mark_as_'. $this->_statusFields[$inverted],
				'lang' => true,
				'url' => $this->scriptUrl . '?action='. $this->name .';topic=' . $context['current_topic'] . ';is_solved='. $inverted,
				'class' => 'you_sure '. $this->_statusFields[$inverted],
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
