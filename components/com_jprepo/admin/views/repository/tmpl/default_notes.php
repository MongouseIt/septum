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


$user = Factory::getApplication()->getIdentity();
$uid  = $user->get('id');

$this_dir  = $this->items['directory'];
$this_path = (empty($this_dir) ? '' : $this_dir->path);

$filter_search  = $this->state->get('filter.search');
$filter_project = (int) $this->state->get('filter.project');
$is_search      = empty($filter_search) ? false : true;

$txt_revs    = Text::_('COM_JOOMPROJECT_VIEW_REVISIONS');
$txt_icon    = Text::_('COM_JOOMPROJECT_FIELD_NOTE_TITLE');
$date_format = Text::_('DATE_FORMAT_LC4');

foreach ($this->items['notes'] as $i => $item) :
    $edit_link = 'task=note.edit&filter_project=' . $item->project_id . 'filter_parent_id=' . $item->dir_id . '&id=' . $item->id;
    $access    = JPrepoHelper::getActions('note', $item->id);

    $can_create   = $access->get('core.create');
    $can_edit     = $access->get('core.edit');
    $can_checkin  = ($user->authorise('core.manage', 'com_checkin') || $item->checked_out == $uid || $item->checked_out == 0);
    $can_edit_own = ($access->get('core.edit.own') && $item->created_by == $uid);
    $can_change   = ($access->get('core.edit.state') && $can_checkin);
    ?>
    <tr class="row<?php echo $i % 2; ?>">
        <td class="center d-none d-sm-table-cell">
            <?php echo HTMLHelper::_('grid.id', $i, $item->id, false, 'nid'); ?>
        </td>
        <td class="has-context">
            <div class="float-start fltlft">
                <?php if ($item->checked_out) : ?>
                    <?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'repository.', $can_change); ?>
                <?php endif; ?>

                <i class="fas fa-file" data-bs-toggle="tooltip" title="<?php echo $txt_icon;?>"></i>

                <?php if ($can_edit || $can_edit_own) : ?>
                    <a href="<?php echo Route::_('index.php?option=com_jprepo&' . $edit_link);?>">
                        <?php echo Text::_($this->escape($item->title)); ?>
                    </a>
                <?php else : ?>
                    <?php echo Text::_($this->escape($item->title)); ?>
                <?php endif; ?>

                <?php if ($item->revision_count) : ?>
                    <span class="small">[<?php echo $item->revision_count + 1; ?>]</span>
                <?php endif; ?>

                <?php if ($filter_project && $is_search): ?>
                    <div class="small">
                        <?php echo str_replace($this_path, '.', $item->path) . '/'; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="float-start">
                <?php
                // Create dropdown items
                HTMLHelper::_('dropdown.edit', $item->id, 'note.');

                if ($item->revision_count) {
                    $cm_revs = 'index.php?option=com_jprepo&view=noterevisions'
                        . '&filter_project=' . $item->project_id . 'filter_parent_id=' . $item->dir_id . '&id=' . $item->id;
                    HTMLHelper::_('dropdown.addCustomItem', $txt_revs, Route::_($cm_revs));
                }

                // Render dropdown list
                echo HTMLHelper::_('dropdown.render');
                ?>
            </div>

        </td>
        <td>
            <?php echo HTMLHelper::_('jp.html.truncate', $item->description); ?>
        </td>
        <td class="d-none d-sm-table-cell small">
            <?php echo $this->escape($item->access_level); ?>
        </td>
        <td class="d-none d-sm-table-cell small">
            <?php echo $this->escape($item->author_name); ?>
        </td>
        <td class="nowrap d-none d-sm-table-cell small">
            <?php echo HTMLHelper::_('date', $item->created, $date_format); ?>
        </td>

        <td class="d-none d-sm-table-cell small">
            <?php echo (int) $item->id; ?>
        </td>
    </tr>
<?php endforeach; ?>
