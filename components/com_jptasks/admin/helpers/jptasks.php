<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jptasks
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

jimport('joomproject.framework');

class JPtasksHelper
{
    /**
     * The component name
     *
     * @var    string
     */
    public static $extension = 'com_jptasks';

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
     * @param     integer    $id      The item id
     * @param     integer    $list    The list id
     *
     * @return    jobject
     */
    public static function getActions($id = 0, $list = 0)
    {
        $user   = Factory::getApplication()->getIdentity();
        $result = new CMSObject;

        if (!empty($id)) {
            $asset = 'com_jptasks.task.' . (int) $id;
        }
        elseif (!empty($list)) {
            $asset = 'com_jptasks.tasklist.' . (int) $list;
        }
        else {
            $pid   = JPApplicationHelper::getActiveProjectId();
            $asset = (empty($pid) ? self::$extension : 'com_jptasks.project.' . $pid);
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


    /**
     * Gets a list of actions that can be performed on a task list.
     *
     * @param     integer    $id    The item id
     *
     * @return    jobject
     */
    public static function getListActions($id = 0)
    {
        $user   = Factory::getApplication()->getIdentity();
        $result = new CMSObject;

        if (empty($id)) {
            $pid   = JPApplicationHelper::getActiveProjectId();
            $asset = (empty($pid) ? self::$extension : 'com_jptasks.project.' . $pid);
        }
        else {
            $asset = 'com_jptasks.tasklist.' . (int) $id;
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


    static public function priority2string($value = null)
    {
        switch((int) $value)
        {
            case 2:
                $text  = Text::_('COM_JOOMPROJECT_PRIORITY_LOW');
                break;

            case 3:
                $text  = Text::_('COM_JOOMPROJECT_PRIORITY_MEDIUM');
                break;

            case 4:
                $text  = Text::_('COM_JOOMPROJECT_PRIORITY_HIGH');
                break;

            case 5:
                $text  = Text::_('COM_JOOMPROJECT_PRIORITY_VERY_HIGH');
                break;

            default:
            case 1:
                $text  = Text::_('COM_JOOMPROJECT_PRIORITY_VERY_LOW');
                break;
        }

        return $text;
    }

    /**
     * Returns a valid section for articles. If it is not valid then null
     * is returned.
     *
     * @param   string  $section  The section to get the mapping for
     *
     * @return  string|null  The new section
     *
     * @since   3.7.0
     */
    public static function validateSection($section)
    {
        if (Factory::getApplication()->isClient('site'))
        {
           

            if($section == 'taskform'){
                $section = 'task';
            }

        }
        return $section;
    }

    /**
     * Returns valid contexts
     *
     * @return  array
     *
     * @since   3.7.0
     */
    public static function getContexts()
    {
        Factory::getLanguage()->load('com_jptasks', JPATH_ADMINISTRATOR);

        $contexts = array(
            'com_jptasks.task'    => Text::_('COM_JPTASKS_TASK')
        );

        return $contexts;
    }
}
