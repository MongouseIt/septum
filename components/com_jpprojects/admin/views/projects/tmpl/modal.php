<?php
/**
 * @package      Joomproject
 * @subpackage   Projects
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('jphtml.script.form');

$func       = $this->escape(\Joomla\CMS\Factory::getApplication()->input->getCmd('function', 'jpSelectActiveProject'));
$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));
$filter_cat = $this->state->get('filter.category') > 0 ? true : false;

$txt_notset  = Text::_('DATE_NOT_SET');
$txt_cat     = Text::_('JCATEGORY');
$txt_nocat   = Text::_('COM_JOOMPROJECT_UNCATEGORISED');
$date_format = Text::_('DATE_FORMAT_LC4');

$link = \Joomla\CMS\Factory::getApplication()->input->server->get('HTTP_REFERER','','RAW');

$table = explode('&',$link);




?>
<form action="<?php echo Route::_('index.php?option=com_jpprojects&view=projects&layout=modal&tmpl=component&function=' . $func);?>" method="post" name="adminForm" id="adminForm">
    <?php
    // Search tools bar
    echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
    ?>


    <table class="adminlist table table-striped">
        <thead>
            <tr>
                <th class="title">
                    <?php echo HTMLHelper::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $list_dir, $list_order); ?>
                </th>
                <th width="24%" class="nowrap">
                    <?php echo HTMLHelper::_('grid.sort', 'JAUTHOR', 'author_name', $list_dir, $list_order); ?>
                </th>
                <th width="20%" class="nowrap">
                    <?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ACCESS', 'access_level', $list_dir, $list_order); ?>
                </th>
                <th width="1%" class="nowrap">
                    <?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $list_dir, $list_order); ?>
                </th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($this->items as $i => $item) :
            $js = 'if (window.parent) window.parent.' . $func . '('
                . "'" . (int) $item->id . "', "
                . "'" . $this->escape(addslashes($item->title)) . "'"
                . ');';
            ?>
            <tr class="row<?php echo $i % 2; ?>">
                <td>
                    <a class="pointer" onclick="<?php echo $js; ?>" style="cursor: pointer;" href="javascript:void(0);">
                        <?php echo $this->escape($item->title); ?>
                    </a>

                    <?php if (!$filter_cat) : ?>
                        <div class="small">
                            <?php echo $txt_cat . ': ' . ($item->category_title ? $this->escape($item->category_title) : $txt_nocat); ?>
                        </div>
                    <?php endif; ?>
                </td>
                <td class="small">
                    <?php echo $this->escape($item->author_name); ?>
                </td>
                <td class="small">
                    <?php echo $this->escape($item->access_level); ?>
                </td>
                <td class="small">
                    <?php echo (int) $item->id; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>

    </table>

    <?php echo $this->pagination->getListFooter();  ?>

    <input type="hidden" name="filter_order" value="<?php echo $list_order; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $list_dir; ?>" />
    <input type="hidden" name="task" value="" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
