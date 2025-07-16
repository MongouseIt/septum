<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jptime
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Factory;


class JPtimeHelper
{
    /**
     * The component name
     *
     * @var    string
     */
    public static $extension = 'com_jptime';

    /**
     * Indicates whether this component uses a project asset or not
     *
     * @var    boolean
     */
    public static $project_asset = true;


    /**
     * Configure the Linkbar.
     *
     * @param     string    $view    The name of the active view.
     *
     * @return    void
     */
    public static function addSubmenu($view)
    {
        JoomprojectHelper::addSubmenu($view);
    }


    /**
     * Gets a list of actions that can be performed.
     *
     * @param     integer    $id         The item id
     *
     * @return    jobject
     */
    public static function getActions($id = 0)
    {
        $user   = Factory::getApplication()->getIdentity();
        $result = new CMSObject;

        if (empty($id)) {
            $pid   = JPApplicationHelper::getActiveProjectId();
            $asset = (empty($pid) ? self::$extension : 'com_jptime.project.' . $pid);
        }
        else {
            $asset = 'com_jptime.time.' . (int) $id;
        }

        $actions = array(
            'core.admin', 'core.manage',
            'core.create', 'core.edit',
            'core.edit.own', 'core.edit.state',
            'core.delete'
        );

        foreach ($actions as $action)
        {
            $result->set($action, $user->authorise($action, $asset));
        }

        return $result;
    }
}
