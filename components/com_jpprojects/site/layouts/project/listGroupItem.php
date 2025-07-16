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
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;

extract($displayData);


$user        = Factory::getApplication()->getIdentity();
$uid         = $user->get('id');

$access      = JPprojectsHelper::getActions($item->id);
$link        = JPprojectsHelperRoute::getDashboardRoute($item->slug);
$nulldate    = Factory::getDbo()->getNullDate();

$can_edit     = $access->get('core.edit');
$can_checkin  = ($user->authorise('core.manage', 'com_checkin') || $item->checked_out == $uid || $item->checked_out == 0);
$can_edit_own = ($access->get('core.edit.own') && $item->created_by == $uid);
$can_change   = ($access->get('core.edit.state') && $can_checkin);

// Calculate project progress
$task_count = (int) $item->tasks;
$completed  = (int) $item->completed_tasks;

// Repo directory
$repo_dir = (int) $params->get('repo_dir');

if ($item->progress >= 67) $progress_class = 'info';
if ($item->progress == 100) $progress_class = 'success';
if ($item->progress < 67) $progress_class = 'warning';
if ($item->progress < 34) $progress_class = 'danger label-important';

$filter_in          = ($state->get('filter.isset') ? 'in ' : '');
$milestones_enabled = JPApplicationHelper::enabled('com_jpmilestones');
$tasks_enabled      = JPApplicationHelper::enabled('com_jptasks');
$time_enabled       = JPApplicationHelper::enabled('com_jptime');
$repo_enabled       = JPApplicationHelper::enabled('com_jprepo');
$forum_enabled      = JPApplicationHelper::enabled('com_jpforum');
$users_enabled      = JPApplicationHelper::enabled('com_jpusers');
$cmnts_enabled      = JPApplicationHelper::enabled('com_jpcomments');
$is_ssl             = Uri::getInstance()->isSSL();
$params             = ComponentHelper::getParams('com_jpprojects');

$itemid = JPApplicationHelper::getActiveMenuItemId();

$list_url   = JPprojectsHelperRoute::getProjectsRoute($params->get('filter_category'), $itemid);
$return_url = base64_encode($list_url);



// Prepare the watch button
if ($uid)
{
	$options = array('div-class' => '', 'a-class' => 'btn-sm');
	$watch   = HTMLHelper::_('jphtml.button.watch', 'projects', $item->id, $item->watching, $options);
}
else
{
	$watch = '';
}

// Compact List Layout of assigned users
$compactList = new FileLayout(
        'projects.participants.compactlist',
        JPATH_ROOT . '/administrator/components/com_joomproject/layouts', array('component' => 'com_joomproject')
);

//Project Color
$item_color = $item !== null ? $item->params->get('project_color', '') : '';

$project_css = JoomprojectHelperColor::getItemColor($item_color);

$assignedUsersList = JPprojectsHelper::getAssignedUsers($item->id,$item->params->get('userType'));

?>

<li class="list-group-item" <?php echo $project_css ?>>
	<div class="row">
		<div class="col-md-9 py-3">
			<a href="<?php echo Route::_($link); ?>">
				<?php if (!empty($item->logo_img)) : ?>
					<img src="<?php echo $item->logo_img; ?>" width="80"
					     class="thumbnail float-start me-4"
					     alt="<?php echo $current->escape($item->title); ?>"/>
				<?php else : ?>
					<img
						 src="<?php echo Uri::root(true) . '/media/com_joomproject/joomproject/images/icons/project-placeholder.png'; ?>"
					     class="rounded float-start me-4"
					     width="80"
					     alt="<?php echo $current->escape($item->title); ?>"
					/>
				<?php endif; ?>
			</a>
			<h5 class="item-title mb-4">
				<?php if ($can_change || $uid) : ?>
					<?php echo HTMLHelper::_('jp.html.id', $item->id, $item->id); ?>
				<?php endif; ?>

				<?php if ($item->checked_out) : ?>
					<span aria-hidden="true" class="fas fa-lock me-2"></span>
				<?php endif; ?>
				<a href="<?php echo Route::_($link); ?>" class="project-title">
					<?php echo $current->escape($item->title); ?>
				</a>
			</h5>
			<div class="">
				<?php if ($can_edit || $can_edit_own) : ?>
					<a class="hasTooltip btn btn-light btn-sm"
					   href="<?php echo Route::_(JPprojectsHelperRoute::getProjectEditRoute($item->slug) . '&return=' . $return_url); ?>"
                       title="<?php echo Text::_('COM_JOOMPROJECT_ACTION_EDIT'); ?>"
                    >
						<span aria-hidden="true" class="fas fa-edit"></span>
					</a>
				<?php endif; ?>

				<?php if ($cmnts_enabled) : ?>
					<a class="hasTooltip btn btn-light btn-sm" href="<?php echo Route::_($link); ?>#comments"
                       title="<?php echo Text::_('Comments'); ?>"
                    >
                        <span aria-hidden="true" class="fas fa-comment"></span>
                        <?php echo $item->comments; ?>
					</a>
				<?php endif; ?>
				<?php if ($milestones_enabled) : ?>
					<a class="hasTooltip btn btn-light btn-sm"
					   href="<?php echo Route::_(JPmilestonesHelperRoute::getMilestonesRoute($item->slug)); ?>"
                       title="<?php echo Text::_('JGRID_HEADING_MILESTONES'); ?>"
                    >
						<span aria-hidden="true" class="fas fa-map-marker"></span>
						<?php echo (int) $item->milestones; ?>
					</a>
				<?php endif; ?>
				<?php if ($tasks_enabled) : ?>
					<a class="hasTooltip btn btn-light btn-sm"
					   href="<?php echo Route::_(JPtasksHelperRoute::getTasksRoute($item->slug)); ?>"
                       title="<?php echo Text::_('JGRID_HEADING_TASKLISTS'); ?>"
                    >
						<span aria-hidden="true" class="fas fa-list"></span>
						<?php echo (int) $item->tasklists; ?>
					</a>
				<?php endif; ?>
				<?php if ($tasks_enabled) : ?>
					<a class="hasTooltip btn btn-light btn-sm"
					   href="<?php echo Route::_(JPtasksHelperRoute::getTasksRoute($item->slug)); ?>"
                       title="<?php echo Text::_('JGRID_HEADING_TASKS'); ?>"
                    >
						<span aria-hidden="true" class="fas fa-check"></span>
						<?php echo (int) $item->tasks; ?>
					</a>
				<?php endif; ?>
				<?php if ($repo_enabled) : ?>
					<a class="hasTooltip btn btn-light btn-sm"
					   href="<?php echo Route::_(JPrepoHelperRoute::getRepositoryRoute($item->slug, $repo_dir)); ?>"
                       title="<?php echo Text::_('COM_JOOMPROJECT_FIELDSET_ATTACHMENTS'); ?>"
                    >
						<span aria-hidden="true" class="fas fa-flag"></span>
						<?php echo (int) $item->attachments; ?>
					</a>
				<?php endif; ?>
				<?php echo $watch; ?>

			</div>
			<?php if (!empty($state->get('filter.category')) && $state->get('filter.category') != $item->catid): ?>
				<p>
					<strong><?php echo Text::_('COM_JOOMPROJECT_INSUBCATEGORY') ?></strong> <span
						class="badge bg-info"><?php echo $item->category_title ?></span>
				</p>
			<?php endif; ?>
			<div class="clearfix"></div>
			<!-- <hr /> -->

			<div class="project-description mt-4"><?php echo HTMLHelper::_('jp.html.truncate', $item->description, 200); ?></div>

            <?php if(count($assignedUsersList) > 0): ?>
			<div class="partcipants mt-4">
				<div class="row">
					<div class="col-auto d-flex align-items-center">
						<h5 class="mb-0 pb-0">
							<?php
							if (!$params->get('userType', 0))
							{
								echo Text::_('MOD_JPPROJECT_PARTICIPANTS_USERS');
							}
							else
							{
								echo Text::_('MOD_JPPROJECT_ASSIGNED_USERS');
							}
							?>
						</h5>
					</div>
					<div class="col-auto">
						<?php
						echo $compactList->render(
							[
							        'data'   => $assignedUsersList,
							        'modId'  => $item->id,
							        'params' => $item->params
							])
						?>
					</div>
				</div>
			</div>
            <?php endif; ?>
		</div>
		<div class="col-md-3 py-3">
			<hr class="d-sm-none"/>
			<div class="progress my-4">
				<div class="progress-bar progress-bar-striped bg-<?php echo $progress_class; ?>"
				     role="progressbar"
				     style="width: <?php echo ($item->progress > 0) ? $item->progress . "%" : "24px"; ?>"
				     aria-valuenow="<?php echo ($item->progress > 0) ? $item->progress : "24"; ?>"
				     aria-valuemin="0"
				     aria-valuemax="100"
				>
					<span class="float-end"><?php echo $item->progress; ?>%</span>
				</div>
			</div>

			<ul class="list-group border rounded">
				<?php if ($item->end_date != $nulldate): ?>
					<li class="list-group-item">
						<span><?php echo Text::_('JGRID_HEADING_DEADLINE'); ?></span>&nbsp;<span><?php echo HTMLHelper::_('jphtml.label.datetime', $item->end_date); ?></span>

					</li>
				<?php endif; ?>
				<li class="list-group-item">
					<span class="float-start"><?php echo Text::_('JGRID_HEADING_CREATED_BY'); ?></span>&nbsp;<span><?php echo HTMLHelper::_('jphtml.label.author', $item->author_name, $item->created); ?></span>
				</li>
				<?php if ($item->params->get('website')) : ?>
					<li class="list-group-item">
						<span><?php echo Text::_('COM_JOOMPROJECT_FIELD_WEBSITE_LABEL'); ?> </span> <a href="<?php echo $item->params->get('website'); ?>" target="_blank">
							<?php echo Text::_('COM_JOOMPROJECT_FIELD_WEBSITE_VISIT_LABEL'); ?>
						</a>

					</li>
				<?php endif; ?>
				<?php if ($item->params->get('email')) : ?>
					<li class="list-group-item">
						<span><?php echo Text::_('COM_JOOMPROJECT_FIELD_EMAIL_LABEL'); ?> </span>
						<a href="mailto:<?php echo $item->params->get('email'); ?>" target="_blank">
							<?php echo $item->params->get('email'); ?>
						</a>
					</li>
				<?php endif; ?>
				<?php if ($item->params->get('phone')) : ?>
					<li class="list-group-item">
						<span><?php echo Text::_('COM_JOOMPROJECT_FIELD_PHONE_LABEL'); ?> </span>
						<span class="text-muted"><a href="tel:<?php echo $item->params->get('phone'); ?>"><?php echo $item->params->get('phone'); ?></a></span>
					</li>
				<?php endif; ?>
			</ul>
		</div>
	</div>
</li>



