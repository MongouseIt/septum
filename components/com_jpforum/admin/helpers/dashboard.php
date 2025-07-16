<?php
/**
 * @package      Joomproject
 * @subpackage   Forum
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
abstract class JPforumHelperDashboard
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
        $asset = 'com_jpforum';

        if ($pid) {
            $asset .= '.project.' . $pid;
        }

        if ($user->authorise('core.create', $asset)) {
            $buttons[] = array(
                'title' => 'MOD_JP_DASH_BUTTONS_ADD_TOPIC',
                'link'  => JPforumHelperRoute::getTopicsRoute() . '&task=topicform.add',
                'icon'  => "<i class='far text-muted fa-comment fa-3x'></i>",
	            'iconClass' => 'fas text-muted fa-comment'
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

        if ($user->authorise('core.manage', 'com_jpforum')) {
            $buttons[] = array(
                'title' => 'COM_JOOMPROJECT_SUBMENU_FORUM',
                'link'  => 'index.php?option=com_jpforum',
                'icon'  => '<i class="wt-icon-comments"></i>',
	            'iconName' => 'comments', // for count card
	            'iconClass' => 'bg-green text-white', // for count card
                'iconColor' => 'text-green',
	            'countClass' => 'text-dark', // for count card
	            'count' => JoomprojectHelperStats::getCount(null,'jp_topics'),
	            'countTitle' => 'COM_JOOMPROJECT_TOPICS',
                'countByState' => [
                    'published' => JoomprojectHelperStats::getCount('state = 1','jp_topics'),
                    'unpublished' => JoomprojectHelperStats::getCount('state = 0','jp_topics'),
                    'trashed' => JoomprojectHelperStats::getCount('state = -2','jp_topics'),
                    'archived' => JoomprojectHelperStats::getCount('state = 2','jp_topics'),
                 ]
            );
        }

        return $buttons;
    }
}