<?php
/**
 * @package      Joomproject
 * @subpackage   Projects
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;


/**
 * Dashboard Helper Class
 *
 */
abstract class JPprojectsHelperDashboard
{
    /**
     * Returns a list of buttons for the frontend
     *
     * @return    array
     */
    public static function getSiteButtons()
    {
        $user    = Factory::getApplication()->getIdentity();
        $buttons = array();

        if ($user->authorise('core.create', 'com_jpprojects')) {
            $buttons[] = array(
                'title' => 'MOD_JP_DASH_BUTTONS_ADD_PROJECT',
                'link'  => JPprojectsHelperRoute::getProjectsRoute() . '&task=form.add',
                'icon'  => "<i class='fas text-muted fa-briefcase fa-3x'></i>",
                'iconClass' => 'fas text-muted fa-briefcase'
            );
        }

        return $buttons;
    }


    /**
     * Returns a list of buttons for the backend
     *
     * @return    array
     */
    public static function getAdminButtons()
    {
        $user    = Factory::getApplication()->getIdentity();
        $buttons = array();


        if ($user->authorise('core.manage', 'com_jpprojects')) {
            $buttons[] = array(
                'title' => 'COM_JOOMPROJECT_SUBMENU_PROJECTS',
                'link'  => 'index.php?option=com_jpprojects',
                'icon'  => '<i class="wt-icon-briefcase"></i>',
	            'iconName' => 'briefcase', // for count card
	            'iconClass' => 'bg-blue text-white', // for count card
                'iconColor' => 'text-blue',
	            'countClass' => 'text-dark', // for count card
	            'count' => JoomprojectHelperStats::getCount(null,'jp_projects'),
                'countByState' => [
                    'published' => JoomprojectHelperStats::getCount('state = 1','jp_projects'),
                    'unpublished' => JoomprojectHelperStats::getCount('state = 0','jp_projects'),
                    'trashed' => JoomprojectHelperStats::getCount('state = -2','jp_projects'),
                    'archived' => JoomprojectHelperStats::getCount('state = 2','jp_projects'),
                ]
            );
        }

        return $buttons;
    }
}