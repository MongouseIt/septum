<?php
/**
 * @package      Joomproject
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
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;

// Load the tooltip uitab.
HTMLHelper::_('bootstrap.tooltip');

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate');
$this->useCoreUI = true;

HTMLHelper::_('jphtml.script.form');
$app = Factory::getApplication();

$this->ignore_fieldsets = ['task', 'completion'];
$user                   = Factory::getApplication()->getIdentity();
?>

<div id="joomproject">
    <form action="<?php echo Route::_('index.php?option=com_jptasks&view=task&id=' . (int) $this->item->id); ?>"
          method="post" name="adminForm" id="item-form" class="form-validate">
        <h3 class="mb-4 border-bottom pb-4"><?php echo empty($this->item->id) ? Text::_('COM_JOOMPROJECT_NEW_TASK') : Text::_('COM_JOOMPROJECT_EDIT_TASK'); ?></h3>

		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'general', 'recall' => true, 'breakpoint' => 768]); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', '<i class="fas fa-copy"></i> ' . Text::_('COM_JOOMPROJECT_FIELDSET_TASK')); ?>
        <div class="row">
            <div class="col-md-9">
				<?php echo $this->form->renderField('project_id'); ?>
				<?php if (JPApplicationHelper::enabled('com_jpmilestones')) : ?>
                    <div class="control-group">
                        <div class="control-label">
							<?php echo $this->form->getLabel('milestone_id'); ?>
                        </div>
                        <div class="controls">
                            <div id="jform_milestone_id_reload">
								<?php echo $this->form->getInput('milestone_id'); ?>
                            </div>
                        </div>
                    </div>
				<?php endif; ?>
                <div class="control-group">
                    <div class="control-label">
						<?php echo $this->form->getLabel('list_id'); ?>
                    </div>
                    <div class="controls">
                        <div id="jform_list_id_reload">
							<?php echo $this->form->getInput('list_id'); ?>
                        </div>
                    </div>
                </div>
                <div class="form-group">
					<?php echo $this->form->renderField('title'); ?>
                </div>
                <div class="form-group">
					<?php echo $this->form->renderField('description'); ?>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
					<?php echo $this->form->renderField('state') ?>
                </div>
                <div class="form-group">
					<?php echo $this->form->renderField('priority') ?>
                </div>
                <div class="form-group">
					<?php echo $this->form->renderFieldset('completion') ?>
                </div>
                <div class="form-group">
					<?php echo $this->form->renderField('rate') ?>
                </div>
                <div class="form-group">
					<?php echo $this->form->renderField('estimate') ?>
                </div>
				<?php $fieldsets = $this->form->getFieldsets('attribs'); ?>
				<?php foreach ($fieldsets as $name => $fieldset) : ?>
					<?php foreach ($this->form->getFieldset($name) as $field) : ?>
                        <div class="form-group">
							<?php echo $field->label ?>
							<?php echo $field->input; ?>
                        </div>
					<?php endforeach; ?>
				<?php endforeach; ?>
            </div>
        </div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'publishing', '<i class="fas fa-edit"></i> ' . Text::_('COM_JOOMPROJECT_FIELDSET_PUBLISHING')); ?>
        <fieldset class="panelform">
            <div class="form-group">
				<?php echo $this->form->renderField('created_by') ?>
            </div>
            <div class="form-group">
                <div class="control-group">
                    <div class="control-label">
		                <?php echo $this->form->getLabel('start_date'); ?>
                    </div>
                    <div class="controls">
                        <span id="jform_start_date_reload"><?php echo $this->form->getInput('start_date'); ?></span>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="control-group">
                    <div class="control-label">
				        <?php echo $this->form->getLabel('end_date'); ?>
                    </div>
                    <div class="controls">
                        <span id="jform_end_date_reload"><?php echo $this->form->getInput('end_date'); ?></span>
                    </div>
                </div>
            </div>
			<?php if ($this->item->modified_by) : ?>
                <div class="form-group">
					<?php echo $this->form->renderField('modified_by'); ?>
                </div>
                <div class="form-group">
					<?php echo $this->form->renderField('modified') ?>
                </div>

			<?php endif; ?>
        </fieldset>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php if (ComponentHelper::isEnabled('com_jpreminders')) : ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'reminders', '<i class="fas fa-bell"></i> ' . Text::_('COM_JOOMPROJECT_FIELDSET_REMINDERS')); ?>
            <div class="remindersList my-3">

				<?php
				echo LayoutHelper::render(
					'reminders.reminders',
					['reminders' => $this->item->reminders, 'item' => $this->item],
					JPATH_ADMINISTRATOR . '/components/com_joomproject/layouts/'
				);
				?>
            </div>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php endif; ?>


		<?php if (JPApplicationHelper::enabled('com_jprepo')) : ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'attachements', '<i class="fas fa-paperclip"></i> ' . Text::_('COM_JOOMPROJECT_FIELDSET_ATTACHMENTS')); ?>
            <div class="form-group">
				<?php echo $this->form->getInput('attachment'); ?>
            </div>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php endif; ?>
		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'users', '<i class="fas fa-users"></i> ' . Text::_('COM_JOOMPROJECT_FIELDSET_ASSIGNED_USERS')); ?>
        <fieldset class="panelform">
            <div class="form-group" id="jform_users_element">
                <div id="jform_users_reload">
					<?php echo $this->form->getInput('users'); ?>
                </div>
            </div>
        </fieldset>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'dependency', '<i class="fab fa-pushed"></i> ' . Text::_('COM_JOOMPROJECT_FIELDSET_DEPENDENCIES')); ?>
        <fieldset class="panelform">
            <div id="jform_dependency_element">
                <div class="form-group" id="jform_dependency_reload">
					<?php echo $this->form->getInput('dependency'); ?>
                </div>
            </div>
        </fieldset>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>


		<?php echo LayoutHelper::render('joomla.edit.params', $this); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'permissions', '<i class="fas fa-lock"></i> ' . Text::_('COM_JOOMPROJECT_FIELDSET_RULES_ACCESS')); ?>
		<?php if ($user->authorise('core.admin', 'com_jptasks')) : ?>
            <p class="alert alert-info"><?php echo Text::_('COM_JOOMPROJECT_RULES_LABEL'); ?></p>
            <p class="alert alert-warning"><?php echo Text::_('COM_JOOMPROJECT_RULES_NOTE'); ?></p>
            <div id="jform_rules_element">
                <div class="form-group" id="jform_rules_reload" style="clear: both;">
					<?php echo $this->form->getInput('rules'); ?>
                </div>
            </div>
		<?php endif; ?>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>


		<?php echo HTMLHelper::_('uitab.endTabSet'); ?>

        <div class="clr"></div>

        <div>
            <div id="jform_access_element">
                <div id="jform_access_reload" style="display: none;">
					<?php echo $this->form->getInput('access'); ?>
                </div>
            </div>
			<?php
			echo $this->form->getInput('alias');
			echo $this->form->getInput('created');
			echo $this->form->getInput('elements');
			?>
            <input type="hidden" name="task" value=""/>
            <input type="hidden" name="view"
                   value="<?php echo htmlspecialchars($this->get('Name'), ENT_COMPAT, 'UTF-8'); ?>"/>
            <input type="hidden" name="return"
                   value="<?php echo \Joomla\CMS\Factory::getApplication()->input->getCmd('return'); ?>"/>

			<?php echo HTMLHelper::_('form.token'); ?>
        </div>
    </form>
</div>
<input id="baseUrl" value="<?php echo Uri::base(); ?>" type="hidden">



