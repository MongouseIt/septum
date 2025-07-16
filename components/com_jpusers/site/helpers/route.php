<?php
/**
 * @package      Joomproject
 * @subpackage   Users
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2006-2012 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;


jimport('joomla.application.component.helper');


/**
 * Component Route Helper
 *
 * @static
 */
abstract class JPusersHelperRoute
{
    /**
     * Creates a link to the users overview
     *
     * @param     string    $project    The project slug. Optional
     *
     * @return    string    $link       The link
     */
    public static function getUsersRoute($project = '')
    {
        $link  = 'index.php?option=com_jpusers&view=users';
        $link .= '&filter_project=' . $project;

        $needles = array('filter_project' => array((int) $project)
                        );

        if ($item = JPApplicationHelper::itemRoute($needles, 'com_jpusers.users')) {
            $link .= '&Itemid=' . $item;
        }
        elseif ($item = JPApplicationHelper::itemRoute(null, 'com_jpusers.users')) {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }


    /**
     * Creates a link to a user item view
     *
     * @param     string    $id      The user slug
     *
     * @return    string    $link    The link
     */
    public static function getUserRoute($id)
    {
        static $dest = null;

        if (is_null($dest)) {
            $params = ComponentHelper::getParams('com_joomproject');

            $dest = $params->get('user_profile_link');
        }

        $link = null;

        switch ($dest)
        {
            case 'cb':
                $link = self::getCBRoute($id);
                break;

            case 'js':
                $link = self::getJSRoute($id);
                break;

            case 'kunena':
                $link = self::getKRoute($id);
                break;
	        case 'osmembership':
		        $link = self::getOsmembershipRoute($id);
		        break;
        }

        if (!empty($link)) {
            return $link;
        }

        // Default - Joomproject Profile
        $link  = 'index.php?option=com_jpusers&view=user';
        $link .= '&id=' . $id;

        $needles = array('id' => array((int) $id));

        if ($item = JPApplicationHelper::itemRoute($needles, 'com_jpusers.user')) {
            $link .= '&Itemid=' . $item;
        }
        elseif ($item = JPApplicationHelper::itemRoute(null, 'com_jpusers.users')) {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }


    /**
     * Method to link to a Community Builder user profile page
     *
     * @param     integer    $id      The user slug
     *
     * @return    string              The profile url
     */
    protected static function getCBRoute($id)
    {
        static $itemid = null;

        // Try to find a suitable menu item
        if (is_null($itemid)) {
            $app	= Factory::getApplication();
			$menu	= $app->getMenu();
			$com	= ComponentHelper::getComponent('com_comprofiler');

            if (empty($com) || !isset($com->id)) {
                $itemid = 0;
                return null;
            }

			$items	= $menu->getItems('component_id', $com->id);

            $profile_id = 0;
            $cb_id = 0;

			// If no items found, set to empty array.
			if (!$items) $items = array();

            foreach ($items as $item)
            {
                if (!isset($item->query['task']) || $item->query['task'] == 'userProfile') {
    				$itemid = $item->id;
    				break;
    			}
            }

            if (!$itemid) $itemid = 0;
        }

        // Return null if item id was not found
        if (!$itemid) return null;

        // Return link
        return 'index.php?option=com_comprofiler&task=userProfile&user=' . intval($id) . ($itemid ? '&Itemid=' . $itemid : '');
    }

    public static function getOsmembershipRoute($id){

	    $itemId = '';

	    // Try to find a suitable menu item
	    $app	= Factory::getApplication();
	    $menu	= $app->getMenu();
	    $com	= ComponentHelper::getComponent('com_osmembership');

	    if (empty($com) || !isset($com->id)) {
		    return null;
	    }

	    $items	= $menu->getItems('component_id', $com->id);


	    // If no items found, set to empty array.
	    if (!$items) $items = array();

	    foreach ($items as $item)
	    {
		    if (!isset($item->query['view']) || $item->query['view'] == 'profile') {
			    $itemId = '&Itemid='.$item->id;
			    break;
		    }
	    }

	    return 'index.php?option=com_osmembership&view=profile'.$itemId;
    }


    /**
     * Method to link to a JomSocial user profile page
     *
     * @param     integer    $id      The user id
     *
     * @return    string              The profile url
     */
    protected static function getJSRoute($id)
    {
        static $exists = null;

        // Include the route helper once
        if (is_null($exists)) {
            $file   = JPATH_SITE . '/components/com_community/helpers/url.php';
            $exists = file_exists($file);

            if ($exists) {
                require_once $file;

                $file   = JPATH_ROOT . '/components/com_community/libraries/core.php';
                $exists = file_exists($file);

                if ($exists) require_once $file;
            }
        }

        // Return null if router was not found
        if (!$exists) return null;

        // Return link
        return CUrlHelper::userLink((int) $id, true);
    }


    /**
     * Method to link to a Kunena user profile page
     *
     * @param     integer    $id      The user id
     *
     * @return    string              The profile url
     */
    protected static function getKRoute($id)
    {
        static $router = null;

        // Include the route helper once
        if (is_null($router)) {
            $file = JPATH_SITE . '/components/com_kunena/lib/kunena.link.class.php';

            if (!file_exists($file)) {
                $router = false;
            }
            else {
                require_once $file;
                $router = true;
            }
        }

        // Return null if router was not found
        if (!$router) return null;

        // Return link
        return CKunenaLink::GetMyProfileURL((int) $id);
    }
}
