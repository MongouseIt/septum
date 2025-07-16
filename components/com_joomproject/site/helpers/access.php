<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jpprojects
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2006-2016 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */


defined('_JEXEC') or die;

use Joomla\CMS\Factory;


/**
 * Projects Component Route Helper
 *
 * @static
 */
abstract class JoomprojectHelperAccess
{
    /*
     * Check if given or current user can change project permissions and access
     */
    public static function canChangePermissions($id = null, $item = null, $user = null, $component = "com_jpprojects"){



        if(is_null($user))
            $user = Factory::getApplication()->getIdentity();

        if(is_null($id))
            $id = Factory::getApplication()->input->get('id', 0,'int');



        // is admin, admin can change permissions
        if($user->authorise('core.admin', $component))
            return true;

        // is manager, yes manager can change permissions
        if($user->authorise('core.manage', $component))
            return true;

        // is owner and permitter to change permissions of own projects


        if(
            isset($item->created_by) &&
            $user->authorise('core.edit.own.permissions', 'com_jpprojects') &&
            $user->id == $item->created_by
        ){

            return true;
        }


        // if new item and owner can change permission
        if(
            $id == 0 && // is new item
            $user->authorise('core.edit.own.permissions', 'com_jpprojects')
        ) // permitted to change permissions
            return true;

        return false;



    }

}
