<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jpmilestones
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2006-2015 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;

// Include css
HTMLHelper::_('stylesheet', 'com_joomproject/joomproject/print.css');


$db       = Factory::getDbo();
$query    = $db->getQuery(true);
$nulldate = $db->getNullDate();

$params = ComponentHelper::getParams('com_joomproject');
$date_format = $params->get('date_format');

if (!$date_format) {
    $date_format = Text::_('DATE_FORMAT_LC4');
}

$list_total_time = 0;
$list_total_billable = 0.00;

$doc = Factory::getDocument();
$doc->addScriptDeclaration('
jQuery(document).ready(function()
{
    window.focus();
    window.print();
});
');
?>
<div id="joomproject" class="category-list<?php echo $this->pageclass_sfx;?> view-milestones-print">

    <?php if ($this->state->get('filter.project')) : ?>
        <h2>
            <?php echo $this->escape(JPApplicationHelper::getActiveProjectTitle()); ?>
        </h2>
    <?php endif; ?>

    <table class="table table-striped cat-items">
        <thead>
            <tr>
                <th><?php echo Text::_('JGRID_HEADING_TASK'); ?></th>
                <th class="nowrap" style="width: 20%"><?php echo Text::_('JGRID_HEADING_AUTHOR'); ?></th>
                <th class="nowrap" style="width: 8%"><?php echo Text::_('JGRID_HEADING_DATE'); ?></th>
                <th class="nowrap" style="width: 12%"><?php echo Text::_('COM_JOOMPROJECT_TIME_TRACKING_TIME'); ?></th>
                <th class="nowrap" style="width: 8%"><?php echo Text::_('COM_JOOMPROJECT_TIME_TRACKING_RATE'); ?></th>
                <th class="nowrap" style="width: 8%"><?php echo Text::_('COM_JOOMPROJECT_TIME_TRACKING_BILLABLE'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach($this->items AS $i => $item) :
                if ($item->log_time > 0) {
                    $list_total_time += (int) $item->log_time;
                }

                if ((float) $item->billable_total > 0.00) {
                    $list_total_billable += (float) $item->billable_total;
                }
                ?>
                <tr>
                    <td><?php echo $this->escape($item->task_title); ?></td>
                    <td><?php echo $this->escape($item->author_name); ?></td>
                    <td><?php echo HTMLHelper::_('date', $item->log_date, Text::_('DATE_FORMAT_LC4')); ?></td>
                    <td><?php echo HTMLHelper::_('time.format', $item->log_time); ?></td>
                    <td><?php echo HTMLHelper::_('jphtml.format.money', $item->rate);?></td>
                    <td><?php echo HTMLHelper::_('jphtml.format.money', $item->billable_total);?></td>
                </tr>
                <?php
            endforeach;
            ?>
        </tbody>
        <tfoot>
    		<tr>
    			<th><?php echo Text::_('COM_JOOMPROJECT_TIME_TRACKING_TOTALS');?></th>
    			<th></th>
                <th></th>
                <th><?php echo HTMLHelper::_('time.format', $list_total_time); ?></th>
    			<th></th>
        		<th><?php echo HTMLHelper::_('jphtml.format.money', $list_total_billable);?></th>
    		</tr>
       	</tfoot>
    </table>
</div>
