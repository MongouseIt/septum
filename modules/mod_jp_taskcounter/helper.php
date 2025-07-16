<?php
/**
 * @package      mod_jp_taskcounter
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2015 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;


/**
 * Module helper class
 *
 */
abstract class modJPtaskcounterHelper
{
    /**
     * Method to get the number of tasks
     *
     * @param     object     $params    Module settings
     *
     * @return    integer    $count     The number of tasks
     */
    public static function getCount($params)
    {
        $user = Factory::getApplication()->getIdentity();

        if (!$user->id || !count($user->getAuthorisedViewLevels())) {
            return 0;
        }

        // Get module filter settings
        $filter_progress    = $params->get('filter_progress', "");
        $filter_priority    = (int) $params->get('filter_priority', 0);
        $filter_assignee    = (int) $params->get('filter_assignee', 0);
        $filter_schedule    = (int) $params->get('filter_schedule', 0);
        $filter_published   = (int) $params->get('filter_published', 1);
        $filter_unpublished = (int) $params->get('filter_unpublished', 0);
        $filter_archived    = (int) $params->get('filter_archived', 0);
        $filter_trashed     = (int) $params->get('filter_trashed', 0);
        $filter_project     = (int) $params->get('filter_project', 0);

        // Prepare project filter
        if (!$filter_project) {
            $filter_project = (int) JPApplicationHelper::getActiveProjectId();
        }

        // Prepare state filter
        $filter_state = array();

        if ($filter_published)   $filter_state[] = 1;
        if ($filter_unpublished) $filter_state[] = 0;
        if ($filter_archived)    $filter_state[] = 2;
        if ($filter_trashed)     $filter_state[] = -2;

        if (!count($filter_state)) {
            $filter_state[] = 1;
            $filter_state[] = 2;
        }

        // Prepare query
        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select('COUNT(*)')
              ->from('#__jp_tasks AS a');

        // Filter on project
        if ($filter_project) {
            $query->where('a.project_id = ' . $filter_project);
        }

        // Filter state
        if (count($filter_state) > 1) {
            $query->where('a.state IN(' . implode(', ', $filter_state) . ')');
        }
        else {
            $query->where('a.state = ' . $filter_state[0]);
        }

        // Filter access, old method
        /*if (!$user->authorise('core.admin')) {
            $levels = implode(',', $user->getAuthorisedViewLevels());

            $query->where('a.access IN (' . $levels . ')');
        }*/


	    /* the new method of access level */
	    JPUserHelper::filterByViewAccess($query,'project','tasks','tasks');

        // Filter priority
        if ($filter_priority) {
            $query->where('a.priority = ' . $filter_priority);
        }

        // Filter schedule and progress
        if ($filter_schedule) {
            $date = Factory::getDate()->toSql();

            $query->where('(a.end_date != ' . $db->quote($db->getNullDate()) . ' AND a.end_date < \'' . $date . '\' AND a.complete = 0)');
        }
        elseif ($filter_progress === "0") {
            $query->where('a.complete = 0');
        }
        elseif ($filter_progress === "1") {
            $query->where('a.complete = 1');
        }

        // Filter assignee
        if ($filter_assignee) {
            $query->join('inner', '#__jp_ref_users AS u ON (u.item_type = \'com_jptasks.task\' AND u.item_id = a.id AND u.user_id = ' . (int) $user->id . ')');
        }


        $db->setQuery($query);
        $count = (int) $db->loadResult();

        return $count;
    }


    /**
     * Method to get the link to task list
     *
     * @param     object    $params    Module settings
     *
     * @return    string    $link      The generated link
     */
    public static function getLink($params)
    {
        $user = Factory::getApplication()->getIdentity();

        if (!$user->id || !count($user->getAuthorisedViewLevels())) {
            return "";
        }

        // Get module filter settings
        $filter_progress    = $params->get('filter_progress', "");
        $filter_priority    = $params->get('filter_priority', "");
        $filter_assignee    = (int) $params->get('filter_assignee', 0);
        $filter_schedule    = (int) $params->get('filter_schedule', 0);
        $filter_published   = (int) $params->get('filter_published', 1);
        $filter_unpublished = (int) $params->get('filter_unpublished', 0);
        $filter_archived    = (int) $params->get('filter_archived', 0);
        $filter_trashed     = (int) $params->get('filter_trashed', 0);
        $filter_project     = (int) $params->get('filter_project', 0);


        // Prepare project filter
        if (!$filter_project) {
            $filter_project = (int) JPApplicationHelper::getActiveProjectId();
        }

        // Prepare state filter
        $filter_state = array();

        if ($filter_published)   $filter_state[] = 1;
        if ($filter_unpublished) $filter_state[] = 0;
        if ($filter_archived)    $filter_state[] = 2;
        if ($filter_trashed)     $filter_state[] = -2;

        if (!count($filter_state)) {
            $filter_state[] = 1;
            $filter_state[] = 2;
        }

        $link = 'index.php?option=com_jptasks'
              . '&filter_project=' . $filter_project
              . '&filter_milestone=0'
              . '&filter_tasklist=0'
              . '&filter_author=0'
              . '&filter_published=' . $filter_state[0];

        if ($filter_assignee) {
            $link .= '&filter_assigned=' . $user->id;
        }
        else {
            $link .= '&filter_assigned=';
        }

        if ($filter_priority === "") {
            $link .= '&filter_priority=';
        }
        else {
            $link .= '&filter_priority=' . (int) $filter_priority;
        }

        if ($filter_schedule) {
            $link .= '&filter_complete=0&ordering=a.end_date&direction=asc';
        }
        elseif ($filter_progress === "0") {
            $link .= '&filter_complete=0';
        }
        elseif ($filter_progress === "1") {
            $link .= '&filter_complete=1';
        }
        else {
            $link .= '&filter_complete=';
        }

        if ($item = JPApplicationHelper::itemRoute(null, 'com_jptasks.tasks')) {
            $link .= '&Itemid=' . $item;
        }

        return Route::_($link);
    }
}
