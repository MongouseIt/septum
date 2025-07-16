<?php
/**
 * @package      Joomproject
 * @subpackage   Repository
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
use Joomla\CMS\Component\ComponentHelper;


HTMLHelper::_('bootstrap.tooltip');

$function   = \Joomla\CMS\Factory::getApplication()->input->getCmd('function', 'jpSelectAttachment');
$user       = Factory::getApplication()->getIdentity();
$uid        = $user->get('id');
$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));
$project    = (int) $this->state->get('filter.project');
$this_dir   = $this->items['directory'];

$link_append = '&layout=modal&tmpl=component&function=' . $function;
$access      = JPrepoHelper::getActions('directory', $this_dir->id);

$allowed      = JPrepoHelper::getAllowedFileExtensions();
$config       = ComponentHelper::getParams('com_jprepo');
$filter_admin = $config->get('filter_ext_admin');
$is_admin     = $user->authorise('core.admin');

// Restrict file extensions?
$txt_upload = '';

if ($is_admin && !$filter_admin) $allowed = array();

if (count($allowed)) {
    $txt_upload = Text::_('COM_JOOMPROJECT_UPLOAD_ALLOWED_EXT') . ' ' . implode(', ', $allowed);
}

Factory::getDocument()->addScriptDeclaration("jQuery(document).ready(function($){
        $('table').on('click','a.add-repo-item',function(){
           
            $(this).closest('tr').fadeOut('fast');
        });
    });");


?>
<form action="<?php echo Route::_('index.php?option=com_jprepo&view=repository' . $link_append); ?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">

    <fieldset id="filter-bar">
        <div class="filter-search btn-toolbar float-start">
            <div class="btn-group float-start">
                <input class="form-control form-control-sm" type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" />
            </div>
            <div class="btn-group float-start ms-1">
                <button type="submit" class="btn btn-outline-success btn-sm"><?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="document.getElementById('filter_search').value='';this.form.submit();"><?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?></button>
            </div>
        </div>
        <div class="filter-select btn-toolbar float-end">
            <div class="btn-group">
                <?php if ($project) : ?>
                    <select name="filter_parent_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value=""><?php echo Text::_('JOPTION_SELECT_DIRECTORY');?></option>
                        <?php echo HTMLHelper::_('select.options', HTMLHelper::_('jprepo.pathOptions', $project), 'value', 'text', $this->state->get('filter.parent_id'));?>
                </select>
                <?php endif; ?>
            </div>
        </div>
    </fieldset>

    <div class="clr clearfix"></div>

    <?php if ($access->get('core.create')) : ?>

        <fieldset id="upload-form" class="border p-2 mt-2">
            <div class="filter-select btn-toolbar float-end">
                <div class="btn-group float-start">
                    <input type="file" class="form-control form-control-sm" name="jform[file]" id="jform_file"/>
                </div>
                <div class="btn-group float-start">
                    <button type="button" class="btn btn-sm btn-outline-success" onclick="Joomla.submitbutton('file.save');"><i class="fas fa-upload"></i> <?php echo Text::_('JACTION_UPLOAD'); ?></button>
                </div>
                <?php if (count($allowed)) : ?>
                    <div class="small">
                        <?php echo $txt_upload; ?>
                    </div>
                <?php endif; ?>
            </div>
            <input type="hidden" name="jform[dir_id]" id="jform_dir_id" value="<?php echo $this->escape((int) $this_dir->id);?>"/>
            <input type="hidden" name="jform[project_id]" id="jform_dir_id" value="<?php echo $this->escape((int) $this->state->get('filter.project'));?>"/>
            <input type="hidden" name="jform[access]" id="jform_access" value="<?php echo $this->escape((int) $this_dir->access);?>"/>
        </fieldset>
    <?php endif; ?>

    <div class="clr clearfix"></div>

    <table class="adminlist table table-striped">
        <thead>
            <tr>
                <th width="1%">

                </th>
                <th width="45%">
                    <?php echo HTMLHelper::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $list_dir, $list_order); ?>
                </th>
                <th>
                    <?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_DESCRIPTION', 'a.description', $list_dir, $list_order); ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php echo $this->loadTemplate('directories'); ?>
            <?php echo $this->loadTemplate('notes'); ?>
            <?php echo $this->loadTemplate('files'); ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">

                </td>
            </tr>
        </tfoot>
    </table>

    <input type="hidden" name="filter_project" value="<?php echo $project; ?>" />
    <input type="hidden" name="filter_order" value="<?php echo $list_order; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $list_dir; ?>" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="layout" value="modal" />
    <input type="hidden" name="tmpl" value="component" />
    <input type="hidden" name="function" value="<?php echo $this->escape($function);?>" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
