<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jprepo
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
use Joomla\CMS\Uri\Uri;


$user       = Factory::getApplication()->getIdentity();
$uid        = $user->get('id');
$dir        = $this->items['directory'];
$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));
$project    = (int) $this->state->get('filter.project');

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');

    ?>
    <script type="text/javascript">
    Joomla.orderTable = function()
    {
        table     = document.getElementById("sortTable");
        direction = document.getElementById("directionTable");
        order     = table.options[table.selectedIndex].value;

        if (order != '<?php echo $list_order; ?>') {
            dirn = 'asc';
        }
        else {
            dirn = direction.options[direction.selectedIndex].value;
        }

        Joomla.tableOrdering(order, dirn, '');
    }
    </script>

<form action="<?php echo Route::_('index.php?option=com_jprepo&view=repository'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div id="j-sidebar-container" class="col-md-2">
            <?php echo $this->sidebar; ?>
        </div>
        <div id="j-main-container" class="col-md-10">
            <?php   echo  LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
         <?php
    if ($dir->id > 1 && $user->authorise('core.create', 'com_jprepo.directory.' . $dir->id)) :?>
    <div id="joomproject" style="margin-top: 20px">
        <?php echo $this->loadTemplate('upload'); ?>
                </div>
                  <?php  endif; ?>


    <table class="adminlist table table-striped">
        <thead>
            <tr>
                <th width="1%" class="d-none d-md-table-cell">
                    <?php echo HTMLHelper::_('grid.checkall'); ?>
                </th>
                <th width="25%">
                    <?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $list_dir, $list_order); ?>
                </th>
                <th>
                    <?php echo Text::_('JGRID_HEADING_DESCRIPTION'); ?>
                </th>
                <th width="10%" class="d-none d-md-table-cell">
                    <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'access_level', $list_dir, $list_order); ?>
                </th>
                <th width="15%" class="d-none d-md-table-cell">
                    <?php echo HTMLHelper::_('searchtools.sort', 'JAUTHOR', 'author_name', $list_dir, $list_order); ?>
                </th>
                <th width="10%" class="d-none d-md-table-cell">
                    <?php echo HTMLHelper::_('searchtools.sort', 'JDATE', 'a.created', $list_dir, $list_order); ?>
                </th>
                <th width="1%" class="nowrap d-none d-md-table-cell">
                    <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $list_dir, $list_order); ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php echo $this->loadTemplate('directories'); ?>
            <?php echo $this->loadTemplate('notes'); ?>
            <?php echo $this->loadTemplate('files'); ?>
        </tbody>
    </table>

    <?php
    if ($this->pagination) :
        echo $this->pagination->getListFooter();
    endif;
    ?>
        </div>
    </div>
    <?php echo $this->loadTemplate('batch'); ?>

    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="filter_parent_id" value="<?php echo (int) $dir->id; ?>" />
    <?php echo HTMLHelper::_('form.token'); ?>

</form>
<input id="baseUrl" value="<?php echo Uri::base(); ?>" type="hidden">
