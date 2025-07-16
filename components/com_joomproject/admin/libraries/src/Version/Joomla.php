<?php
/**
 * @package      Joomproject
 * @subpackage   Tasks
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

namespace JoomProject\Version;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Version;
use JoomprojectHelperRoute;
use JPStatsHelper;

defined('_JEXEC') or die();

/*
 * Utility class to check Joomla version
 */
class Joomla{

    public static function isJoomla5()
    {
        // Method 1: Using JVERSION constant
        if (defined('JVERSION')) {
            return version_compare(JVERSION, '5.0', '>=');
        }

        // Method 2: Using JVersion class
        $version = new Version();
        return version_compare($version->getShortVersion(), '5.0', '>=');
    }

}