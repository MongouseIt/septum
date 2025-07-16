<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jpmilestones
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


class JPmilestonesHelper
{
    /**
     * The component name
     *
     * @var    string
     */
    public static $extension = 'com_jpmilestones';

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
     * @param     integer    $id    The item id
     *
     * @return    jobject
     */
    public static function getActions($id = 0)
    {
        $user   = Factory::getApplication()->getIdentity();
        $result = new CMSObject;

        if (empty($id)) {
            $pid   = JPApplicationHelper::getActiveProjectId();
            $asset = (empty($pid) ? self::$extension : 'com_jpmilestones.project.' . $pid);
        }
        else {
            $asset = 'com_jpmilestones.milestone.' . (int) $id;
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

            if($section == 'form'){
                $section = 'milestone';
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
        Factory::getLanguage()->load('com_jpmilestones', JPATH_ADMINISTRATOR);

        $contexts = array(
            'com_jpmilestones.milestone'    => Text::_('COM_JPPROJECTS_MILESTONE')
        );

        return $contexts;
    }
}
