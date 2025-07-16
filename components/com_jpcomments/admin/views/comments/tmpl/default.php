<?php
/**
 * @package      Joomproject
 * @subpackage   Comments
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


$user       = Factory::getApplication()->getIdentity();
$uid        = $user->get('id');
$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));
$archived   = $this->state->get('filter.published') == 2 ? true : false;
$trashed    = $this->state->get('filter.published') == -2 ? true : false;

$filter_project = (int) $this->state->get('filter.project');
$filter_context = (int) $this->state->get('filter.context');

$txt_context = Text::_('JGRID_HEADING_CONTEXT');
$txt_title   = Text::_('JGLOBAL_TITLE');
$date_format = Text::_('DATE_FORMAT_LC4');

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');


    HTMLHelper::_('dropdown.init');
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

<form action="<?php echo Route::_('index.php?option=com_jpcomments&view=comments'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div id="j-sidebar-container" class="col-md-2">
            <?php echo $this->sidebar; ?>
        </div>
        <div id="j-main-container" class="col-md-10">
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
                    <th width="15%" class="nowrap">
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
                    $access  = JPcommentsHelper::getActions($item->id);
                    $context = str_replace('.', '_', strtoupper($item->context)) . '_TITLE';

                    if ($item->level == 0) $item->level = 1;

                    $can_create   = $access->get('core.create');
                    $can_edit     = $access->get('core.edit');
                    $can_checkin  = ($user->authorise('core.manage', 'com_checkin') || $item->checked_out == $uid || $item->checked_out == 0);
                    $can_edit_own = ($access->get('core.edit.own') && $item->created_by == $uid);
                    $can_change   = ($access->get('core.edit.state') && $can_checkin);

                    // Prepare subtitles
                    $subtitles = array();

                    if (!$filter_context) {
                        $subtitles[] = $txt_context . ': ' . Text::_($context);
                    }

                    $subtitles[] = $txt_title . ': ' . $this->escape($item->title);
                    $subtitles   = implode(', ', $subtitles);
                    ?>
                    <tr class="row<?php echo $i % 2; ?>">
                        <td class="d-none d-md-table-cell">
                            <?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
                        </td>
                        <td class="center">
                            <?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'comments.', $can_change, 'cb'); ?>
                        </td>
                        <td class="has-context">
                            <div class="float-start">
                                <?php echo str_repeat('<span class="gi">|&mdash;</span>', $item->level - 1) ?>

                                <?php if ($item->checked_out) : ?>
                                    <?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'comments.', $can_checkin); ?>
                                <?php endif; ?>

                                <?php if ($can_edit || $can_edit_own) : ?>
                                    <a href="<?php echo Route::_('index.php?option=com_jpcomments&task=comment.edit&id=' . $item->id);?>">
                                        <?php echo HTMLHelper::_('string.truncate', $item->description, 80, true, false); ?>
                                    </a>
                                <?php else : ?>
                                    <?php echo HTMLHelper::_('string.truncate', $item->description, 80, true, false); ?>
                                <?php endif; ?>

                                <div class="small">
                                    <?php echo $subtitles; ?>

                                </div>
                            </div>

                        </td>
                        <td class="nowrap">
                            <?php echo $this->escape($item->author_name); ?>
                        </td>
                        <td class="nowrap d-none d-md-table-cell small">
                            <?php echo HTMLHelper::_('date', $item->created, $date_format); ?>
                        </td>
                        <td class="d-none d-md-table-cell small">
                            <?php echo (int) $item->id; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>

            </table>
            <?php  echo $this->pagination->getListFooter();  ?>
        </div>
    </div>
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="task" value="" />
    <?php echo HTMLHelper::_('form.token'); ?>

</form>
