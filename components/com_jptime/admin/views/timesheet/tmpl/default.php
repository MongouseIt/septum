<?php
/**
 * @package      Joomproject
 * @subpackage   Timetracking
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;


$total_time = 0;
$user       = Factory::getApplication()->getIdentity();
$uid        = $user->get('id');
$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));
$archived   = $this->state->get('filter.published') == 2 ? true : false;
$trashed    = $this->state->get('filter.published') == -2 ? true : false;

$filter_project = (int) $this->state->get('filter.project');

$date_format = Text::_('DATE_FORMAT_LC4');
$txt_project = Text::_('JGRID_HEADING_PROJECT');

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('bootstrap.popover', '.hasPopover', ['trigger' => 'hover focus']);

    HTMLHelper::_('dropdown.init');
    ?>
    <script type="text/javascript">
    Joomla.orderTable = function()
    {
        table     = document.getElementById("sortTable");
        direction = document.getElementById("directionTable");
        order     = table.options[table.selectedIndex].value;

        if (order != '<?php echo $list_order; ?>') {
            dirn = 'asc';
        }
        else {
            dirn = direction.options[direction.selectedIndex].value;
        }

        Joomla.tableOrdering(order, dirn, '');
    }
    </script>

<form action="<?php echo Route::_('index.php?option=com_jptime&view=timesheet'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div id="j-sidebar-container" class="col-md-2">
            <?php echo $this->sidebar; ?>
        </div>
        <div id="j-main-container" class="col-md-10">
            <?php   echo  LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
    <table class="adminlist table table-striped">
        <thead>
            <tr>
                <th width="1%" class="d-none d-md-table-cell">
                    <?php echo HTMLHelper::_('grid.checkall'); ?>
                </th>
                <th width="5%" class="center">
                    <?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $list_dir, $list_order); ?>
                </th>
                <th>
                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_JOOMPROJECT_TASK_TITLE', 'a.task_title', $list_dir, $list_order); ?>
                </th>
                <th width="10%" class="nowrap">
                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_JOOMPROJECT_TIME_SPENT_HEADING', 'a.log_time', $list_dir, $list_order); ?>
                </th>
                <th width="10%" class="nowrap">
                    <?php echo HTMLHelper::_('searchtools.sort', 'JDATE', 'a.log_date', $list_dir, $list_order); ?>
                </th>
                <th width="10%" class="d-none d-md-table-cell">
                    <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'access_level', $list_dir, $list_order); ?>
                </th>
                <th width="15%" class="d-none d-md-table-cell">
                    <?php echo HTMLHelper::_('searchtools.sort', 'JAUTHOR', 'author_name', $list_dir, $list_order); ?>
                </th>
                <th width="1%" class="nowrap d-none d-md-table-cell">
                    <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $list_dir, $list_order); ?>
                </th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($this->items as $i => $item) :
            $access = JPtimeHelper::getActions($item->id);

            $total_time += (int) $item->log_time;

            $can_create   = $access->get('core.create');
            $can_edit     = $access->get('core.edit');
            $can_checkin  = ($user->authorise('core.manage', 'com_checkin') || $item->checked_out == $uid || $item->checked_out == 0);
            $can_edit_own = ($access->get('core.edit.own') && $item->created_by == $uid);
            $can_change   = ($access->get('core.edit.state') && $can_checkin);
            ?>
            <tr class="row<?php echo $i % 2; ?>">
                <td class="center d-none d-md-table-cell">
                    <?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
                </td>
                <td class="center">
                    <?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'timesheet.', $can_change, 'cb'); ?>
                </td>
                <td>
                    <div class="float-start">
                        <?php if ($item->checked_out) : ?>
                            <?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'timesheet.', $can_checkin); ?>
                        <?php endif; ?>
                        <?php if ($can_edit || $can_edit_own) : ?>
                            <a href="<?php echo Route::_('index.php?option=com_jptime&task=time.edit&id=' . $item->id);?>">
                                <?php echo $this->escape($item->task_title); ?>
                            </a>
                        <?php else : ?>
                            <?php echo $this->escape($item->task_title); ?>
                        <?php endif; ?>

                        <?php if (!$filter_project) : ?>
                            <div class="small">
                                <?php echo $txt_project . ': ' . $this->escape($item->project_title); ?>
                            </div>
                        <?php endif; ?>



                    </div>

                    <?php if(!empty($item->description)): ?>
                        <button
                                type="button"
                                class="hasPopover float-end py-0 px-1 text-info btn btn-light btn-sm shadow-sm"
                                data-bs-container="body"
                                data-bs-toggle="popover"
                                title="<?php echo $this->escape($item->task_title); ?>"
                                data-bs-content="<?php echo $this->escape($item->description); ?>">
                            <i class="fas fa-info-circle"></i>
                        </button>
                    <?php endif; ?>

                </td>
                <td class="nowrap">
                    <?php echo HTMLHelper::_('time.format', $item->log_time); ?>
                </td>
                <td class="nowrap">
                    <?php echo HTMLHelper::_('date', $item->log_date, Text::_('DATE_FORMAT_LC4')); ?>
                </td>
                <td class="d-none d-md-table-cell small">
                    <?php echo $this->escape($item->access_level); ?>
                </td>
                <td class="d-none d-md-table-cell small">
                    <?php echo $this->escape($item->author_name); ?>
                </td>
                <td class="d-none d-md-table-cell small">
                    <?php echo (int) $item->id; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="3" style="text-align: right"><strong><?php echo Text::_('COM_JOOMPROJECT_TOTALTIME_SPENT_HEADING'); ?></strong></td>
                <td class="nowrap"><strong><?php echo HTMLHelper::_('time.format', $total_time); ?></strong></td>
                <td class="d-none d-md-table-cell" colspan="4"></td>
            </tr>
        </tbody>
        
    </table>

    <?php echo $this->pagination->getListFooter()?>
        </div>
    </div>

    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="task" value="" />
    <?php echo HTMLHelper::_('form.token'); ?>

</form>
