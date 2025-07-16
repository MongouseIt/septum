<?php
/**
 * @package      Joomproject
 * @subpackage   Time
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
abstract class JPtimeHelperDashboard
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
        $asset = 'com_jptime';

        if ($pid) {
            $asset .= '.project.' . $pid;
        }

        if ($user->authorise('core.create', $asset)) {
            $buttons[] = array(
                'title' => 'MOD_JP_DASH_BUTTONS_ADD_TIME',
                'link'  => JPtimeHelperRoute::getTimesheetRoute() . '&task=form.add',
                'icon'  => "<i class='far text-muted fa-clock fa-3x'></i>",
                'iconClass' => 'far text-muted fa-clock'
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

        if ($user->authorise('core.manage', 'com_jptime')) {
            $buttons[] = array(
                'title' => 'COM_JOOMPROJECT_SUBMENU_TIME_TRACKING',
                'link'  => 'index.php?option=com_jptime',
                'icon'  => '<i class="wt-icon-clock"></i>',
                'iconName' => 'clock', // for count card
                'iconClass' => 'bg-info text-white', // for count card,
                'iconColor' => 'text-info',
                'countClass' => 'text-dark', // for count card
                'count' => JoomprojectHelperStats::getCount(null,'jp_timesheet'),
                'countByState' => [
                    'published' => JoomprojectHelperStats::getCount('state = 1','jp_timesheet'),
                    'unpublished' => JoomprojectHelperStats::getCount('state = 0','jp_timesheet'),
                    'trashed' => JoomprojectHelperStats::getCount('state = -2','jp_timesheet'),
                    'archived' => JoomprojectHelperStats::getCount('state = 2','jp_timesheet'),
                ]
            );
        }

        return $buttons;
    }
}