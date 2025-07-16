<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jpprojects
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2006-2016 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */


defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;


HTMLHelper::_('jphtml.script.listform');

$list_order  = $this->escape($this->state->get('list.ordering'));
$list_dir    = $this->escape($this->state->get('list.direction'));
$user        = Factory::getApplication()->getIdentity();
$app         = Factory::getApplication();
$nulldate    = Factory::getDbo()->getNullDate();
$uid         = $user->get('id');
$itemid      = JPApplicationHelper::getActiveMenuItemId();
$compactList = new FileLayout('projects.participants.compactlist', JPATH_ROOT . '/administrator/components/com_joomproject/layouts', array('component' => 'com_joomproject'));

$filter_in          = ($this->state->get('filter.isset') ? 'in ' : '');
$milestones_enabled = JPApplicationHelper::enabled('com_jpmilestones');
$tasks_enabled      = JPApplicationHelper::enabled('com_jptasks');
$time_enabled       = JPApplicationHelper::enabled('com_jptime');
$repo_enabled       = JPApplicationHelper::enabled('com_jprepo');
$forum_enabled      = JPApplicationHelper::enabled('com_jpforum');
$users_enabled      = JPApplicationHelper::enabled('com_jpusers');
$cmnts_enabled      = JPApplicationHelper::enabled('com_jpcomments');
$is_ssl             = Uri::getInstance()->isSSL();


$list_url   = JPprojectsHelperRoute::getProjectsRoute($this->params->get('filter_category'), $itemid);
$return_url = base64_encode($list_url);
$print_url  = $list_url . '&tmpl=component&layout=print';
$print_opt  = 'width=1024,height=600,resizable=yes,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no';

?>

<?php
// load header
echo JPhtmlNav::loadHeader($this->params);
?>
<div class="grid">
    <form name="adminForm" id="adminForm" action="<?php echo Route::_($list_url); ?>" method="post">
        <div class="form-group mb-4">
			<?php echo $this->toolbar; ?>
            <a class="btn btn-primary btn-sm button" id="print_btn" href="javascript:void(0);"
               onclick="window.open('<?php echo Route::_($print_url); ?>', 'print', '<?php echo $print_opt; ?>')">
                <i class="fas fa-print"></i> <?php echo Text::_('COM_JOOMPROJECT_PRINT'); ?>
            </a>

            <?php echo LayoutHelper::render(
                'common.export',
                [
                    'url' => JPprojectsHelperRoute::getProjectsRoute()
                        . '&tmpl=component'
                ],
                null,
                ['client' => 'administrator', 'component' => 'com_joomproject']
            ) ?>

        </div>
		<?php if ($this->params->get('show_filter', '1')) : ?>
            <div class="collapse" id="filters">
                <div class="input-group mb-4">
                    <input type="text" class="form-control" name="filter_search"
                           placeholder="<?php echo Text::_('JSEARCH_FILTER_SEARCH'); ?>" id="filter_search"
                           value="<?php echo $this->escape($this->state->get('filter.search')); ?>"/>
                        <button type="submit" class="btn btn-secondary" data-bs-toggle="tooltip" data-placement="top"
                                title="<?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?>">
                            <span aria-hidden="true" class="fas fa-search"></span>
                        </button>
                        <button type="button" class="btn btn-danger" data-bs-toggle="tooltip" data-placement="top"
                                title="<?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>"
                                onclick="document.getElementById('filter_search').value='';this.form.submit();">
                            <span aria-hidden="true" class="fas fa-times"></span>
                        </button>
                </div>
                <div class="row">
                    <div class="col-md-3 mb-4">
                        <select name="filter_order" class="inputbox form-control" onchange="this.form.submit()">
							<?php echo HTMLHelper::_('select.options', $this->sort_options, 'value', 'text', $list_order, true); ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-4">
                        <select name="filter_order_Dir" class="inputbox form-control" onchange="this.form.submit()">
							<?php echo HTMLHelper::_('select.options', $this->order_options, 'value', 'text', $list_dir, true); ?>
                        </select>

                    </div>
                    <div class="col-md-3 mb-4">
                        <select name="filter_category" class="inputbox form-control" onchange="this.form.submit()">
                            <option value=""><?php echo Text::_('JOPTION_SELECT_CATEGORY'); ?></option>
							<?php echo HTMLHelper::_('select.options', HTMLHelper::_('category.options', 'com_jpprojects'), 'value', 'text', $this->state->get('filter.category')); ?>
                        </select>
                    </div>

					<?php if ($this->access->get('core.edit.state') || $this->access->get('core.edit')) : ?>
                        <div class="col-md-3 mb-4">
                            <select name="filter_published" class="form-control" onchange="this.form.submit()">
                                <option value=""><?php echo Text::_('JOPTION_SELECT_PUBLISHED'); ?></option>
								<?php echo HTMLHelper::_('select.options', HTMLHelper::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true); ?>
                            </select>
                        </div>
					<?php endif; ?>
                </div>
            </div>
		<?php endif; ?>
        <div class="card shadow-sm">
            <table class="table m-0">
                <thead>
                <th class="border-top-0" scope="col"><?php echo Text::_('COM_JPPROJECT_NAME'); ?></th>
                <th class="border-top-0" scope="col">
					<?php echo !empty($this->items) && (int) $this->items[0]->params->get('userType') == 0 ? Text::_('MOD_JPPROJECT_PARTICIPANTS_USERS') : Text::_('MOD_JPPROJECT_ASSIGNED_USERS') ?>
                </th>
                <th class="border-top-0" scope="col"><?php echo Text::_('JGRID_HEADING_DEADLINE'); ?> </th>
                <th class="border-top-0" scope="col"><?php echo Text::_('COM_JOOMPROJECT_MILESTONES'); ?></th>
                <th class="border-top-0" scope="col"><?php echo Text::_('JGRID_HEADING_TASKS'); ?></th>
                <th class="border-top-0" scope="col"><?php echo Text::_('JGRID_HEADING_STATUS'); ?> </th>
                <th class="border-top-0" scope="col"><?php echo Text::_('JGRID_HEADING_ID'); ?> </th>
                </thead>
                <tbody>
				<?php
				$current_cat  = '';
				$count        = count($this->items) - 1;
				$this->items  = $this->array_group_by($this->items, 'category_title');

				foreach ($this->items as $title => $category) : ?>
                    <?php  if ($title != $this->state->get('filter.category') && !is_numeric($this->state->get('filter.category')) && $title != 'ROOT') :  ?>
                    <tr class="bg-light">
                        <td colspan="7"><h4 class="m-0"><?php echo $title ?></h4></td>
                    </tr>
                    <?php endif; ?>
					<?php foreach ($category as $i => $item) :
						$access = JPprojectsHelper::getActions($item->id);
						$link = JPprojectsHelperRoute::getDashboardRoute($item->slug);

						$can_edit     = $access->get('core.edit');
						$can_checkin  = ($user->authorise('core.manage', 'com_checkin') || $item->checked_out == $uid || $item->checked_out == 0);
						$can_edit_own = ($access->get('core.edit.own') && $item->created_by == $uid);
						$can_change   = ($access->get('core.edit.state') && $can_checkin);

						// Calculate project progress
						$task_count = (int) $item->tasks;
						$completed  = (int) $item->completed_tasks;

						// Repo directory
						$repo_dir = (int) $this->params->get('repo_dir');

						if ($item->progress >= 67) $progress_class = 'info';
						if ($item->progress == 100) $progress_class = 'success';
						if ($item->progress < 67) $progress_class = 'warning';
						if ($item->progress < 34) $progress_class = 'danger label-important';

						// Prepare the watch button
						if ($uid)
						{
							$options = array('div-class' => '', 'a-class' => 'btn-sm');
							$watch   = HTMLHelper::_('jphtml.button.watch', 'projects', $i, $item->watching, $options);
						}
						else
						{
							$watch = '';
						}
						?>


                        <tr>
                            <td class="align-middle">
                                <h5 class="item-title m-0">
									<?php if ($can_change || $uid) : ?>
										<?php echo HTMLHelper::_('jp.html.id', $i, $item->id); ?>
									<?php endif; ?>
									<?php if ($item->checked_out) : ?>
                                        <span aria-hidden="true" class="fas fa-lock me-2"></span>
									<?php endif; ?>
                                    <a href="<?php echo Route::_($link); ?>" class="project-title">
										<?php echo $this->escape($item->title); ?>
                                    </a>
                                </h5>
                            </td>
                            <td class="align-middle">
								<?php
								echo $compactList->render(
									['data'   => JPprojectsHelper::getAssignedUsers($item->id,
										(int) $item->params->get('userType')),
									 'modId'  => $item->id,
									 'params' => $item->params
									])
								?>
                            </td>
                            <td class="align-middle">
								<?php echo HTMLHelper::_('jphtml.label.datetime', $item->end_date); ?>
                            </td>
							<?php if ($milestones_enabled) : ?>
                                <td class="align-middle">
                                    <a class="text-decoration-none"
                                       href="<?php echo Route::_(JPmilestonesHelperRoute::getMilestonesRoute($item->slug)); ?>">
                                        <span class="d-inline-block border font-weight-bolder mb-1 py-0 px-1 bg-light rounded text-muted"><?php echo (int) $item->milestones; ?></span> <?php echo Text::_('JGRID_HEADING_MILESTONES'); ?>
                                    </a>
                                </td>
							<?php endif; ?>
							<?php if ($tasks_enabled) : ?>
                                <td class="align-middle">
                                    <a class="text-decoration-none"
                                       href="<?php echo Route::_(JPtasksHelperRoute::getTasksRoute($item->slug)); ?>">

                                        <span class="d-inline-block border font-weight-bolder mb-1 py-0 px-1 bg-light rounded text-muted"><?php echo $item->tasks; ?></span> <?php echo Text::_('JGRID_HEADING_TASKS'); ?>
                                    </a>
                                </td>

							<?php endif; ?>
                            <td class="align-middle">
                                <div class="progress">
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
                            </td>
                            <td class="align-middle">
								<?php echo $item->id; ?>
                            </td>
                        </tr>
					<?php endforeach; ?>
				<?php endforeach; ?>
                </tbody>
            </table>
        </div>


		<?php echo LayoutHelper::render('common.pagination', ['pagination' => $this->pagination, 'params' => $this->params]) ?>

        <input type="hidden" id="boxchecked" name="boxchecked" value="0"/>
        <input type="hidden" name="task" value=""/>
		<?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>



