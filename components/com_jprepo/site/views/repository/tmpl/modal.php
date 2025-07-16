<?php
/**
 * @package      Joomproject
 * @subpackage   Repository
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2006-2012 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Component\ComponentHelper;
HTMLHelper::_('bootstrap.dropdown');
HTMLHelper::_('bootstrap.collapse');
$function   = Factory::getApplication()->input->getCmd('function', 'jpSelectAttachment');
$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));
$project    = (int) $this->state->get('filter.project');
$user       = Factory::getApplication()->getIdentity();
$uid        = $user->get('id');
$dir        = $this->items['directory'];


$link_append = '&layout=modal&tmpl=component&function=' . $function;
$access      = JPrepoHelper::getActions('directory', $dir->id);

$allowed      = JPrepoHelper::getAllowedFileExtensions();
$config       = ComponentHelper::getParams('com_jprepo');
$filter_admin = $config->get('filter_ext_admin');
$is_admin     = $user->authorise('core.admin');

// Restrict file extensions?
$txt_upload = '';

if ($is_admin && !$filter_admin) $allowed = array();

if (count($allowed))
{
	$txt_upload = Text::_('COM_JOOMPROJECT_UPLOAD_ALLOWED_EXT') . ' ' . implode(', ', $allowed);
}

Factory::getDocument()->addScriptDeclaration("jQuery(document).ready(function($){
        $('table').on('click','a.add-repo-item',function(){
           
            $(this).closest('tr').fadeOut('fast');
        });
    });");

Factory::getDocument()->addStyleDeclaration("body{
        padding: 0px !important;
    }");

?>
<div id="joomproject" class="category-list<?php echo $this->pageclass_sfx; ?> view-repository">

    <div class="repo-items p-3">
        <form name="adminForm" id="adminForm"
              action="<?php echo Route::_(JPrepoHelperRoute::getRepositoryRoute($dir->project_id, $dir->id) . $link_append); ?>"
              method="post"
              enctype="multipart/form-data"
              class="m-0"
        >

            <div class="input-group mb-4">
                <input type="text" class="form-control" name="filter_search"
                       placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>" id="filter_search"
                       value="<?php echo $this->escape($this->state->get('filter.search')); ?>"/>
				<?php if ($project) : ?>
                    <select name="filter_parent_id" class="form-control" onchange="this.form.submit()">
                        <option value=""><?php echo Text::_('JOPTION_SELECT_DIRECTORY'); ?></option>
						<?php echo HTMLHelper::_('select.options', HTMLHelper::_('jprepo.pathOptions', $project), 'value', 'text', $this->state->get('filter.parent_id')); ?>
                    </select>
				<?php endif; ?>

                    <button type="submit" class="btn btn-secondary" data-bs-toggle="tooltip" data-placement="top"
                            title="<?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="fas fa-search"></i>
                    </button>
                    <button type="button" class="btn btn-danger" data-bs-toggle="tooltip" data-placement="top"
                            title="<?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>"
                            onclick="document.getElementById('filter_search').value='';this.form.submit();"><i
                                class="fas fa-times"></i></button>
            </div>

			<?php if ($access->get('core.create') && !defined('JPDEMO')) : ?>
                <fieldset id="upload-form" class="mb-3">
                    <div class="p-1">
                       <div class="row">
                           <div class="col-8">
                               <input type="file" class="form-control " name="jform[file]" id="jform_file"
                                      aria-describedby="jform_file">
                           </div>
                           <div class="col-4">
                               <button type="button" class="btn btn-primary w-100"
                                       onclick="Joomla.submitbutton('fileform.save');"><i
                                           class="fas fa-upload"></i> <?php echo Text::_('JACTION_UPLOAD'); ?></button>

                           </div>
                       </div>

                    </div>
	                <?php if (count($allowed)) : ?>
                        <p class="text-muted m-0 mt-1">
                            <small><?php echo $txt_upload; ?></small>
                        </p>
	                <?php endif; ?>
                    <input type="hidden" name="jform[dir_id]" id="jform_dir_id"
                           value="<?php echo $this->escape((int) $dir->id); ?>"/>
                    <input type="hidden" name="jform[project_id]" id="jform_dir_id"
                           value="<?php echo $this->escape((int) $this->state->get('filter.project')); ?>"/>
                    <input type="hidden" name="jform[access]" id="jform_access"
                           value="<?php echo $this->escape((int) $dir->access); ?>"/>
                </fieldset>
			<?php endif; ?>


            <div class="border rounded">
                <table class="m-0 table table-hover">
                    <thead class="bg-light">
                    <tr>
                        <th width="1%" class="border-0">

                        </th>
                        <th width="40%" class="border-0">
				            <?php echo HTMLHelper::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $list_dir, $list_order); ?>
                        </th>
                        <th class="border-0">
				            <?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_DESCRIPTION', 'a.description', $list_dir, $list_order); ?>
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

            <input type="hidden" name="boxchecked" value="0"/>
            <input type="hidden" name="filter_order" value="<?php echo $list_order; ?>"/>
            <input type="hidden" name="filter_order_Dir" value="<?php echo $list_dir; ?>"/>
            <input type="hidden" name="task" value=""/>
            <input type="hidden" name="layout" value="modal"/>
            <input type="hidden" name="tmpl" value="component"/>
            <input type="hidden" name="function" value="<?php echo $this->escape($function); ?>"/>
			<?php echo HTMLHelper::_('form.token'); ?>
        </form>
    </div>
</div>