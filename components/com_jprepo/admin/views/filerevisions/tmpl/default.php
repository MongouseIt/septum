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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;


$user       = Factory::getApplication()->getIdentity();
$uid        = $user->get('id');
$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));
$project    = (int) $this->state->get('filter.project');

$txt_root    = Text::_('COM_JOOMPROJECT_REV_ROOT');
$txt_head    = Text::_('COM_JOOMPROJECT_REV_HEAD');
$date_format = Text::_('DATE_FORMAT_LC4');

HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('dropdown.init');
HTMLHelper::_('formbehavior.chosen', 'select');

?>
<script type="text/javascript">
Joomla.submitbutton = function(task)
{
    Joomla.submitform(task, document.getElementById('adminForm'));
}
</script>
<form action="<?php echo Route::_('index.php?option=com_jprepo&view=filerevisions'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div id="j-sidebar-container" class="col-md-2">
            <?php echo $this->sidebar; ?>
        </div>
        <div id="j-main-container" class="col-md-10">
            <?php echo $this->loadTemplate('filter'); ?>
    <table class="adminlist table table-striped">
        <thead>
            <tr>
                <th width="1%" class="nowrap">
                    #
                </th>
                <th width="25%">
                    <?php echo Text::_('JGLOBAL_TITLE'); ?>
                </th>
                <th>
                    <?php echo Text::_('JGRID_HEADING_DESCRIPTION'); ?>
                </th>
                <th width="15%" class="d-none d-sm-table-cell">
                    <?php echo Text::_('JAUTHOR'); ?>
                </th>
                <th width="10%" class="d-none d-sm-table-cell">
                    <?php echo Text::_('JDATE'); ?>
                </th>
                <th width="1%" class="d-none d-sm-table-cell">
                    <?php echo Text::_('JGRID_HEADING_ID'); ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($this->items as $i => $item) :
                $rev_id    = '';
                $rev_class = '';
                $icon      = 'icon-checkbox-unchecked';
                $rev_tt    = Text::_('COM_JOOMPROJECT_REV_DESC');

                if (!isset($item->ordering)) {
                    $rev_id    = $txt_head;
                    $rev_class = ' bg-success';
                    $icon      = 'icon-checkbox';
                    $rev_tt    = Text::_('COM_JOOMPROJECT_REV_HEAD_DESC');
                }
                elseif ($item->ordering == 1) {
                    $rev_id    = $txt_root;
                    $rev_class = ' bg-dark';
                    $icon      = 'icon-checkbox-partial';
                    $rev_tt    = Text::_('COM_JOOMPROJECT_REV_ROOT_DESC');
                }
                else {
                    $rev_id = (int) $item->ordering;
                }

                $dl_link = 'index.php?option=com_jprepo&task=file.download'
                         . '&filter_project=' . $item->project_id
                         . '&filter_parent_id=' . $this->item->dir_id
                         . '&id=' . (isset($item->parent_id) ? $item->parent_id . '&rev=' . $item->id : $item->id)
                ?>
                <tr class="row<?php echo $i % 2; ?>">
                    <td class="nowrap">
                        <span class="badge <?php echo $rev_class; ?>" data-bs-toggle="tooltip" title="<?php echo $rev_tt; ?>">
                            <i class="<?php echo $icon; ?>"></i>
                            <?php echo $rev_id; ?>
                        </span>
                    </td>
                    <td>
                        <a href="<?php echo Route::_($dl_link); ?>">
                            <?php echo $this->escape($item->title); ?>
                        </a>
                    </td>
                    <td>
                        <?php echo HTMLHelper::_('jp.html.truncate', $item->description); ?>
                    </td>
                    <td class="d-none d-sm-table-cell small">
                        <?php echo $this->escape($item->author_name); ?>
                    </td>
                    <td class="d-none d-sm-table-cell  small">
                        <?php echo HTMLHelper::_('date', $item->created, $date_format); ?>
                    </td>
                    <td class="d-none d-sm-table-cell  small">
                        <?php echo (int) $item->id; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
        </div>
    </div>

    <input type="hidden" name="filter_order" value="<?php echo $list_order; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $list_dir; ?>" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="filter_project" value="<?php echo (int) $this->item->project_id; ?>" />
    <input type="hidden" name="filter_parent_id" value="<?php echo (int) $this->item->dir_id; ?>" />
    <input type="hidden" name="id" value="<?php echo (int) $this->item->id; ?>" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
<input id="baseUrl" value="<?php echo Uri::base(); ?>" type="hidden">
