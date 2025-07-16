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

HTMLHelper::_('bootstrap.dropdown');
HTMLHelper::_('bootstrap.collapse');

HTMLHelper::_('jphtml.script.listform');
HTMLHelper::script('com_joomproject/joomproject/jquery.PrintArea.js',array('version' => 'auto', 'relative' => true));

$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));

$user       = Factory::getApplication()->getIdentity();
$uid        = $user->get('id');
$dir        = $this->items['directory'];

$doc   = Factory::getDocument();
$style = '.text-large {'
        . 'font-size: 20px;'
        . 'line-height: 24px;'
        . '}'
        . '.text-medium {'
        . 'font-size: 16px;'
        . 'line-height: 22px;'
        . '}'
        . '.margin-none {'
        . 'margin: 0;'
        . '}'
        . '.icon-jpg:before,.icon-png:before,.icon-bmp:before,.icon-psd:before,.icon-tiff:before,.icon-jpeg:before {'
        . 'content: "\2f";'
        . 'color: #468847;'
        . '}'
        . '.icon-mov:before,.icon-swf:before,.icon-flv:before,.icon-mp4:before,.icon-wmv:before {'
        . 'content: "\56";'
        . 'color: #b94a48;'
        . '}'
        . '.icon-pdf:before {'
        . 'margin: 0;'
        . '}'
        . '.item-title {'
        . 'margin-right: 10px;'
        . '}'
        . '.item-count {'
        . 'margin-left: 5px;'
        . '}';
$doc->addStyleDeclaration( $style );
$doc->addScriptDeclaration('
jQuery(document).ready(function()
{
	jQuery("div#print_btn").click(function(){		
		var options = {mode:"popup"};
		jQuery(".PrintArea.all").printArea(options);
	});
});
');
?>
<div id="joomproject" class="category-list<?php echo $this->pageclass_sfx;?> view-repository PrintArea all">

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
        <form name="adminForm" id="adminForm" action="<?php echo Route::_(JPrepoHelperRoute::getRepositoryRoute($dir->project_id, $dir->id)); ?>"
            method="post" autocomplete="off">
            <div class="form-group mb-4">
                <?php echo $this->toolbar;?>
                <div class="filter-project btn-group">
                    <?php echo HTMLHelper::_('jphtml.project.filter');?>
                </div>
				<div class="btn btn-primary btn-sm button b1" id="print_btn"><i class="fas fa-print"></i> <?php echo Text::_('COM_JOOMPROJECT_PRINT'); ?></div>
            </div>

	        <?php echo LayoutHelper::render('repository.filter',['current' => $this]); ?>

            <?php if ($dir->id > 1 && $user->authorise('core.create', 'com_jprepo.directory.' . $dir->id) && !defined('JPDEMO')) :
                echo $this->loadTemplate('upload');
            endif; ?>
            <div class="border rounded mb-3">
                <table class="m-0 table table-hover">
                    <thead class="bg-light">
                    <tr>
                        <th width="1%" class="border-0"></th>
                        <th width="1%" <?php if($dir->id == 1) echo 'style="display:none"'; ?> class="border-0 text-center d-none d-sm-table-cell">
                            <input type="checkbox" name="checkall-toggle" value=""
                                   title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>"
                                   onclick="Joomla.checkAll(this); JPlist.toggleBulkButton();"
                            />
                        </th>
                        <th width="25%" class="border-0">
				            <?php echo HTMLHelper::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $list_dir, $list_order); ?>
                        </th>
                        <th width="6%" class="d-none d-sm-table-cell border-0">

                        </th>
                        <th width="6%" class="d-none d-sm-table-cell border-0">
				            <?php echo Text::_('JGRID_HEADING_TYPE'); ?>
                        </th>
                        <th width="8%" class="d-none d-sm-table-cell border-0">
				            <?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_CREATED_BY', 'a.created_by', $list_dir, $list_order); ?>
                        </th>
                        <th width="8%" class="d-none d-sm-table-cell border-0">
				            <?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_CREATED_ON', 'a.created', $list_dir, $list_order); ?>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
		            <?php echo $this->loadTemplate('directories'); ?>
		            <?php echo $this->loadTemplate('notes'); ?>
		            <?php echo $this->loadTemplate('files'); ?>
                    </tbody>
                </table>
            </div>

	        <?php echo LayoutHelper::render('common.pagination',['pagination' => $this->pagination,'params' => $this->params]) ?>

            <input type="hidden" id="boxchecked" name="boxchecked" value="0" />
            <input type="hidden" name="task" value="" />
            <?php echo HTMLHelper::_('form.token'); ?>
        </form>
    </div>
</div>
