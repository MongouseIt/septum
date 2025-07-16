<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Associations;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('formbehavior.chosen', '#jform_catid', null, array('disable_search_threshold' => 0));
HTMLHelper::_('formbehavior.chosen', '#jformtask_id,#jformassigned_users,#jform_assigned_users');
$jinput = Factory::getApplication()->input;


$user                   = Factory::getApplication()->getIdentity();
$this->configFieldsets  = array('editorConfig');
$this->hiddenFieldsets  = array('basic-limited');
$this->ignore_fieldsets = array('jmetadata', 'item_associations');

// Create shortcut to parameters.
$params = clone $this->state->get('params');
$params->merge(new Registry($this->item->attribs));

$app   = Factory::getApplication();
$input = $app->input;

$assoc = Associations::isEnabled();

// get task informations
Factory::getDocument()->addScriptDeclaration('
	Joomla.submitbutton = function(task)
	{
		if (task == "reminder.cancel" || document.formvalidator.isValid(document.getElementById("item-form")))
		{
			
			Joomla.submitform(task, document.getElementById("item-form"));

		
		}
	};
	
	
');


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
    <form action="<?php echo Uri::root() . 'index.php?option=com_jpreminders&layout=edit&id=' . (int) $this->item->id; ?>"
          method="post" name="adminForm" id="item-form" class="form-validate m-3 p-2">
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
		<?php if ($jinput->get('tmpl', '', 'word') == "component") : ?>
            <div class="mb-5"></div>
            <div class="p-3 bg-light fixed-bottom d-flex justify-content-between">

                <button type="button" class="btn btn-sm btn-success reminder-save"
                        onclick="Joomla.submitbutton('form.apply');">
                    <i class="fas fa-paper-plane"></i> <?php echo Text::_('COM_JPREMINDERS_SAVE'); ?>
                </button>

                <button type="button" class="btn btn-sm btn-danger"
                        onclick="parent.document.querySelector('.iziModal-button-close').click()">
                    <i class="fas fa-times"></i> <?php echo Text::_('COM_JPREMINDERS_CANCEL'); ?>
                </button>
            </div>
		<?php endif; ?>

        <input type="hidden" name="taskid" value="<?php echo (int) $this->task->id ?>"/>
        <input type="hidden" name="task_created_by" value="<?php echo (int) $this->task->created_by ?>"/>
        <input type="hidden" name="task_milestone_id" value="<?php echo (int) $this->task->milestone_id ?>"/>
        <input type="hidden" name="task_list_id" value="<?php echo (int) $this->task->list_id ?>"/>
        <input type="hidden" name="tmpl"
               value="<?php echo Factory::getApplication()->input->get('tmpl', '', 'WORD') ?>"/>
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="return" value="<?php echo $input->getCmd('return'); ?>"/>

		<?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>