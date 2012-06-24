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
	die('Hacking attempt...');

/* Autoload */
function __autoload($class_name)
{
	global $sourcedir;

	$file_path = $sourcedir.TopicSolved::$_folder.$class_name . '.php';

	if(file_exists($file_path))
		require_once($file_path);

	else
		return false;
}

class TopicSolved
{
	public static $_name = 'TopicSolved';
	public static $_folder = '/TopicSolved/';

	function changeStatus($topic, $status)
	{
		global $board, $board_info, $user_info;

		// We can't do this without a topic.
		if (empty($topic))
			fatal_lang_error('TopicSolved_not_a_topic', false);

		// Better safe than sorry...
		checkSession('get');

		// Make sure that we are in a "topic solved" board.
		if (!$board_info['topic_solved'])
			fatal_lang_error('topic_solved_no_board', false);

		// Let's get some info about the topic.
		$request = $smcFunc['db_query']('', '
			SELECT id_member_started, id_first_msg, id_last_msg, is_solved
			FROM {db_prefix}topics
			WHERE id_topic = {int:topic}
			LIMIT {int:limit}',
			array(
				'topic' => $topic,
				'limit' => 1,
			)
		);
		$row = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);

		// Check if he is allowed.
		if (!allowedTo('solve_topic_any') && $user_info['id'] == $row['id_member_started'])
			isAllowedTo('solve_topic_own');
		else
			isAllowedTo('solve_topic_any');

		// Change the status.
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}topics
			SET is_solved = {int:is_solved}
			WHERE id_topic = {int:topic}
			LIMIT {int:limit}',
			array(
				'topic' => $topic,
				'is_solved' => empty($row['is_solved']) ? 1 : 0,
				'limit' => 1,
			)
		);

		// Change the icon.
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}messages
			SET icon = {string:icon}
			WHERE id_msg = {int:msg}
			LIMIT {int:limit}',
			array(
				'msg' => $row['id_first_msg'],
				'icon' => empty($row['is_solved']) ? 'topicsolved' : 'xx',
				'limit' => 1,
			)
		);

		// Do some logging, only for moderators though...
		if ($user_info['id'] != $row['id_member_started'])
			logAction(empty($row['is_solved']) ? 'solve' : 'not_solve', array('topic' => $topic, 'board' => $board), 'topic_solved');

		// Take us back to last post.
		redirectexit('topic=' . $topic . '.msg' . $row['id_last_msg'] . '#new');
	}

	static public function tools()
	{
		return TopicSolvedTools::getInstance();
	}

	public function db($table)
	{
		return new TopicSolvedDB($table);
	}

	/**
	 * Builds the admin button via hooks
	 *
	 * @access public
	 * @static
	 * @param array The admin menu
	 * @return void
	 */
	static function admin(&$admin_areas)
	{
		$admin_areas['config']['areas'][self::$_name] = array(
					'label' => self::tools()->getText('menuTitle'),
					'file' => self::$_name .'.php',
					'function' => 'wrapper_subActions',
					'icon' => 'posts.gif',
					'subsections' => array(
						'general' => array(self::tools()->getText('settingsGeneral')),
						'buttons' => array(self::tools()->getText('settingsCus'))
				),
		);
	}

	/**
	 * Creates the pages for the admin panel via hooks
	 *
	 * @access public
	 * @static
	 * @global $context
	 * @global $scripturl
	 * @global $sourcedir
	 * @param boolean
	 * @return void
	 */
	static function subActions($return_config = false)
	{
		global $context, $sourcedir;

		require_once($sourcedir . '/ManageSettings.php');

		$context['page_title'] = self::tools()->getText('menuTitle');

		$subActions = array(
			'general' => 'self::generalSettings',
			'cus' => 'self::cusSettings'
		);

		loadGeneralSettingParameters($subActions, 'general');

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => self::tools()->getText('menuTitle'),
			'description' => self::tools()->getText('menuDesc'),
			'tabs' => array(
				'general' => array(),
				'cus' => array()
			),
		);

		call_user_func($subActions[$_REQUEST['sa']]);
	}

	/**
	 * The General settings page
	 *
	 * @access public
	 * @static
	 * @global $context
	 * @global $scripturl
	 * @global $sourcedir
	 * @param boolean
	 * @return void
	 */
	static function generalSettings($return_config = false)
	{
		global $scripturl, $context, $sourcedir;

		/* We need this */
		require_once($sourcedir . '/ManageServer.php');

		/* Generate the settings */
		$config_vars = array(
			array('check', TopicSolved::_$name .'_enable', 'subtext' => self::tools()->getText('enable_sub')),
			array('text', TopicSolved::_$name .'_boards', 'size' => 36, 'subtext' => self::tools()->getText('boards_sub')),
		);

		if ($return_config)
			return $config_vars;

		/* Set some settings for the page */
		$context['post_url'] = $scripturl . '?action=admin;area='. self::$_name .';sa=general;save';
		$context['page_title'] = $txt['share_default_menu'];

		if (isset($_GET['save']))
		{
			/* Clean the boards var, we only want integers and nothing else! */
			if (!empty($_POST[TopicSolved::_$name .'_boards']))
			{
				$tsBoards = explode(',', preg_replace('/[^0-9,]/', '', $_POST[TopicSolved::_$name .'_boards']));

				foreach ($tsBoards as $key => $value)
					if ($value == '')
						unset($tsBoards[$key]);

				$_POST[TopicSolved::_$name .'_boards'] = implode(',', $tsBoards);
			}

			/* Save the settings */
			checkSession();
			saveDBSettings($config_vars);
			redirectexit('action=admin;area=sharethis;sa=general');
		}

		prepareDBSettingContext($config_vars);
	}

	/**
	 * The Buttons settings page
	 *
	 * @access public
	 * @static
	 * @global $context
	 * @global $scripturl
	 * @global $sourcedir
	 * @param boolean
	 * @return void
	 */
	static function cusSettings($return_config = false)
	{
		global $scripturl, $context, $sourcedir;

		/* We need this */
		require_once($sourcedir . '/ManageServer.php');

		/* Generate the settings */
		$config_vars = array(
			array('text', TopicSolved::_$name .'_solvedColor', 'size' => 20, 'subtext' => $txt['share_twitter_options_via_sub']),
		);

		if ($return_config)
			return $config_vars;

		/* Page settings */
		$context['post_url'] = $scripturl . '?action=admin;area=sharethis;sa=buttons;save';
		$context['page_title'] = $txt['share_default_menu'];

		/* Save */
		if (isset($_GET['save']))
		{
			/* We will accept only an hexadecimal number here */
			if (!empty($_POST['share_options_show_space']))
				$_POST['share_options_show_space'] = preg_replace('/[^0-9,]/', '', $_POST['share_options_show_space']);

			checkSession();
			saveDBSettings($config_vars);
			redirectexit('action=admin;area=sharethis;sa=buttons');
		}
		prepareDBSettingContext($config_vars);
	}

	/**
	 * Shows the mod's author copyright
	 *
	 * Show the copyright in the credits action,  ?action=credits
	 * @access public
	 * @static
	 * @return string The copyright link
	 */
	static function who()
	{
		return '<a href="http://missallsunday.com" title="Free SMF Mods">Topic Solved mod &copy Suki</a>';
	}

	/**
	 * Set all the necessary CSS and JavaScript
	 *
	 * Via $context['html_headers'] that means no template edits.
	 * @access public
	 * @global $context SMF's context array
	 * @static
	 * @return void
	 */
	static function headers()
	{
		global $context;
	}
}