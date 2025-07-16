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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die();

extract($displayData);

$user = Factory::getApplication()->getIdentity();
$uid = $user->get('id');

$taskLists = JoomprojectHelperFrontend::groupTasksByTaskLists($current->items);

$i = 0;
$x = 0;

// Include Bootstrap component
Factory::getApplication()
    ->getDocument()
    ->getWebAssetManager()
    ->useScript('bootstrap.collapse');

HTMLHelper::_('bootstrap.tooltip', '.hasTooltip');

// check if there is only main tasks
$onlyMain = count($taskLists) == 1 && empty($taskLists[0]['title']) ? true : false;

// js code essential for select all button of task list to work
if($current->access->get('core.edit') || $current->access->get('core.edit.state'))
	JoomProjectHelperWebasset::$wa->useScript('com_joomproject.tasklist.checkAll');

?>
<script>

    // Function to parse URL parameters
    function getUrlParameter(name) {
        name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
        var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
        var results = regex.exec(location.search);
        return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
    }

    // Function to calculate total height of fixed elements at the top of the page
    function getFixedHeadersHeight() {
        var totalHeight = 0;
        var possibleHeaders = document.querySelectorAll('header, .fixed-top, .sticky-top');
        possibleHeaders.forEach(function (header) {
            if (window.getComputedStyle(header).position === 'fixed' || window.getComputedStyle(header).position === 'sticky') {
                totalHeight += header.offsetHeight;
            }
        });
        return totalHeight;
    }

    // Function to open an accordion item and scroll to it, accounting for all fixed headers
    function openAccordionAndScroll(accordionItem) {
        if (accordionItem) {
            var bsCollapse = new bootstrap.Collapse(accordionItem, {
                toggle: false
            });
            bsCollapse.show();

            // Scroll to the accordion item
            setTimeout(function () {
                // Get the total height of all fixed headers
                var fixedHeadersHeight = getFixedHeadersHeight();

                // Calculate the top position of the accordion item
                var accordionTop = accordionItem.getBoundingClientRect().top + window.pageYOffset - fixedHeadersHeight;

                // Scroll to the adjusted position
                window.scrollTo({
                    top: accordionTop,
                    behavior: 'smooth'
                });
            }, 350); // Adjust this delay if needed
        }
    }

    // Function to handle accordion opening, scrolling, and local storage
    function handleAccordion() {
        var tasklistParam = getUrlParameter('filter_tasklist');
        var accordionItem;

        if (tasklistParam && tasklistParam !== '0') {
            // If there's a valid tasklist parameter in the URL, use that
            var tasklistId = tasklistParam.split(':')[0];
            accordionItem = document.querySelector('#jpTaskList-' + tasklistId);
        } else {
            // If no parameter or if it's '0', check local storage
            var lastOpenedId = localStorage.getItem('lastOpenedAccordion');
            if (lastOpenedId) {
                accordionItem = document.querySelector('#' + lastOpenedId);
            }
        }

        if (accordionItem) {
            openAccordionAndScroll(accordionItem);
            localStorage.setItem('lastOpenedAccordion', accordionItem.id);
        }
    }

    // Event listener for accordion changes
    document.addEventListener('shown.bs.collapse', function (event) {
        // Store the ID of the opened accordion item
        localStorage.setItem('lastOpenedAccordion', event.target.id);
    });

    // Call the function when the page loads
    document.addEventListener('DOMContentLoaded', function () {
        handleAccordion();
    });


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
<div class="accordion mb-4" id="jpTaskLists">
    <?php foreach ($taskLists as $id => $taskList):

        $access = JPtasksHelper::getListActions($id);

        $can_create = $access->get('core.create');
        $can_edit = $access->get('core.edit');
        $can_checkin = ($user->authorise('core.manage', 'com_checkin') || $taskList['checked_out_list'] == $uid || $taskList['checked_out_list'] == 0);
        $can_edit_own = ($access->get('core.edit.own') && $taskList['created_by'] == $uid);
        $can_change = ($access->get('core.edit.state') && $can_checkin);

        ?>
        <div class="accordion-item">
            <h2 class="accordion-header d-flex">


	            <?php
	            $current->menu->start(array('class' => 'btn-sm btn-link rounded-0', 'pull' => 'left'));

	            $current->menu->itemNew('taskform',"COM_JOOMPROJECT_ACTION_NEW_TASK", $can_create,false, ['list_id' => $id]);
	            $current->menu->itemEdit('tasklistform', $id, ($can_edit || $can_edit_own));
	            $current->menu->itemTrash('tasklists', $x, ($can_edit || $can_edit_own));

	            $current->menu->end();
	            echo $current->menu->render(array('class' => 'btn-sm btn-link', 'pull' => 'left'));
	            ?>

	            <?php if($current->access->get('core.edit') || $current->access->get('core.edit.state')): ?>
                    <div class="w-auto px-1">
                        <small class="px-2">
                            <input
                                    type="checkbox"
                                   id="checkall-jpTaskList-<?php echo $id ?>"
                                    class="tasklist-checkall hasTooltip"
                                   data-tasklist="tasklist_<?php echo $i; ?>"
                                   data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                   title="<?php echo \Joomla\CMS\Language\Text::_('COM_JPTASKS_SELECT_ALL_TASKS_IN_LIST'); ?>"
                                   onclick="event.stopPropagation(); toggleTaskListCheckboxes('tasklist_<?php echo $i; ?>');"
                            >
                        </small>
                    </div>

	            <?php endif; ?>

                <button
                        class="accordion-button"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#jpTaskList-<?php echo $id ?>"
                        aria-expanded="true"
                        aria-controls="jpTaskList-<?php echo $id ?>"
                >

                    <?php echo empty($taskList['title']) ? \Joomla\CMS\Language\Text::_('COM_JOOMPROJECT_MAIN') : $taskList['title'] ?> <?php echo LayoutHelper::render('tasklist.parts.taskCounts',['id' => $id]) ?>
                    <?php if (!empty($taskList['description'])): ?>
                        <i class="ms-2 fas fa-info-circle hasTooltip" title="<?php echo strip_tags($current->escape($taskList['description'])) ?>"></i>
                    <?php endif; ?>
                </button>


            </h2>
            <div id="jpTaskList-<?php echo $id ?>"
                 class="accordion-collapse collapse <?php echo $onlyMain && $i == 0 ? 'show' : '' ?>"
                 data-bs-parent="#jpTaskLists">
                <div class="accordion-body p-0">
                    <div id="list-reorder">
                        <ul class="list-tasks list-group list-group-flush m-0" id="tasklist_<?php echo $i; ?>">
                            <?php foreach ($taskList['tasks'] as $item): ?>
                                <?php
                                // Start task item
                                $access = JPtasksHelper::getActions($item->id);
                                $item_order[] = $item->ordering;

                                $can_create = $access->get('core.create');
                                $can_edit = $access->get('core.edit');
                                $can_checkin = ($user->authorise('core.manage', 'com_checkin') || $item->checked_out == $uid || $item->checked_out == 0);
                                $can_edit_own = ($access->get('core.edit.own') && $item->created_by == $uid);
                                $can_change = ($access->get('core.edit.state') && $can_checkin);

                                // Task completed javascript
                                $cbjs = '';
                                $disabled = ' disabled = disabled';
                                $checked = ($item->complete ? ' checked="checked"' : '');

                                if ($can_change) {
                                    $cbjs = ' onclick="setTaskComplete(' . intval($item->id) . ', this.checked);"';
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
                                <?php $x++; ?>
                            <?php endforeach; ?>
                        </ul>

                        <?php $i++ ?>
                    </div>

                    <input
                            type="hidden"
                            name="item-order-<?php echo $i; ?>"
                            id="item_order_<?php echo $i; ?>"
                            value="<?php echo implode('|', $item_order); ?>"
                    />

                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <?php

    if(\Joomla\CMS\Component\ComponentHelper::getParams('com_jptasks')->get('show_empty_tasklists', 0)):

    // display empty task lists
    $emptyLists = JoomprojectHelperFrontend::getEmptyTaskLists();

    foreach ($emptyLists as $id => $list):
        $access = JPtasksHelper::getListActions($id);
        $taskAccess = JPtasksHelper::getActions(0, $id);

        $task_can_create = $taskAccess->get('core.create');
        $can_edit = $access->get('core.edit');
        $can_checkin = ($user->authorise('core.manage', 'com_checkin') || $list['checked_out_list'] == $uid || $list['checked_out_list'] == 0);
        $can_edit_own = ($access->get('core.edit.own') && $list['created_by'] == $uid);
        $can_change = ($access->get('core.edit.state') && $can_checkin);
        ?>
        <div class="accordion-item">
            <h2 class="accordion-header d-flex">
                <?php
                $current->menu->start(array('class' => 'btn-sm btn-link rounded-0', 'pull' => 'left'));
                $current->menu->itemNew('taskform', "COM_JOOMPROJECT_ACTION_NEW_TASK", $task_can_create, false, ['list_id' => $id]);
                $current->menu->itemEdit('tasklistform', $id, ($can_edit || $can_edit_own));
                $current->menu->itemTrash('tasklists', $x, ($can_edit || $can_edit_own));
                $current->menu->end();
                echo $current->menu->render(array('class' => 'btn-sm btn-link', 'pull' => 'left'));
                ?>
                <button class="accordion-button" type="button" data-bs-toggle="collapse"
                        data-bs-target="#jpTaskList-<?php echo $id ?>" aria-expanded="true"
                        aria-controls="jpTaskList-<?php echo $id ?>">
                    <?php echo $list['title'] ?>
                    <?php if (!empty($list['description'])): ?>
                        <i class="ms-2 fas fa-info-circle hasTooltip"
                           title="<?php echo strip_tags($current->escape($list['description'])) ?>"></i>
                    <?php endif; ?>
                </button>
            </h2>
            <div id="jpTaskList-<?php echo $id ?>" class="accordion-collapse collapse" data-bs-parent="#jpTaskLists">
                <div class="accordion-body p-0">

                    <ul class="list-tasks list-group list-group-flush m-0">
                            <li class="task-incomplete list-group-item">
                                <div class="taskInlineContainer">
                                    <div class="task-row clearfix text-warning">
                                        <?php echo \Joomla\CMS\Language\Text::_('COM_JPTASKS_NO_TASKS'); ?>
                                    </div>
                                </div>
                            </li>
                    </ul>


                </div>
            </div>
        </div>
        <?php
        $i++;
    endforeach;

    endif;


    ?>
</div>