<?php
/**
 * @package      Joomproject
 * @subpackage   Comments
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
abstract class JPcommentsHelperDashboard
{

    /**
     * Returns a list of buttons for the backend
     *
     * @return    array
     */
    public static function getAdminButtons()
    {
        $user    = Factory::getApplication()->getIdentity();
        $buttons = array();

        if ($user->authorise('core.manage', 'com_jpcomments')) {
            $buttons[] = array(
                'title' => 'COM_JOOMPROJECT_SUBMENU_COMMENTS',
                'link'  => 'index.php?option=com_jpcomments',
                'icon'  => '<i class="wt-icon-bubble"></i>',
                'iconName' => 'comment', // for count card
                'iconClass' => 'bg-grey text-white', // for count card
                'iconColor' => 'text-grey',
                'countClass' => 'text-dark', // for count card
                'count' => JoomprojectHelperStats::getCount("id > 1",'jp_comments'),
                'countByState' => [
                    'published' => JoomprojectHelperStats::getCount('state = 1','jp_comments'),
                    'unpublished' => JoomprojectHelperStats::getCount('state = 0','jp_comments'),
                    'trashed' => JoomprojectHelperStats::getCount('state = -2','jp_comments'),
                    'archived' => JoomprojectHelperStats::getCount('state = 2','jp_comments'),
                ]

            );
        }

        return $buttons;
    }
}