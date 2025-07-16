<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_joomproject
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2019 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */
# No Permission

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;

extract($displayData);

// some inits
$menu = new JPMenuContext();
$user = Factory::getApplication()->getIdentity();
$uid = $user->get('id');
$access = JPtasksHelper::getActions($item->id);
$cParams = ComponentHelper::getParams('com_jptasks');

// check if some features enabled
$time_enabled = JPApplicationHelper::enabled('com_jptime');
$enable_quicklog_time = ComponentHelper::getParams('com_jptasks')->get('enable_quicklogtime', 0);
$cmnts_enabled = JPApplicationHelper::enabled('com_jpcomments');
$repo_enabled = JPApplicationHelper::enabled('com_jprepo');


// access
$can_create = $access->get('core.create');
$can_edit = $access->get('core.edit');
$can_checkin = ($user->authorise('core.manage', 'com_checkin') || $item->checked_out == $uid || $item->checked_out == 0);
$can_edit_own = ($access->get('core.edit.own') && $item->created_by == $uid);
$can_change = ($access->get('core.edit.state') && $can_checkin);
$can_track = ($user->authorise('core.create', 'com_jptime') && $time_enabled);

// Prepare the watch button
$watch = '';

if ($uid) {
    $options = array('a-class' => 'btn-sm', 'div-class' => 'float-end');
    $watch = HTMLHelper::_('jphtml.button.watch', 'tasks', $x, $item->watching, $options);
}

// Deadline and completition date
$task_date = HTMLHelper::_(
    'jphtml.label.datetime',
    ($item->complete ? $item->completed : $item->end_date),
    false,
    ($item->complete ? array('past-class' => 'bg-success', 'past-icon' => 'calendar') : array())
);


$showTaskDetailsMode = ComponentHelper::getParams('com_jptasks')->get('show_task_details', 'close');

$collapseClass = '';
if (($item->comments > 0 && $showTaskDetailsMode == 'hasComments') || $showTaskDetailsMode == 'open')
    $collapseClass = ' show';


$showTaskId = \Joomla\CMS\Component\ComponentHelper::getParams('com_jptasks')->get('show_task_id', 0);

$taskDescDisplay = ComponentHelper::getParams('com_jptasks')->get('task_desc_display', 1);

$enable_not_applicable = ComponentHelper::getParams('com_jptasks')->get('enable_not_applicable', 0);


?>

<div class="taskInlineContainer">
    <div class="task-row clearfix">
        <?php if ($can_change || $uid) : ?>
            <span for="cb<?php echo $x; ?>" class="p-0 checkbox float-start d-none d-sm-block">
			    <?php echo HTMLHelper::_('jp.html.id', $x, $item->id); ?>
            </span>
        <?php endif; ?>
        <input type="hidden" name="order[]" value="<?php echo (int)$item->ordering; ?>"/>
        <div id="list-toolbar-<?php echo $x; ?>" class="float-end">
            <div class="d-inline">
                <?php echo $watch; ?>
            </div>
	        <?php if ($enable_not_applicable && $item->not_applicable): ?>
                <div class="d-inline hasTooltip" title="<?php echo Text::_('COM_JPTASKS_TASK_NOT_APPLICABLE') ?>">
                    <button type="button" disabled class="btn btn-sm btn-secondary "><?php echo Text::_('COM_JPTASKS_TASK_NA') ?></button>
                </div>
            <?php else: ?>
                <?php echo HTMLHelper::_('jptasks.complete', $x, $item->complete, $can_change, $item->dependencies, $item->users, $item->start_date); ?>
            <?php endif; ?>
            <?php
            $menu->start(array('class' => 'btn-sm btn-link'));
            $itm_icon = 'fas fa-list';
            $itm_txt = 'COM_JOOMPROJECT_DETAILS_LABEL';
            $itm_link = '#collapse-' . $x;
            $menu->itemCollapse($itm_icon, $itm_txt, $itm_link);

            $menu->itemDivider();


            $menu->itemEdit('taskform', $item->id, ($can_edit || $can_edit_own));
            $menu->itemTrash('tasks', $x, ($can_edit || $can_edit_own));

            // Add Not Applicable feature
            if ($enable_not_applicable && $can_change) {
	            $menu->itemDivider();

	            if ($item->not_applicable) {
		            $menu->itemJavaScript('fas fa-check-circle', 'COM_JPTASKS_MARK_AS_APPLICABLE', 'document.getElementById(\'not_applicable\').value=0; return Joomla.listItemTask(\'cb' . $x . '\',\'tasks.notApplicable\');');
	            } else {
		            $menu->itemJavaScript('fas fa-ban', 'COM_JPTASKS_MARK_AS_NOT_APPLICABLE', 'document.getElementById(\'not_applicable\').value=1; return Joomla.listItemTask(\'cb' . $x . '\',\'tasks.notApplicable\');');
	            }
            }

            if ($can_track) {
                $menu->itemDivider();
                $menu->itemJavaScript('fas fa-clock ', 'COM_JOOMPROJECT_TASKS_TRACK_TIME', 'JPtask.trackItem(' . $item->id . ');', false, 'tracktime');

                // add quick log time input
                if ($enable_quicklog_time) {
                    $menu->itemDivider();
                    $menu->customCode(LayoutHelper::render('task.quicklogtime', ['task' => $item], '', ['client' => 'site', 'component' => 'com_jptasks']));
                }

            }

            if (($can_edit || $can_edit_own)) {
                $itm_icon = 'fas fa-plus';
                $itm_txt = 'COM_JOOMPROJECT_ASSIGN_TO_USER';
                //$itm_link = JPusersHelperRoute::getUsersRoute() . '&amp;layout=modal&amp;tmpl=component&amp;field=JPtaskAssignUser';
                $itm_link = Uri::root() . 'index.php?option=com_jpusers&view=users&filter_project=&amp;layout=modal&amp;tmpl=component&amp;field=JPtaskAssignUser';
                $menu->itemDivider();
                $menu->itemModal($itm_icon, $itm_txt, $itm_link, "JPlist.setTarget(" . $x . ");", 800, 500, false, 'btn-izimodal');
            }

            if ($can_change) {
                $itm_icon = 'fas fa-exclamation-triangle';
                $itm_jpx = 'COM_JOOMPROJECT_PRIORITY';
                $itm_ac = 'JPtask.priority(' . $x . ',';

                $menu->itemDivider();
                $menu->itemJavaScript($itm_icon, $itm_jpx . '_VERY_LOW', $itm_ac . ' 1, \'' . addslashes(Text::_($itm_jpx . '_VERY_LOW')) . '\')');
                $menu->itemJavaScript($itm_icon, $itm_jpx . '_LOW', $itm_ac . ' 2, \'' . addslashes(Text::_($itm_jpx . '_LOW')) . '\')');
                $menu->itemJavaScript($itm_icon, $itm_jpx . '_MEDIUM', $itm_ac . ' 3, \'' . addslashes(Text::_($itm_jpx . '_MEDIUM')) . '\')');
                $menu->itemJavaScript($itm_icon, $itm_jpx . '_HIGH', $itm_ac . ' 4, \'' . addslashes(Text::_($itm_jpx . '_HIGH')) . '\')');
                $menu->itemJavaScript($itm_icon, $itm_jpx . '_VERY_HIGH', $itm_ac . ' 5, \'' . addslashes(Text::_($itm_jpx . '_VERY_HIGH')) . '\')');
            }

            $menu->end();

            echo $menu->render(array('class' => 'btn-sm'));
            ?>
        </div>

        <span class="task-title">
            <?php if (in_array($showTaskId, [2, 3])): ?>
                <span class="text-muted">[#<?php echo $item->id ?>]</span>
            <?php endif; ?>

            <span class="<?php echo $taskDescDisplay == 2 ? 'd-inline-flex flex-column' : '' ?>">

                <div class="d-inline">
                    <a
                            href="<?php echo Route::_(JPtasksHelperRoute::getTaskRoute($item->slug, $item->project_slug, $item->milestone_slug, $item->list_slug)); ?>"
                            class=""
                    >
                    <?php if ($item->checked_out) : ?>
                        <span aria-hidden="true" class="fas fa-lock"></span>
                    <?php endif; ?>
                        <?php echo $item->title; ?>
                    </a>
                    <button
                            class="task-collapse-icon btn btn-sm border py-0 px-1"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#collapse-<?php echo $x; ?>"
                            aria-expanded="false"
                            aria-controls="collapse-<?php echo $x; ?>"
                    >
                        <?php echo $collapseClass ? '-' : '+'; ?>
                    </button>
                </div>

                <?php if ($taskDescDisplay): ?>
                    <small class="task-description <?php echo $taskDescDisplay == 1 ? 'ms-2' : '' ?>">
                    <?php echo htmlspecialchars_decode(HTMLHelper::_('jp.html.truncate', $item->description, '100')); ?>
                </small>
                <?php endif; ?>



            </span>

        </span>
    </div>
    <div id="collapse-<?php echo $x; ?>" class="task-collapse-content collapse <?php echo $collapseClass ?>">
        <hr/>
        <div class="task-collapse-desc">
            <?php echo $item->description; ?>
        </div>
        <div class="d-flex justify-content-between">
            <div>
                <?php echo HTMLHelper::_('jptasks.assignedLabel', $item->id, $x, $item->users); ?>
                <?php echo HTMLHelper::_('jptasks.priorityLabel', $item->id, $x, $item->priority); ?>
                <?php echo $task_date; ?>
                <?php if ($item->access != 1) {
                    echo HTMLHelper::_('jphtml.label.access', $item->access);
                }
                ?>
                <?php if ($cmnts_enabled) : echo HTMLHelper::_('jpcomments.label', $item->comments, $item->complete); endif; ?>
                <?php if ($repo_enabled) : echo HTMLHelper::_('jprepo.attachmentsLabel', $item->attachments); endif; ?>
                <?php if ($item->label_count) : echo HTMLHelper::_('jphtml.label.labels', $item->labels); endif; ?>
            </div>
            <div class="d-inline-block">
		        <?php echo $cParams->get('task_show_created_by',0) ? LayoutHelper::render('task.parts.createdBy', ['item' => $item]) : '' ?>
		        <?php echo $cParams->get('task_show_modified_by',0) ? LayoutHelper::render('task.parts.modifiedBy', ['item' => $item]) : '' ?>
		        <?php echo $cParams->get('task_show_completed_by',0) ? LayoutHelper::render('task.parts.completedBy', ['item' => $item]) : '' ?>
		        <?php echo $cParams->get('task_show_last_comment_by',0) ? LayoutHelper::render('task.parts.lastCommentBy', ['item' => $item]) : '' ?>
            </div>
        </div>


    </div>
    <?php echo LayoutHelper::render('project.link', ['item' => $item], '', ['client' => 'site', 'component' => 'com_jpprojects']) ?>
</div>
