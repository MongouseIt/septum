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
abstract class modJPcalendarHelperProjects
{
    /**
     * Method to get a list of projects
     *
     * @return    array
     */
    public static function getItems()
    {
        $user   = Factory::getApplication()->getIdentity();
        $config = Factory::getConfig();
        $db     = Factory::getDbo();
        $query  = $db->getQuery(true);
        $params = modJPcalendarHelper::$params;

        $filter_author   = (int) $params->get('filter_project_author');
        $filter_complete = (int) $params->get('filter_project_complete');

        $query->select('a.id, a.title, a.alias, a.created, a.start_date, a.end_date')
              ->from('#__jp_projects AS a')
              ->where('a.state = 1');

        // Filter by author
        if ($filter_author) {
            $query->where('a.created_by = ' . (int) $user->get('id'));
        }

        // Filter access, old method
        /*if (!$user->authorise('core.admin')) {
            $query->where('a.access IN(' . implode(', ', $user->getAuthorisedViewLevels()) . ')');
        }*/

	    /* the new method of access level */
	    JPUserHelper::filterByViewAccess($query,'project','projects','projects');

        $query->order('id ASC');

	    $query->group('a.id');

        $db->setQuery($query);
        $data = $db->loadObjectList();

        if (!is_array($data)) return array();

        $items  = array();
        $frames = array();
        $nd     = $db->getNullDate();

        $pks       = ArrayHelper::getColumn($data, 'id');
        $completed = self::getCompleted($pks);

        // Prepare data
        foreach ($data AS $pk => $item)
        {
            // Check start date
            if ($item->start_date == $nd || is_null($item->start_date)) $item->start_date = $item->created;

            // Check end date
            if ($item->end_date == $nd || is_null($item->end_date)) $item->end_date = self::getEndDate($item->id);

            // Skip item if no end is set
            if ($item->end_date == $nd || is_null($item->end_date)) continue;

            // Skip if complete and filter is set
            if ($filter_complete && $completed[$item->id]) continue;

            // Set completition state
            $item->complete = $completed[$item->id];

            // Floor the start and end date
            if ($item->start_date == $nd || is_null($item->start_date)) {
                $item->start_date = null;
                $item->start_time = 0;
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

            $item->end_date  = $end_date->format('Y-m-d H:i:s', true, false);
            $item->end_time  = $end_time;
            $item->end_year  = (int) $end_date->format('Y', true, false);
            $item->end_month = (int) $end_date->format('m', true, false);
            $item->end_day   = (int) $end_date->format('d', true, false);

            // Calculate the duration
            $item->duration = $end_time - $start_time;

            // Add item to time frame
            if (!isset($frames[$start_time])) {
                $frames[$start_time] = array();
            }

            $frames[$start_time][] = $item;
        }

        // Sort frames by time
        ksort($frames);

        // Flatten the frames
        foreach ($frames AS $key => $elements)
        {
            foreach ($elements AS $item)
            {
                $items[] = $item;
            }
        }

        return $items;
    }


    /**
     * Method to get the start and end date of a project
     *
     * @param    integer    $pid    Optional project id
     *
     * @return   array              The start and end date
     */
    public static function getDateRange($pid = null)
    {
        $db    = Factory::getDbo();
        $nd    = $db->getNullDate();
        $start = $nd;
        $end   = $nd;

        if (!$pid) return array($start, $end);

        // Get the project dates
        $query = $db->getQuery(true);
        $query->select('created, start_date, end_date')
              ->from('#__jp_projects')
              ->where('id = ' . $pid);

        $db->setQuery($query);
        $dates = $db->loadObject();

        if (empty($dates)) return array($start, $end);

        // Check if the start date is set
        if ($dates->start_date == $nd) {
            $dates->start_date = $dates->created;
        }

        // Check if a deadline is set
        if ($dates->end_date == $nd) {
            $dates->end_date = self::getEndDate($pid);
        }

        $start = $dates->start_date;
        $end   = $dates->end_date;

        return array($start, $end);
    }


    /**
     * Method to get the end date of a project
     * based on the latest milestone or task deadline
     *
     * @param    integer    $id    The project id
     *
     * @return   string            The end date
     */
    public static function getEndDate($id)
    {
        $db    = Factory::getDbo();
        $nd    = $db->getNullDate();
        $query = $db->getQuery(true);

        // Get the latest task deadline
        $query->select('end_date')
              ->from('#__jp_tasks')
              ->where('project_id = ' . (int) $id)
              ->where('state != -2')
              ->order('end_date DESC');

        $db->setQuery($query, 0, 1);
        $task_end = $db->loadResult();

        if (empty($task_end)) $task_end = $nd;

        // Get the latest milestone deadline
        $query->clear();
        $query->select('end_date')
              ->from('#__jp_milestones')
              ->where('project_id = ' . (int) $id)
              ->where('state != -2')
              ->order('end_date DESC');

        $db->setQuery($query, 0, 1);
        $ms_end = $db->loadResult();

        if (empty($ms_end) || is_null($ms_end)) $ms_end = $nd;

        $task_time = ($task_end == $nd || is_null($task_end) ? 0 : strtotime($task_end));
        $ms_time   = ($ms_end == $nd || is_null($ms_end)   ? 0 : strtotime($ms_end));

        if (!$task_time && !$ms_time) return $nd;

        return ($task_time > $ms_time ? $task_end : $ms_end);
    }


    /**
     * Method to get a list of completed projects
     *
     * @param    array    $pks    The item primary keys
     *
     * @return   array            The completed projects
     */
    protected static function getCompleted($pks)
    {
        if (!is_array($pks) || count($pks) == 0) {
            return array();
        }

        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        // Count completed tasks
        $query->select('project_id, COUNT(id) AS complete')
              ->from('#__jp_tasks')
              ->where('project_id IN(' . implode(',', $pks) . ') ')
              ->where('state != -2')
              ->where('complete = 1')
              ->group('project_id')
              ->order('id ASC');

        $db->setQuery($query);
        $completed = $db->loadAssocList('project_id', 'complete');

        // Count total tasks
        $query->clear();
        $query->select('project_id, COUNT(id) AS total')
              ->from('#__jp_tasks')
              ->where('project_id IN(' . implode(',', $pks) . ') ')
              ->where('state != -2')
              ->group('project_id')
              ->order('id ASC');

        $db->setQuery($query);
        $total = $db->loadAssocList('project_id', 'total');

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