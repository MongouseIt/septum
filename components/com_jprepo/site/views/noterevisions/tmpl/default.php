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

HTMLHelper::_('bootstrap.dropdown');
HTMLHelper::_('bootstrap.collapse');
$file = $this->item;
$form_action = JPrepoHelperRoute::getNoteRevisionsRoute($file->slug, $file->project_slug, $file->dir_slug, $file->path);

$user       = Factory::getApplication()->getIdentity();
$uid        = $user->get('id');
$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));
$project    = (int) $this->state->get('filter.project');

$txt_root    = Text::_('COM_JOOMPROJECT_REV_ROOT');
$txt_head    = Text::_('COM_JOOMPROJECT_REV_HEAD');
$date_format = Text::_('DATE_FORMAT_LC4');

$filter_in = ($this->state->get('filter.isset') ? 'in ' : '');
?>
<div id="joomproject" class="category-list<?php echo $this->pageclass_sfx;?> view-repository">

	<?php
	// load internal navigation
	echo JPhtmlNav::loadMain();
	?>

	<?php
	// load header
	echo JPhtmlNav::loadHeader($this->params);
	?>

	<?php
	// load project internal navigation
	echo JPhtmlNav::loadProject();
	?>

    <div class="clearfix"></div>

    <div class="cat-items">
        <form name="adminForm" id="adminForm" action="<?php echo Route::_($form_action); ?>"
            method="post" autocomplete="off">

            <div class="btn-toolbar btn-toolbar-top d-block mb-3">
                <?php echo $this->toolbar; ?>
            </div>

            <div class="<?php echo $filter_in;?>collapse" id="filters">
                <div class="btn-toolbar clearfix">
                    <div class="filter-search btn-group float-start">
                        <input type="text" name="filter_search" placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" />
                    </div>
                    <div class="filter-search-buttons btn-group float-start">
                        <button type="submit" class="btn btn-sm btn-secondary"  data-bs-toggle="tooltip" data-placement="top"  title="<?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="fas fa-search"></i></button>
                        <button type="button" class="btn btn-sm btn-danger"  data-bs-toggle="tooltip" data-placement="top"  title="<?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><i class="fas fa-times"></i></button>
                    </div>
                </div>
            </div>

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
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->items as $i => $item) :
                        $rev_id    = '';
                        $rev_class = '';
                        $icon      = 'fas fa-square';
                        $rev_tt    = Text::_('COM_JOOMPROJECT_REV_DESC');
                        $link      = null;
                        $desc      = '';

                        if (!isset($item->ordering)) {
                            $rev_id    = $txt_head;
                            $rev_class = ' bg-success';
                            $icon      = 'icon-checkbox';
                            $rev_tt    = Text::_('COM_JOOMPROJECT_REV_HEAD_DESC');
                            $link      = JPrepoHelperRoute::getNoteRoute($item->slug, $item->project_slug, $item->dir_slug, $item->path);
                            $desc      = $item->text;
                        }
                        elseif ($item->ordering == 1) {
                            $rev_id    = $txt_root;
                            $rev_class = ' bg-dark';
                            $icon      = 'fas fa-check-square';
                            $rev_tt    = Text::_('COM_JOOMPROJECT_REV_ROOT_DESC');
                            $desc      = $item->description;
                        }
                        else {
                            $rev_id = (int) $item->ordering;
                            $desc   = $item->description;
                        }

                        if (empty($link)) {
                            $link = JPrepoHelperRoute::getNoteRoute($file->slug, $file->project_slug, $file->dir_slug, $file->path, $item->id);
                        }
                        ?>
                        <tr class="row<?php echo $i % 2; ?>">
                            <td class="nowrap">
                                <span class="badge hasTip <?php echo $rev_class; ?>" title="<?php echo $rev_tt; ?>">
                                    <i class="<?php echo $icon; ?>"></i>
                                    <?php echo $rev_id; ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?php echo Route::_($link); ?>">
                                    <?php echo $this->escape($item->title); ?>
                                </a>
                            </td>
                            <td>
                                <?php echo HTMLHelper::_('jp.html.truncate', $desc); ?>
                            </td>
                            <td class="d-none d-sm-table-cell small">
                                <?php echo $this->escape($item->author_name); ?>
                            </td>
                            <td class="d-none d-sm-table-cell small">
                                <?php echo HTMLHelper::_('date', $item->created, $date_format); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                	<tr>
                		<td colspan="5"></td>
                	</tr>
                </tfoot>
            </table>

            <input type="hidden" name="filter_order" value="<?php echo $list_order; ?>" />
            <input type="hidden" name="filter_order_Dir" value="<?php echo $list_dir; ?>" />
            <input type="hidden" name="task" value="" />
            <input type="hidden" name="filter_project" value="<?php echo (int) $this->item->project_id; ?>" />
            <input type="hidden" name="filter_parent_id" value="<?php echo (int) $this->item->dir_id; ?>" />
            <input type="hidden" name="id" value="<?php echo (int) $this->item->id; ?>" />
            <?php echo HTMLHelper::_('form.token'); ?>
        </form>
    </div>

</div>