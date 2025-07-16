<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_joomproject
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;


HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', '.multipleAccessLevels', null, array('placeholder_text_multiple' => Text::_('JOPTION_SELECT_ACCESS')));
HTMLHelper::_('formbehavior.chosen', '.multipleAuthors', null, array('placeholder_text_multiple' => Text::_('JOPTION_SELECT_AUTHOR')));
HTMLHelper::_('formbehavior.chosen', 'select');

$app       = Factory::getApplication();
$user      = Factory::getApplication()->getIdentity();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == 'a.ordering';
$columns   = 10;
if (strpos($listOrder, 'modified') !== false)
{
    $orderingColumn = 'modified';
}
else {
    $orderingColumn = 'created';
}
if ($saveOrder)
{
    $saveOrderingUrl = 'index.php?option=com_joomproject&task=teams.saveOrderAjax&tmpl=component';
    HTMLHelper::_('sortablelist.sortable', 'timetypeList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

?>

<form action="<?php echo Route::_('index.php?option=com_jpusers&view=teams');?>"
      method="post"
      name="adminForm"
      id="adminForm"
>

    <div class="row">
        <div id="j-sidebar-container" class="col-md-2">
            <?php echo $this->sidebar; ?>
        </div>
        <div id="j-main-container" class="col-md-10">
        <?php
        // Search tools bar
        echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
        ?>

        <table class=" adminlist table table-striped" id="teamList">
            <thead>
            <tr>
                <th width="1%" class="nowrap center d-none d-md-table-cell">
                    <?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
                </th>
                <th width="1%" class="center">
                    <?php echo HTMLHelper::_('grid.checkall'); ?>
                </th>
                <th width="2%" class="nowrap center">
                    <?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
                </th>
                <th width="" class=" left">
                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_JOOMPROJECT_FIELD_TITLE_LABEL', 'a.title', $listDirn, $listOrder); ?>
                </th>
                <th width="" class="nowrap d-none d-md-table-cell">
                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_JOOMPROJECT_HEADING_DATE_' . strtoupper($orderingColumn), 'a.' . $orderingColumn, $listDirn, $listOrder); ?>
                </th>
                <th class=" left">
                    <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                </th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($this->items as $i => $item) :
                $item->max_ordering = 0;
                $ordering = ($listOrder == 'a.ordering');

                $checked = HTMLHelper::_('grid.id', $i, $item->id);
                $canChange = $user->authorise('core.manage.teams', 'com_jpprojects');
                $link = Route::_('index.php?option=com_jpusers&view=team&layout=edit&id=' . $item->id);


                ?>
                <tr class="row<?php echo $i % 2; ?>">
                    <td class="order nowrap center d-none d-md-table-cell">
                        <?php
                        $iconClass = '';
                        if (!$canChange) {
                            $iconClass = ' inactive';
                        } elseif (!$saveOrder) {
                            $iconClass = ' inactive tip-top hasTooltip" title="' . HTMLHelper::_('tooltipText', 'JORDERINGDISABLED');
                        }
                        ?>

                        <span class="sortable-handler<?php echo $iconClass ?>">
								<span class="icon-menu" aria-hidden="true"></span>
							</span>
                        <?php if ($canChange && $saveOrder) : ?>
                            <input type="text" style="display:none" name="order[]" size="5"
                                   value="<?php echo $item->ordering; ?>" class="width-20 text-area-order"/>
                        <?php endif; ?>
                    </td>
                    <td class="center">
                        <?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
                    </td>
                    <td class="center">
                        <div class="btn-group">
                            <?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'teams.', $canChange, 'cb', "", ""); ?>
                            <?php // Create dropdown items and render the dropdown list.
                            if ($canChange) {
                                HTMLHelper::_('actionsdropdown.' . ((int)$item->state === 2 ? 'un' : '') . 'archive', 'cb' . $i, 'timetypes');
                                HTMLHelper::_('actionsdropdown.' . ((int)$item->state === -2 ? 'un' : '') . 'trash', 'cb' . $i, 'timetypes');
                                echo HTMLHelper::_('actionsdropdown.render', $this->escape($item->title));
                            }
                            ?>
                        </div>
                    </td>

                    <td class="left">
                        <a href=" <?php echo $link; ?>">
                            <?php echo $item->title; ?>
                        </a>
                    </td>

                    <td class="nowrap small d-none d-md-table-cell">
                        <?php
                        $date = $item->{$orderingColumn};
                        echo $date > 0 ? HTMLHelper::_('date', $date, Text::_('DATE_FORMAT_LC4')) : '-';
                        ?>
                    </td>
                    <td class="d-none d-md-table-cell">
                        <?php echo (int)$item->id; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>

            <tfoot>
            <tr>
                <td colspan="10">
                    <?php echo $this->pagination->getListFooter(); ?>
                </td>
            </tr>
            </tfoot>
        </table>
        </div>
    </div>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <?php echo HTMLHelper::_('form.token'); ?>

</form>

