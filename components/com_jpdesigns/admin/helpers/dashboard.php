<?php
/**
 * @package      Joomproject Pro
 * @subpackage   Designs
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
abstract class JPdesignsHelperDashboard
{
    /**
     * Returns a list of buttons for the frontend
     *
     * @return    array
     */
    public static function getSiteButtons()
    {
        $buttons = array();

        if (Factory::getApplication()->getIdentity()->authorise('core.create', 'com_jpdesigns')) {
            $buttons[] = array(
                'title' => 'MOD_JP_DASH_BUTTONS_ADD_DESIGN',
                'link'  => JPdesignsHelperRoute::getDesignsRoute() . '&task=designform.add',
                'icon'  => "<i class='far text-muted fa-object-ungroup fa-3x'></i>",
	            'iconClass' => 'far text-muted fa-object-ungroup'
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
        $buttons = array();

        if (Factory::getApplication()->getIdentity()->authorise('core.manage', 'com_jpdesigns')) {
            $buttons[] = array(
                'title' => 'COM_JOOMPROJECT_SUBMENU_DESIGNS',
                'link'  => 'index.php?option=com_jpdesigns',
                'icon'  => '<i class="wt-icon-eye"></i>',
                'iconName' => 'object-ungroup', // for count card
                'iconClass' => 'bg-yellow text-white', // for count card
                'iconColor' => 'text-yellow',
                'countClass' => 'text-dark', // for count card
                'count' => JoomprojectHelperStats::getCount(null,'jp_designs'),
                'countByState' => [
                    'published' => JoomprojectHelperStats::getCount('state = 1','jp_designs'),
                    'unpublished' => JoomprojectHelperStats::getCount('state = 0','jp_designs'),
                    'trashed' => JoomprojectHelperStats::getCount('state = -2','jp_designs'),
                    'archived' => JoomprojectHelperStats::getCount('state = 2','jp_designs'),
                ]
            );
        }

        return $buttons;
    }
}