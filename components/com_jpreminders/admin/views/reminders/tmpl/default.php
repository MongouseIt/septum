<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_joomproject
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2019 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');



$app       = Factory::getApplication();
$user      = Factory::getApplication()->getIdentity();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == 'r.ordering';
$columns   = 10;


if (strpos($listOrder, 'modified') !== false)
{
	$orderingColumn = 'modified';
}
else
{
	$orderingColumn = 'name';
}

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_jpreminders&task=reminders.saveOrderAjax&tmpl=component';
	HTMLHelper::_('sortablelist.sortable', 'project_id', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

?>

<form action="<?php echo Route::_('index.php?option=com_jpreminders&view=reminders'); ?>" method="post"
      name="adminForm" id="adminForm">
    <div class="row">
        <div id="j-sidebar-container" class="col-md-2">
            <?php echo $this->sidebar; ?>
        </div>
        <div id="j-main-container" class="col-md-10">
			<?php

			// Search tools bar
			echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
			?>

			<?php if (empty($this->items)) : ?>
                <div class="alert alert-no-items">
					<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                </div>
			<?php else : ?>
                <table class="table table-striped" id="articleList">
                    <thead>
                    <tr>
                        <th width="1%" class="nowrap center d-none d-md-table-cell">
							<?php echo HTMLHelper::_('searchtools.sort', '', 'r.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
                        </th>
                        <th width="1%" class="center">
							<?php echo HTMLHelper::_('gr
                            id.checkall'); ?>
                        </th>
                        <th width="1%" class="nowrap center">
							<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'r.state', $listDirn, $listOrder); ?>
                        </th>
                        <th style="min-width:100px" class="nowrap">
							<?php echo HTMLHelper::_('searchtools.sort', 'COM_JPREMINDERS_TASK_NAME', 't.title', $listDirn, $listOrder); ?>
                        </th>

                        <th width="10%" class="nowrap d-none d-md-table-cell">
							<?php echo HTMLHelper::_('searchtools.sort', 'COM_JPREMINDERS_AUTHOR', 'r.created_by', $listDirn, $listOrder); ?>
                        </th>
                        <th width="10%" class="nowrap d-none d-md-table-cell">
							<?php echo Text::_('COM_JPREMINDERS_START_DATE'); ?>
                        </th>

                        <th width="10%" class="nowrap d-none d-md-table-cell">
							<?php echo Text::_('COM_JPREMINDERS_NEXT_DATE'); ?>
                        <th>
							<?php echo Text::_('COM_JPREMINDERS_REMAINING'); ?>
                        </th>
                        <th width="1%" class="nowrap d-none d-md-table-cell">
							<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'r.id', $listDirn, $listOrder); ?>
                        </th>
                    </tr>
                    </thead>
                    <tfoot>
                    <tr>
                        <td colspan="<?php echo $columns; ?>">
                        </td>
                    </tr>
                    </tfoot>
                    <tbody>
					<?php foreach ($this->items as $i => $item) :

						$item->max_ordering = 0;
						$ordering = ($listOrder == 'r.ordering');

						$canEdit    = $user->authorise('core.edit', 'com_jpreminders.reminders.' . $item->id);
						$canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
						$canEditOwn = $user->authorise('core.edit.own', 'com_jpreminders.reminders' . $item->id) && $item->created_by == $userId;
						$canChange  = $user->authorise('core.edit.state', 'com_jpreminders.reminders.' . $item->id) && $canCheckin;

						?>

                        <tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->project_id; ?>">
                            <td class="order nowrap center d-none d-md-table-cell">
								<?php
								$iconClass = '';
								if (!$canChange)
								{
									$iconClass = ' inactive';
								}
                                elseif (!$saveOrder)
								{
									$iconClass = ' inactive tip-top" data-bs-toggle="tooltip" title="' . HTMLHelper::_('tooltipText', 'JORDERINGDISABLED');
								}
								?>
                                <span class="sortable-handler<?php echo $iconClass ?>">
								<span class="icon-menu" aria-hidden="true"></span>
							</span>
								<?php if ($canChange && $saveOrder) : ?>
                                    <input type="text" style="display:none" name="order[]" size="5"
                                           value="<?php echo $item->ordering; ?>" class="width-20 text-area-order"/>
								<?php endif; ?>
                            </td>
                            <td class="center">
								<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
                            </td>
                            <td class="center">
								<?php echo (int) $item->reminderDates->total > 0 ? '<span class="badge bg-warning">' . Text::_('COM_JPREMINDERS_PROGRESS') . '</span>' : '<span class="badge bg-success">' . Text::_('COM_JPREMINDERS_FINISHED') . '</span>'; ?>

                            </td>

                            <td class="has-context">
								<?php if ($item->checked_out) : ?>
									<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->name, $item->checked_out_time, 'reminders.', $canCheckin); ?>
								<?php endif; ?>
								<?php if ($canEdit || $canEditOwn) : ?>
                                    <a href="<?php echo Route::_('index.php?option=com_jpreminders&task=reminder.edit&id=' . $item->id); ?>">
										<?php echo $this->escape($item->title); ?></a>
								<?php else : ?>
									<?php echo $this->escape($item->title); ?>
								<?php endif; ?>
                            </td>
                            <td class="small d-none d-md-table-cell">
								<?php echo $item->name; ?>
                            </td>

                            <td class="nowrap small d-none d-md-table-cell">
								<?php
								echo $item->start_date > 0 ? HTMLHelper::_('date', $item->start_date, 'Y-m-d H:i') : '-';
								?>
                            </td>
                            <td class="nowrap small d-none d-md-table-cell">
								<?php
								echo ($item->reminderDates->total == 0) ? '-' : Factory::getDate($item->reminderDates->date)->format('Y-m-d H:i');
								?>
                            </td>

                            <td>
                                <span class="badge bg-<?php echo (int) $item->reminderDates->total > 0 ? 'success' : 'danger'; ?>">
                                <?php
                                echo (int) $item->reminderDates->total; ?>
                                </span>
                            </td>

                            <td class="d-none d-md-table-cell">
								<?php echo (int) $item->id; ?>
                            </td>
                        </tr>
					<?php endforeach; ?>
                    </tbody>
                </table>
			<?php endif ?>
			<?php echo $this->pagination->getListFooter(); ?>
        </div>
    </div>

            <input type="hidden" name="task" value=""/>
            <input type="hidden" name="boxchecked" value="0"/>
			<?php echo HTMLHelper::_('form.token'); ?>

</form>
