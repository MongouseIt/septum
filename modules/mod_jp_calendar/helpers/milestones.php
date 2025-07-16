<?php
/**
* @package      mod_jp_calendar
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
 * Calendar Module helper class
 *
 */
abstract class modJPcalendarHelperMilestones
{
    /**
     * Method to get a list of milestones
     *
     * @param     integer    $pid      The parent project
     *
     * @return    array      $items    The milestones
     */
    public static function getItems($pid = 0)
    {
        if (!$pid) return array();

        $user   = Factory::getApplication()->getIdentity();
        $config = Factory::getConfig();
        $db     = Factory::getDbo();
        $query  = $db->getQuery(true);
        $nd     = $db->getNullDate();

        $query->select('a.id, a.title, a.alias, a.created, a.state, a.start_date, a.end_date')
              ->select('p.alias AS p_alias')
              ->from('#__jp_milestones AS a')
              ->join('LEFT', '#__jp_projects AS p ON p.id = a.project_id')
              ->where('a.project_id = ' . $pid)
              ->where('a.state != -2');

        // Filter access, old method
        /*if (!$user->authorise('core.admin')) {
            $query->where('a.access IN(' . implode(', ', $user->getAuthorisedViewLevels()) . ')');
        }*/
		// new method of filter by access
	    JPUserHelper::filterByViewAccess($query,'project','milestones','milestones');

        $query->order('a.id ASC');

        $db->setQuery($query);
        $data = $db->loadObjectList();

        if (!is_array($data)) return array();

        // Prepare data
        $frames    = array();
        $pks       = ArrayHelper::getColumn($data, 'id');
        $completed = self::getCompleted($pks);

        foreach ($data AS $i => $item)
        {
            // Check start date
            if ($item->start_date == $nd || is_null($item->start_date)) $item->start_date = self::getStartDate($item->id);

            // Check end date
            if ($item->end_date == $nd || is_null($item->end_date)) $item->end_date = self::getEndDate($item->id);

            // Skip item if no end is set
            if ($item->end_date == $nd || is_null($item->end_date)) continue;

            // Set completition state
            $item->complete = $completed[$item->id];

            // Floor the start and end date
            if ($item->start_date == $nd || is_null($item->start_date)) {
                $item->start_date = null;
                $item->start_time = 0;
                $start_time = 0;
            }
            else {
                $start_date = new Date($item->start_date, 'UTC');
                $start_date->setTimezone(new DateTimeZone($user->getParam('timezone', $config->get('offset'))));

                $start_time = floor($start_date->toUnix() / 86400) * 86400;

                $item->start_date  = $start_date->format('Y-m-d H:i:s', true, false);
                $item->start_time  = $start_time;
                $item->start_year  = (int) $start_date->format('Y', true, false);
                $item->start_month = (int) $start_date->format('m', true, false);
                $item->start_day   = (int) $start_date->format('d', true, false);
            }

            $end_date = new Date($item->end_date, 'UTC');
            $end_date->setTimezone(new DateTimeZone($user->getParam('timezone', $config->get('offset'))));

            $end_time = floor($end_date->toUnix() / 86400) * 86400;

            $item->end_date   = $end_date->format('Y-m-d H:i:s', true, false);
            $item->end_time   = $end_time;
            $item->end_year   = (int) $end_date->format('Y', true, false);
            $item->end_month  = (int) $end_date->format('m', true, false);
            $item->end_day    = (int) $end_date->format('d', true, false);

            // Calculate the duration
            $item->duration = $end_time - $start_time;

            // Set item type
            $item->type = 'milestone';

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
     * Method to get the start date of a milestone
     * based on the earliest task start date assigned to it
     *
     * @param    integer    $id      The milestone id
     *
     * @return   string              The start date
     */
    public static function getStartDate($id)
    {
        static $cache = array();

        // Check the cache
        if (isset($cache[$id])) return $cache[$id];

        $db    = Factory::getDbo();
        $nd    = $db->getNullDate();
        $query = $db->getQuery(true);

        // Get the earliest task start date
        $query->select('start_date')
              ->from('#__jp_tasks')
              ->where('milestone_id = ' . (int) $id)
              ->where('state != -2')
              ->order('start_date ASC');

        $db->setQuery($query, 0, 1);
        $date = $db->loadResult();

        // Fall back to project start if the task has no start set
        if (empty($date) || $date == $nd || is_null($date)) {
            $date = modJPcalendarHelper::$project_start;
        }

        $cache[$id] = $date;

        return $cache[$id];
    }


    /**
     * Method to get the end date of a milestone
     * based on the latest task assigned to it
     *
     * @param    integer    $id      The milestone id
     * @param    string     $date    The milestone end date
     *
     * @return   string              The end date
     */
    public static function getEndDate($id, $date = null)
    {
        static $cache = array();

        // Check the cache
        if (isset($cache[$id])) return $cache[$id];

        $db    = Factory::getDbo();
        $nd    = $db->getNullDate();
        $query = $db->getQuery(true);

        // Get the latest task deadline
        $query->select('end_date')
              ->from('#__jp_tasks')
              ->where('milestone_id = ' . (int) $id)
              ->where('state != -2')
              ->order('end_date DESC');

        $db->setQuery($query, 0, 1);
        $date = $db->loadResult();

        // Fall back to project end if the task has no date set
        if (empty($date) || $date == $nd || is_null($date)) {
            $date = modJPcalendarHelper::$project_end;
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
        $query->select('milestone_id, COUNT(id) AS complete')
              ->from('#__jp_tasks')
              ->where('milestone_id IN(' . implode(',', $pks) . ') ')
              ->where('state != -2')
              ->where('complete = 1')
              ->group('milestone_id')
              ->order('id ASC');

        $db->setQuery($query);
        $completed = $db->loadAssocList('milestone_id', 'complete');

        // Count total tasks
        $query->clear();
        $query->select('milestone_id, COUNT(id) AS total')
              ->from('#__jp_tasks')
              ->where('milestone_id IN(' . implode(',', $pks) . ') ')
              ->where('state != -2')
              ->group('milestone_id')
              ->order('id ASC');

        $db->setQuery($query);
        $total = $db->loadAssocList('milestone_id', 'total');

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