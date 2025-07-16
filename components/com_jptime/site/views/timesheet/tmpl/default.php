<?php
/**
 * @package      Joomproject
 * @subpackage   Timetracking
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2006-2012 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */
defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('bootstrap.dropdown');
HTMLHelper::_('bootstrap.collapse');
HTMLHelper::_('bootstrap.popover', '.hasPopover', ['trigger' => 'hover focus']);

HTMLHelper::_('jphtml.script.listform');

$default_date_range_type = $this->params->get('predefined_date_range', 0);


$list_order      = $this->escape($this->state->get('list.ordering'));
$list_dir        = $this->escape($this->state->get('list.direction'));
$start_date      = $this->escape($this->state->get('filter.start_date'));
$end_date        = $this->escape($this->state->get('filter.end_date'));
$range_date_type = $this->escape($this->state->get('filter.date_range_type'),$default_date_range_type);
$user            = Factory::getApplication()->getIdentity();
$uid             = $user->get('id');

$list_total_time     = 0;
$list_total_billable = 0.00;

// Calculate un/billable time percentage
$billable_percent   = ($this->total_time == 0) ? 0 : round($this->total_time_billable * (100 / $this->total_time));
$unbillable_percent = ($this->total_time == 0) ? 0 : round($this->total_time_unbillable * (100 / $this->total_time));

$total_hours =  $this->total_time > 0 ? HTMLHelper::_('time.format', $this->total_time, 'decimal') : 0;

$filter_in = ($this->state->get('filter.isset') ? 'in ' : '');

$print_url = JPtimeHelperRoute::getTimesheetRoute($this->state->get('filter.project'))
	. '&tmpl=component&layout=print';
$print_opt = 'width=1024,height=600,resizable=yes,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no';



?>
<script type="text/javascript">
    Joomla.submitbutton = function (task) {
        if (task == 'recorder') {
            var win_attr = 'width=500,height=600,resizable=yes,'
                + 'scrollbars=yes,toolbar=no,location=no,'
                + 'directories=no,status=no,menubar=no';

            window.open('<?php echo Route::_('index.php?option=com_jptime&view=recorder&tmpl=component'); ?>', 'winJPtimerec', win_attr);
        } else {
            Joomla.submitform(task, document.getElementById('adminForm'));
        }
    }
</script>
<div id="joomproject" class="category-list<?php echo $this->pageclass_sfx; ?> view-timesheet PrintArea all">

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

    <div class="clearfix"></div>

    <div class="cat-items">

        <form name="adminForm" id="adminForm" action="<?php echo Route::_(JPtimeHelperRoute::getTimesheetRoute()); ?>"
              method="post">
            <div class="mb-4">
				<?php echo $this->toolbar; ?>
				<?php echo HTMLHelper::_('jphtml.project.filter'); ?>
                <a class="btn btn-primary btn-sm button" id="print_btn" href="javascript:void(0);"
                   onclick="window.open('<?php echo Route::_($print_url); ?>', 'print', '<?php echo $print_opt; ?>')">
                    <i class="fas fa-print"></i> <?php echo Text::_('COM_JOOMPROJECT_PRINT'); ?>
                </a>

                <?php echo LayoutHelper::render(
                    'common.export',
                    [
                        'url' => JPtimeHelperRoute::getTimesheetRoute($this->state->get('filter.project'))
                            . '&tmpl=component'
                    ],
                    null,
                    ['client'=>'administrator','component' => 'com_joomproject']
                ) ?>

            </div>

            <div class="collapse bg-light p-3 rounded mb-4" id="filters">
                <div class="row">
		            <?php if ($this->access->get('time.edit.state') || $this->access->get('time.edit')) : ?>
                        <div class="col-md-3 mb-2 mb-xs-2">
                            <select name="filter_published" class="form-control" onchange="this.form.submit()">
                                <option value=""><?php echo Text::_('JOPTION_SELECT_PUBLISHED'); ?></option>
					            <?php echo HTMLHelper::_('select.options', HTMLHelper::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true); ?>
                            </select>
                        </div>
		            <?php endif; ?>
		            <?php if (intval($this->state->get('filter.project')) > 0) : ?>
                        <div class="col-md-3 mb-2 mb-xs-2">
                            <select id="filter_author" name="filter_author" class="form-control"
                                    onchange="this.form.submit()">
                                <option value=""><?php echo Text::_('JOPTION_SELECT_AUTHOR'); ?></option>
					            <?php echo HTMLHelper::_('select.options', $this->authors, 'value', 'text', $this->state->get('filter.author'), true); ?>
                            </select>
                        </div>

                        <div class="col-md-3 mb-2 mb-xs-2">
                            <select id="filter_task" name="filter_task" class="form-control"
                                    onchange="this.form.submit()">
                                <option value=""><?php echo Text::_('COM_JOOMPROJECT_OPTION_SELECT_TASK'); ?></option>
					            <?php echo HTMLHelper::_('select.options', $this->tasks, 'value', 'text', $this->state->get('filter.task'), true); ?>
                            </select>
                        </div>

		            <?php endif; ?>
                    <div class="col-md-3 mb-2 mb-xs-2">
                        <select name="filter_order" class="form-control" onchange="this.form.submit()">
				            <?php echo HTMLHelper::_('select.options', $this->sort_options, 'value', 'text', $list_order, true); ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2 mb-xs-2">
                        <select name="filter_order_Dir" class="form-control" onchange="this.form.submit()">
				            <?php echo HTMLHelper::_('select.options', $this->order_options, 'value', 'text', $list_dir, true); ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 mb-2 mb-xs-2">
                        <input type="text" class="form-control" name="filter_search"
                               placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>" id="filter_search"
                               value="<?php echo $this->escape($this->state->get('filter.search')); ?>"/>
                    </div>
                    <div class="col-md-3 mb-2 mb-xs-2">
		                <?php
		                $cal = HTMLHelper::calendar(
			                $start_date,
			                'filter_start_date',
			                'filter_start_date',
			                '%Y-%m-%d',
			                [

				                'placeholder' => Text::_('COM_JOOMPROJECT_FILTER_DATE_FROM'),
				                'onChange'    => "document.getElementById('predefinedDateRange').getElementsByTagName('option')[0].selected = 'selected';",
				                'class'       => 'form-control'
			                ]
		                );
		                echo $cal
		                ?>
                    </div>
                    <div class="col-md-3 mb-2 mb-xs-2">
		                <?php
		                $cal = HTMLHelper::calendar(
			                $end_date,
			                'filter_end_date',
			                'filter_end_date',
			                '%Y-%m-%d',
			                [
				                'placeholder' => Text::_('COM_JOOMPROJECT_FILTER_DATE_TO'),
				                'onChange'    => "document.getElementById('predefinedDateRange').getElementsByTagName('option')[0].selected = 'selected';",
				                'class'       => 'form-control'
			                ]

		                );
		                echo $cal
		                ?>
                    </div>
                    <div class="col-md-3">
	                    <?php echo LayoutHelper::render('filter.field.predefineddaterange', ['default' => $range_date_type]) ?>

                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="submit" class="btn btn-success" data-bs-toggle="tooltip"
                                data-placement="top" title="<?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?>"><i
                                    class="fas fa-search"></i> <?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
                        <button type="button" class="btn btn-danger" data-bs-toggle="tooltip"
                                data-placement="top" title="<?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>"
                                onclick="document.getElementById('filter_search').value='';this.form.submit();"><i
                                    class="fas fa-times"></i> <?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?></button>
                    </div>
                </div>
            </div>

			<?php if (intval($this->state->get('filter.project')) > 0) : ?>
                <div class="row mb-4">
                    <div class="col-md-3 d-flex align-content-stretch mb-2 mb-xs-2 mb-md-0">
                        <div class="card overflow-hidden w-100">
                            <div class="card-icon-floater">
                                <i class="fas fa-clock fa-5x"></i>
                            </div>
                            <div class="card-header bg-light"><h4
                                        class="m-0"><?php echo Text::_('COM_JOOMPROJECT_TIME_TRACKING_TOTAL_HOURS'); ?></h4>
                            </div>
                            <div class="card-body">
                                <h5 class="font-weight-normal">
                				<span>
                					<strong><?php echo $total_hours ?></strong> <?php echo Text::_('COM_JOOMPROJECT_TIME_TRACKING_TOTAL_HOURS'); ?>
                				</span>
                                </h5>
                                <div>
								<span class="text-muted"><?php echo Text::_('COM_JOOMPROJECT_TIME_TRACKING_ESTIMATED'); ?>
                                (<?php echo HTMLHelper::_('time.format', $this->total_estimated_time, 'decimal'); ?>)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 d-flex align-content-stretch mb-2 mb-xs-2 mb-md-0">
                        <div class="card overflow-hidden w-100">
                            <div class="card-icon-floater">
                                <i class="fas fa-briefcase fa-5x"></i>
                            </div>
                            <div class="card-header bg-light"><h4
                                        class="m-0"><?php echo Text::_('COM_JOOMPROJECT_TIME_TRACKING_TOTAL_HOURS'); ?>
                                    : <?php echo JPApplicationHelper::getActiveProjectTitle(); ?></h4></div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-10">
                                        <div>
                                            <h5 class="font-weight-normal"><?php echo Text::_('COM_JOOMPROJECT_TIME_TRACKING_BILLABLE'); ?></h5>
                                            <div class="progress">
                                                <div class="progress-bar progress-bar-striped bg-success " role="progressbar"
                                                     style="width: <?php echo $billable_percent; ?>%"
                                                     aria-valuenow="<?php echo $billable_percent; ?>" aria-valuemin="0"
                                                     aria-valuemax="100">
									                <?php if ($billable_percent) : ?>
										                <?php echo HTMLHelper::_('time.format', $this->total_time_billable, 'decimal'); ?>
									                <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <h5 class="font-weight-normal"><?php echo Text::_('COM_JOOMPROJECT_TIME_TRACKING_UNBILLABLE'); ?></h5>
                                            <div class="progress">
                                                <div class="progress-bar progress-bar-striped bg-info" role="progressbar"
                                                     style="width: <?php echo $unbillable_percent; ?>%"
                                                     aria-valuenow="<?php echo $unbillable_percent; ?>" aria-valuemin="0"
                                                     aria-valuemax="100">
									                <?php if ($unbillable_percent) : ?>
										                <?php echo HTMLHelper::_('time.format', $this->total_time_unbillable, 'decimal'); ?>
									                <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-content-stretch mb-2 mb-xs-2 mb-md-0">
                        <div class="card overflow-hidden w-100">
                            <div class="card-icon-floater">
                                <i class="fas fa-coins fa-5x"></i>
                            </div>
                            <div class="card-header bg-light"><h4
                                        class="m-0"><?php echo Text::_('COM_JOOMPROJECT_TIME_TRACKING_BILLABLE_TOTAL'); ?></h4>
                            </div>
                            <div class="card-body">
                                <h5 class="font-weight-normal">
                				<span>
                					<strong><?php echo HTMLHelper::_('jphtml.format.money', $this->total_billable); ?></strong> <?php echo Text::_('COM_JOOMPROJECT_TIME_TRACKING_BILLABLE_TOTAL'); ?>
                				</span>
                                </h5>
                                <div class="text-muted">
					                <?php echo Text::_('COM_JOOMPROJECT_TIME_TRACKING_ESTIMATED'); ?>
                                    (<?php echo HTMLHelper::_('jphtml.format.money', $this->total_estimated_cost); ?>)
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

			<?php endif; ?>

            <div class="border rounded">
                <table class="m-0 table table-hover">
                    <thead class="bg-light">
                    <tr>
                        <th class="border-0" width="1%"></th>
                        <th width="1%" class="border-0">#</th>
                        <th class="border-0"><?php echo Text::_('JGRID_HEADING_TASK'); ?></th>
                        <th class="border-0" width="13%"><?php echo Text::_('COM_JOOMPROJECT_TIME_TRACKING_TIME'); ?></th>
                        <th width="10%" class="border-0 d-none d-md-table-cell"></th>
                        <th width="10%" class="border-0 d-none d-md-table-cell"><?php echo Text::_('JGRID_HEADING_AUTHOR'); ?></th>
                        <th width="10%" class="border-0 d-none d-md-table-cell"><?php echo Text::_('JGRID_HEADING_DATE'); ?></th>
                        <th width="10%" class="border-0 d-none d-md-table-cell"><?php echo Text::_('COM_JOOMPROJECT_TIME_TRACKING_RATE'); ?></th>
                        <th width="13%" class="border-0"><?php echo Text::_('COM_JOOMPROJECT_TIME_TRACKING_BILLABLE'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
		            <?php
		            $k = 0;
		            foreach ($this->items AS $i => $item) :
			            $access = JPtimeHelper::getActions($item->id);

			            $can_create   = $access->get('core.create');
			            $can_edit     = $access->get('core.edit');
			            $can_change   = $access->get('core.edit.state');
			            $can_edit_own = ($access->get('core.edit.own') && $item->created_by == $uid);

			            if ($item->log_time > 0)
			            {
				            $list_total_time += (int) $item->log_time;
			            }

			            if ((float) $item->billable_total > 0.00)
			            {
				            $list_total_billable += (float) $item->billable_total;
			            }

			            $percentage = ($item->estimate == 0) ? 0 : round($item->log_time * (100 / $item->estimate));

			            if ($percentage > 100)
			            {
				            $percentage = 100;
			            }

			            $percentage_class = '';
			            $percentage_class .= ($item->billable == 1) ? ' bg-success' : ' bg-info';

			            $exists = ((int) $item->task_exists > 0);
			            ?>
                        <tr>
                            <td class="align-middle">
		                        <?php
		                        $this->menu->start(array('class' => 'btn-sm btn-link'));
		                        $this->menu->itemEdit('form', $item->id, ($can_edit || $can_edit_own));
		                        $this->menu->itemTrash('timesheet', $i, $can_change);
		                        $this->menu->end();

		                        echo $this->menu->render(array('class' => 'btn-sm'));
		                        ?>
                            </td>
                            <td class="d-md-table-cell align-middle">
					            <?php echo HTMLHelper::_('jp.html.id', $i, $item->id); ?>
                            </td>
                            <td class="align-middle">
					            <?php if ($exists) : ?>
                                    <a href="<?php echo Route::_(JPtasksHelperRoute::getTaskRoute($item->task_slug, $item->project_slug, $item->milestone_slug, $item->list_slug)); ?>" title="<?php echo $this->escape($item->task_title); ?>"
                                    >
							            <?php echo $this->escape($item->task_title); ?>
                                    </a>

					            <?php else : ?>
						            <?php echo $this->escape($item->task_title); ?>
					            <?php endif; ?>

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
                            <td class="align-middle">
					            <?php echo HTMLHelper::_('time.format', $item->log_time); ?>
                            </td>
                            <td class="d-none d-md-table-cell align-middle">
                                <div class="progress mb-0">
                                    <div class="progress-bar progress-bar-striped <?php echo $percentage_class; ?>" role="progressbar"
                                         style="width: <?php echo $percentage; ?>%;"
                                         aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0"
                                         aria-valuemax="100"></div>
                                </div>
                            </td>
                            <td class="d-none d-md-table-cell align-middle">
					            <?php echo $item->author_name; ?>
                            </td>
                            <td class="d-none d-md-table-cell align-middle">
					            <?php echo HTMLHelper::_('date', $item->log_date, Text::_('DATE_FORMAT_LC4')); ?>
                            </td>
                            <td class="d-none d-md-table-cell align-middle">
					            <?php echo HTMLHelper::_('jphtml.format.money', $item->rate); ?>
                            </td>
                            <td class="d-md-table-cell align-middle">
					            <?php echo HTMLHelper::_('jphtml.format.money', $item->billable_total); ?>
                            </td>
                        </tr>
			            <?php
			            $k = 1 - $k;
		            endforeach;
		            ?>
                    </tbody>
                    <tfoot>
                    <tr class="bg-light">
                        <th colspan="2"><?php echo Text::_('COM_JOOMPROJECT_TIME_TRACKING_TOTALS'); ?></th>

                        <th class=""></th>
                        <th><?php echo HTMLHelper::_('time.format', $list_total_time); ?></th>
                        <th class="d-none d-md-table-cell"></th>
                        <th class="d-none d-md-table-cell"></th>
                        <th class="d-none d-md-table-cell"></th>
                        <th class="d-none d-md-table-cell"></th>
                        <th class="text-success"><?php echo HTMLHelper::_('jphtml.format.money', $list_total_billable); ?></th>
                    </tr>
                    </tfoot>
                </table>
            </div>


	        <?php echo LayoutHelper::render('common.pagination',['pagination' => $this->pagination,'params' => $this->params]) ?>

            <input type="hidden" id="boxchecked" name="boxchecked" value="0"/>
            <input type="hidden" name="task" value=""/>
			<?php echo HTMLHelper::_('form.token'); ?>
        </form>
    </div>
</div>
