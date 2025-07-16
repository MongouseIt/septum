<?php
/**
 * @package      Joomproject
 * @subpackage   Tasks
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
jimport('joomproject.library');


/**
 * Dashboard Helper Class
 *
 */
abstract class JPtasksHelperDashboard
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

        $pid   = JPApplicationHelper::getActiveProjectId();
        $asset = 'com_jptasks';

        if ($pid) {
            $asset .= '.project.' . $pid;
        }

        if ($user->authorise('core.create', $asset)) {
            $buttons[] = array(
                'title' => 'MOD_JP_DASH_BUTTONS_ADD_TASK',
                'link'  => JPtasksHelperRoute::getTasksRoute() . '&task=taskform.add',
                'icon'  => "<i class='far text-muted fa-calendar-plus fa-3x'></i>",
                'iconClass' => 'far text-muted fa-calendar-plus'
            );
            $buttons[] = array(
                'title' => 'MOD_JP_DASH_BUTTONS_ADD_TASKLIST',
                'link'  => JPtasksHelperRoute::getTasksRoute() . '&task=tasklistform.add',
                'icon'  => "<i class='far text-muted fa-list-alt fa-3x'></i>",
                'iconClass' => 'far text-muted fa-list-alt'
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

        if ($user->authorise('core.manage', 'com_jptasks')) {
            $buttons[] = array(
                'title' => 'COM_JOOMPROJECT_SUBMENU_TASKS',
                'link'  => 'index.php?option=com_jptasks',
                'icon'  => '<i class="wt-icon-check"></i>',
                'iconName' => 'tasks', // for count card
                'iconClass' => 'bg-red text-white', // for count card
                'iconColor' => 'text-red',
                'countClass' => 'text-dark', // for count card
                'count' => JoomprojectHelperStats::getCount(null,'jp_tasks'),
                'countByState' => [
                    'published' => JoomprojectHelperStats::getCount('state = 1','jp_tasks'),
                    'unpublished' => JoomprojectHelperStats::getCount('state = 0','jp_tasks'),
                    'trashed' => JoomprojectHelperStats::getCount('state = -2','jp_tasks'),
                    'archived' => JoomprojectHelperStats::getCount('state = 2','jp_tasks'),
                ]
            );            
        }

        return $buttons;
    }
}