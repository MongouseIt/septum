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

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;


// check if there is items to export
if(count($this->items) == 0)
    die('No items to export');

$records = [];

// build data according to defined header
foreach($this->items as $item){

    $records[] = [
        $item->title,
        $item->description,
        $item->project_title,
        $item->milestone_title,
        HTMLHelper::_('jptasks.priorityLabel', $item->id, null, $item->priority,true),
        $item->complete ? Text::_('JYES') : Text::_('JNO'),
        $item->completed == '0000-00-00 00:00:00' ? '' : $item->completed,
        \Joomla\CMS\Factory::getUser($item->completed_by)->name,
        isset($item->watching) ? $item->watching : '',
        $item->attachments,
        $item->comments,
        $item->dependency_count,
        count($item->users),
        $item->start_date == '0000-00-00 00:00:00' ? '' : $item->start_date,
        $item->end_date == '0000-00-00 00:00:00' ? '' : $item->end_date,
        $item->created,
        \Joomla\CMS\Factory::getUser($item->created_by)->name
    ];

}

// csv params
$params = [
    'header' => ['TITLE',
        'DESCRIPTION',
        'PROJECT',
        'MILESTONE',
        'PRIORITY',
        'COMPLETE',
        'COMPLETED',
        'COMPLETED_BY',
        'WATCHING',
        'ATTACHMENTS',
        'COMMENTS',
        'DEPENDENCIES',
        'ASSIGNED_USERS',
        'STARTDATE',
        'DEADLINE',
        'DATE',
        'AUTHOR'], // define columns to use
    'records' => $records, // array of data
    'context' => 'tasks'
];

// export to CSV
$csv = new Csv($params);
$csv->export();

die;