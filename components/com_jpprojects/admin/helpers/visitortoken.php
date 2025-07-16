<?php

/**
 * @package      Joomproject
 * @subpackage   Projects
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */


defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Crypt\Crypt;

class JoomprojectVisitorToken
{
	/**
	 *  Class instance
	 *
	 *  @var  object
	 */
	private static $instance;

	/**
	 *  Cookie Name
	 *
	 *  @var  string
	 */
	private $cookieName = "nrid";

	/**
	 *  Represents the maximum age of the visitor's cookie in seconds.
	 *
	 *  @var  Integer
	 */
	private $expire = 90000000;

	/**
	 *  Cookies Object
	 *
	 *  @var  object
	 */
	private $cookies;

	/**
	 *  Class constructor
	 */
	private function __construct()
	{
		$this->cookies = Factory::getApplication()->input->cookie;

		$token = $this->cookies->get($this->cookieName, null);

		if ($token === null)
		{
			$this->store($this->create());
		}
	}

	/**
	 *  Returns class instance
	 *
	 *  @return  object
	 */
	public static function getInstance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 *  Get a visitor's unique token id, if a token isn't set yet one will be generated.
	 *
	 *  @param   boolean   $forceNew  If true, force a new token to be created
	 *
	 *  @return  string    The session token
	 */
	public function get($forceNew = false)
	{
		return $this->cookies->get($this->cookieName);
	}

	/**
	 *  Create a token-string
	 *
	 *  @param   integer $length  Length of string
	 *
	 *  @return  string  Generated token
	 */
	private function create($length = 8)
	{
		return bin2hex(Crypt::genRandomBytes($length));
	}

	/**
	 *  Saves the cookie to the visitor's browser
	 *
	 *  @param   string  $value  Cookie Value
	 *
	 *  @return  void
	 */
	private function store($value)
	{
		$this->cookies->set($this->cookieName, $value, time() + $this->expire, '/', '', true);
	}
}