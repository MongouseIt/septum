<?php
/**
 * @package      pkg_jpactivities
 * @subpackage   com_jpactivities
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;


HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');

$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));
$can_change = $this->user->authorise('core.edit.state', 'com_jpactivities');

$date_format = $this->params->get('date_format', Text::_('DATE_FORMAT_LC1'));
$date_rel    = (int) $this->params->get('date_relative', 1);
$count = sizeOf($this->items);



?>
<script type="text/javascript">
    Joomla.orderTable = function()
    {
    	table     = document.getElementById("sortTable");
    	direction = document.getElementById("directionTable");
    	order     = table.options[table.selectedIndex].value;

    	if (order != '<?php echo $list_order; ?>') {
    		dirn = 'desc';
    	} else {
    		dirn = direction.options[direction.selectedIndex].value;
    	}

    	Joomla.tableOrdering(order, dirn, '');
    }
</script>
<form
        action="<?php echo Route::_('index.php?option=com_jpactivities&view=activities'); ?>"
        method="post"
        name="adminForm"
        id="adminForm"
>
    <div class="row">
        <div class="col-md-2">
            <?php echo $this->sidebar; ?>
        </div>
        <div class="col-md-10">
            <?php  echo  LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
            <table class="adminlist table table-striped">
                <thead>
                <th width="1%" class="d-none d-md-table-cell">
                    <?php echo HTMLHelper::_('grid.checkall'); ?>
                </th>
                <th width="1%" style="min-width:55px" class="nowrap center">
                    <?php echo Text::_('JSTATUS'); ?>
                </th>
                <th>
                    <?php echo Text::_('COM_JPACTIVITIES_HEADING_ACTIVITY'); ?>
                </th>
                <th width="20%" class="nowrap">
                    <?php echo HTMLHelper::_('searchtools.sort', 'JDATE', 'a.created', $list_dir, $list_order); ?>
                </th>
                <th width="10%" class="d-none d-md-table-cell">
                    <?php echo Text::_('COM_JPACTIVITIES_HEADING_LOCATION'); ?>
                </th>
                <th width="1%" class="nowrap d-none d-md-table-cell">
                    <?php echo Text::_('JGRID_HEADING_ID'); ?>
                </th>
                </thead>
                <tbody>
                <?php
                foreach ($this->items as $i => $item) :
                    $date = HTMLHelper::_('date', $item->created, $date_format);
                    ?>
                    <tr class="row<?php echo $i % 2; ?>">
                        <td class="d-none d-md-table-cell">
                            <?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
                        </td>
                        <td class="center">
                            <?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'activities.', $can_change, 'cb'); ?>
                        </td>
                        <td>
                            <?php echo $item->text; ?>
                        </td>
                        <td class="nowrap">
                            <?php
                            if ($date_rel) :
                                ?>
                                <span class="label hasTip" title="<?php echo $date; ?>" style="cursor: help;">
                                        <?php echo JPactivitiesHelper::relativeDateTime($item->created); ?>
                                    </span>
                            <?php
                            else :
                                ?>
                                <span class="label">
                                        <?php echo $date; ?>
                                    </span>
                            <?php
                            endif;
                            ?>
                        </td>
                        <td class="d-none d-md-table-cell small">
                            <?php echo $item->client; ?>
                        </td>
                        <td class="d-none d-md-table-cell small">
                            <?php echo (int) $item->id; ?>
                        </td>
                    </tr>
                <?php
                endforeach;
                ?>
                </tbody>
            </table>

            <?php echo $this->pagination->getListFooter(); ?>

        </div>
    </div>

    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="filter_order" value="<?php echo $list_order; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $list_dir; ?>" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
