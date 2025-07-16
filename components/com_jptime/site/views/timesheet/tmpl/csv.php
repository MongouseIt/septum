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
use Joomla\CMS\HTML\HTMLHelper;


// check if there is items to export
if (count($this->items) == 0)
    die('No items to export');

$records = [];

// build data according to defined header
foreach ($this->items as $item) {

    $percentage = ($item->estimate == 0) ? 0 : round($item->log_time * (100 / $item->estimate));

    if ($percentage > 100) {
        $percentage = 100;
    }

    $records[] = [
        $item->task_title,
        $item->description,
        $item->project_title,
        HTMLHelper::_('time.format', $item->log_time),
        $percentage,
        $item->log_date,
        HTMLHelper::_('jphtml.format.money', $item->rate),
        HTMLHelper::_('jphtml.format.money', $item->billable_total),
        $item->author_name
    ];

}

// csv params
$params = [
    'header' => ['TASK', 'DESCRIPTION', 'PROJECT', 'TIME', 'PERCENT', 'DATE','RATE','BILLABLE', 'AUTHOR'], // define columns to use
    'records' => $records, // array of data
    'context' => 'timesheet'
];

// export to CSV
$csv = new Csv($params);
$csv->export();

die;