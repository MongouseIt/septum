<?php
/**
 * @package      Joomproject
 * @subpackage   Tasks
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die();

// extract layout data
extract($displayData);

// some inits
$user = Factory::getApplication()->getIdentity();
$uid  = $user->get('id');

// js code essential for select all button of task list to work
if ($current->access->get('core.edit') || $current->access->get('core.edit.state'))
	JoomProjectHelperWebasset::$wa->useScript('com_joomproject.tasklist.checkAll');


?>
    <script>
        // add functionality to toggle between + and - icons when the collapse button is clicked.
        document.addEventListener('DOMContentLoaded', function () {
            const buttons = document.querySelectorAll('.task-collapse-icon');

            buttons.forEach(button => {
                const icon = button;
                const collapsible = document.querySelector(button.dataset.bsTarget);

                // Listen for bootstrap collapse events
                collapsible.addEventListener('show.bs.collapse', () => {
                    icon.textContent = '-';
                });

                collapsible.addEventListener('hide.bs.collapse', () => {
                    icon.textContent = '+';
                });
            });
        });

    </script>
    <div id="list-reorder" class="mb-4">
		<?php
		$k            = 0;
		$x            = 0;
		$current_list = '';
		$list_open    = false;
		$item_order   = array();

		foreach ($current->items as $i => $item) :

		if ($current_list !== $item->list_title) :
		if ($item->list_title) :
			$access = JPtasksHelper::getListActions($item->list_id);

			$can_create   = $access->get('core.create');
			$can_edit     = $access->get('core.edit');
			$can_checkin  = ($user->authorise('core.manage', 'com_checkin') || $item->checked_out_list == $uid || $item->checked_out_list == 0);
			$can_edit_own = ($access->get('core.edit.own') && $item->list_created_by == $uid);
			$can_change   = ($access->get('core.edit.state') && $can_checkin);
		endif;
		?>
		<?php if ($list_open) : ?>
        </ul>
        <input type="hidden" name="item-order-<?php echo $k; ?>" id="item_order_<?php echo $k; ?>"
               value="<?php echo implode('|', $item_order); ?>"/>
    </div>
	<?php

	$list_open  = false;
	$item_order = array();
endif;

	?>

    <div class="cat-list-row<?php echo $k; ?> card mb-4">
		<?php if ($item->list_title) : ?>
            <div class="card-header">
                <h5 class="m-0 d-flex justify-content-between">
                <span>
	            <?php if ($current->access->get('core.edit') || $current->access->get('core.edit.state')): ?>
                    <small class="me-2">
                        <input
                                type="checkbox"
                                id="checkall-tasklist_<?php echo $i; ?>"
                                class="tasklist-checkall hasTooltip"
                                data-tasklist="tasklist_<?php echo $i; ?>"
                                onclick="toggleTaskListCheckboxes('tasklist_<?php echo $i; ?>');"
                                data-bs-toggle="tooltip"
                                data-bs-placement="top"
                                title="<?php echo \Joomla\CMS\Language\Text::_('COM_JPTASKS_SELECT_ALL_TASKS_IN_LIST'); ?>"
                        >
                    </small>
	            <?php endif; ?>


                    <a
                            href="<?php echo Route::_(JPtasksHelperRoute::getTasksRoute($item->project_slug, $item->milestone_slug, $item->list_slug)); ?>"
                    >
                    <?php echo $current->escape($item->list_title); ?>
                </a> <?php echo LayoutHelper::render('tasklist.parts.taskCounts', ['id' => $item->list_id]) ?>
                </span>

					<?php
					$current->menu->start(array('class' => 'btn-sm btn-link', 'pull' => 'left'));
					$current->menu->itemEdit('tasklistform', $item->list_id, ($can_edit || $can_edit_own));
					$current->menu->itemTrash('tasklists', $x, ($can_edit || $can_edit_own));
					$current->menu->end();
					echo $current->menu->render(array('class' => 'btn-sm btn-link', 'pull' => 'left'));
					?>

                </h5>
				<?php if (!empty($item->list_description)): ?>
                    <p class="m-0 p-0 text-muted">
                        <small><?php echo htmlspecialchars_decode($current->escape($item->list_description)); ?></small>
                    </p>
				<?php endif; ?>
            </div>
		<?php endif; ?>
        <ul class="list-tasks list-group list-group-flush m-0" id="tasklist_<?php echo $i; ?>">

			<?php
			$k            = 1 - $k;
			$list_open    = true;
			$current_list = $item->list_title;
			$x++;
			// End of Task List
			endif;
			?>
			<?php
			// Start task item
			$access       = JPtasksHelper::getActions($item->id);
			$item_order[] = $item->ordering;

			$can_create   = $access->get('core.create');
			$can_edit     = $access->get('core.edit');
			$can_checkin  = ($user->authorise('core.manage', 'com_checkin') || $item->checked_out == $uid || $item->checked_out == 0);
			$can_edit_own = ($access->get('core.edit.own') && $item->created_by == $uid);
			$can_change   = ($access->get('core.edit.state') && $can_checkin);

			// Task completed javascript
			$cbjs     = '';
			$disabled = ' disabled = disabled';
			$checked  = ($item->complete ? ' checked="checked"' : '');

			if ($can_change)
			{
				$cbjs     = ' onclick="setTaskComplete(' . intval($item->id) . ', this.checked);"';
				$disabled = '';
			}

			// task bg color
			$item_css = JoomprojectHelperColor::getItemColor($item->params->get('task_color', ''));

			// list item class
			$class = ($item->complete ? 'task-complete' : 'task-incomplete');


			?>

            <li id="list-item-<?php echo $x; ?>"
                class="<?php echo $class; ?> list-group-item  <?php if ($item->complete) : echo "complete"; endif; ?> priority-<?php echo $item->priority; ?>" <?php echo $item_css ?>>

				<?php echo LayoutHelper::render('task.taskInline', ['x' => $x, 'item' => $item], '', ['client' => 'site', 'component' => 'com_jptasks']); ?>

            </li>
			<?php
			$x++;
			endforeach;
			?>
			<?php if ($list_open) : ?>
        </ul>
        <input type="hidden" name="item-order-<?php echo $k; ?>" id="item_order_<?php echo $k; ?>"
               value="<?php echo implode('|', $item_order); ?>"/>
    </div>
	<?php $list_open = false;
endif; ?>