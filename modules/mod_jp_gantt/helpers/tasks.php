<?php
/**
* @package      mod_jp_gantt
*
* @author       JoomBoost
* @copyright    Copyright (C) 2018 JoomBoost. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();

use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;
/**
 * Gantt Module helper class
 *
 */
abstract class modJPganttHelperTasks
{
    public static function getItems($pid = 0)
    {
        if (!$pid) return array();

        $user   = Factory::getApplication()->getIdentity();
        $config = Factory::getConfig();
        $db     = Factory::getDbo();
        $query  = $db->getQuery(true);
        $nd     = $db->getNullDate();

        $query->select('a.id, a.milestone_id, a.list_id, a.title, a.alias')
              ->select('a.created, a.state, a.priority, a.complete, a.created, a.start_date, a.end_date')
              ->select('l.milestone_id AS l_ms, l.alias AS l_alias')
              ->select('m.alias AS m_alias, m.start_date AS m_start, m.end_date AS m_end')
              ->select('p.alias AS p_alias')
              ->from('#__jp_tasks AS a')
              ->join('LEFT', '#__jp_task_lists AS l ON l.id = a.list_id')
              ->join('LEFT', '#__jp_milestones AS m ON m.id = a.milestone_id')
              ->join('LEFT', '#__jp_projects AS p ON p.id = a.project_id')
              ->where('a.project_id = ' . $pid)
              ->where('a.state != -2');

        // Filter access, old method
        /*if (!$user->authorise('core.admin')) {
            $query->where('a.access IN(' . implode(', ', $user->getAuthorisedViewLevels()) . ')');
        }*/

	    /* the new method of access level */
	    JPUserHelper::filterByViewAccess($query,'project','tasks');



        $query->order('a.id ASC');

        $query->group('a.id');

        $db->setQuery($query);
        $data = $db->loadObjectList();

        if (!is_array($data)) return array();

        $frames       = array();
        $dependencies = self::getDependencies($pid);
        $children     = $dependencies['children'];
        $parents      = $dependencies['parents'];

        $pks   = ArrayHelper::getColumn($data, 'id');
        $users = self::getAssignees($pks);

        foreach ($data AS $i => $item)
        {
            // Check start date
            if ($item->start_date == $nd) {
                $item->start_date = self::getStartDate($item->id, $item->milestone_id, $item->m_start);
            }

            // Check end date
            if ($item->end_date == $nd) {
                $item->end_date = self::getEndDate($item->id, $item->milestone_id, $item->m_end);
            }

            // Skip item if no start or end is set
            if ($item->start_date == $nd || $item->end_date == $nd) {
                continue;
            }

            // Inject dependencies and assigned users
            $item->children = (isset($children[$item->id]) ? $children[$item->id] : array());
            $item->parents  = (isset($parents[$item->id])  ? $parents[$item->id]  : array());
            $item->users    = (isset($users[$item->id])    ? $users[$item->id]    : array());

            // Floor the start and end date
            $start_date = new Date((string) $item->start_date, 'UTC');
            $start_date->setTimezone(new DateTimeZone($user->getParam('timezone', $config->get('offset'))));

            $start_time = floor($start_date->toUnix() / 86400) * 86400;


            $end_date = new Date((string) $item->end_date, 'UTC');
            $end_date->setTimezone(new DateTimeZone($user->getParam('timezone', $config->get('offset'))));

            $end_time = floor($end_date->toUnix() / 86400) * 86400;


            $item->start_date  = $start_date->toSql();
            $item->start_time  = $start_time;
            $item->start_year  = (int) $start_date->format('Y', true, false);
            $item->start_month = (int) $start_date->format('m', true, false);
            $item->start_day   = (int) $start_date->format('d', true, false);

            $item->end_date   = $end_date->toSql();
            $item->end_time   = $end_time;
            $item->end_year   = (int) $end_date->format('Y', true, false);
            $item->end_month  = (int) $end_date->format('m', true, false);
            $item->end_day    = (int) $end_date->format('d', true, false);

            // Calculate the duration
            $item->duration = $end_time - $start_time;

            // Set item type
            $item->type = 'task';

            // Add item to time frame
            if (!isset($frames[$start_time])) {
                $frames[$start_time] = array();
            }

            $frames[$start_time][] = $item;
        }

        ksort($frames, SORT_NUMERIC);

        $items = array();

        foreach ($frames AS $key => $data)
        {
            foreach ($data AS $item)
            {
                $items[] = $item;
            }
        }

        return $items;
    }


    /**
     * Method to get the start date of a task
     * based on the parent list, milestone or project.
     *
     * @param    integer    $id          The task id
     * @param    integer    $ms          The parent milestone id
     * @param    string     $ms_date     The parent milestone start date
     *
     * @return   string                  The start date
     */
    public static function getStartDate($id, $ms = 0, $ms_date = null)
    {
        static $cache = array();

        // Check the cache
        if (isset($cache[$id])) return $cache[$id];

        $nd = Factory::getDbo()->getNullDate();

        if (!empty($ms_date) && $ms_date != $nd) {
            return $ms_date;
        }
        elseif ($ms) {
            $date = modJPganttHelperMilestones::getStartDate($ms);
        }
        else {
            $date = modJPganttHelper::$project_start;
        }

        $cache[$id] = $date;

        return $cache[$id];
    }


    /**
     * Method to get the end date of a task
     * based on the parent list, milestone or project.
     *
     * @param    integer    $id          The task id
     * @param    integer    $ms          The parent milestone id
     * @param    string     $ms_date     The parent milestone end date
     *
     * @return   string                  The end date
     */
    public static function getEndDate($id, $ms = 0, $ms_date = null)
    {
        static $cache = array();

        // Check the cache
        if (isset($cache[$id])) return $cache[$id];

        $nd = Factory::getDbo()->getNullDate();

        if (!empty($ms_date) && $ms_date != $nd) {
            return $ms_date;
        }
        elseif ($ms) {
            $date = modJPganttHelperMilestones::getEndDate($ms);
        }
        else {
            $date = modJPganttHelper::$project_end;
        }

        $cache[$id] = $date;

        return $cache[$id];
    }


    /**
     * Method to get the assigned users
     *
     * @param     array    $pks    The primary keys
     *
     * @return    array            The assigned users
     */
    protected static function getAssignees($pks)
    {
        $db    = Factory::getDbo();
        $query = $db->getQuery(true);
        $users = array();

        foreach ($pks AS $pk)
        {
            $query->clear()
                  ->select('u.id, u.username, u.name')
                  ->from('#__jp_ref_users AS a')
                  ->join('RIGHT', '#__users AS u ON u.id = a.user_id')
                  ->where('a.item_type = ' . $db->quote('com_jptasks.task'))
                  ->where('a.item_id = ' . (int) $pk);

            $db->setQuery($query);
            $items = $db->loadObjectList();

            if (!is_array($items)) $items = array();

            $users[$pk] = $items;
        }

        return $users;
    }


    /**
     * Method to get the task dependencies
     *
     * @param     array    $pid    The project id
     *
     * @return    array            The dependencies
     */
    protected static function getDependencies($pid)
    {
        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->clear()
              ->select('task_id, parent_id')
              ->from('#__jp_ref_tasks')
              ->where('project_id = ' . (int) $pid);

        $db->setQuery($query);
        $items = $db->loadObjectList();

        if (!is_array($items)) $items = array();

        $map_parents  = array();
        $map_children = array();

        foreach ($items AS $item)
        {
            if (!isset($map_parents[$item->task_id])) {
                $map_parents[$item->task_id] = array();
            }

            if (!isset($map_children[$item->parent_id])) {
                $map_children[$item->parent_id] = array();
            }

            $map_parents[$item->task_id][]    = $item->parent_id;
            $map_children[$item->parent_id][] = $item->task_id;
        }

        return array('parents' => $map_parents, 'children' => $map_children);
    }
}