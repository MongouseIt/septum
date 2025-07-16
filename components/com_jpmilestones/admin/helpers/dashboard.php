<?php
/**
 * @package      Joomproject
 * @subpackage   Milestones
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
abstract class JPmilestonesHelperDashboard
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
        $asset = 'com_jpmilestones';

        if ($pid) {
            $asset .= '.project.' . $pid;
        }

        if ($user->authorise('core.create', $asset)) {
            $buttons[] = array(
                'title' => 'MOD_JP_DASH_BUTTONS_ADD_MILESTONE',
                'link'  => JPmilestonesHelperRoute::getMilestonesRoute() . '&task=form.add',
                'icon'  => "<i class='far text-muted fa-flag fa-3x'></i>",
                'iconClass' => 'far text-muted fa-flag'
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

        if ($user->authorise('core.manage', 'com_jpmilestones')) {
            $buttons[] = array(
                'title' => 'COM_JOOMPROJECT_SUBMENU_MILESTONES',
                'link'  => 'index.php?option=com_jpmilestones',
                'icon'  => '<i class="wt-icon-flag2"></i>',
                'iconName' => 'flag', // for count card
                'iconClass' => 'bg-purple text-white', // for count card
                'iconColor' => 'text-purple',
                'countClass' => 'text-dark', // for count card
                'count' => JoomprojectHelperStats::getCount(null,'jp_milestones'),
                'countByState' => [
                    'published' => JoomprojectHelperStats::getCount('state = 1','jp_milestones'),
                    'unpublished' => JoomprojectHelperStats::getCount('state = 0','jp_milestones'),
                    'trashed' => JoomprojectHelperStats::getCount('state = -2','jp_milestones'),
                    'archived' => JoomprojectHelperStats::getCount('state = 2','jp_milestones'),
                ]
            );
        }

        return $buttons;
    }
}