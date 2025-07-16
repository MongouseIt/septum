<?php
/**
 * @package      Joomproject
 * @subpackage   Tasks
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

namespace JoomProject\Permission;

use JLoader;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use JoomprojectHelperRoute;
use JPStatsHelper;

defined('_JEXEC') or die();

JLoader::register('JoomprojectHelperRoute',JPATH_SITE.'/components/com_joomproject/helpers/route.php');

class GlobalAccess
{

    static $notAllowedComponents = [];

    /*
     * this method checks if current user is autorized to global access level of component
     */
    public static function check($component, $displayErrors = true)
    {

        // get application
        $app = Factory::getApplication();

        // get global component config
        $cParams = ComponentHelper::getParams($component);

        // check first if component not enabled
        if (!ComponentHelper::isEnabled($component)){
            if(!in_array($component,static::$notAllowedComponents))
                static::$notAllowedComponents[] =  $component;
            return false;

        }

        // get current user authorized view levels
        $userAuthorizedViewLevels = Factory::getUser()->getAuthorisedViewLevels();

        // not activated always return true
        if (empty($cParams->get('global_access', '')))
            return true;

        // user authorized to global access level return true
        if (in_array($cParams->get('global_access', ''), $userAuthorizedViewLevels))
            return true;

        // display errors and set header to 403
        if ($displayErrors) {
            $app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
            $app->setHeader('status', 403, true);
        }

        if(!in_array($component,static::$notAllowedComponents))
            static::$notAllowedComponents[] =  $component;
        return false;

    }


    public static function allowedMainComponents()
    {


        $components = [
            [
                'com' => 'com_joomproject',
                'title' => 'COM_JOOMPROJECT_DASHBOARD_TITLE',
                'link' => JoomprojectHelperRoute::getDashboardRoute(),
                'icon' => 'fas fa-tachometer-alt',
                'view' => 'dashboard'
            ],
            [
                'com' => 'com_jpprojects',
                'title' => 'COM_JOOMPROJECT_PROJECTS',
                'link' => JoomprojectHelperRoute::getProjectsRoute(),
                'icon' => 'fas fa-briefcase',
                'view' => 'projects'
            ]];


        if (self::check('com_jpmilestones', false))
            $components[] = [
                'com' => 'com_jpmilestones',
                'title' => 'COM_JOOMPROJECT_MILESTONES',
                'link' => JoomprojectHelperRoute::getMilestonesRoute(),
                'icon' => 'fas fa-flag',
                'view' => 'milestones'
            ];

        if (self::check('com_jptasks', false))
            $components[] = [
                'com' => 'com_jptasks',
                'title' => 'COM_JOOMPROJECT_TASKS',
                'link' => JoomprojectHelperRoute::getTasksRoute(),
                'icon' => 'fas fa-tasks',
                'view' => 'tasks'
            ];

        if (self::check('com_jptime', false))
            $components[] = [
                'com' => 'com_jptime',
                'title' => 'COM_JOOMPROJECT_TIMESHEET',
                'link' => JoomprojectHelperRoute::getTimeRoute(),
                'icon' => 'fas fa-business-time',
                'view' => 'timesheet'
            ];


        if (self::check('com_jprepo', false))
            $components[] = [
                'com' => 'com_jprepo',
                'title' => 'COM_JOOMPROJECT_REPO',
                'link' => JoomprojectHelperRoute::getRepoRoute(),
                'icon' => 'fas fa-folder-open',
                'view' => 'repository'
            ];

        if (self::check('com_jpforum', false))
            $components[] = [
                'com' => 'com_jpforum',
                'title' => 'COM_JOOMPROJECT_SUBMENU_FORUM',
                'link' => JoomprojectHelperRoute::getTopicsRoute(),
                'icon' => 'fas fa-comments',
                'view' => 'topics'
            ];


        if (self::check('com_jpdesigns', false))
            $components[] = [
                'com' => 'com_jpdesigns',
                'title' => 'COM_JOOMPROJECT_FIELDSET_DESIGNS',
                'link' => JoomprojectHelperRoute::getDesignsRoute(),
                'icon' => 'fas fa-object-ungroup',
                'view' => 'designs'
            ];

        $components[] = [
            'com' => 'com_jpusers',
            'title' => 'COM_JOOMPROJECT_USERS',
            'link' => JoomprojectHelperRoute::getUsersRoute(),
            'icon' => 'fas fa-users',
            'view' => 'users'
        ];

        return $components;

    }

    public static function allowedProjectComponents($project_id)
    {

        $components = [];

        $components[] = [
            'com' => 'com_joomproject',
            'title' => 'COM_JOOMPROJECT_DETAILS_LABEL',
            'link' => JoomprojectHelperRoute::getDashboardRoute(),
            'icon' => 'fas fa-info',
            'view' => 'dashboard'
        ];

        if (self::check('com_jpmilestones', false))
            $components[] = [
                'com' => 'com_jpmilestones',
                'title' => 'COM_JOOMPROJECT_MILESTONES',
                'link' => JoomprojectHelperRoute::getMilestonesRoute(),
                'icon' => 'fas fa-clipboard-list',
                'view' => ['milestones', 'milestone', 'form'],
                'count' => JPStatsHelper::getCount($project_id, '#__jp_milestones')
            ];

        if (self::check('com_jptasks', false))
            $components[] = [
                'com' => 'com_jptasks',
                'title' => 'COM_JOOMPROJECT_TASKS',
                'link' => JoomprojectHelperRoute::getTasksRoute(true),
                'icon' => 'fas fa-list',
                'view' => ['tasks', 'task', 'taskform', 'tasklistform'],
                'count' => JPStatsHelper::getCount($project_id, '#__jp_tasks')
            ];

        if (self::check('com_jptime', false))
            $components[] = [
                'com' => 'com_jptime',
                'title' => 'COM_JOOMPROJECT_FIELDSET_TIME',
                'link' => JoomprojectHelperRoute::getTimeRoute(),
                'icon' => 'fas fa-clock',
                'view' => ['timesheet', 'form'],
                'count' => JPStatsHelper::getCount($project_id, '#__jp_timesheet')
            ];


        if (self::check('com_jprepo', false))
            $components[] = [
                'com' => 'com_jprepo',
                'title' => 'COM_JOOMPROJECT_FILES',
                'link' => JoomprojectHelperRoute::getRepoRoute(),
                'icon' => 'fas fa-copy',
                'view' => ['repository', 'note', 'noteform', 'repository', 'noterevisions', 'fileform', 'directoryform'],
                'count' => JPStatsHelper::getCount($project_id, '#__jp_repo_files', 'id', false, false)
            ];

        if (self::check('com_jpforum', false))
            $components[] = [
                'com' => 'com_jpforum',
                'title' => 'COM_JOOMPROJECT_TOPICS',
                'link' => JoomprojectHelperRoute::getTopicsRoute(),
                'icon' => 'fas fa-comment-dots',
                'view' => ['topics', 'replies', 'replyform', 'topicform', 'topics'],
                'count' => JPStatsHelper::getCount($project_id, '#__jp_topics')
            ];

        if(self::check('com_jpdesigns',false))
        $components[] = [
            'com' => 'com_jpdesigns',
            'title' => 'COM_JOOMPROJECT_FIELDSET_DESIGNS',
            'link' => JoomprojectHelperRoute::getDesignsRoute(),
            'icon' => 'fas fa-eye',
            'view' => ['designs', 'albumform', 'albums', 'design', 'designform', 'revision', 'revisionform'],
            'count' => JPStatsHelper::getCount($project_id, '#__jp_designs')
        ];

        $components[] = [
            'com' => 'com_jpusers',
            'title' => 'COM_JOOMPROJECT_TEAM',
            'link' => JoomprojectHelperRoute::getUsersRoute(),
            'icon' => 'fas fa-user-friends',
            'view' => ['users', 'user'],
            'count' => JPStatsHelper::getUsersCount($project_id)
        ];


        // display comments tab if enabled from config
        if (!ComponentHelper::getParams('com_jpcomments')->get('project_dash_comments_display', 1)) {

            require_once JPATH_ROOT.'/components/com_jpcomments/helpers/comments.php';

            $count = \JPCommentsHelperComments::getCountOfPublishedComments($project_id);

            $components[] = [
                'com' => 'com_jpcomments',
                'title' => 'COM_JOOMPROJECT_COMMENTS',
                'link' => JoomprojectHelperRoute::getProjectDashCommentsRoute(),
                'icon' => 'fas fa-comments',
                'view' => ['comments'],
                'count' => $count
            ];


        }


        return $components;

    }


}