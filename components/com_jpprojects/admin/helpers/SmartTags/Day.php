<?php

/**
 * @package      Joomproject
 * @subpackage   Projects
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

class Day extends Date
{
    /**
     * Returns the numeric representation of a day of the month without leading zeros. Eg: 22.
     * 
     * @return  string
     */
    public function getDay()
    {
        return $this->date->format('j');
    }
}