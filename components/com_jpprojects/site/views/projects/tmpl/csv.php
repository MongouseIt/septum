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


// check if there is items to export
if(count($this->items) == 0)
    die('No items to export');

$records = [];

// build data according to defined header
foreach($this->items as $item){

    $item->attribs = json_decode($item->attribs);

    $records[] = [
        $item->title,
        $item->description,
        $item->attribs->website,
        $item->attribs->email,
        $item->attribs->phone,
        $item->category_title,
        $item->milestones,
        $item->tasklists,
        $item->tasks,
        $item->completed_tasks,
        $item->attachments,
        $item->comments,
        $item->progress,
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
        'WEBSITE',
        'EMAIL',
        'PHONE',
        'CATEGORY',
        'MILESTONES',
        'TASKLISTS',
        'TASKS',
        'COMPLETED_TASKS',
        'ATTACHMENTS',
        'COMMENTS',
        'PROGRESS',
        'WATCHING',
        'STARTDATE',
        'DEADLINE',
        'DATE',
        'AUTHOR'
    ], // define columns to use
    'records' => $records, // array of data
    'context' => 'projects'
];

// export to CSV
$csv = new Csv($params);
$csv->export();

die;