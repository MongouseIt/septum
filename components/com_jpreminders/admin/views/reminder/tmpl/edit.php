<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_joomproject
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2019 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Associations;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

// Load the tooltip bootstrap.
HTMLHelper::_('bootstrap.tooltip');

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate');

$this->useCoreUI = true;
HTMLHelper::_('formbehavior.chosen', '#jformtask_id,#jformassigned_users,#jform_assigned_users');
$jinput = Factory::getApplication()->input;

BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jptasks/models', 'JPtasksModel');
$modelTask = BaseDatabaseModel::getInstance('Task', 'JPtasksModel', array('ignore_request' => true));

$this->configFieldsets  = array('editorConfig');
$this->hiddenFieldsets  = array('basic-limited');
$this->ignore_fieldsets = array('jmetadata', 'item_associations');

// Create shortcut to parameters.
$params = clone $this->state->get('params');
$params->merge(new Registry($this->item->attribs));

$app = Factory::getApplication();
$input = $app->input;

$assoc = Associations::isEnabled();

Factory::getDocument()->addStyleDeclaration('
#jformassigned_users_chzn{
width:100% !important
}
.js-calendar {
    margin-top: 0 !important;
}

.chosen-container{
    width: 100% !important;
}

.chosen-search-input{height: 37px !important}

');
?>
<div id="joomproject">
    <?php
    if(Factory::getApplication()->input->get('tmpl','','WORD') != "component" && $this->item->task_id > 0) :?>
        <div class="page-header mb-4">
            <h3>Edit : <?php echo $modelTask->getItem($this->item->task_id)->title?></h3>
        </div>
    <?php endif; ?>

    <form action="<?php echo Route::_('index.php?option=com_jpreminders&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">

        <div class="row mb-3">
            <div class="col-sm-6">
                <div><?php echo $this->form->getLabel('assigned_users'); ?></div>
                <div><?php echo $this->form->getInput('assigned_users'); ?></div>
            </div>
            <div class="col-sm-6">
                <div><?php echo $this->form->getLabel('start_date'); ?></div>
                <div><?php echo $this->form->getInput('start_date'); ?></div>
            </div>
        </div>


        <div class="row mb-3">
            <div class="col-sm-12">
                <div><?php echo $this->form->getLabel('description'); ?></div>
                <div><?php echo $this->form->getInput('description'); ?></div>
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-sm-8">
                <div><?php echo $this->form->getLabel('amount'); ?></div>
                <div class="input-group"><?php echo $this->form->getInput('amount'); ?><?php echo $this->form->getInput('types'); ?></div>
            </div>
            <div class="col-sm-4">
                <div><?php echo $this->form->getLabel('repeats'); ?></div>
                <div class="input-group"><?php echo $this->form->getInput('repeats'); ?></div>
            </div>
        </div>
        <?php if($jinput->get('tmpl','','STRING') == "component") :?>
            <div class="p-3 bg-light fixed-bottom d-flex justify-content-between">

                <button type="button" class="btn btn-success reminder-save" onclick="Joomla.submitbutton('reminder.apply');">
                    <i class="fas fa-paper-plane"></i> <?php echo Text::_('COM_JPREMINDERS_SAVE'); ?>
                </button>

                <button type="button" class="btn btn-danger" onclick="parent.document.querySelector('.iziModal-button-close').click()">
                    <i class="fas fa-times"></i> <?php echo Text::_('COM_JPREMINDERS_CANCEL'); ?>
                </button>
            </div>
        <?php endif; ?>
        <input type="hidden" name="taskid" value="<?php echo (int) $this->task->id ?>"/>
        <input type="hidden" name="task_created_by" value="<?php echo (int) $this->task->created_by ?>"/>
        <input type="hidden" name="task_milestone_id" value="<?php echo (int) $this->task->milestone_id ?>"/>
        <input type="hidden" name="task_list_id" value="<?php echo (int) $this->task->list_id ?>"/>
        <input type="hidden" name="tmpl" value="<?php echo  Factory::getApplication()->input->get('tmpl','','WORD')?>" />
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="return" value="<?php echo $input->getCmd('return'); ?>" />

        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>
