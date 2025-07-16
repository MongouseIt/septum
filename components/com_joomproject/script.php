<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_joomproject
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Helper\ModuleHelper;


JLoader::register('JoomprojectHelperMaintenance', JPATH_ADMINISTRATOR . '/components/com_joomproject/helpers/maintenance.php');

class com_joomprojectInstallerScript
{
    /**
     * Previous version number before updating
     *
     * @var    string
     */
    protected $prev_version;


    /**
     * Called before any type of action
     *
     * @param string $route Which action is happening (install|uninstall|discover_install)
     * @param jadapterinstance $adapter The object responsible for running this script
     *
     * @return    boolean                         True on success
     */
    public function preflight($route, $parent)
    {

        // Do not run on uninstall.
        if ($route == 'uninstall')
        {
            return true;
        }


        // remove layout
        $joomlaLayout=JPATH_ADMINISTRATOR . '/components/com_joomproject/layouts/joomla';

        if (Folder::exists($joomlaLayout)) {
            Folder::delete($joomlaLayout);
        }

        // remove old component com_joomactivities if exist
         $this->removeJpactivities();

        if (strtolower($route) == 'install') {
            if (!defined('JP_LIBRARY')) {
                jimport('joomproject.library');
            }
            // Check if the library is installed
            if (!defined('JP_LIBRARY')) {
                Log::add('This extension requires the Joomproject Library to be installed!', Log::WARNING, 'jerror');

                return false;
            }
        }

        return true;
    }


    /**
     * Called after any type of action
     *
     * @param string $route Which action is happening (install|uninstall|discover_install)
     * @param jadapterinstance $adapter The object responsible for running this script
     *
     * @return    boolean                         True on success
     */
    public function postflight($route, $parent)
    {

        // Do not run on uninstall.
        if ($route == 'uninstall')
        {
            return true;
        }

        // do maintenance
        $this->executeMaintenance($route);

        return true;
    }

    public function executeMaintenance($route)
    {

        JLoader::register('JoomprojectHelperMaintenance', JPATH_ADMINISTRATOR . '/components/com_joomproject/helpers/maintenance.php');

        // update projects that didn't use viewaction method
        JoomprojectHelperMaintenance::updateProjectsWithNoViewAction();

        // add menu if doesn't exist
        JoomprojectHelperMaintenance::addMenu();

        // fix broken menu items
        JoomprojectHelperMaintenance::fixBrokenMenuItems();

        // create menu module
        JoomprojectHelperMaintenance::createMenuModule($route);

    }










    // remove joomactivies
    private function removeJpactivities(){

        if (ComponentHelper::isInstalled('com_joomactivities')) {
            /** @var  Joomla\Component\Installer\Administrator\Model\ManageModel $manageModel */
            $manageModel = Factory::getApplication()->bootComponent('com_installer')
                ->getMVCFactory()->createModel('Manage', 'Administrator', ['ignore_request' => true]);


            $ids = [
                ComponentHelper::getComponent('com_joomactivities')->id,
                ModuleHelper::getModule('mod_jpactivities_jp_site')->id,
                ModuleHelper::getModule('mod_joomactivities_site')->id,
                PluginHelper::getPlugin('joomactivities', 'content')->id,
                PluginHelper::getPlugin('joomactivities', 'joomproject')->id,
                PluginHelper::getPlugin('content', 'joomactivities')->id
                ];

            $manageModel->remove($ids);
        }

        // end remove
    }
}
