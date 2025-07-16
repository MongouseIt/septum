<?php
/**
* @package      Joomproject Dashboard Buttons
*
* @author       JoomBoost
* @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();
use Joomla\CMS\Filesystem\File;

/**
 * Module helper class
 *
 */
abstract class modJPdashButtonsHelper
{
    /**
     * Method to get a list of available buttons
     *
     * @return    array    $buttons    The available buttons
     */
    public static function getButtons()
    {
        $components = JPApplicationHelper::getComponents('com_jpreminders');
        $buttons    = array();

        foreach ($components AS $component)
        {
            if (!JPApplicationHelper::enabled($component->element)) {
                continue;
            }

            // Register component route helper if exists
            $router = JPATH_SITE . '/components/' . $component->element . '/helpers/route.php';
            $class  = str_replace('com_jp', 'JP', $component->element) . 'HelperRoute';

            if (File::exists($router)) {
                JLoader::register($class, $router);
            }

            // Register component dashboard helper if exists
            $helper = JPATH_ADMINISTRATOR . '/components/' . $component->element . '/helpers/dashboard.php';
            $class  = str_replace('com_jp', 'JP', $component->element) . 'HelperDashboard';

            if (!File::exists($helper)) {
                continue;
            }

            JLoader::register($class, $helper);

            // Get the dashboard button
            if (class_exists($class)) {
                if (in_array('getSiteButtons', get_class_methods($class))) {
                    $com_buttons = (array) call_user_func(array($class, 'getSiteButtons'));

                    $buttons[$component->element] = array();

                    foreach ($com_buttons AS $button)
                    {
                        $buttons[$component->element][] = $button;
                    }
                }
            }
        }

        return $buttons;
    }
}
