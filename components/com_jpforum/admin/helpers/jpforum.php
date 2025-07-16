<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jpforum
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
class JPforumHelper
{
    /**
     * The component name
     *
     * @var    string
     */
    public static $extension = 'com_jpforum';

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
     * Gets a list of actions that can be performed on a topic.
     *
     * @param     integer    $id         The item id
     * @param     integer    $project    The project id
     *
     * @return    jobject
     */
    public static function getActions($id = 0)
    {
        $user   = Factory::getApplication()->getIdentity();
        $result = new CMSObject;

        if (empty($id)) {
            $pid   = JPApplicationHelper::getActiveProjectId();
            $asset = (empty($pid) ? self::$extension : 'com_jpforum.project.' . $pid);
        }
        else {
            $asset = 'com_jpforum.topic.' . (int) $id;
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
     * Gets a list of actions that can be performed on a reply.
     *
     * @param     integer    $id         The item id
     * @param     integer    $topic      The topic id
     *
     * @return    jobject
     */
    public static function getReplyActions($id = 0, $topic = 0)
    {
        $user   = Factory::getApplication()->getIdentity();
        $result = new CMSObject;

        if (!empty($id)) {
            $asset = 'com_jpforum.reply.' . (int) $id;
        }
        elseif(!empty($topic)) {
            $asset = 'com_jpforum.topic.' . (int) $topic;
        }
        else {
            $pid   = JPApplicationHelper::getActiveProjectId();
            $asset = (empty($pid) ? self::$extension : 'com_jpforum.project.' . $pid);
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

            if($section == 'topicform'){
                $section = 'topic';
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
        Factory::getLanguage()->load('com_jpforum', JPATH_ADMINISTRATOR);

        $contexts = array(
            'com_jpforum.topic'    => Text::_('COM_JPPROJECTS_TOPIC')
        );

        return $contexts;
    }
}
