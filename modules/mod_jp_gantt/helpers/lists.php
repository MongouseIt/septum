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
abstract class modJPganttHelperLists
{
    /**
     * Method to get a list of task lists
     *
     * @param     integer    $pid      The parent project
     *
     * @return    array      $items    The task lists
     */
    public static function getItems($pid = 0)
    {
        if (!$pid) return array();

        $user   = Factory::getApplication()->getIdentity();
        $config = Factory::getConfig();
        $db     = Factory::getDbo();
        $query  = $db->getQuery(true);
        $nd     = $db->getNullDate();

        $query->select('a.id, a.milestone_id, a.title, a.alias, a.created, a.state')
              ->select('p.alias AS p_alias, m.alias AS m_alias')
              ->select('m.start_date AS m_start, m.end_date AS m_end')
              ->from('#__jp_task_lists AS a')
              ->join('LEFT', '#__jp_projects AS p ON p.id = a.project_id')
              ->join('LEFT', '#__jp_milestones AS m ON m.id = a.milestone_id')
              ->where('a.project_id = ' . $pid)
              ->where('a.state != -2');

        // Filter access
        if (!$user->authorise('core.admin')) {
            $query->where('a.access IN(' . implode(', ', $user->getAuthorisedViewLevels()) . ')');
        }

        $query->order('a.id ASC');
        $query->group('a.id');

        $db->setQuery($query);
        $data = $db->loadObjectList();

        if (!is_array($data)) return array();

        // Prepare data
        $frames    = array();
        $pks       = \Joomla\Utilities\ArrayHelper::getColumn($data, 'id');
        $completed = self::getCompleted($pks);

        foreach ($data AS $i => $item)
        {
            // Set dates
            $item->start_date = self::getStartDate($item->id, $item->milestone_id, $item->m_start);
            $item->end_date   = self::getEndDate($item->id, $item->milestone_id, $item->m_end);

            // Skip item if no start or end is set
            if ($item->start_date == $nd || $item->end_date == $nd) continue;

            // Set completition state
            $item->complete = $completed[$item->id];

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
            $item->type = 'tasklist';

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
     * Method to get the start date of a task list
     * based on the earliest task assigned to it.
     * Falls back to milestone and then to the project.
     *
     * @param    integer    $id         The task list id
     * @param    integer    $ms         The parent milestone id
     * @param    string     $ms_date    The parent milestone start date
     *
     * @return   string                 The start date
     */
    public static function getStartDate($id, $ms = 0, $ms_date = null)
    {
        static $cache = array();

        // Check the cache
        if (isset($cache[$id])) return $cache[$id];

        $db    = Factory::getDbo();
        $nd    = $db->getNullDate();
        $query = $db->getQuery(true);

        // Get the start date from the task
        $query->select('a.id, a.start_date, a.milestone_id')
              ->select('m.start_date AS m_start')
              ->from('#__jp_tasks AS a')
              ->join('left', '#__jp_milestones AS m ON m.id = a.milestone_id')
              ->where('a.list_id = ' . (int) $id)
              ->where('a.state != -2')
              ->order('a.start_date, a.id ASC');

        $db->setQuery($query, 0, 1);
        $task = $db->loadObject();

        if (empty($task)) {
            if (!empty($ms_date) && $ms_date != $nd) {
                $date = $ms_date;
            }
            elseif ($ms) {
                $date = modJPganttHelperMilestones::getStartDate($ms);
            }
            else {
                $date = modJPganttHelper::$project_start;
            }
        }
        elseif ($task->start_date == $nd) {
            $date = modJPganttHelperTasks::getStartDate($task->id, $task->milestone_id, $task->m_start);
        }
        else {
            $date = $task->start_date;
        }

        $cache[$id] = $date;

        return $cache[$id];
    }


    /**
     * Method to get the end date of a task list
     * based on the latest task assigned to it.
     * Falls back to milestone and then to the project.
     *
     * @param    integer    $id         The task list id
     * @param    integer    $ms         The parent milestone id
     * @param    string     $ms_date    The parent milestone end date
     *
     * @return   string                 The end date
     */
    public static function getEndDate($id, $ms = 0, $ms_date = null)
    {
        static $cache = array();

        // Check the cache
        if (isset($cache[$id])) return $cache[$id];

        $db    = Factory::getDbo();
        $nd    = $db->getNullDate();
        $query = $db->getQuery(true);

        // Get the end date from the task
        $query->select('a.id, a.end_date, a.milestone_id')
              ->select('m.end_date AS m_end')
              ->from('#__jp_tasks AS a')
              ->join('left', '#__jp_milestones AS m ON m.id = a.milestone_id')
              ->where('a.list_id = ' . (int) $id)
              ->where('a.state != -2')
              ->order('a.end_date DESC, a.id ASC');

        $db->setQuery($query, 0, 1);
        $task = $db->loadObject();

        if (empty($task)) {
            if (!empty($ms_date) && $ms_date != $nd) {
                $date = $ms_date;
            }
            elseif ($ms) {
                $date = modJPganttHelperMilestones::getEndDate($ms);
            }
            else {
                $date = modJPganttHelper::$project_end;
            }
        }
        elseif ($task->end_date == $nd) {
            $date = modJPganttHelperTasks::getEndDate($task->id, $task->milestone_id, $task->m_end);
        }
        else {
            $date = $task->end_date;
        }

        $cache[$id] = $date;

        return $cache[$id];
    }


    /**
     * Method to get a list of completed milestones
     *
     * @param    array    $pks    The item primary keys
     *
     * @return   array            The completed milestones
     */
    protected static function getCompleted($pks)
    {
        if (!is_array($pks) || count($pks) == 0) {
            return array();
        }

        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        // Count completed tasks
        $query->select('list_id, COUNT(id) AS complete')
              ->from('#__jp_tasks')
              ->where('list_id IN(' . implode(',', $pks) . ') ')
              ->where('state != -2')
              ->where('complete = 1')
              ->group('list_id')
              ->order('id ASC');

        $db->setQuery($query);
        $completed = $db->loadAssocList('list_id', 'complete');

        // Count total tasks
        $query->clear();
        $query->select('list_id, COUNT(id) AS total')
              ->from('#__jp_tasks')
              ->where('list_id IN(' . implode(',', $pks) . ') ')
              ->where('state != -2')
              ->group('list_id')
              ->order('id ASC');

        $db->setQuery($query);
        $total = $db->loadAssocList('list_id', 'total');

        $items = array();

        foreach ($pks AS $pk)
        {
            $count_complete = (int) (isset($completed[$pk]) ? $completed[$pk] : 0);
            $count_total    = (int) (isset($total[$pk])     ? $total[$pk]   : 0);

            if (!$count_total || $count_complete == $count_total) {
                $items[$pk] = true;
            }
            else {
                $items[$pk] = false;
            }
        }

        return $items;
    }
}