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


$user      = Factory::getApplication()->getIdentity();
$uid       = $user->get('id');
$this_dir  = $this->items['directory'];
$this_path = (empty($this_dir) ? '' : $this_dir->path);

$filter_search  = $this->state->get('filter.search');
$filter_project = (int) $this->state->get('filter.project');
$count_elements = (int) $this->state->get('list.count_elements');
$is_search      = empty($filter_search) ? false : true;

$txt_icon    = Text::_('COM_JOOMPROJECT_FIELD_DIRECTORY_TITLE');
$txt_edit    = Text::_('JACTION_EDIT');
$txt_delete  = Text::_('JACTION_DELETE');
$date_format = Text::_('DATE_FORMAT_LC4');

if ($this_dir->parent_id > 1) : ?>
    <tr class="row1">
        <td class="center"></td>
        <td colspan="6">
            <a href="<?php echo Route::_('index.php?option=com_jprepo&view=repository&filter_parent_id=' . $this_dir->parent_id);?>">
                ..
            </a>
        </td>
    </tr>
<?php
endif;

foreach ($this->items['directories'] as $i => $item) :
    $edit_link = 'task=directory.edit&filter_project=' . $item->project_id . 'filter_parent_id=' . $item->parent_id . '&id=' . $item->id;
    $access    = JPrepoHelper::getActions('directory', $item->id);

    $can_create   = $access->get('core.create');
    $can_edit     = $access->get('core.edit');
    $can_checkin  = ($user->authorise('core.manage', 'com_checkin') || $item->checked_out == $uid || $item->checked_out == 0);
    $can_edit_own = ($access->get('core.edit.own') && $item->created_by == $uid);
    $can_change   = ($access->get('core.edit.state') && $can_checkin);
    $can_delete   = ($access->get('core.delete') && ($item->orphaned || $item->parent_id > 1));

    // Set folder icon
    $icon = 'icon-folder';

    if ($item->orphaned) {
        $icon = 'icon-warning';
    }
    elseif ($item->parent_id == 1) {
        $icon = 'icon-folder-2';
    }
    elseif ($item->protected) {
        $icon = 'icon-locked';
    }

    $nav_link = Route::_('index.php?option=com_jprepo&view=repository&filter_parent_id=' . $item->id);

    if (!$filter_project) {
        $nav_link .= '&filter_project=' . $item->project_id;
    }
    ?>
    <tr class="row<?php echo $i % 2; ?>">
        <td class="center d-none d-md-table-cell">
            <?php echo HTMLHelper::_('grid.id', $i, $item->id, false, 'did'); ?>
        </td>
        <td class="has-context">
            <div class="">



                <div class>
                    <?php if ($item->checked_out) : ?>
                        <?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'repository.', $can_change); ?>
                    <?php endif; ?>

                    <i class="<?php echo $icon; ?>" data-bs-toggle="tooltip" title="<?php echo $txt_icon;?>"></i>
                    <a href="<?php echo $nav_link;?>">
                        <?php echo Text::_($this->escape($item->title)); ?>
                    </a>

                    <?php if ($count_elements && $item->element_count) : ?>
                        <span class="small">[<?php echo $item->element_count; ?>]</span>
                    <?php endif; ?>

                    <?php if ($filter_project && $is_search): ?>
                        <div class="small">
                            <?php echo str_replace($this_path, '.', $item->path) . '/'; ?>
                        </div>
                    <?php endif; ?>

                    <a data-bs-togglle="tooltip" href="<?php echo Route::_('index.php?option=com_jprepo&' . $edit_link);?>" title="  <?php echo $txt_edit; ?>">
                        <i class="icon-edit"></i>
                    </a>

                </div>



            </div>



        </td>
        <td>
            <?php echo HTMLHelper::_('jp.html.truncate', (string) $item->description); ?>
        </td>
        <td class="d-none d-sm-table-cell small">
            <?php echo $this->escape($item->access_level); ?>
        </td>
        <td class="d-sm-table-cell small">
            <?php echo $this->escape($item->author_name); ?>
        </td>
        <td class="nowrap d-sm-table-cell small">
            <?php echo HTMLHelper::_('date', $item->created, $date_format); ?>
        </td>
        <td class="d-sm-table-cell small">
            <?php echo (int) $item->id; ?>
        </td>
    </tr>
<?php endforeach; ?>
