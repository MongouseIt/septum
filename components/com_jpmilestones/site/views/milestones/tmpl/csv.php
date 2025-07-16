<?php
/**
 * @package      Joomproject
 * @subpackage   Tasks
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

use JoomProject\Export\Csv;

defined('_JEXEC') or die();

use Joomla\CMS\MVC\Model\BaseDatabaseModel;


// check if there is items to export
if(count($this->items) == 0)
    die('No items to export');

$records = [];


// build data according to defined header
foreach($this->items as $item){

    // Get the tasks / task lists names
    $item->tasks_names = $item->taskslistNames = '';
    if (JPApplicationHelper::exists('com_jptasks')) {

        // get model
        JLoader::register('JPtasksModelTasks', JPATH_SITE . '/components/com_jptasks/models/tasks.php');
        $tmodel             = BaseDatabaseModel::getInstance('Tasks', 'JPtasksModel', array('ignore_request' => true));

        // get tasks names
        $item->tasks_names           = $tmodel->getTasksColumnList([$item->id], 'milestone_id');
        $item->tasks_names = count($item->tasks_names) > 0 ? implode(', ',$item->tasks_names) : '';

        // get tasks list names
        $item->tasklists_names           = $tmodel->getTasksColumnList([$item->id], 'milestone_id',null,'title','task_lists');
        $item->tasklists_names = count($item->tasklists_names) > 0 ? implode(', ',$item->tasklists_names) : '';

    }

    $records[] = [
        $item->title,
        $item->description,
        $item->project_title,
        $item->category_title,
        $item->tasklists_names,
        $item->tasks_names,
        $item->tasklists,
        $item->tasks,
        $item->completed_tasks,
        $item->progress,
        $item->attachments,
        $item->comments,
        isset($item->watching) ? $item->watching : '',
        $item->start_date == '0000-00-00 00:00:00' ? '' : $item->start_date,
        $item->end_date == '0000-00-00 00:00:00' ? '' : $item->end_date,
        $item->created,
        \Joomla\CMS\Factory::getUser($item->created_by)->name
    ];

}

// csv params
$params = [
    'header' => [
        'TITLE',
        'DESCRIPTION',
        'PROJECT',
        'CATEGORY',
        'TASKLISTS',
        'TASKS',
        'TOTAL_TASKLISTS',
        'TOTAL_TASKS',
        'COMPLETED_TASKS',
        'PROGRESS',
        'ATTACHMENTS',
        'COMMENTS',
        'WATCHING',
        'STARTDATE',
        'DEADLINE',
        'DATE',
        'AUTHOR'
    ], // define columns to use
    'records' => $records, // array of data
    'context' => 'milestones'
];

// export to CSV
$csv = new Csv($params);
$csv->export();

die;