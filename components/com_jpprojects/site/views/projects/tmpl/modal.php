<?php
/**
 * @package      Joomproject
 * @subpackage   Projects
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2006-2012 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\Helpers\StringHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Layout\FileLayout;


$function   = Factory::getApplication()->input->getCmd('function', 'jpSelectActiveProject');
$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));
$user       = Factory::getApplication()->getIdentity();
$uid        = $user->get('id');
?>
<div id="joomproject" class="category-list<?php echo $this->pageclass_sfx;?> view-projects">

    <div class="cat-items">

        <form name="adminForm" id="adminForm" action="<?php echo Route::_(JPprojectsHelperRoute::getProjectsRoute() . '&layout=modal&tmpl=component&function=' . $function);?>" method="post">

            <div class="filters input-group mb-4">
                <input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" />
                <?php if ($this->access->get('core.edit.state') || $this->access->get('core.edit')) : ?>
                        <select id="filter_published" name="filter_published" class="form-control" onchange="this.form.submit()">
                            <option value=""><?php echo Text::_('JOPTION_SELECT_PUBLISHED');?></option>
                            <?php echo HTMLHelper::_('select.options', HTMLHelper::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
                        </select>

                <?php endif; ?>
                    <select name="filter_category" class="form-control" onchange="this.form.submit()">
                        <option value=""><?php echo Text::_('JOPTION_SELECT_CATEGORY');?></option>
                        <?php echo HTMLHelper::_('select.options', HTMLHelper::_('category.options', 'com_jpprojects'), 'value', 'text', $this->state->get('filter.category'));?>
                    </select>
                <div class="filter-search">
                    <button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i> <?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
                    <button type="button" class="btn btn-danger" onclick="document.getElementById('filter_search').value='';this.form.submit();"><i class="fas fa-times"></i> <?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?></button>
                </div>
            </div>
            <div class="filter-limit text-center mb-4">
                    <?php echo $this->pagination->getLimitBox(); ?>
                </div>

            <table class="category table table-striped">
                <thead>
                    <tr>
                        <th id="tableOrdering0" class="list-title">
                            <?php echo HTMLHelper::_('grid.sort', 'JGLOBAL_TITLE', 'category_title, a.title', $list_dir, $list_order); ?>
                        </th>
                        <th id="tableOrdering1" class="list-category">
                            <?php echo HTMLHelper::_('grid.sort', 'JCATEGORY', 'category_title', $list_dir, $list_order); ?>
                        </th>
                        <th id="tableOrdering2" class="list-milestones">
                            <?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_MILESTONES', 'milestones', $list_dir, $list_order); ?>
                        </th>
                        <th id="tableOrdering3" class="list-tasks">
                            <?php echo HTMLHelper::_('grid.sort', Text::sprintf('JGRID_HEADING_TASKLISTS_AND_TASKS', '', ''), 'tasks', $list_dir, $list_order); ?>
                        </th>
                    </tr>
               </thead>
               <tbody>
                    <?php
                    $k = 0;
                    foreach($this->items AS $i => $item) :
                    ?>
                        <tr class="cat-list-row<?php echo $k;?>">
                            <td class="list-title">
                                <a class="pointer" style="cursor: pointer;" onclick="if (window.parent) window.parent.<?php echo $this->escape($function);?>('<?php echo $item->id; ?>', '<?php echo $this->escape(addslashes($item->title)); ?>');">
                                    <?php echo $this->escape($item->title);?>
                                </a>
                            </td>
                            <td class="list-categories">
                                <?php echo $this->escape($item->category_title);?>
                            </td>
                            <td class="list-milestones">
                                <i class="fas fa-map-marker"></i> <?php echo (int) $item->milestones;?>
                            </td>
                            <td class="list-tasks">
                                <i class="fas fa-check"></i> <?php echo intval($item->tasklists) . ' / ' . intval($item->tasks);?>
                            </td>
                        </tr>
                    <?php
                    $k = 1 - $k;
                    endforeach;
                    ?>
                </tbody>
            </table>

            <?php if ($this->pagination->pagesTotal > 1) : ?>
                <div class="pagination">
                    <p class="counter"><?php echo $this->pagination->getPagesCounter(); ?></p>
                    <?php echo $this->pagination->getPagesLinks(); ?>
                </div>
            <?php endif; ?>

            <input type="hidden" name="filter_order" value="<?php echo $list_order; ?>" />
            <input type="hidden" name="filter_order_Dir" value="<?php echo $list_dir; ?>" />
            <input type="hidden" name="task" value="" />
            <?php echo HTMLHelper::_('form.token'); ?>
        </form>
    </div>
</div>
