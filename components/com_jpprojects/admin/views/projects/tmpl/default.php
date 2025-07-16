<?php
/**
 * @package      Joomproject
 * @subpackage   Projects
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


HTMLHelper::_('behavior.multiselect');

$user = Factory::getApplication()->getIdentity();
$uid = $user->get('id');
$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir  = $this->escape($this->state->get('list.direction'));
$saveOrder = $list_order == 'a.id';
if (strpos($list_order, 'start_date') !== false)
{
    $orderingColumn = 'start_date';
}
elseif (strpos($list_order, 'end_date') !== false)
{
    $orderingColumn = 'end_date';
}
elseif (strpos($list_order, 'modified') !== false)
{
    $orderingColumn = 'modified';
}
else
{
    $orderingColumn = 'created';
}


$archived = $this->state->get('filter.published') == 2 ? true : false;
$trashed = $this->state->get('filter.published') == -2 ? true : false;
$filter_cat = $this->state->get('filter.category') > 0 ? true : false;

$txt_notset = Text::_('DATE_NOT_SET');
$txt_cat = Text::_('JCATEGORY');
$txt_nocat = Text::_('COM_JOOMPROJECT_UNCATEGORISED');
$date_format = Text::_('DATE_FORMAT_LC4');

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('dropdown.init');
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

<form action="<?php echo Route::_('index.php?option=com_jpprojects&view=projects'); ?>" method="post" name="adminForm"
      id="adminForm">

    <div class="row">
        <div id="j-sidebar-container" class="col-md-2">
            <?php echo $this->sidebar; ?>
        </div>
        <div id="j-main-container" class="col-md-10">
            <?php
				// Search tools bar
				echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
          ?>
            <table class="adminlist table table-striped mt-3">
                <thead>
                <tr>
                    <th width="1%" class="d-none d-md-table-cell">
                        <input type="checkbox" name="checkall-toggle" value=""
                               title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
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
                    <th width="15%" class="nowrap d-none d-md-table-cell">
                        <?php echo HTMLHelper::_('searchtools.sort', 'JAUTHOR', 'author_name', $list_dir, $list_order); ?>
                    </th>
                    <th width="10%" class="nowrap d-none d-md-table-cell">
                        <?php echo HTMLHelper::_('searchtools.sort', 'JDATE', 'a.created', $list_dir, $list_order); ?>
                    </th>
                    <th width="1%" class="nowrap d-none d-md-table-cell">
                        <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $list_dir, $list_order); ?>
                    </th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($this->items as $i => $item) :
                    $access = JPprojectsHelper::getActions($item->id);

                    $can_create = $access->get('core.create');
                    $can_edit = $access->get('core.edit');
                    $can_checkin = ($user->authorise('core.manage', 'com_checkin') || $item->checked_out == $uid || $item->checked_out == 0);
                    $can_edit_own = ($access->get('core.edit.own') && $item->created_by == $uid);
                    $can_change = ($access->get('core.edit.state') && $can_checkin);
                    ?>
                    <tr class="row<?php echo $i % 2; ?>">
                        <td class="center d-none d-md-table-cell">
                            <?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
                        </td>
                        <td class="center">
                            <?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'projects.', $can_change, 'cb'); ?>
                        </td>
                        <td class="nowrap has-context">
                            <div class="float-start">
                                <?php if ($item->checked_out) : ?>
                                    <?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'projects.', $can_change); ?>
                                <?php endif; ?>

                                <?php if ($can_edit || $can_edit_own) : ?>
                                    <a href="<?php echo Route::_('index.php?option=com_jpprojects&task=project.edit&id=' . $item->id); ?>">
                                        <?php echo $this->escape($item->title); ?></a>
                                <?php else : ?>
                                    <?php echo $this->escape($item->title); ?>
                                <?php endif; ?>

                                <?php if (!$filter_cat) : ?>
                                    <div class="small">
                                        <?php echo $txt_cat . ': ' . ($item->category_title ? $this->escape($item->category_title) : $txt_nocat); ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                        </td>
                        <td class="nowrap">

                            <?php echo(($item->end_date == $this->nulldate) || is_null($item->end_date) ? $txt_notset : HTMLHelper::_('date', $item->end_date, $date_format)); ?>
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
    <?php
    if (
        $user->authorise('core.create', 'com_jpprojects')
        && $user->authorise('core.edit', 'com_jpprojects')
        && $user->authorise('core.edit.state', 'com_jpprojects')
    ) : ?>
        <?php if(Joomla::isJoomla5()): ?>
            <template id="joomla-dialog-batch"><?php echo $this->loadTemplate('batch_body'); ?></template>
        <?php endif; ?>
    <?php endif; ?>


    <input type="hidden" name="task" value="">
    <input type="hidden" name="boxchecked" value="0">
    <?php echo HTMLHelper::_('form.token'); ?>

</form>
