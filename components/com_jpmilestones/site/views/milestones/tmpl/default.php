<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jpmilestones
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
use Joomla\CMS\Component\ComponentHelper;

HTMLHelper::_('bootstrap.dropdown');
HTMLHelper::_('bootstrap.collapse');

HTMLHelper::_('jphtml.script.listform');


$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir = $this->escape($this->state->get('list.direction'));
$groupingType = $this->params->get('groupingType', 'project_id');
$pid = (int)$this->state->get('filter.project');

// additional style
$doc = Factory::getDocument();
$style = '.large {'
    . 'font-size: 20px;'
    . 'line-height: 24px;'
    . '}'
    . '.medium {'
    . 'font-size: 16px;'
    . 'line-height: 22px;'
    . '}'
    . '.margin-none {'
    . 'margin: 0;'
    . '}';
$doc->addStyleDeclaration($style);

$print_url = JPmilestonesHelperRoute::getMilestonesRoute($this->state->get('filter.project'))
    . '&tmpl=component&layout=print';
$print_opt = 'width=1024,height=600,resizable=yes,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no';

$itemid = JPApplicationHelper::getActiveMenuItemId();
$list_url = JPmilestonesHelperRoute::getMilestonesRoute($this->params->get('filter_category'), $itemid);
$return_url = base64_encode($list_url);
$internalProjectNav = ComponentHelper::getParams('com_joomproject')->get('internal_project_nav', 1);




?>
<div id="joomproject" class="category-list<?php echo $this->pageclass_sfx; ?> view-milestones PrintArea all">

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

    <div class="cat-items">
        <form name="adminForm" id="adminForm" action="<?php echo Route::_($list_url); ?>" method="post">
            <div class="my-4">
                <?php echo $this->toolbar; ?>
                <?php echo HTMLHelper::_('jphtml.project.filter'); ?>
                <a class="btn btn-sm btn-light button" id="print_btn" href="javascript:void(0);"
                   onclick="window.open('<?php echo Route::_($print_url); ?>', 'print', '<?php echo $print_opt; ?>')">
                    <i class="fas fa-print"></i> <?php echo Text::_('COM_JOOMPROJECT_PRINT'); ?>
                </a>

                <?php echo LayoutHelper::render(
                    'common.export',
                    [
                        'url' => JPmilestonesHelperRoute::getMilestonesRoute($this->state->get('filter.project'))
                            . '&tmpl=component'
                    ],
                    null,
                    ['client' => 'administrator', 'component' => 'com_joomproject']
                ) ?>

            </div>

            <?php echo LayoutHelper::render('milestones.filter', ['current' => $this]); ?>


            <?php if($this->items): ?>
                <?php $itemsGrouping = JoomprojectHelperFrontend::arrayGroupBy($this->items, $groupingType); ?>
                <?php foreach ($itemsGrouping as $group => $itemGroup): ?>
                    <div class="card mb-3">
                        <?php if ($pid == 0 || $groupingType == 'category_title'): // show grouping by project if not filtered by any project or show if grouping type by category title ?>
                            <div class="card-header">
                                <h3 class="m-0">
                                    <?php echo $this->escape($group); ?>
                                </h3>
                            </div>
                        <?php endif; ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($itemGroup as $item): ?>
                                <?php echo LayoutHelper::render(
                                    'milestone.listGroupItem',
                                    ['item' => $item, 'current' => $this, 'params' => $this->params, 'state' => $this->state]
                                ); ?>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
                <?php echo LayoutHelper::render('common.pagination', ['pagination' => $this->pagination, 'params' => $this->params]) ?>
            <?php endif; ?>



            <input type="hidden" id="boxchecked" name="boxchecked" value="0"/>
            <input type="hidden" name="task" value=""/>
            <?php echo HTMLHelper::_('form.token'); ?>
        </form>
    </div>
</div>
