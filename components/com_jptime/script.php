<?php
/**
 * @package      Joomproject
 * @subpackage   Time Tracking
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die();


class com_jptimeInstallerScript
{
    /**
     * Called before any type of action
     *
     * @param     string              $route      Which action is happening (install|uninstall|discover_install)
     * @param     jadapterinstance    $adapter    The object responsible for running this script
     *
     * @return    boolean                         True on success
     */
    public function preflight($route, $parent)
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
            $element = $parent->getElement();

            // Restore assets from backup
            JPInstallerHelper::restoreAssets($element);

            // Make the admin component menu item a child of com_joomproject
            JPInstallerHelper::setComponentMenuItem($element);
        }

        if (strtolower($route) == 'update') {
            $element = $parent->getElement();

            // Make the admin component menu item a child of com_joomproject
            JPInstallerHelper::setComponentMenuItem($element);
        }

        return true;
    }


    /**
     * Called on uninstallation
     *
     * @param    jadapterinstance    $adapter    The object responsible for running this script
     */
    public function uninstall($parent)
    {
        // Skip this step if the user is removing the entire joomproject package
        if ($this->isRemovingAll()) {
            return true;
        }

        $element = $parent->getElement();
        $asset   = Table::getInstance('Asset');

        // Backup any assets for another component that might take over
        if ($asset->loadByName($element)) {
            $asset->name = $asset->name . '_bak';
            $asset->store();
        }

        return true;
    }


    /**
     * Method to find out if the user is removing com_joomproject
     * or pkg_joomproject
     *
     * @return    boolean
     */
    protected function isRemovingAll()
    {
        $cid = Factory::getApplication()->input->get('cid', array(), 'array');

		ArrayHelper::toInteger($cid, array());

        if (count($cid) == 0) {
            $extensions = array();
        }
        else {
            $db    = Factory::getDbo();
            $query = $db->getQuery(true);

            $query->select('element')
                  ->from('#__extensions')
                  ->where('extension_id IN(' . implode(', ', $cid) . ')');

            $db->setQuery($query);
            $extensions = (array) $db->loadColumn();
        }

        if (in_array('pkg_joomproject', $extensions) || in_array('com_joomproject', $extensions)) {
            return true;
        }

        return false;
    }
}
