<?php
/**
 * @package      Joomproject Pro
 * @subpackage   Designs
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


HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');

$user   = Factory::getApplication()->getIdentity();
$uid    = $user->get('id');
$path   = JPdesignsHelper::getBasePath($this->state->get('filter.project'));
$path   = str_replace(JPATH_ROOT, '', $path);

HTMLHelper::_('dropdown.init');
    //HTMLHelper::_('formbehavior.chosen', 'select');

?>
<form action="<?php echo Route::_('index.php?option=com_jpdesigns&view=import'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div id="j-sidebar-container" class="col-md-2">
            <?php echo $this->sidebar; ?>
        </div>
        <div id="j-main-container" class="col-md-10">
            <?php echo $this->loadTemplate('filter'); ?>
            <table class="adminlist table table-striped mt-4">
                <thead>
                <tr>
                    <th width="1%" class="d-none d-md-table-cell">
                        <input type="checkbox" name="checkall-toggle" value="" title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
                    </th>
                    <th width="15%">
                        <?php echo Text::_('JGLOBAL_TITLE'); ?>
                    </th>
                    <th width="15%">
                        <?php echo Text::_('JGRID_HEADING_DESIGN'); ?>
                    </th>
                    <th width="15%">
                        <?php echo Text::_('JGRID_HEADING_DESIGN_ALBUM'); ?>
                    </th>
                    <th>
                        <?php echo Text::_('JGRID_HEADING_FILE_NAME'); ?>
                    </th>
                    <th width="8%" class="d-none d-md-table-cell">
                        <?php echo Text::_('JGRID_HEADING_FILE_SIZE'); ?>
                    </th>
                    <th width="8%" class="d-none d-md-table-cell">
                        <?php echo Text::_('JGRID_HEADING_SIZE'); ?>
                    </th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($this->items as $i => $item) :
                    ?>
                    <tr class="row<?php echo $i % 2; ?>">
                        <td class="center d-none d-md-table-cell">
                            <?php echo HTMLHelper::_('grid.id', $i, $i); ?>
                        </td>
                        <td>
                            <input type="text" size="40" maxlength="128" name="import[<?php echo $i;?>][title]" value=""/>
                            <input type="hidden" name="import[<?php echo $i;?>][source]" value="<?php echo $this->escape($item->file_source); ?>"/>
                            <input type="hidden" name="import[<?php echo $i;?>][project_id]" value="<?php echo $this->escape((int) $item->project_id); ?>"/>
                        </td>
                        <td>
                            <?php if ($item->parent_id || $item->album_id) : ?>
                                <?php echo $this->escape($item->design_title); ?>
                                <input type="hidden" name="import[<?php echo $i;?>][parent_id]" value="<?php echo $this->escape((int) $item->parent_id); ?>"/>
                            <?php else : ?>
                                <select name="import[<?php echo $i;?>][parent_id]">
                                    <option value="0">New</option>
                                    <?php echo HTMLHelper::_('select.options', $this->designs, 'value', 'text', 0); ?>
                                </select>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($item->parent_id || $item->album_id) : ?>
                                <?php echo $this->escape($item->album_title); ?>
                                <input type="hidden" name="import[<?php echo $i;?>][album_id]" value="<?php echo $this->escape((int) $item->album_id); ?>"/>
                            <?php else : ?>
                                <select name="import[<?php echo $i;?>][catid]">
                                    <option value="0">Uncategorised</option>
                                    <?php echo HTMLHelper::_('select.options', $this->albums, 'value', 'text', 0); ?>
                                </select>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo $this->escape($item->title); ?>
                            <input type="hidden" name="import[<?php echo $i;?>][file_name]" value="<?php echo $item->title; ?>"/>
                        </td>
                        <td class="d-none d-md-table-cell">
                            <?php echo $this->escape($item->file_size); ?>kb
                        </td>
                        <td class="d-none d-md-table-cell">
                            <?php echo $this->escape($item->size); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php if ($this->state->get('filter.project')) : ?>
                <div class="alert alert-info" role="alert">
                    <p><?php echo Text::sprintf('COM_JOOMPROJECT_FIELD_IMPORT_INFO_1', $path . '/_import'); ?></p>
                    <p><?php echo Text::sprintf('COM_JOOMPROJECT_FIELD_IMPORT_INFO_2', $path . '/_import/album_<span style="color:red">N</span>'); ?></p>
                    <p><?php echo Text::sprintf('COM_JOOMPROJECT_FIELD_IMPORT_INFO_3', $path . '/_import/design_<span style="color:red">N</span>'); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>


    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="task" value="" />
    <?php echo HTMLHelper::_('form.token'); ?>


</form>
