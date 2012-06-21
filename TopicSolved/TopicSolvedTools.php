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

/**
 * Ohara Tools Class
 *
 * How to use:
 *
 * - Change the name of the class to something unique
 * - Set the $_name property with your own mod name, $modSettings and $txt keys should use the same name followed by an underscore, example:
 *   $txt[MyMod_enable], $modSettings[MyMod_enable], etc.
 * - Load the file via __autoload() or some other method.
 * @package OharaTools
 */
class TopicSolvedTools
{
	/**
	 * @var object The unique instance of the class
	 * @access private
	 */
	private static $_instance;

	/**
	 * @var array An array containing all the settings founded by $this->extract()
	 * @see OharaTools::extract()
	 * @access protected
	 */
	protected $_settings = array();

	/**
	 * @var array An array containing all the txt strings founded by $this->extract()
	 * @see OharaTools::extract()
	 * @access protected
	 */
	protected $_text = array();

	/**
	 * @var string The name of your mod or some unique identifier, you should replace this with your own identifier/mod name
	 * @access protected
	 */
	protected $_name = TopicSolved::$_name;

	/**
	 * @var string The pattern used to search the modsettings and txt arrays, should be: /identifier_/ this is defined with the value of $_name
	 * @access protected
	 */
	protected $_pattern;

	/**
	 * Initialize the extract() method and sets the pattern property using $_name's value.
	 *
	 * @access protected
	 * @return void
	 */
	protected function __construct()
	{
		/* Set the pattern property with $_name's value */
		$this->_pattern = '/'. $this->_name .'_/';

		/* Extract the requested values from the arrays */
		$this->extract();
	}

	/**
	 * Set's a unique instance for the class.
	 *
	 * @access public
	 * @return object
	 */
	public static function getInstance()
	{
		if (!self::$_instance)
			self::$_instance = new self();

		return self::$_instance;
	}

	/**
	 * Extracts the requested values form the $modSettings and txt arrays, sets $_text and $_settings with the founded data.
	 *
	 * @global array $modSettings SMF's modSettings variable
	 * @global array $txt SMF's text strings
	 * @access public
	 * @return void
	 */
	public function extract()
	{
		global $modSettings, $txt;

		/* Load the mod's language file */
		loadLanguage($this->_name);

		/* Get only the settings that we need */
		foreach ($modSettings as $km => $vm)
			if (preg_match($this->_pattern, $km))
				$this->_settings[str_replace($this->_name .'_', '', $km)] = $vm;

		/* Do the same for the text */
		foreach ($txt as $kt => $vt)
			if (preg_match($this->_pattern, $kt))
				$this->_text[str_replace($this->_name .'_', '', $kt)] = $vt;
	}

	/**
	 * Return true if the param value do exists on the $_settings array, false otherwise.
	 *
	 * @param string the name of the key
	 * @access public
	 * @return bool
	 */
	public function enable($var)
	{
		if (!empty($this->_settings[$var]))
			return true;

		else
			return false;
	}

	/**
	 * Get the requested array element.
	 *
	 * @param string the key name for the requested element
	 * @access public
	 * @return mixed
	 */
	public function getSetting($var)
	{
		if (empty($var))
			return false;

		elseif (!empty($this->_settings[$var]))
			return $this->_settings[$var];

		else
			return false;
	}

	/**
	 * Get the requested array element.
	 *
	 * @param string the key name for the requested element
	 * @access public
	 * @return mixed
	 */
	public function getText($var)
	{
		if (!empty($this->_text[$var]))
			return $this->_text[$var];

		else
			return false;
	}
}