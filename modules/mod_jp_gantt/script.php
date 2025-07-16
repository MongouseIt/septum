<?php
/**
* @package      mod_jp_gantt
*
* @author       JoomBoost
* @copyright    Copyright (C) 2018 JoomBoost. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

class mod_jp_ganttInstallerScript
{
    /**
     * Called before any type of action
     *
     * @param     string              $route      Which action is happening (install|uninstall|discover_install)
     * @param     jadapterinstance    $adapter    The object responsible for running this script
     *
     * @return    boolean                         True on success
     */
    public function preflight($route,$parent)
    {
        if (strtolower($route) == 'install' || strtolower($route) == 'update') {
            if (!defined('JP_LIBRARY')) {
                jimport('joomproject.library');
            }

            $name = htmlspecialchars($parent->getManifest()->name, ENT_QUOTES, 'UTF-8');

            // Check if the library is installed
            if (!defined('JP_LIBRARY')) {
                Factory::getApplication()->enqueueMessage( Text::_('This extension (' . $name . ') requires the Joomproject Library to be installed!'),'warning');
                return false;
            }

            // Check if the joomproject component is installed
            if (!JPApplicationHelper::exists('com_joomproject')) {
               Factory::getApplication()->enqueueMessage( Text::_('This extension (' . $name . ') requires the Joomproject Component to be installed!'),'warning');
                return false;
            }
        }

        return true;
    }


    /**
     * Called after any type of action
     *
     * @param     string              $route      Which action is happening (install|uninstall|discover_install)
     * @param     jadapterinstance    $adapter    The object responsible for running this script
     *
     * @return    boolean                         True on success
     */
    public function postflight($route, $parent)
    {
        if (strtolower($route) == 'install') {
            // Get the XML manifest data
            $manifest = $parent->getManifest();

            // Set the module params
            JPInstallerHelper::setModuleParams($manifest);
        }

        return true;
    }
}
