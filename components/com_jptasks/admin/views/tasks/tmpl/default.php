<?php
/**
 * @package      Joomproject
 * @subpackage   Tasks
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
use JoomProject\Version\Joomla;


$user = Factory::getApplication()->getIdentity();
$uid = $user->get('id');
$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir = $this->escape($this->state->get('list.direction'));
$save_order = ($list_order == 'a.ordering');
$archived = $this->state->get('filter.published') == 2 ? true : false;
$trashed = $this->state->get('filter.published') == -2 ? true : false;

$filter_project = (int)$this->state->get('filter.project');
$filter_ms = (int)$this->state->get('filter.milestone');
$filter_tl = (int)$this->state->get('filter.tasklist');

$txt_project = Text::_('JGRID_HEADING_PROJECT');
$txt_ms = Text::_('JGRID_HEADING_MILESTONE');
$txt_tl = Text::_('JGRID_HEADING_TASKLIST');
$txt_notset = Text::_('DATE_NOT_SET');
$txt_norder = Text::_('JORDERINGDISABLED');
$date_format = Text::_('DATE_FORMAT_LC4');

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');


HTMLHelper::_('dropdown.init');
//  HTMLHelper::_('formbehavior.chosen', 'select');

if ($save_order) {
    $order_url = 'index.php?option=com_jptasks&task=tasks.saveOrderAjax&tmpl=component';
    HTMLHelper::_('sortablelist.sortable', 'taskList', 'adminForm', strtolower($list_dir), $order_url, false);
}
?>
<script type="text/javascript">
    Joomla.orderTable = function () {
        table = document.getElementById("sortTable");
        direction = document.getElementById("directionTable");
        order = table.options[table.selectedIndex].value;

        if (order != '<?php echo $list_order; ?>') {
            dirn = 'asc';
        } else {
            dirn = direction.options[direction.selectedIndex].value;
        }

        Joomla.tableOrdering(order, dirn, '');
    }
</script>

<form action="<?php echo Route::_('index.php?option=com_jptasks&view=tasks'); ?>" method="post" name="adminForm"
      id="adminForm">
    <div class="row">
        <div id="j-sidebar-container" class="col-md-2">
            <?php echo $this->sidebar; ?>
        </div>
        <div id="j-main-container" class="col-md-10">
            <?php
            //$this->loadTemplate('filter');
            echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
            ?>
            <table class="adminlist table table-striped" id="taskList">
                <thead>
                <tr>
                    <th width="1%" class="nowrap center d-none d-md-table-cell">
                        <?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $list_dir, $list_order, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-sort'); ?>
                    </th>
                    <th width="1%" class="d-none d-md-table-cell">
                        <?php echo HTMLHelper::_('grid.checkall'); ?>
                    </th>
                    <th width="5%" class="center">
                        <?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $list_dir, $list_order); ?>
                    </th>
                    <th>
                        <?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $list_dir, $list_order); ?>
                    </th>

                    <th width="10%" class="nowrap">
                        <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_DEADLINE', 'a.end_date', $list_dir, $list_order); ?>
                    </th>
                    <th width="10%" class="d-none d-md-table-cell nowrap">
                        <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'access_level', $list_dir, $list_order); ?>
                    </th>
                    <th width="15%" class="nowrap d-none d-md-table-cell">
                        <?php echo HTMLHelper::_('searchtools.sort', 'JAUTHOR', 'author_name', $list_dir, $list_order); ?>
                    </th>
                    <th width="10%" class="nowrap d-none d-md-table-cell">
                        <?php echo HTMLHelper::_('searchtools.sort', 'JDATE', 'a.created', $list_dir, $list_order); ?>
                    </th>
                    <th width="1%" class="d-none d-md-table-cell nowrap">
                        <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $list_dir, $list_order); ?>
                    </th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($this->items as $i => $item) :
                    $access = JPtasksHelper::getActions($item->id);

                    $can_create = $access->get('core.create');
                    $can_edit = $access->get('core.edit');
                    $can_checkin = ($user->authorise('core.manage', 'com_checkin') || $item->checked_out == $uid || $item->checked_out == 0);
                    $can_edit_own = ($access->get('core.edit.own') && $item->created_by == $uid);
                    $can_change = ($access->get('core.edit.state') && $can_checkin);


                    // Sorting group for Joomla 3.0 or higher
                    $sgroup = $item->project_id;

                    if ($item->list_id) {
                        $sgroup .= '-l-' . $item->list_id;
                    } elseif ($item->milestone_id) {
                        $sgroup .= '-m-' . $item->milestone_id;
                    }

                    // Prepare sub title
                    $subtitles = array();

                    if (!$filter_project) {
                        $subtitles[] = $txt_project . ': ' . $this->escape($item->project_title);
                    }
                    if (!$filter_ms && $item->milestone_id) {
                        $subtitles[] = $txt_ms . ': ' . $this->escape($item->milestone_title);
                    }
                    if (!$filter_tl && $item->list_id) {
                        $subtitles[] = $txt_tl . ': ' . $this->escape($item->tasklist_title);
                    }

                    $subtitles = implode(', ', $subtitles);
                    ?>
                    <tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $sgroup; ?>">
                        <td class="order nowrap center d-none d-md-table-cell">
                            <?php
                            if ($can_change) :
                                $r_disable = '';
                                $r_lbl = '';

                                if (!$save_order) :
                                    $r_disable = 'inactive tip-top';
                                    $r_lbl = $txt_norder;
                                endif;
                                ?>
                                <span class="sortable-handler hasTooltip <?php echo $r_disable; ?>"
                                      title="<?php echo $r_lbl; ?>">
    							<i class="icon-menu"></i>
    						</span>
                                <input type="text" style="display:none" name="order[]" size="5"
                                       value="<?php echo $item->ordering; ?>" class="width-20 text-area-order"/>
                            <?php else : ?>
                                <span class="sortable-handler inactive">
    							<i class="icon-menu"></i>
    						</span>
                            <?php endif; ?>
                        </td>
                        <td class="center d-none d-md-table-cell">
                            <?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
                        </td>
                        <td class="center">
                            <?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'tasks.', $can_change, 'cb'); ?>
                        </td>
                        <td class="has-context">
                            <div class="float-start">
                                <?php if ($item->checked_out) : ?>
                                    <?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'tasks.', $can_checkin); ?>
                                <?php endif; ?>

                                <?php if ($can_edit || $can_edit_own) : ?>
                                    <a href="<?php echo Route::_('index.php?option=com_jptasks&task=task.edit&id=' . $item->id); ?>">
                                        <?php echo $this->escape($item->title); ?></a>
                                <?php else : ?>
                                    <?php echo $this->escape($item->title); ?>
                                <?php endif; ?>

                                <div class="small">
                                    <?php echo $subtitles; ?>
                                </div>
                            </div>

                        </td>
                        <td class="nowrap">
                            <?php echo(($item->end_date == $this->nulldate || is_null($item->end_date)) ? $txt_notset : HTMLHelper::_('date', $item->end_date, $date_format)); ?>
                        </td>
                        <td class="d-none d-md-table-cell small">
                            <?php echo $this->escape($item->access_level); ?>
                        </td>
                        <td class="d-none d-md-table-cell small">
                            <?php echo $this->escape($item->author_name); ?>
                        </td>
                        <td class="d-none d-md-table-cell nowrap small">
                            <?php echo HTMLHelper::_('date', $item->created, $date_format); ?>
                        </td>
                        <td class="d-none d-md-table-cell small">
                            <?php echo (int)$item->id; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>

            </table>

            <?php echo $this->pagination->getListFooter(); ?>

        </div>
    </div>

    <?php // Load the batch processing form. ?>
    <?php if ($user->authorise('core.create', 'com_jptasks')
        && $user->authorise('core.edit', 'com_jptasks')
        && $user->authorise('core.edit.state', 'com_jptasks')) : ?>
        <?php if(Joomla::isJoomla5()): ?>
            <template id="joomla-dialog-batch">
                <?php echo $this->loadTemplate('batch_body'); ?>
            </template>
        <?php endif; ?>
    <?php endif; ?>



    <input type="hidden" name="task" value="">
    <input type="hidden" name="boxchecked" value="0">

    <?php echo HTMLHelper::_('form.token'); ?>




</form>
