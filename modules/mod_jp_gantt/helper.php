<?php
/**
* @package      mod_jp_gantt
*
* @author       JoomBoost
* @copyright    Copyright (C) 2006-2018 JoomBoost. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();

use Joomla\CMS\Date\Date;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;


JLoader::register('JPprojectsHelperRoute', JPATH_SITE . '/components/com_jpprojects/helpers/route.php');
JLoader::register('JPmilestonesHelperRoute', JPATH_SITE . '/components/com_jpmilestones/helpers/route.php');
JLoader::register('JPtasksHelperRoute', JPATH_SITE . '/components/com_jptasks/helpers/route.php');

require_once dirname(__FILE__) . '/helpers/projects.php';
require_once dirname(__FILE__) . '/helpers/milestones.php';
require_once dirname(__FILE__) . '/helpers/lists.php';
require_once dirname(__FILE__) . '/helpers/tasks.php';


/**
 * Module helper class
 *
 */
abstract class modJPganttHelper
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

        $date_now = new Date('now', 'UTC');
        $date_now->setTimezone(new DateTimeZone($user->getParam('timezone', $config->get('offset'))));

        self::$time_today = floor($date_now->toUnix() / 86400) * 86400;
        self::$params     = $params;
        self::$id         = (int) $id;

        if ($params->get('mode')) {
            self::$project = (int) $params->get('project');
        }
        else {
            self::$project = (int) JPApplicationHelper::getActiveProjectId();
        }

        // Get project dates
        $dates = modJPganttHelperProjects::getDateRange();

        self::$project_start = $dates[0];
        self::$project_end   = $dates[1];
    }


    /**
     * Method to load the gantt js script
     *
     * @return    void
     */
    public static function loadMedia()
    {
        // Load only of doc type is HTML
        if (Factory::getDocument()->getType() == 'html') {

            JoomProjectHelperWebasset::$wa
                ->useScript('mod_jp_gantt.gantt')
                ->useStyle('mod_jp_gantt.gantt');
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
     * Method to get a list of projects to render in the gantt chart
     *
     * @return    array    $items    The items to render
     */
    protected static function getItemsOverview()
    {
        $data   = modJPganttHelperProjects::getItems();
        $user   = Factory::getApplication()->getIdentity();
        $config = Factory::getConfig();
        $params = self::$params;
        $items  = array();

        $highlight = (int) $params->get('highlight_today');

        $date_now = new Date('now', 'UTC');
        $date_now->setTimezone(new DateTimeZone($user->getParam('timezone', $config->get('offset'))));

        $today = $date_now->toUnix() * 1000;

        foreach ($data AS $i => $record)
        {
            $link = Route::_(JPprojectsHelperRoute::getDashboardRoute($record->id . ':' . $record->alias));

            $name = '<i class="fas fa-star' . ($record->complete ? '' : '-empty') . '"></i> '
                  . '<a href="' . $link . '">'
                  . ucfirst(htmlspecialchars($record->title))
                  . '</a>';

            // Create row object
            $row = new stdClass();
            $row->name   = $name;
            $row->desc   = self::formatDuration($record->duration, $record->start_time, $record->end_time, $record->complete);
            $row->values = array();

            // Create row item object
            $item = new stdClass();
            $item->label = ucfirst(htmlspecialchars($record->title));
            $item->desc  = '';
            $item->from  = '/Date(' . $record->start_year . ', ' . ($record->start_month - 1) . ', ' . $record->start_day . ')/';
            $item->to    = '/Date(' . $record->end_year . ', ' . ($record->end_month - 1) . ', ' . $record->end_day . ')/';

            // Determine custom class
            $item->customClass = 'gantt-p';
            $item->id = 'gantt-p-' . $record->id;

            if ($record->complete) {
                $item->customClass .= '-complete';
            }
            elseif ($record->start_time > self::$time_today) {
                $item->customClass .= '-notstarted';
            }
            elseif ($record->end_time < self::$time_today) {
                $item->customClass .= '-behind';
            }

            // Highlight today?
            if ($highlight) {
                $hl = new stdClass();
                $hl->label = '';
                $hl->desc  = '';
                $hl->from  = '/Date(' . intval($date_now->format('Y', true, false)) .', ' . intval($date_now->format('m', true, false) - 1) .', ' . intval($date_now->format('d', true, false)) .')/';
                $hl->to    = '/Date(' . intval($date_now->format('Y', true, false)) .', ' . intval($date_now->format('m', true, false) - 1) .', ' . intval($date_now->format('d', true, false)) .')/';
                $hl->customClass = 'gantt-today';

                $row->values[] = $hl;
            }

            $row->values[] = $item;
            $items[]       = $row;
        }

        return $items;
    }


    /**
     * Method to get a list of items of a project to render in the gantt chart
     *
     * @return    array    $items    The items to render
     */
    protected static function getItemsDetail()
    {
        $data   = self::getProjectDetails();
        $user   = Factory::getApplication()->getIdentity();
        $config = Factory::getConfig();
        $params = self::$params;
        $items  = array();

        $can_edit_m_state = $user->authorise('core.edit.state', 'com_jpmilestones');
        $can_edit_t_state = $user->authorise('core.edit.state', 'com_jptasks');

        $default    = array('1', '0', '2');
        $show_ms    = $params->get('show_milestones', $default);
        $show_l     = $params->get('show_lists', $default);
        $show_t     = $params->get('show_tasks', $default);
        $ms_display = (int) $params->get('ms_display');
        $highlight  = (int) $params->get('highlight_today');

        $date_now = new Date('now', 'UTC');
        $date_now->setTimezone(new DateTimeZone($user->getParam('timezone', $config->get('offset'))));

        $today = $date_now->toUnix() * 1000;

        $icons = array(
            'milestone'       => 'fas fa-flag',
            'tasklist'        => 'fas fa-list',
            'task-complete'   => 'far fa-check-square',
            'task-incomplete' => 'far fa-square',
        );

        $prev_level = 0;
        $level      = 0;

        // Fix "Show Task Lists" setting from older module versions
        if (!is_array($show_l)) {
            if ($show_l == '1') {
                $show_l = array('1', '0', '2');
            }
            else {
                $show_l = array('-1');
            }
        }

        // Fix empty "Show X" settings
        if (is_array($show_ms) && count($show_ms) == 0) {
            $show_ms = $default;
        }

        if (is_array($show_l) && count($show_l) == 0) {
            $show_l = $default;
        }

        if (is_array($show_t) && count($show_t) == 0) {
            $show_t = $default;
        }


        // Map item keys
        $map_ms = array();
        $map_l  = array();
        $map_t  = array();

        foreach ($data AS $i => $record)
        {
            if ($record->type == 'milestone') {
                $map_ms[$record->id] = $i;
            }

            if ($record->type == 'tasklist') {
                $map_l[$record->id] = $i;
            }

            if ($record->type == 'task') {
                $map_t[$record->id] = $i;
            }
        }


        foreach ($data AS $i => $record)
        {
            $link       = '#';
            $prev_level = $level;
            $dep        = array();

            if ($record->type == 'milestone') {
                // Hide milestone?
                if (!in_array($record->state, $show_ms)) {
                    continue;
                }

                $slug  = $record->id . ':' . $record->alias;
                $pslug = self::$project . ':' . $record->p_alias;
                $link  = Route::_(JPmilestonesHelperRoute::getMilestoneRoute($slug, $pslug));
                $level = 0;
                $class = 'gantt-m';
                $bid   = 'gantt-m-' . $record->id;

                $can_edit = $can_edit_m_state;
            }

            if ($record->type == 'tasklist') {
                // Hide list?
                if (!in_array($record->state, $show_l)) {
                    continue;
                }

                $slug  = $record->id . ':' . $record->alias;
                $pslug = self::$project . ':' . $record->p_alias;
                $mslug = $record->milestone_id . ':' . $record->m_alias;
                $link  = Route::_(JPtasksHelperRoute::getTasksRoute($pslug, $mslug, $slug));
                $class = 'gantt-l';
                $bid   = 'gantt-l-' . $record->id;

                $can_edit = $can_edit_t_state;

                if ($record->milestone_id) {
                    if (isset($map_ms[$record->milestone_id])) {
                        $k  = $map_ms[$record->milestone_id];
                        $ms = $data[$k];

                        if (!in_array($ms->state, $show_ms)) {
                            $record->milestone_id = 0;
                        }
                    }
                }

                $level = ($record->milestone_id ? 1 : 0);
            }

            if ($record->type == 'task') {
                // Hide task?
                if (!in_array($record->state, $show_t)) {
                    continue;
                }

                $slug  = $record->id . ':' . $record->alias;
                $pslug = self::$project . ':' . $record->p_alias;
                $mslug = $record->milestone_id . ':' . $record->m_alias;
                $lslug = $record->list_id . ':' . $record->l_alias;
                $link  = Route::_(JPtasksHelperRoute::getTaskRoute($slug, $pslug, $mslug, $lslug));
                $bid   = 'gantt-t-' . $record->id;

                $record->type .= '-' . ($record->complete ? 'complete' : 'incomplete');

                if ($record->milestone_id) {
                    if (isset($map_ms[$record->milestone_id])) {
                        $k  = $map_ms[$record->milestone_id];
                        $ms = $data[$k];

                        if (!in_array($ms->state, $show_ms)) {
                            $record->milestone_id = 0;
                        }
                    }
                }

                if ($record->list_id) {
                    if (isset($map_l[$record->list_id])) {
                        $k = $map_l[$record->list_id];
                        $l = $data[$k];

                        if (!in_array($l->state, $show_l)) {
                            $record->list_id = 0;
                        }
                    }
                }

                if ($record->l_ms) {
                    if (isset($map_l[$record->l_ms])) {
                        $k = $map_l[$record->list_id];
                        $l = $data[$k];

                        if (!in_array($l->state, $show_l)) {
                            $record->l_ms = 0;
                        }
                    }
                }

                if (in_array('-1', $show_l)) $record->l_ms = 0;

                $level = ($record->list_id ? ($record->l_ms ? 2 : 1) : ($record->milestone_id ? 1 : 0));
                $class = 'gantt-t';

                $can_edit = $can_edit_t_state;

                // Add task dependencies
                if (count($record->parents)) {
                    foreach ($record->parents AS $parent)
                    {
                        $dep[] = 'gantt-t-' . $parent;
                    }
                }
            }

            // Set arrow direction and indentation
            if ($level == 0) {
                $indent = '<i class="fas fa-caret-right"></i> ';
            }
            else {
                $indent = '<i class="fas fa-caret-' . ($ms_display ? 'down' : 'up') . '" style="margin-left:' . (16 * $level) . 'px"></i> ';
            }

            if (!$can_edit) {
                $name = '<i class="' . $icons[$record->type] . '"></i> '
                      . '<span>'
                      . ucfirst(htmlspecialchars($record->title))
                      . '</span>';
            }
            else {
                $name = '<i class="' . $icons[$record->type] . '"></i> '
                      . '<a href="' . $link . '">'
                      . ucfirst(htmlspecialchars($record->title))
                      . '</a>';
            }

            // Create row object
            $row = new stdClass();
            $row->name   = $indent . $name;
            $row->desc   = self::formatDuration($record->duration, $record->start_time, $record->end_time, $record->complete);
            $row->values = array();

            // Create row item object
            $item = new stdClass();

            if ($record->type == 'milestone' && $ms_display) {
                $item->label = '<i class="fas fa-flag"></i>';
                $item->from  = '/Date(' . $record->end_year . ', ' . ($record->end_month - 1) . ', ' . $record->end_day . ')/';
            }
            else {
                $item->label = ucfirst(htmlspecialchars($record->title));
                $item->from  = '/Date(' . $record->start_year . ', ' . ($record->start_month - 1) . ', ' . $record->start_day . ')/';
            }

            $item->to    = '/Date(' . $record->end_year . ', ' . ($record->end_month - 1) . ', ' . $record->end_day . ')/';
            $item->id    = $bid;
            $item->desc  = '';

            // Determine custom class
            $item->customClass = $class;

            if ($record->complete) {
                $item->customClass .= '-complete';
            }
            elseif ($record->start_time > self::$time_today) {
                $item->customClass .= '-notstarted';
            }
            elseif ($record->end_time < self::$time_today) {
                $item->customClass .= '-behind';
            }

            // Add dependencies
            if (count($dep)) {
                $item->dep = $dep;
            }

            // Highlight today?
            if ($highlight) {
                $hl = new stdClass();
                $hl->label = '';
                $hl->desc  = '';
                $hl->from  = '/Date()/';
                $hl->to    = '/Date()/';
                $hl->customClass = 'gantt-today';

                $row->values[] = $hl;
            }

            $row->values[] = $item;
            $items[]       = $row;
        }

        return $items;
    }


    /**
     * Method to get a list of project items:
     * Milestones, task lists and tasks
     *
     * @return    array
     */
    protected static function getProjectDetails()
    {
        $params = self::$params;
        $pid    = self::$project;

        $default    = array('1', '0', '2');
        $show_lists = $params->get('show_lists', $default);

        // Fix "Show Task Lists" setting from older module versions
        if (!is_array($show_lists)) {
            if ($show_lists == '1') {
                $show_lists = array('1', '0', '2');
            }
            else {
                $show_lists = array('-1');
            }
        }

        $milestones = modJPganttHelperMilestones::getItems($pid);
        $lists      = ((!in_array('-1', $show_lists)) ? modJPganttHelperLists::getItems($pid) : array());
        $tasks      = modJPganttHelperTasks::getItems($pid);

        // Sort data by hierarchy
        $items = self::sortByHierarchy($milestones, $lists, $tasks);

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
     * @param     array    $lists         The task lists
     * @param     array    $tasks         The tasks
     *
     * @return    array    $items         The sorted items
     */
    protected static function sortByHierarchy($milestones, $lists, $tasks)
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

            // Loop through milestone lists
            foreach ($lists AS $li => $list)
            {
                if ($list->milestone_id != $milestone->id) {
                    continue;
                }

                $l_key = 'l.' . $list->id;

                $structure[$ms_key][] = $list;
                $structure[$l_key]    = array();

                // Loop through list tasks
                foreach ($tasks AS $ti => $task)
                {
                    if ($task->list_id != $list->id) {
                        continue;
                    }

                    $t_key = 't.' . $task->id;

                    $structure[$l_key][] = $task;
                    $structure[$t_key]   = array();

                    unset($tasks[$ti]);
                }

                unset($lists[$li]);
            }

            // Loop through milestone tasks
            foreach ($tasks AS $ti => $task)
            {
                if ($task->milestone_id != $milestone->id || $task->list_id != 0) {
                    continue;
                }

                $t_key = 't.' . $task->id;

                $structure[$ms_key][] = $task;
                $structure[$t_key]    = array();

                unset($tasks[$ti]);
            }
        }

        // Loop through remaining lists
        foreach ($lists AS $li => $list)
        {
            $l_key = 'l.' . $list->id;

            $structure['top'][] = $list;
            $structure[$l_key]  = array();

            // Loop through list tasks
            foreach ($tasks AS $ti => $task)
            {
                if ($task->list_id != $list->id) {
                    continue;
                }

                $t_key = 't.' . $task->id;

                $structure[$l_key][] = $task;
                $structure[$t_key]   = array();

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
        $keys = array('milestone' => 'm.', 'tasklist' => 'l.', 'task' => 't.');

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


    /**
     * Method to format the duration timestamp of an item
     * into HTML output
     *
     * @param     integer    $time      The duration timestamp
     * @param     integer    $start     The item start timestamp
     * @param     integer    $end       The item deadline timestamp
     *
     * @return    string                The formatted html output
     */
    protected static function formatDuration($time, $start, $end, $complete = false)
    {
        // Format duration
        $duration_days = ($time <= 0 ? 0 : ceil($time / 86400));
        $duration_days++;

        $isMonths = false;

        $labelType = $duration_days == 1 ? '' : '_N';

        $durationType = 'd';


        if($duration_days > 90){

            $start_date = new DateTime(date("Y/m/d"));
            $end_date = new DateTime(date("Y/m/d",strtotime("+$duration_days days")));
            $dd = date_diff($start_date,$end_date);
            $duration_days = $dd->m;

            $isMonths  = true;

            $labelType = $duration_days == 1 ? '_SM' : '_M';
            $durationType = 'm';

        }

        // Prepare duration tooltip
        $txt_duration = 'MOD_JP_GANTT_TT_DURATION' . $labelType;
        $txt_duration = Text::sprintf($txt_duration, $duration_days);

        // Prepare time left tooltip
        $today   = self::$time_today;
        $past    = $end < $today;
        $future  = $start > $today;

        if ($future) {
            $time_left = $start - $today;
            $days_left = round($time_left / 86400);
        }
        else {
            $time_left = ($past ? ($today - $end) : ($end - $today));
            $days_left = round($time_left / 86400);
        }


        $txt_left = '';

        if (!$complete) {
            if ($future) {
                $txt_left = 'MOD_JP_GANTT_TT_DURATION_DAY_IN' . ($days_left == 1 ? '' : '_N');
                $txt_left = Text::sprintf($txt_left, $days_left);
            }
            else {
                $txt_left = 'MOD_JP_GANTT_TT_DURATION_DAY' . ($past ? '_BEHIND' : '_LEFT') . ($days_left == 1 ? '' : '_N');
                $txt_left = Text::sprintf($txt_left, $days_left);
            }
        }

        $tt = 'title="' . htmlspecialchars($txt_duration . ' ' . $txt_left) . '"';

        $string = '<small class="" ' . $tt . ' style="cursor: help"><i class="far fa-clock me-1"></i>' . ($duration_days.' '.$durationType) . '</small>';

        return $string;
    }
}


function triggerJPganttScript()
{
   // HTMLHelper::_('script', 'mod_jp_gantt/jquery.fn.gantt.min.js', false, true, false, false, false);
    // HTMLHelper::_('script', 'mod_jp_gantt/jquery.fn.gantt.js', false, true, false, false, false);
}
