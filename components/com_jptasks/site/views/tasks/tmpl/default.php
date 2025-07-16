<?php
/**
 * @package      Joomproject
 * @subpackage   Tasks
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
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('bootstrap.dropdown');
HTMLHelper::_('bootstrap.collapse');
LayoutHelper::$defaultBasePath = JPATH_ADMINISTRATOR . '/components/com_joomproject/layouts';

HTMLHelper::_('jphtml.script.jquerysortable');
HTMLHelper::_('jphtml.script.listform');
HTMLHelper::_('jphtml.script.task');
$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir = $this->escape($this->state->get('list.direction'));
$user = Factory::getApplication()->getIdentity();
$uid = $user->get('id');

$action_count = count((array)$this->actions);
$filter_in = ($this->state->get('filter.isset') ? 'in ' : '');
$can_order = $user->authorise('core.edit.state', 'com_jptasks');


$time_enabled = JPApplicationHelper::enabled('com_jptime');
$can_track = ($user->authorise('core.create', 'com_jptime') && $time_enabled);

// enable quick log time
$enable_quicklog_time = ComponentHelper::getParams('com_jptasks')->get('enable_quicklogtime', 0);
if ($enable_quicklog_time) {
    // alerts container
    echo LayoutHelper::render('common.alertscontainer', null, '', ['component' => 'com_joomproject', 'client' => 'admin']);
    // quick log time js code
    Factory::getDocument()->addScript(Uri::root() . 'media/com_joomproject/joomproject/js/quicklog.js');
}


$doc = Factory::getDocument();
$style = '.complete .task-title > a, .complete .task-description, .complete .caret {'
    . 'opacity:0.35;'
    . '}'
    . '.task-title > a {'
    . 'margin-left:10px;'
    . 'margin-right:10px;'
    . '}'
    . '.margin-none {'
    . 'margin: 0;'
    . '}'
    . '.priority-1 {'
    . 'border-right:8px solid #CCC !important;'
    . '}'
    . '.priority-2 {'
    . 'border-right:8px solid #468847 !important;'
    . '}'
    . '.priority-3 {'
    . 'border-right:8px solid #3a87ad !important;'
    . '}'
    . '.priority-4 {'
    . 'border-right:8px solid #c09853 !important;'
    . '}'
    . '.priority-5 {'
    . 'border-right:8px solid #b94a48 !important;'
    . '}'
    . '.list-striped .dropdown-menu li {'
    . 'background-color:transparent;'
    . 'padding: 0;'
    . 'border-bottom-width: 0;'
    . '}'
    . '.list-striped .dropdown-menu li.divider {'
    . 'background-color: rgba(0, 0, 0, 0.1);'
    . 'margin: 2px 0;'
    . '}';
$doc->addStyleDeclaration($style);

$print_url = JPtasksHelperRoute::getTasksRoute($this->state->get('filter.project'), $this->state->get('filter.milestone'), $this->state->get('filter.tasklist'))
    . '&tmpl=component&layout=print';
$print_opt = 'width=1024,height=600,resizable=yes,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no';

$wa = Factory::getApplication()->getDocument()->getWebAssetManager();


$js = array();
$js[] = 'jQuery(document).ready(function()';
$js[] = '{';
if ($can_track) {
    $js[] = ' var turl ="' . Uri::root() . "index.php?option=com_jptime&task=recorder.add&tmpl=component" . '";';
    $js[] = 'var topts = "width=500,height=600,resizable=yes,"';
    $js[] = '+ "scrollbars=yes,toolbar=no,location=no,"';
    $js[] = '+ "directories=no,status=no,menubar=no;"';
    $js[] = 'JPtask.setTimeTracker(turl, topts);';
}
if ($uid && $this->state->get('filter.project') && $can_order) {
    $js[] = 'JPlist.sortable(".list-tasks", "tasks");';
}
$js[] = '});';


$wa->addInlineScript(implode("\n", $js), ['position' => 'after'], ['type' => 'module'], ['com_joomproject.task']);

// task lists layout
$taskListsLayout = ComponentHelper::getParams('com_jptasks')->get('tasklists_layout', 'cards');

$enable_not_applicable = ComponentHelper::getParams('com_jptasks')->get('enable_not_applicable', 0);

?>
<div id="joomproject" class="category-list<?php echo $this->pageclass_sfx; ?> view-tasks PrintArea all">

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
        <form id="adminForm" name="adminForm" method="post"
              action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>">
            <div class="mb-4">
                <?php echo $this->toolbar; ?>
                <?php echo HTMLHelper::_('jphtml.project.filter'); ?>
                <a class="btn btn-light btn-sm button" id="print_btn" href="javascript:void(0);"
                   onclick="window.open('<?php echo Route::_($print_url); ?>', 'print', '<?php echo $print_opt; ?>')">
                    <i class="fas fa-print"></i> <?php echo Text::_('COM_JOOMPROJECT_PRINT'); ?>
                </a>

                <?php echo LayoutHelper::render(
                    'common.export',
                    [
                        'url' => JPtasksHelperRoute::getTasksRoute($this->state->get('filter.project'), $this->state->get('filter.milestone'), $this->state->get('filter.tasklist'))
                            . '&tmpl=component'
                    ],
                    null,
                    ['client' => 'administrator', 'component' => 'com_joomproject']
                ) ?>

                <?php
                if(Factory::getUser()->authorise('can.import','com_jptasks')):

                    $returnLink = base64_encode(Uri::getInstance()->toString());


                ?>
                <a class="btn btn-light btn-sm button" id="import_btn" href="<?php echo Route::_('index.php?option=com_jptasks&view=import&return='.$returnLink); ?>">
                    <i class="fas fa-file-import"></i> <?php echo Text::_('COM_JOOMPROJECT_IMPORT'); ?>
                </a>
                <?php endif; ?>


            </div>

            <div class="clearfix"></div>
            <!-- tasks filter -->
            <?php echo LayoutHelper::render('tasks.filter', ['current' => $this], '', ['client' => 'site', 'component' => 'com_jptasks']); ?>

            <!-- task lists & tasks -->
            <?php echo LayoutHelper::render("tasklists.$taskListsLayout", ['current' => $this], '', ['client' => 'site', 'component' => 'com_jptasks']) ?>

            <?php if ($can_order) : ?>
                <?php if (!$this->state->get('filter.project')) : ?>
                    <div class="alert alert-secondary"><?php echo Text::_('COM_JOOMPROJECT_REORDER_DISABLED'); ?></div>
                <?php else: ?>
                    <div class="alert alert-info"><i
                                class="fas fa-info-circle"></i> <?php echo Text::_('COM_JOOMPROJECT_REORDER_ENABLED'); ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($this->access->get('core.create') 
                && $this->access->get('core.edit')
                && $this->access->get('core.edit.state')) : ?>
                <?php echo HTMLHelper::_(
                    'bootstrap.renderModal',
                    'collapseModal',
                    array(
                        'title' => Text::_('COM_JPTASKS_BATCH_OPTIONS'),
                        'footer' => $this->loadTemplate('batch_footer'),
                    ),
                    $this->loadTemplate('batch_body')
                ); ?>
            <?php endif; ?>

            <?php echo LayoutHelper::render('common.pagination', ['pagination' => $this->pagination, 'params' => $this->params]) ?>
            <?php if($enable_not_applicable): ?>
                <input type="hidden" name="not_applicable" id="not_applicable" value="" />
            <?php endif; ?>
            <input type="hidden" id="boxchecked" name="boxchecked" value="0"/>
            <input type="hidden" id="target-item" name="target_item" value="0"/>
            <input type="hidden" name="task" value=""/>
            <?php echo HTMLHelper::_('form.token'); ?>

        </form>
    </div>
</div>
