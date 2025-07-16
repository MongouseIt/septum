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
defined( '_JEXEC' ) or die ;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

extract($displayData);

$user       = Factory::getApplication()->getIdentity();
$uid        = $user->get('id');

$filter_in     = ($state->get('filter.isset') ? 'in ' : '');
$tasks_enabled = JPApplicationHelper::enabled('com_jptasks');
$repo_enabled  = JPApplicationHelper::enabled('com_jprepo');
$cmnts_enabled = JPApplicationHelper::enabled('com_jpcomments');

// return url
$itemid     = JPApplicationHelper::getActiveMenuItemId();
$list_url   = JPmilestonesHelperRoute::getMilestonesRoute($params->get('filter_category'), $itemid);
$return_url = base64_encode($list_url);

$access = JPmilestonesHelper::getActions($item->id);
$can_create   = $access->get('core.create');
$can_edit     = $access->get('core.edit');
$can_edit_own = ($access->get('core.edit.own') && $item->created_by == $uid);
$can_checkin  = ($user->authorise('core.manage', 'com_checkin') || $item->checked_out == $uid || $item->checked_out == 0);
$can_change   = ($access->get('core.edit.state') && $can_checkin);

// Calculate milestone progress
$task_count = (int) $item->tasks;
$completed  = (int) $item->completed_tasks;
$progress   = $item->progress;

// Repo directory
$repo_dir = (int) $params->get('repo_dir');

if ($item->progress >= 67) $progress_class = 'info';
if ($item->progress == 100) $progress_class = 'success';
if ($item->progress < 67) $progress_class = 'warning';
if ($item->progress < 34) $progress_class = 'danger';

$item_css   = JoomprojectHelperColor::getItemColor($item->params->get('milestone_color', ''));

// Prepare the watch button
$watch = '';

if ($uid)
{
	$options = array('div-class' => '', 'a-class' => 'btn-sm');
	$watch   = HTMLHelper::_('jphtml.button.watch', 'milestones', $item->id, $item->watching, $options);
}

?>

<li class="list-group-item" <?php echo $item_css ?>>
	<div class="row">
		<div class="col-md-1 d-none d-sm-block">
			<div class="rounded text-center">
				<div class=" w-100 h-100 d-block <?php echo ($progress == 100) ? "bg-success text-white" : "bg-secondary text-white"; ?>"
				     data-bs-toggle="tooltip"
				     data-placement="top" <?php if ($progress == 100) : echo "title=\"" . Text::_('COM_JOOMPROJECT_FIELD_COMPLETE_LABEL') . "\""; endif; ?>>
					<div class="large"><?php echo HTMLHelper::_('date', (string) $item->end_date, Text::_('d')); ?></div>
					<div class="medium"><?php echo HTMLHelper::_('date',(string)  $item->end_date, Text::_('M')); ?></div>
				</div>
			</div>
			<hr/>
			<?php if ($tasks_enabled) : ?>
				<div class="progress">
					<div class="progress-bar bg-<?php echo $progress_class; ?> progress-bar-striped progress-milestone"
					     style="min-width:20px; width: <?php echo ($progress > 0) ? $progress . "%" : "20px"; ?>"
					     role="progressbar"
					     aria-valuenow="<?php echo ($progress > 0) ? $progress . "%" : "20px"; ?>"
					     aria-valuemin="0" aria-valuemax="100">
                                        <span class="">
                                            <?php echo $progress; ?>%
                                        </span>
					</div>
				</div>
			<?php endif; ?>
		</div>
		<div class="col-md-11">
			<div class="">
				<span class="float-end"><?php echo HTMLHelper::_('jphtml.label.datetime', $item->end_date); ?></span>
				<?php if ($can_change || $uid) : ?>
					<label for="cb<?php echo $item->id; ?>" class="checkbox p-0 float-start">
						<?php echo HTMLHelper::_('jp.html.id', $item->id, $item->id); ?>
					</label>
				<?php endif; ?>
				<h4>
					<?php if ($item->checked_out) : ?>
						<span aria-hidden="true" class="fas fa-lock"></span>
					<?php endif; ?>
					<a href="<?php echo Route::_(JPmilestonesHelperRoute::getMilestoneRoute($item->slug, $item->project_slug)); ?>">
						<?php echo $this->escape($item->title); ?>
					</a>
					<?php if ($item->label_count) : echo HTMLHelper::_('jphtml.label.labels', $item->labels); endif; ?>
				</h4>
				<div class="well-description">
					<?php echo HTMLHelper::_('jp.html.truncate', $item->description, 180); ?>
				</div>
				<hr/>
				<div class="m-0">
					<?php if ($can_edit || $can_edit_own) : ?>
						<a class="btn btn-light  btn-sm"
						   href="<?php echo Route::_('index.php?option=com_jpmilestones&task=form.edit&id=' . $item->slug . '&return=' . $return_url); ?>">
							<span aria-hidden="true" class="fas fa-edit"></span>
							<span class="d-none d-md-inline-block"><?php echo Text::_('COM_JOOMPROJECT_ACTION_EDIT'); ?></span>
						</a>
					<?php endif; ?>
					<?php if ($cmnts_enabled) : ?>
						<a class="btn btn-light  btn-sm"
						   href="<?php echo Route::_(JPmilestonesHelperRoute::getMilestoneRoute($item->slug, $item->project_slug)); ?>#comments">
							<span aria-hidden="true" class="fas fa-comment"></span>
							<span class="d-none d-md-inline-block"><?php echo $item->comments; ?> <?php echo Text::_('COM_JOOMPROJECT_COMMENTS'); ?></span>
						</a>
					<?php endif; ?>
					<?php if ($tasks_enabled) : ?>
						<a href="<?php echo Route::_(JPtasksHelperRoute::getTasksRoute($item->project_id, $item->id)); ?>"
						   class="btn btn-light  btn-sm">
							<span aria-hidden="true" class="fas fa-list"></span>
							<?php echo (int) $item->tasklists; ?> <span
								class="d-none d-md-inline-block"><?php echo Text::_('JGRID_HEADING_TASKLISTS'); ?></span>
						</a>
					<?php endif; ?>
					<?php if ($tasks_enabled) : ?>
						<a href="<?php echo Route::_(JPtasksHelperRoute::getTasksRoute($item->id, $item->id)); ?>"
						   class="btn btn-light  btn-sm">
							<span aria-hidden="true" class="fas fa-check"></span>
							<?php echo (int) $item->tasks; ?> <span
								class="d-none d-md-inline-block"><?php echo Text::_('JGRID_HEADING_TASKS'); ?></span>
						</a>
					<?php endif; ?>
					<?php if ($repo_enabled) : ?>
						<a href="<?php echo Route::_(JPrepoHelperRoute::getRepositoryRoute($item->id, $repo_dir,'',$item->id)); ?>"
						   class="btn btn-light  btn-sm">
							<span aria-hidden="true" class="fas fa-flag"></span>
							<?php echo (int) $item->attachments; ?> <span
								class="d-none d-md-inline-block"><?php echo Text::_('COM_JOOMPROJECT_FIELDSET_ATTACHMENTS'); ?></span>
						</a>
					<?php endif; ?>

					<?php echo $watch; ?>
				</div>
			</div>

		</div>
	</div>
</li>






