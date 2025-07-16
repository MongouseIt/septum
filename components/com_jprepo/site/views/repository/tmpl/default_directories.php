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
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;


$user      = Factory::getApplication()->getIdentity();
$uid       = $user->get('id');
$this_dir  = $this->items['directory'];
$this_path = (empty($this_dir) ? '' : $this_dir->path);

$filter_search  = $this->state->get('filter.search');
$filter_project = (int) $this->state->get('filter.project');
$count_elements = (int) $this->state->get('list.count_elements');
$is_search      = empty($filter_search) ? false : true;

$dispatcher = \Joomla\CMS\Factory::getApplication();




if ($this_dir->parent_id > 1) : ?>
    <tr class="row1">
        <td class="center"></td>
        <td colspan="6">

            <a class="btn btn-light btn-sm bg-light text-dark" href="<?php echo Route::_(JPrepoHelperRoute::getRepositoryRoute($this_dir->project_id, $this_dir->parent_id, $this_dir->path));?>">
                <span aria-hidden="true" class="fas fa-arrow-left"></span> <?php echo Text::_('JPREVIOUS'); ?>
            </a>
        </td>
    </tr>
<?php endif; ?>
<?php
foreach ($this->items['directories'] as $i => $item) :
    $dispatcher->triggerEvent('onContentPrepare', array('com_jprepo.directory', &$item, &$this->params, 0));
    $access = JPrepoHelper::getActions('directory', $item->id);

    // Set folder icon
    $icon = 'far fa-folder text-muted me-2';

    if ($item->orphaned) {
        $icon = 'fas fa-exclamation-triangle me-2';
    }
    elseif ($item->parent_id == 1) {
        $icon = 'far fa-folder-open text-muted me-2';
    }
    elseif ($item->protected) {
        $icon = 'text-danger fas fa-lock me-1';
    }

    // Prepare the watch button
    $watch = '';

    if ($uid) {
        $options = array('a-class' => 'btn-sm float-end');
        $watch = HTMLHelper::_('jphtml.button.watch', 'repository', $i, $item->watching, $options);
    }

    $can_create   = $access->get('core.create');
    $can_edit     = $access->get('core.edit');
    $can_checkin  = ($user->authorise('core.manage', 'com_checkin') || $item->checked_out == $uid || $item->checked_out == 0);
    $can_edit_own = ($access->get('core.edit.own') && $item->created_by == $uid);
    $can_change   = ($access->get('core.edit.state') && $can_checkin);
    $can_delete   = ($access->get('core.delete') && ($item->orphaned || $item->parent_id > 1));
    $date_opts    = array('past-class' => '', 'past-icon' => 'calendar');

    ?>
    <tr class="row<?php echo $i % 2; ?>">
        <td><?php
	        $this->menu->start(array('class' => 'btn-sm btn-link'));
	        $this->menu->itemEdit('directoryform', $item->id, ($can_edit || $can_edit_own));
	        $this->menu->itemDelete('repository', $i, $can_delete);
	        $this->menu->end();

	        echo $this->menu->render(array('class' => 'btn-sm'));
	        ?></td>
        <td <?php if($this_dir->id == 1) echo 'style="display:none"'; ?> class="text-center d-none d-sm-table-cell">
            <label for="cb<?php echo $i; ?>" class="checkbox p-0">
                <?php echo HTMLHelper::_('jp.html.id', $i, $item->id, false, 'did','mt-2 me-0'); ?>
            </label>
        </td>
        <td>
            <?php if ($item->checked_out) : ?><span aria-hidden="true" class="fas fa-lock"></span> <?php endif; ?>

            <span class="item-title">
            	<?php if (!$item->orphaned) : ?>
	                <a  href="<?php echo Route::_(JPrepoHelperRoute::getRepositoryRoute($item->project_slug, $item->slug, $item->path));?>"
                        data-html='true'
                       data-bs-toggle="popover"
                       data-title="<?php echo Text::_($this->escape($item->title)); ?>"
                       data-content='<?php echo LayoutHelper::render(
                               'repository.popovercontent',
                               ['this' => $this, 'item' => $item],
                               JPATH_ADMINISTRATOR.'/components/com_joomproject/layouts'
                       ) ?>'
                       data-placement="right"
                       data-sanitize='false'
                    >
	                	<span aria-hidden="true" class="<?php echo $icon;?>"></span>
	                    <?php echo Text::_($this->escape($item->title)); ?>
	                </a>

	            <?php else : ?>
	                <span class=""  data-bs-toggle="tooltip" data-placement="top"  title="<?php echo Text::_('COM_JOOMPROJECT_ORPHANED_REPO'); ?>" style="cursor: help;">
	                    <span aria-hidden="true" class="<?php echo $icon;?>"></span>
                        <?php echo Text::_($this->escape($item->title)); ?>
	                </span>
	            <?php endif; ?>

	            <?php if ($count_elements && $item->element_count) : ?>
                    <span class="item-count badge bg-info"><?php echo $item->element_count; ?></span>
                <?php endif; ?>
            </span>
			
			<?php if ($item->label_count) : echo HTMLHelper::_('jphtml.label.labels', $item->labels); endif; ?>
			
            <?php if ($filter_project && $is_search): ?>
                <div class="small">
                    <?php echo str_replace($this_path, '.', $item->path) . '/'; ?>
                </div>
            <?php endif; ?>
            <?php echo $watch; ?>
        </td>
        <td class="d-none d-sm-table-cell">
	        <?php if ($item->access != 1) : ?>
		        <?php echo HTMLHelper::_('jphtml.label.access', $item->access); ?>
	        <?php endif; ?>
        </td>
        <td class="d-none d-sm-table-cell align-middle">
        	<?php echo Text::_('JGRID_HEADING_DIRECTORY'); ?>
        </td>
        <td class="d-none d-sm-table-cell align-middle">
            <?php echo $item->author_name; ?>
        </td>
        <td  class="d-none d-sm-table-cell">
            <?php echo HTMLHelper::_('date', $item->created, Text::_('M d')); ?>
        </td>
    </tr>
<?php endforeach; ?>
