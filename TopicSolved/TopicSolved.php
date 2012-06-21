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
	public static $_folder = 'TopicSolved/';

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
			array('check', 'share_all_messages', 'subtext' => $txt['share_all_messages_sub']),
			array('check', 'share_disable_jquery', 'subtext' => $txt['share_disable_jquery_sub']),
			array('text', 'share_options_boards', 'size' => 36, 'subtext' => $txt['share_options_boards_sub']),
			array(
				'select',
				'share_options_position', array(
					'below' => $txt['share_options_position_below'],
					'above' => $txt['share_options_position_above']
				),
				'subtext' => $txt['share_options_position_sub']
			),
			array('int', 'share_options_show_space', 'size' => 3, 'subtext' => $txt['share_options_show_space_sub']),
			'',
			array('check', 'share_addthisbutton_enable', 'subtext' => $txt['share_addthisbutton_enable_sub']),
		);

		if ($return_config)
			return $config_vars;

		/* Set some settings for the page */
		$context['post_url'] = $scripturl . '?action=admin;area=sharethis;sa=general;save';
		$context['page_title'] = $txt['share_default_menu'];

		if (isset($_GET['save']))
		{
			/* Clean the boards var, we only want integers and nothing else! */
			if (!empty($_POST['share_options_boards']))
			{
				$share_options_boards = explode(',', preg_replace('/[^0-9,]/', '', $_POST['share_options_boards']));

				foreach ($share_options_boards as $key => $value)
					if ($value == '')
						unset($share_options_boards[$key]);

				$_POST['share_options_boards'] = implode(',', $share_options_boards);
			}

			/* If for some reason the user put something like this:  12px, then remove the "px" part, we want only numbers! */
			if (!empty($_POST['share_options_show_space']))
				$_POST['share_options_show_space'] = preg_replace('/[^0-9,]/', '', $_POST['share_options_show_space']);

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
			array('check', 'share_buttons_enable', 'subtext' => $txt['share_buttons_enable_sub']),
			'',
			array('check', 'share_plusone_enable'),
			array('check', 'share_twibutton_enable'),
			array('check', 'share_likebutton_enable'),
			array('check', 'share_addthismessages_enable', 'subtext' => $txt['share_addthismessages_enable_sub']),
			'',
			$txt['share_twitter_options_dec'],
			array('text', 'share_twitter_options_via', 'size' => 20, 'subtext' => $txt['share_twitter_options_via_sub']),
		);

		if ($return_config)
			return $config_vars;

		/* Page settings */
		$context['post_url'] = $scripturl . '?action=admin;area=sharethis;sa=buttons;save';
		$context['page_title'] = $txt['share_default_menu'];

		/* Save */
		if (isset($_GET['save']))
		{
			/* Just an extra check... */
			if (isset($_POST['share_twitter_options_via']))
				$_POST['share_twitter_options_via'] = str_replace('@', '', $_POST['share_twitter_options_via']);

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