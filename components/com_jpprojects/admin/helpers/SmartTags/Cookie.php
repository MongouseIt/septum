<?php

/**
 * @package      Joomproject
 * @subpackage   Projects
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

class Cookie extends JoomprojectSmartTag
{
	/**
     * This is a Pro-only feature
     *
     * @var boolean
     */
    public $proOnly = true;

	/**
	 * Returns the value of a cookie as stored in the visitor’s browser. 
	 * 
	 * @param   string  $key
	 * 
	 * @return  string
	 */
	public function fetchValue($key)
	{
		return $this->factory->getCookie($key);
	}
}