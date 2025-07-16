<?php
/**
* @package      mod_jp_calendar
*
* @author       JoomBoost
* @copyright    Copyright (C) 2006-2018 JoomBoost. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;


JLoader::register('JPprojectsHelperRoute', JPATH_SITE . '/components/com_jpprojects/helpers/route.php');
JLoader::register('JPmilestonesHelperRoute', JPATH_SITE . '/components/com_jpmilestones/helpers/route.php');
JLoader::register('JPtasksHelperRoute', JPATH_SITE . '/components/com_jptasks/helpers/route.php');

require_once dirname(__FILE__) . '/helpers/projects.php';
require_once dirname(__FILE__) . '/helpers/milestones.php';
require_once dirname(__FILE__) . '/helpers/tasks.php';


/**
 * Module helper class
 *
 */
abstract class modJPcalendarHelper
{
    /**
     * Module parameters
     *
     * @var    object
     */
    public static $params;

    /**
     * Module id
     *
     * @var    integer
     */
    public static $id;

    /**
     * Currently active project id
     *
     * @var    integer
     */
    public static $project;

    /**
     * Calculated project start date
     *
     * @var    string
     */
    public static $project_start;

    /**
     * Calculated project end date
     *
     * @var    string
     */
    public static $project_end;

    /**
     * The current (floored to day) time stamp
     *
     * @var    integer
     */
    public static $time_today;


    /**
     * Init the helper class and populates member vars
     *
     * @param    object    $params    The module params
     * @param    integer   $id        The module id
     *
     * @return    void
     */
    public static function init($params, $id)
    {

        $user   = Factory::getApplication()->getIdentity();
        $config = Factory::getConfig();

        $date   = Factory::getDate('now', 'UTC');
        $date->setTimezone(new DateTimeZone($user->getParam('timezone', $config->get('offset'))));

        self::$time_today = floor(strtotime($date->format('Y-m-d H:i:s', true, false)) / 86400) * 86400;
        self::$params     = $params;
        self::$id         = (int) $id;

        if ($params->get('mode')) {
            self::$project = (int) $params->get('project');
        }
        else {
            self::$project = (int) JPApplicationHelper::getActiveProjectId();
        }

        // Get project dates
        $dates = modJPcalendarHelperProjects::getDateRange();

        self::$project_start = $dates[0];
        self::$project_end   = $dates[1];
    }


    /**
     * Method to load the calendar js script
     *
     * @return    void
     */
    public static function loadMedia()
    {
        // Load only of doc type is HTML
        if (Factory::getDocument()->getType() == 'html') {
            JoomProjectHelperWebasset::$wa
                ->useScript('mod_jp_calendar.fullcalendar')
                ->useStyle('mod_jp_calendar.fullcalendar');

        }
    }


    /**
     * Method to return a list of items to render in the chart
     *
     * @return    array
     */
    public static function getItems()
    {
        return (self::$project > 0 ? self::getItemsDetail() : self::getItemsOverview());
    }


    /**
     * Method to get a json encoded list of projects to render in the calendar
     *
     * @return    array    $items    The items to render
     */
    protected static function getItemsOverview()
    {
        $params = self::$params;
        $data   = modJPcalendarHelperProjects::getItems();
        $db     = Factory::getDbo();
        $items  = array();

        $display  = (int) $params->get('project_display', 0);
        $truncate = (int) $params->get('truncate', 0);

        foreach ($data AS $i => $record)
        {
            $title  = ucfirst(htmlspecialchars($record->title, ENT_COMPAT, 'UTF-8'));
            $title2 = str_replace('&amp;', '&', $title);

            if ($truncate > 0 && strlen($title) > ($truncate + 3)) {
                $title = substr($title, 0, $truncate) . '...';
            }

            $item  = [];
            $item['title'] = $title;
            $item['title_alt'] = $title2;

            // Display style
            if ($display == 0) {
                // Deadline
                // $item .= 'start:new Date(' . ($record->end_time * 1000) . '),';
                $item['start']= Factory::getDate()->setDate($record->end_year,$record->end_month,$record->end_day)->format('Y-m-d');
                $item['ev_type']= '';
                $item['allDay'] = true;
            }
            elseif ($display == 1) {
                // Start and Deadline
                if ($record->start_time > 0) {
                    $item2  = [];
                    $item2['title'] = $title;
                    $item2['title_alt'] = $title2;
                    // $item2 .= 'start:new Date(' . ($record->start_time * 1000) . '),';
                    $item2['start'] = Factory::getDate()->setDate($record->start_year,$record->start_month,$record->start_day)->format('Y-m-d');
                    $item2['allDay'] = true;
                    $item2['ev_type'] = "start";

                    // Color
                    if ($record->complete) {
                        $item2['color'] = "#468847";
                    }

                    elseif ($record->end_time < self::$time_today) {
                        $item2['color'] = "#B94A48";
                    }

                    $item2['url'] = Route::_(JPprojectsHelperRoute::getDashboardRoute($record->id . ':' . $record->alias));

                    $items[] = $item2;
                }

                // $item .= 'start:new Date(' . ($record->end_time * 1000) . '),';
                $item['start']= Factory::getDate()->setDate($record->end_year,$record->end_month,$record->end_day)->format('Y-m-d');
                $item['ev_type']= 'end';
                $item['allDay'] = true;
            }
            elseif ($display == 2) {


                // Entire span
                if ($record->start_time > 0) {
                    // $item .= 'start:new Date(' . ($record->start_time * 1000) . '),';
                    $item['start'] = Factory::getDate()->setDate($record->start_year,$record->start_month,$record->start_day)->format('Y-m-d');
                    // $item .= 'end:new Date(' . ($record->end_time * 1000) . '),';
                    $item['end'] = Factory::getDate()->setDate($record->end_year,$record->end_month,$record->end_day)->format('Y-m-d');

                    $item['ev_type'] = 'span';
                }
                else {
                    // $item .= 'start:new Date(' . ($record->end_time * 1000) . '),';
                    $item['start'] = Factory::getDate()->setDate($record->end_year,$record->end_month,$record->end_day)->format('Y-m-d');
                    $item['ev_type'] = 'end';
                    $item['allDay'] = true;
                }
            }

            // Color
            if ($record->complete) {
                $item['color'] = '#468847';
            }
            elseif ($record->end_time < self::$time_today) {
                $item['color'] = '#B94A48';
            }

            $item['url'] = Route::_(JPprojectsHelperRoute::getDashboardRoute($record->id . ':' . $record->alias));

            $items[] = $item;

        }

	    $items = json_encode($items);

	    $items = preg_replace('/"([^"]+)"\s*:\s*/', '$1:', $items);

	    return $items;
    }


    /**
     * Method to get a list of items of a project to render in the calendar
     *
     * @return    array    $items    The items to render
     */
    protected static function getItemsDetail()
    {
        $data   = self::getProjectDetails();
        $user   = Factory::getApplication()->getIdentity();
        $params = self::$params;
        $uid    = $user->id;
        $items  = array();

        $can_edit_m_state = $user->authorise('core.edit.state', 'com_jpmilestones');
        $can_edit_t_state = $user->authorise('core.edit.state', 'com_jptasks');

        $default    = array('1', '0', '2');
        $show_ms    = $params->get('show_milestones', $default);
        $show_t     = $params->get('show_tasks', $default);
        $ms_display = (int) $params->get('ms_display', 0);
        $t_display  = (int) $params->get('task_display', 0);

        $filter_ms_complete = (int) $params->get('filter_ms_complete', 1);
        $filter_t_complete  = (int) $params->get('filter_t_complete', 1);
        $filter_assigned    = (int) $params->get('filter_assigned', 0);
        $truncate           = (int) $params->get('truncate', 0);

        // Fix empty "Show X" settings
        if (is_array($show_ms) && count($show_ms) == 0) {
            $show_ms = $default;
        }

        if (is_array($show_t) && count($show_t) == 0) {
            $show_t = $default;
        }

        foreach ($data AS $i => $record)
        {
            $title  = ucfirst(htmlspecialchars($record->title, ENT_COMPAT, 'UTF-8'));
            $title2 = str_replace('&amp;', '&', $title);

            if ($truncate > 0 && strlen($title) > ($truncate + 3)) {
                $title = substr($title, 0, $truncate) . '...';
            }

            $item = [];
            $item['title'] = $title;
            $item['title_alt'] = $title2;

            if ($record->type == 'milestone') {
                // Hide milestone?
                if (!in_array($record->state, $show_ms) || ($filter_ms_complete == 1 && $record->complete)) {
                    continue;
                }

                $slug  = $record->id . ':' . $record->alias;
                $pslug = self::$project . ':' . $record->p_alias;
                $link  = Route::_(JPmilestonesHelperRoute::getMilestoneRoute($slug, $pslug));

                $item['i_type']= 'ms';

                if ($ms_display == 0) {
                    // Deadline
                    // $item .= 'start:new Date(' . ($record->end_time * 1000) . '),';
                    $item['start'] = Factory::getDate()->setDate($record->end_year,$record->end_month,$record->end_day)->format('Y-m-d');
                    $item['ev_type'] = "";
                    $item['allDay'] = true;
                }
                elseif ($ms_display == 1) {
                    // Start and Deadline
                    if ($record->start_time > 0) {
                        $item2  = [];
                        $item2['title'] = $title;
                        $item2['title_alt'] = $title2;
                        // $item2 .= 'start:new Date(' . ($record->start_time * 1000) . '),';
                        $item2['start'] = Factory::getDate()->setDate($record->start_year,$record->start_month,$record->start_day)->format('Y-m-d');
                        $item2['allDay'] = true;
                        $item2['ev_type'] = 'start';

                        // Color
                        if ($record->complete) {
                            $item2['color'] = "#468847";
                        }
                        elseif ($record->end_time < self::$time_today) {
                            $item2['color'] = "#B94A48";
                        }

                        $item2['url'] = Route::_(JPmilestonesHelperRoute::getMilestoneRoute($slug, $pslug));


                        $items[] = $item2;
                    }

                    // $item .= 'start:new Date(' . ($record->end_time * 1000) . '),';
                    $item['start'] = Factory::getDate()->setDate($record->end_year,$record->end_month,$record->end_day)->format('Y-m-d');
                    $item['ev_type'] = "end";
                    $item['allDay'] = true;
                }
                elseif ($ms_display == 2) {
                    // Entire span
                    if ($record->start_time > 0) {
                        // $item .= 'start:new Date(' . ($record->start_time * 1000) . '),';
                        $item['start'] = Factory::getDate()->setDate($record->start_year,$record->start_month,$record->start_day)->format('Y-m-d');
                        // $item .= 'end:new Date(' . ($record->end_time * 1000) . '),';
                        $item['end'] = Factory::getDate()->setDate($record->end_year,$record->end_month,$record->end_day)->format('Y-m-d');
                        $item['ev_type'] = 'span';
                    }
                    else {
                        // $item .= 'start:new Date(' . ($record->end_time * 1000) . '),';
                        $item['start'] = Factory::getDate()->setDate($record->end_year,$record->end_month,$record->end_day)->format('Y-m-d');
                        $item['ev_type'] = "end";
                        $item['allDay'] = true;
                    }
                }
            }

            if ($record->type == 'task') {
                // Hide task?
                if (!in_array($record->state, $show_t) || ($filter_t_complete == 1 && $record->complete)) {
                    continue;
                }

                if ($filter_assigned && !in_array($uid, $record->users)) {
                    continue;
                }

                if ($record->complete) {
                    $item['i_type'] = "tc";
                }
                else {
                    $item['i_type'] = 'ti';
                }

                $slug  = $record->id . ':' . $record->alias;
                $pslug = self::$project . ':' . $record->p_alias;
                $mslug = $record->milestone_id . ':' . $record->m_alias;
                $lslug = $record->list_id . ':' . $record->l_alias;
                $link  = Route::_(JPtasksHelperRoute::getTaskRoute($slug, $pslug, $mslug, $lslug));

                if ($t_display == 0) {
                    // Deadline
                    // $item .= 'start:new Date(' . ($record->end_time * 1000) . '),';
                    $item['start'] = Factory::getDate()->setDate($record->end_year,$record->end_month,$record->end_day)->format('Y-m-d');
                    $item['ev_type'] = "";
                    $item['allDay'] = true;
                }
                elseif ($t_display == 1) {
                    // Start and Deadline
                    if ($record->start_time > 0) {

                        $item2['title'] = ucfirst(htmlspecialchars($record->title));
                        // $item2 .= 'start:new Date(' . ($record->start_time * 1000) . '),';
                        $item2['start'] = Factory::getDate()->setDate($record->start_year,$record->start_month,$record->start_day)->format('Y-m-d');
                        $item2['allDay'] = true;
                        $item2['ev_type'] = "start";

                        // Color
                        if ($record->complete) {
                            $item2['color'] = '#468847';
                        }
                        elseif ($record->end_time < self::$time_today) {
                            $item2['color'] = '#B94A48';
                        }

                        $item2['url'] = Route::_(JPtasksHelperRoute::getTaskRoute($slug, $pslug, $mslug, $lslug));

                        $items[] = $item2;
                    }

                    // $item .= 'start:new Date(' . ($record->end_time * 1000) . '),';
                    $item['start'] = Factory::getDate()->setDate($record->end_year,$record->end_month,$record->end_day)->format('Y-m-d');
                    $item['ev_type'] = "end";
                    $item['allDay'] = true;
                }
                elseif ($t_display == 2) {
                    // Entire span
                    if ($record->start_time > 0) {
                        // $item .= 'start:new Date(' . ($record->start_time * 1000) . '),';
                        $item['start'] = Factory::getDate()->setDate($record->start_year,$record->start_month,$record->start_day)->format('Y-m-d');
                        // $item .= 'end:new Date(' . ($record->end_time * 1000) . '),';
                        $item['end'] = Factory::getDate()->setDate($record->end_year,$record->end_month,$record->end_day)->format('Y-m-d');
                        $item['ev_type'] = "span";
                    }
                    else {
                        // $item .= 'start:new Date(' . ($record->end_time * 1000) . '),';
                        $item['start'] = Factory::getDate()->setDate($record->end_year,$record->end_month,$record->end_day)->format('Y-m-d');
                        $item['ev_type'] = "end";
                        $item['allDay'] = true;
                    }
                }
            }

            // Color
            if ($record->complete) {
                $item['color'] = "#468847";
            }
            elseif ($record->end_time < self::$time_today) {
                $item['color'] = "#B94A48";
            }

            $item['url'] = $link;

            $items[] = $item;
        }

        $items = json_encode($items);

	    $items = preg_replace('/"([^"]+)"\s*:\s*/', '$1:', $items);

	    return $items;

    }


    /**
     * Method to get a list of project items:
     * Milestones and tasks
     *
     * @return    array
     */
    protected static function getProjectDetails()
    {
        $pid = self::$project;

        $milestones = modJPcalendarHelperMilestones::getItems($pid);
        $tasks      = modJPcalendarHelperTasks::getItems($pid);

        // Sort data by hierarchy
        $items = self::sortByHierarchy($milestones, $tasks);

        return $items;
    }


    /**
     * Method to sort items by duration
     *
     * @param     array     $data     The items to sort
     *
     * @return    array     $items    The sorted items
     */
    protected static function sortByDuration($data)
    {
        $frames = array();
        $items  = array();

        foreach ($data AS $i => $item)
        {
            // Add item to time frame
            if (!isset($frames[$item->duration])) $frames[$item->duration] = array();

            $frames[$item->duration][] = $item;
        }

        // Sort frames
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
     * Method to sort items by start date
     *
     * @param     array     $data     The items to sort
     *
     * @return    array     $items    The sorted items
     */
    protected static function sortByStartDate($data, $duration = true)
    {
        $frames = array();
        $items  = array();

        foreach ($data AS $i => $item)
        {
            // Add item to time frame
            if (!isset($frames[$item->start_time])) $frames[$item->start_time] = array();

            $frames[$item->start_time][] = $item;
        }

        // Sort by duration
        if ($duration) {
            foreach ($frames AS $key => $elements)
            {
                $frames[$key] = self::sortByDuration($elements);
            }
        }

        // Sort frames
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
     * Method to sort items by hierarchy
     *
     * @param     array    $milestones    The milestones
     * @param     array    $tasks         The tasks
     *
     * @return    array    $items         The sorted items
     */
    protected static function sortByHierarchy($milestones, $tasks)
    {
        $params = self::$params;

        $ms_display = (int) $params->get('ms_display');

        $items    = array();
        $structure = array('top' => array());

        foreach($milestones AS $milestone)
        {
            $ms_key = 'm.' . $milestone->id;

            $structure['top'][] = $milestone;
            $structure[$ms_key] = array();

            // Loop through milestone tasks
            foreach ($tasks AS $ti => $task)
            {
                if ($task->milestone_id != $milestone->id) {
                    continue;
                }

                $t_key = 't.' . $task->id;

                $structure[$ms_key][] = $task;
                $structure[$t_key]    = array();

                unset($tasks[$ti]);
            }
        }

        // Loop through remaining tasks
        foreach ($tasks AS $ti => $task)
        {
            $t_key = 't.' . $task->id;

            $structure['top'][] = $task;
            $structure[$t_key]  = array();

            unset($tasks[$ti]);
        }

        $top  = self::sortByStartDate($structure['top']);
        $keys = array('milestone' => 'm.', 'task' => 't.');

        foreach ($top AS $i => $item)
        {
            if (!($item->type == 'milestone' && $ms_display)) {
                $items[] = $item;
            }

            $key = $keys[$item->type] . $item->id;

            if (isset($structure[$key]) && count($structure[$key])) {
                $children = self::sortByStartDate($structure[$key]);

                foreach ($children AS $child)
                {
                    $items[] = $child;

                    $key2 = $keys[$child->type] . $child->id;

                    if (isset($structure[$key2]) && count($structure[$key2])) {
                        $children2 = self::sortByStartDate($structure[$key2]);

                        foreach ($children2 AS $child2)
                        {
                            $items[] = $child2;
                        }
                    }
                }
            }

            if ($item->type == 'milestone' && $ms_display) {
                $items[] = $item;
            }
        }

        return $items;
    }
}


function triggerJPcalendarScript()
{
    HTMLHelper::_('script', 'mod_jp_calendar/jquery.fn.fullcalendar.min.js', array('version' => 'auto', 'relative' => true));
}
