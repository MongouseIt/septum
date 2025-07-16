<?php
/**
 * @package      Joomproject Pro
 * @subpackage   Designs
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

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');

$user       = Factory::getApplication()->getIdentity();
$uid        = $user->get('id');
$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));
$save_order = ($list_order == 'a.ordering');


if ($save_order) {
    $order_url = 'index.php?option=com_jpdesigns&task=designs.saveOrderAjax&tmpl=component';
    HTMLHelper::_('sortablelist.sortable', 'designList', 'adminForm', strtolower($list_dir), $order_url);
}

?>
<script type="text/javascript">
    Joomla.orderTable = function() {
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
<form action="<?php echo Route::_('index.php?option=com_jpdesigns&view=designs'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div id="j-sidebar-container" class="col-md-2">
            <?php echo $this->sidebar; ?>
        </div>
        <div id="j-main-container" class="col-md-10">
            <?php
            // Search tools bar
           // echo $this->loadTemplate('filter');

            echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
            ?>

    <table class="adminlist table table-striped" id="designList">
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
                <th width="5%">
                    <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_REVISIONS', 'revision_count', $list_dir, $list_order); ?>
                </th>
                <?php if (!$this->state->get('filter.project')) : ?>
                    <th width="20%" class="d-none d-md-table-cell">
                        <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_PROJECT', 'project_title', $list_dir, $list_order); ?>
                    </th>
                <?php endif; ?>
                <th width="15%" class="d-none d-md-table-cell">
                    <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_DESIGN_ALBUM', 'album_title', $list_dir, $list_order); ?>
                </th>
                <th width="15%" class="d-none d-md-table-cell">
                    <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_CREATED_BY', 'a.created_by', $list_dir, $list_order); ?>
                </th>
                <th width="10%" class="d-none d-md-table-cell">
                    <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_CREATED_ON', 'a.created', $list_dir, $list_order); ?>
                </th>
                <th width="1%" class="nowrap d-none d-md-table-cell">
                    <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $list_dir, $list_order); ?>
                </th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($this->items as $i => $item) :
            $access   = JPdesignsHelper::getActions($item->id);
            $ordering = ($list_order == 'a.ordering');

            $can_create   = $access->get('core.create');
            $can_edit     = $access->get('core.edit');
            $can_checkin  = ($user->authorise('core.manage', 'com_checkin') || $item->checked_out == $uid || $item->checked_out == 0);
            $can_edit_own = ($access->get('core.edit.own') && $item->created_by == $uid);
            $can_change   = ($access->get('core.edit.state') && $can_checkin);

            // Prepare re-order conditions
            $order_up   = false;
            $order_down = false;
            $prev_item  = null;
            $next_item  = null;
            $prev_i     = $i - 1;
            $next_i     = $i + 1;

            if (array_key_exists($prev_i, $this->items)) {
                $prev_item = $this->items[$prev_i];
            }

            if (array_key_exists($next_i, $this->items)) {
                $next_item = $this->items[$next_i];
            }

            if ($prev_item) {
                $order_up = ($item->project_id == $prev_item->project_id && $item->album_id == $prev_item->album_id);
            }

            if ($next_item) {
                $order_down = ($item->project_id == $next_item->project_id && $item->album_id == $next_item->album_id);
            }
            ?>
            <tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->album_id; ?>-<?php echo $item->project_id; ?>">
                <td class="order nowrap center d-none d-md-table-cell">
                    <?php if ($can_change) :
                        $disableClassName = '';
                        $disabledLabel	  = '';

                        if (!$save_order) :
                            $disabledLabel    = Text::_('JORDERINGDISABLED');
                            $disableClassName = 'inactive tip-top';
                        endif; ?>
                        <span class="sortable-handler hasTooltip <?php echo $disableClassName; ?>" title="<?php echo $disabledLabel; ?>">
							<i class="icon-menu"></i>
						</span>
                        <input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order " />
                    <?php else : ?>
                        <span class="sortable-handler inactive" >
							<i class="icon-menu"></i>
						</span>
                    <?php endif; ?>
                </td>
                <td class="center d-none d-md-table-cell">
                    <?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
                </td>
                <td class="center">
                    <?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'designs.', $can_change, 'cb'); ?>
                </td>
                <td>
                    <?php if ($item->checked_out) : ?>
                        <?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'designs.', $can_change); ?>
                    <?php endif; ?>
                    <?php if ($can_edit || $can_edit_own) : ?>
                        <a href="<?php echo Route::_('index.php?option=com_jpdesigns&task=design.edit&id=' . $item->id);?>">
                            <?php echo $this->escape($item->title); ?></a>
                    <?php else : ?>
                        <?php echo $this->escape($item->title); ?>
                    <?php endif; ?>
                </td>
                <td class="center">
                    <?php if ($item->revision_count == 0) : ?>
                        <?php if ($can_create) : ?>
                            <a href="<?php echo Route::_('index.php?option=com_jpdesigns&view=revisions&filter_parent_id=' . $item->id . '&task=revision.add');?>">
                                0
                            </a>
                        <?php else : ?>
                            0
                        <?php endif; ?>
                    <?php else : ?>
                        <a href="<?php echo Route::_('index.php?option=com_jpdesigns&view=revisions&filter_parent_id=' . $item->id);?>">
                            <?php echo (int) $item->revision_count; ?>
                        </a>
                    <?php endif; ?>
                </td>
                <?php if (!$this->state->get('filter.project')) : ?>
                    <td class="d-none d-md-table-cell small"><?php echo $this->escape($item->project_title); ?></td>
                <?php endif; ?>
                <td class="d-none d-md-table-cell small">
                    <?php echo $this->escape($item->album_title); ?>
                </td>
                <td class="d-none d-md-table-cell">
                    <?php echo $this->escape($item->author_name); ?>
                </td>
                <td class="nowrap d-none d-md-table-cell">
                    <?php echo HTMLHelper::_('date', $item->created, Text::_('DATE_FORMAT_LC4')); ?>
                </td>

                <td class="center d-none d-md-table-cell">
                    <?php echo (int) $item->id; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="10">
                    <?php echo $this->pagination->getListFooter(); ?>
                </td>
            </tr>
        </tfoot>
    </table>
        </div>
    </div>

    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="task" value="" />
    <?php echo HTMLHelper::_('form.token'); ?>

    </div>
</form>
