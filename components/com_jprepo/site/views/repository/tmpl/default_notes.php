<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jprepo
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

$user     = Factory::getApplication()->getIdentity();
$uid      = $user->get('id');
$x        = count($this->items['directories']);

$this_dir  = $this->items['directory'];
$this_path = (empty($this_dir) ? '' : $this_dir->path);

$filter_search  = $this->state->get('filter.search');
$filter_project = (int) $this->state->get('filter.project');
$is_search      = empty($filter_search) ? false : true;


foreach ($this->items['notes'] as $i => $item) :
    $link   = JPrepoHelperRoute::getNoteRoute($item->slug, $item->project_slug, $item->dir_slug, $item->path);
    $access = JPrepoHelper::getActions('note', $item->id);

    $can_create   = $access->get('core.create');
    $can_edit     = $access->get('core.edit');
    $can_checkin  = ($user->authorise('core.manage', 'com_checkin') || $item->checked_out == $uid || $item->checked_out == 0);
    $can_edit_own = ($access->get('core.edit.own') && $item->created_by == $uid);
    $can_change   = ($access->get('core.edit.state') && $can_checkin);
    $date_opts    = array('past-class' => '', 'past-icon' => 'calendar');
    ?>
    <tr class="row<?php echo $i % 2; ?>">
        <td><?php
	        $this->menu->start(array('class' => 'btn-sm btn-link'));
	        $this->menu->itemEdit('noteform', $item->id, ($can_edit || $can_edit_own));

	        if ($item->revision_count) {
		        $link_revs = JPrepoHelperRoute::getNoteRevisionsRoute($item->slug, $item->project_slug, $item->dir_slug, $item->path);

		        $this->menu->itemLink('fas fa-flag', 'COM_JOOMPROJECT_VIEW_REVISIONS', Route::_($link_revs));
	        }

	        $this->menu->itemDelete('repository', $x, ($can_edit || $can_edit_own));
	        $this->menu->end();

	        echo $this->menu->render(array('class' => 'btn-sm'));
	        ?></td>
        <?php if ($this_dir->parent_id >= 1) : ?>
        <td class="text-center d-none d-sm-table-cell">
            <label for="cb<?php echo $x; ?>" class="checkbox p-0">
		        <?php echo HTMLHelper::_('jp.html.id', $x, $item->id, false, 'nid',"mt-2 me-0"); ?>
            </label>
        </td>
        <?php endif; ?>
        <td>
        	<span class="item-title">
	            <?php if ($item->checked_out) : ?><span aria-hidden="true" class="text-danger fas fa-lock me-1"></span> <?php endif; ?>
	            <a href="<?php echo Route::_($link);?>"  data-bs-toggle="popover"  title="<?php echo Text::_($this->escape($item->title)); ?>" data-content="<?php echo $this->escape($item->description); ?>" data-placement="right">
	            	<span aria-hidden="true" class="far fa-edit text-muted me-2"></span>
	                <?php echo Text::_($this->escape($item->title)); ?>
	            </a>

                <?php if ($item->revision_count) : ?>
                    <span class="item-count badge bg-info"><?php echo $item->revision_count; ?></span>
                <?php endif; ?>
        	</span>
			<?php if ($item->label_count) : echo HTMLHelper::_('jphtml.label.labels', $item->labels); endif; ?>
            <?php if ($filter_project && $is_search): ?>
                <div class="small">
                    <?php echo str_replace($this_path, '.', $item->path) . '/'; ?>
                </div>
            <?php endif; ?>
        </td>
        <td  class="d-none d-sm-table-cell"></td>
        <td class="d-none d-sm-table-cell">
        	<?php echo Text::_('JGRID_HEADING_NOTE'); ?>
        </td>

        <td class="d-none d-sm-table-cell">
            <?php echo $item->author_name; ?>
        </td>
        <td class="d-none d-sm-table-cell">
            <?php echo HTMLHelper::_('date', $item->created, Text::_('M d')); ?>
        </td>
    </tr>
<?php $x++; endforeach; ?>
