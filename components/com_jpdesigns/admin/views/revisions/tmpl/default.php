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

<form action="<?php echo Route::_('index.php?option=com_jpdesigns&view=revisions&filter_parent_id=' . $this->parent_id); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div class="col-md-2">
            <?php echo $this->sidebar; ?>
        </div>
        <div class="col-md-10">
            <?php   echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
    <table class="adminlist table table-striped">
        <thead>
            <tr>
                <th width="1%" class="d-none d-md-table-cell">
                    <?php echo HTMLHelper::_('grid.checkall'); ?>
                </th>
                <th width="5%" class="center">
                    <?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $list_dir, $list_order); ?>
                </th>
                <th>
                    <?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $list_dir, $list_order); ?>
                </th>
                <th width="15%" class="d-none d-md-table-cell">
                    <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_CREATED_BY', 'a.created_by', $list_dir, $list_order); ?>
                </th>
                <th width="10%" class="d-none d-md-table-cell">
                    <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_CREATED_ON', 'a.created', $list_dir, $list_order); ?>
                </th>
                <th width="10%" class="nowrap center">
                    <?php echo HTMLHelper::_('searchtools.sort',  'JGRID_HEADING_REVISION', 'a.ordering', $list_dir, $list_order); ?>
                </th>
                <th width="1%" class="nowrap d-none d-md-table-cell">
                    <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $list_dir, $list_order); ?>
                </th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($this->items as $i => $item) :
            $access   = JPdesignsHelper::getActions($item->id);

            $can_create   = $access->get('core.create');
            $can_edit     = $access->get('core.edit');
            $can_checkin  = ($user->authorise('core.manage', 'com_checkin') || $item->checked_out == $uid || $item->checked_out == 0);
            $can_edit_own = ($access->get('core.edit.own') && $item->created_by == $uid);
            $can_change   = ($access->get('core.edit.state') && $can_checkin);
            ?>
            <tr class="row<?php echo $i % 2; ?>">
                <td class="center d-none d-md-table-cell">
                    <?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
                </td>
                <td class="center">
                    <?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'revisions.', $can_change, 'cb'); ?>
                </td>
                <td>
                    <?php if ($item->checked_out) : ?>
                        <?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'revisions.', $can_change); ?>
                    <?php endif; ?>
                    <?php if ($can_edit || $can_edit_own) : ?>
                        <a href="<?php echo Route::_('index.php?option=com_jpdesigns&task=revision.edit&id=' . $item->id);?>">
                            <?php echo $this->escape($item->title); ?></a>
                    <?php else : ?>
                        <?php echo $this->escape($item->title); ?>
                    <?php endif; ?>
                </td>
                <td class="d-none d-md-table-cell">
                    <?php echo $this->escape($item->author_name); ?>
                </td>
                <td class="nowrap d-none d-md-table-cell">
                    <?php echo HTMLHelper::_('date', $item->created, Text::_('DATE_FORMAT_LC4')); ?>
                </td>
                <td class="order center">
                    <?php echo $item->ordering; ?>
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
    <input type="hidden" name="filter_order" value="<?php echo $list_order; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $list_dir; ?>" />
    <input type="hidden" name="filter_parent_id" value="<?php echo $this->parent_id; ?>" />
    <input type="hidden" name="task" value="" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
