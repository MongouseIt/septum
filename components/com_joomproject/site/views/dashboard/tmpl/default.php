<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_joomproject
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('bootstrap.dropdown');
HTMLHelper::_('bootstrap.collapse');
$globalParams = Joomla\CMS\Component\ComponentHelper::getParams('com_jpprojects');


// Create shortcuts
$item    = $this->item;
$params  = $this->params;
$state   = $this->state;
$modules = $this->modules;

$nulldate = Factory::getDbo()->getNullDate();

$details_in     = ($state->get('project.request') ? 'in ' : '');
$details_active = ($state->get('project.request') ? ' btn-primary' : 'btn-secondary');

$item_color = $item !== null ? $item->params->get('project_color','') : '';
$project_css = JoomprojectHelperColor::getItemColor($item_color);

?>
<div id="joomproject" class="category-list<?php echo $this->pageclass_sfx;?> view-dashboard">


    <?php
    // load internal navigation
    echo JPhtmlNav::loadMain();
    ?>

	<?php
	// load header
	echo JPhtmlNav::loadHeader($this->params);
	?>

	<?php
	// load project internal navigation
	echo JPhtmlNav::loadProject();
	?>

	<div class="cat-items">
		<form id="adminForm" name="adminForm" method="post" action="<?php echo Route::_(JPprojectsHelperRoute::getDashboardRoute($state->get('filter.project'))); ?>">
			<?php if($state->get('filter.project') && !empty($item)) : ?>
				<div class="btn-group float-end mb-4">
					<a class="btn btn-sm <?php echo $details_active;?>"
                       data-bs-toggle="collapse"
                       href="#project-details"
                       role="button"
                       aria-expanded="false"
                       aria-controls="project-details"
                    >
						<?php echo Text::_('COM_JOOMPROJECT_DETAILS_LABEL'); ?> <span class="fas fa-caret-down"></span>
					</a>
				</div>
			<?php endif; ?>
			<div class="my-4">
				<?php echo $this->toolbar; ?>
				<?php echo HTMLHelper::_('jphtml.project.filter'); ?>
			</div>

			<?php if($item) echo $item->event->afterDisplayTitle; ?>

			<input type="hidden" name="task" value="" />
			<?php echo HTMLHelper::_('form.token'); ?>

			<div class="clearfix"></div>

			<?php
			if ($item) echo $item->event->beforeDisplayContent; ?>
			<?php if($state->get('filter.project') && !empty($item)) : ?>
				<div class="collapse mb-3" id="project-details">
					<div class="card p-3" <?php echo $project_css ?>>
						<div class="item-description row">
							<div class="col-md-8"><?php echo $item->text; ?></div>
							<div class="col-md-4">
								<table class="table table-bordered bg-white">
									<?php if($item->start_date != $nulldate): ?>
										<tr>
											<td class="start-title">
												<?php echo Text::_('JGRID_HEADING_START_DATE'); ?>:
											</td>
											<td class="start-data">
												<?php echo HTMLHelper::_('jphtml.label.datetime', $item->start_date); ?>
											</td>
										</tr>
									<?php endif; ?>
									<?php if($item->end_date != $nulldate): ?>
										<tr>
											<td class="due-title">
												<?php echo Text::_('JGRID_HEADING_DEADLINE'); ?>:
											</td>
											<td class="due-data">
												<?php echo HTMLHelper::_('jphtml.label.datetime', $item->end_date); ?>
											</td>
										</tr>
									<?php endif;?>
									<tr>
										<td class="owner-title">
											<?php echo Text::_('JGRID_HEADING_CREATED_BY'); ?>:
										</td>
										<td class="owner-data">
											<?php echo HTMLHelper::_('jphtml.label.author', $item->author, $item->created); ?>
										</td>
									</tr>
									<?php if ($item->params->get('website')) : ?>
										<tr>
											<td class="owner-title">
												<?php echo Text::_('COM_JOOMPROJECT_FIELD_WEBSITE_LABEL'); ?>:
											</td>
											<td class="owner-data">
												<a href="<?php echo $item->params->get('website');?>" target="_blank">
													<?php echo Text::_('COM_JOOMPROJECT_FIELD_WEBSITE_VISIT_LABEL');?>
												</a>
											</td>
										</tr>
									<?php endif; ?>

									<?php if ($item->params->get('email')) : ?>
										<tr>
											<td class="owner-title">
												<?php echo Text::_('COM_JOOMPROJECT_FIELD_EMAIL_LABEL'); ?>:
											</td>
											<td class="owner-data">
												<a href="mailto:<?php echo $item->params->get('email');?>" target="_blank">
													<?php echo $item->params->get('email');?>
												</a>
											</td>
										</tr>
									<?php endif; ?>
									<?php if ($item->params->get('phone')) : ?>
										<tr>
											<td class="owner-title">
												<?php echo Text::_('COM_JOOMPROJECT_FIELD_PHONE_LABEL'); ?>:
											</td>
											<td class="owner-data">
												<?php echo $item->params->get('phone');?>
											</td>
										</tr>
									<?php endif; ?>
									<?php if (JPApplicationHelper::enabled('com_jprepo') && count($item->attachments)) : ?>
										<tr>
											<td class="owner-title">
												<?php echo Text::_('COM_JOOMPROJECT_FIELDSET_ATTACHMENTS'); ?>:
											</td>
											<td class="owner-data">
												<?php echo HTMLHelper::_('jprepo.attachments', $item->attachments); ?>
											</td>
										</tr>
									<?php endif; ?>
								</table>
								</dl>
							</div>
							<div class="clearfix"></div>
						</div>
					</div>
				</div>
				<div class="clearfix"></div>

               <?php echo \Joomla\CMS\Layout\LayoutHelper::render('default', ['item' => $item,'fieldParams' =>  $globalParams],JPATH_ADMINISTRATOR.'/components/com_jpprojects/layouts');


               ?>
			<?php endif; ?>
			<input type="hidden" name="task" value="" />
			<?php echo HTMLHelper::_('form.token'); ?>
		</form>

		<!-- Begin Dashboard Modules -->

		<div class="mb-3">
			<?php echo JoomprojectHelperFrontend::renderModules('jp-dashboard-top') ?>
        </div>
        <div class="clearfix"></div>
		<div class="row">
			<div class="col-md-6">
				<?php echo JoomprojectHelperFrontend::renderModules('jp-dashboard-left') ?>
			</div>
			<div class="col-md-6">
				<?php echo JoomprojectHelperFrontend::renderModules('jp-dashboard-right') ?>
			</div>
		</div>
        <div class="clearfix"></div>
		<div class="mt-3">
			<?php echo JoomprojectHelperFrontend::renderModules('jp-dashboard-bottom') ?>
        </div>


		<!-- End Dashboard Modules -->
        <?php if ($item) echo $item->event->afterDisplayContent; ?>

	</div>
</div>