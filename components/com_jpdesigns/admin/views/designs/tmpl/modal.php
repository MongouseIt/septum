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

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

require_once JPATH_ROOT . '/components/com_jpdesigns/helpers/route.php';

$app        = Factory::getApplication();
$function   = $app->input->getCmd('function', 'jSelectDesign');
$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));
$action     = 'index.php?option=com_jpdesigns&view=designs&layout=modal&tmpl=component&function=' . $function . '&' . Session::getFormToken() . '=1';
?>
<form action="<?php echo Route::_($action); ?>" method="post" name="adminForm" id="adminForm">

    <?php echo $this->loadTemplate('filter'); ?>

    <table class="table table-striped table-condensed">
        <thead>
            <tr>
                <th>
                    <?php echo HTMLHelper::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $list_dir, $list_order); ?>
                </th>
                <?php if (!$this->state->get('filter.project')) : ?>
                    <th width="20%">
                        <?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_PROJECT', 'project_title', $list_dir, $list_order); ?>
                    </th>
                <?php endif; ?>
                <th width="15%">
                    <?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_DESIGN_ALBUM', 'album_title', $list_dir, $list_order); ?>
                </th>
                <th width="15%">
                    <?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_CREATED_BY', 'a.created_by', $list_dir, $list_order); ?>
                </th>
                <th width="1%" class="nowrap">
                    <?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $list_dir, $list_order); ?>
                </th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($this->items as $i => $item) :
            $onclick = "if (window.parent) window.parent." . $this->escape($function)
                     . "('" . $item->id . "', '" . $this->escape(addslashes($item->title)) . "', null, "
                     . "'" . $this->escape(JPdesignsHelperRoute::getDesignRoute($item->id, $item->project_id, $item->album_id)) . "', "
                     . "'', null);";
            ?>
            <tr class="row<?php echo $i % 2; ?>">
                <td>
                    <a class="pointer" style="cursor: pointer;" onclick="<?php echo $onclick; ?>">
                        <?php echo $this->escape($item->title); ?>
                    </a>
                </td>
                <?php if (!$this->state->get('filter.project')) : ?>
                    <td class="small"><?php echo $this->escape($item->project_title); ?></td>
                <?php endif; ?>
                <td class="small">
                    <?php echo $this->escape($item->album_title); ?>
                </td>
                <td class="small">
                    <?php echo $this->escape($item->author_name); ?>
                </td>
                <td class="small">
                    <?php echo (int) $item->id; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5">
                    <?php echo $this->pagination->getListFooter(); ?>
                </td>
            </tr>
        </tfoot>
    </table>

    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="filter_order" value="<?php echo $list_order; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $list_dir; ?>" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
