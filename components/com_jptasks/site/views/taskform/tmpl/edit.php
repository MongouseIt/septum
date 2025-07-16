<?php
/**
 * @package      Joomproject
 * @subpackage   Tasks
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2006-2012 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Component\ComponentHelper;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('jphtml.script.form');
$app = Factory::getApplication();

$this->useCoreUI = true;

// Create shortcut to parameters.
$params = $this->state->get('params');
$user   = Factory::getApplication()->getIdentity();



?>
<div id="joomproject" class="edit item-page<?php echo $this->pageclass_sfx; ?>">

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

    <form action="<?php echo Route::_('index.php?option=com_jptasks&view=taskform&id=' . (int) $this->item->id . '&layout=edit'); ?>"
          method="post" name="adminForm" id="item-form" class="form-validate">
        <div class="formelm-buttons mb-4">
			<?php echo $this->toolbar; ?>
        </div>

        <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'general', 'recall' => true, 'breakpoint' => 768]); ?>
		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', '<i class="fas fa-copy"></i> ' . Text::_('COM_JOOMPROJECT_FIELDSET_TASK')); ?>
        <fieldset>
			<?php if ($this->item->id <= 0) : ?>
                <div class="formelm form-group">
					<?php echo $this->form->getLabel('project_id'); ?>
					<?php echo $this->form->getInput('project_id'); ?>
                </div>
			<?php endif; ?>
			<?php if (JPApplicationHelper::enabled('com_jpmilestones')) : ?>
                <div class="formelm form-group">
					<?php echo $this->form->getLabel('milestone_id'); ?>
                    <div id="jform_milestone_id_reload">
						<?php echo $this->form->getInput('milestone_id'); ?>
                    </div>
                </div>
			<?php endif; ?>
            <div class="formelm form-group">
				<?php echo $this->form->getLabel('list_id'); ?>
                <div class="" id="jform_list_id_reload">
					<?php echo $this->form->getInput('list_id'); ?>
                </div>
            </div>
            <div id="jform_access_element">
                <div id="jform_access_reload"><?php echo $this->form->getInput('access'); ?></div>
            </div>
            <div class="formelm form-group">
				<?php echo $this->form->getLabel('title'); ?>
				<?php echo $this->form->getInput('title'); ?>
            </div>
            <div class="formelm form-group">
				<?php echo $this->form->getLabel('labels'); ?>
                <div id="jform_labels_reload">
					<?php echo $this->form->getInput('labels'); ?>
                </div>
            </div>
            <div class="formelm form-group">
				<?php echo $this->form->getInput('description'); ?>
            </div>
        </fieldset>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'publishing', '<i class="fas fa-edit"></i> ' . Text::_('COM_JOOMPROJECT_FIELDSET_PUBLISHING')); ?>
        <fieldset>
            <div class="formelm form-group">
				<?php echo $this->form->getLabel('state'); ?>
				<?php echo $this->form->getInput('state'); ?>
            </div>
            <div class="formelm form-group">
				<?php echo $this->form->getLabel('priority'); ?>
				<?php echo $this->form->getInput('priority'); ?>
            </div>

            <?php echo $this->form->renderFieldset('completion') ?>

            <div class="formelm form-group">
				<?php echo $this->form->getLabel('start_date'); ?>
                <div id="jform_start_date_reload">
					<?php echo $this->form->getInput('start_date'); ?>
                </div>
            </div>
            <div class="formelm form-group">
				<?php echo $this->form->getLabel('end_date'); ?>
                <div id="jform_end_date_reload">
					<?php echo $this->form->getInput('end_date'); ?>
                </div>
            </div>
            <div class="formelm form-group">
				<?php echo $this->form->getLabel('rate'); ?>
				<?php echo $this->form->getInput('rate'); ?>
            </div>
            <div class="formelm form-group">
				<?php echo $this->form->getLabel('estimate'); ?>
				<?php echo $this->form->getInput('estimate'); ?>
            </div>
			<?php if ($this->item->modified_by) : ?>
                <div class="formelm form-group">
					<?php echo $this->form->getLabel('modified_by'); ?>
					<?php echo $this->form->getInput('modified_by'); ?>
                </div>
                <div class="formelm form-group">
					<?php echo $this->form->getLabel('modified'); ?>
					<?php echo $this->form->getInput('modified'); ?>
                </div>
			<?php endif; ?>
        </fieldset>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php if (ComponentHelper::isEnabled('com_jpreminders')): ?>
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
		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'users', '<i class="fas fa-users"></i> ' . Text::_('COM_JOOMPROJECT_FIELDSET_ASSIGNED_USERS')); ?>
        <fieldset>
            <div id="jform_users_element" class="formelm form-group">
                <div id="jform_users_reload">
					<?php echo $this->form->getInput('users'); ?>
                </div>
            </div>
        </fieldset>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'dependency', '<i class="fab fa-pushed"></i> ' . Text::_('COM_JOOMPROJECT_FIELDSET_DEPENDENCIES')); ?>
        <fieldset>
            <div id="jform_dependency_element" class="formelm form-group">
                <div id="jform_dependency_reload">
					<?php echo $this->form->getInput('dependency'); ?>
                </div>
            </div>
        </fieldset>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php if (JPApplicationHelper::enabled('com_jprepo')) : ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'attachements', '<i class="fas fa-paperclip"></i> ' . Text::_('COM_JOOMPROJECT_FIELDSET_ATTACHMENTS')); ?>
            <fieldset>
                <div id="jform_attachment_element" class="formelm form-group">
                    <div id="jform_attachment_reload">
						<?php echo $this->form->getInput('attachment'); ?>
                    </div>
                </div>
            </fieldset>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php endif; ?>
		<?php $fieldsets = $this->form->getFieldsets('attribs');
		if (count($fieldsets)) : ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', '<i class="fas fa-list"></i> ' . Text::_('COM_JOOMPROJECT_DETAILS_FIELDSET')); ?>
			<?php foreach ($fieldsets as $name => $fieldset) : ?>
                <fieldset>
					<?php foreach ($this->form->getFieldset($name) as $field) : ?>
                        <div class="formelm form-group">
							<?php echo $field->label; ?>
							<?php echo $field->input; ?>
                        </div>
					<?php endforeach; ?>
                </fieldset>
			<?php endforeach; ?>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php endif; ?>
		<?php if ($user->authorise('core.admin', 'com_jptasks') || $user->authorise('core.manage', 'com_jptasks')) : ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'permissions', '<i class="fas fa-lock"></i> ' . Text::_('COM_JOOMPROJECT_FIELDSET_RULES_ACCESS')); ?>
            <fieldset>
                <p><?php echo Text::_('COM_JOOMPROJECT_RULES_LABEL'); ?></p>
                <p><?php echo Text::_('COM_JOOMPROJECT_RULES_NOTE'); ?></p>
                <div class="formlm" id="jform_rules_element">
                    <div id="jform_rules_reload">
						<?php echo $this->form->getInput('rules'); ?>
                    </div>
                </div>
            </fieldset>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php endif; ?>

		<?php $this->ignore_fieldsets = array('task','completion', 'basic', 'general', 'publishing', 'users', 'dependency', 'attachements', 'permissions'); ?>
		<?php echo LayoutHelper::render('joomla.edit.params', $this); ?>

        <?php echo HTMLHelper::_('uitab.endTabSet'); ?>


        <div style="display: none;">
			<?php
			if ($this->item->id > 0)
			{
				echo $this->form->getInput('project_id');
			}
			echo $this->form->getInput('alias');
			echo $this->form->getInput('created');
			echo $this->form->getInput('elements');
			?>
        </div>
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="return" value="<?php echo $this->return_page; ?>"/>
        <input type="hidden" name="view"
               value="<?php echo htmlspecialchars($this->get('Name'), ENT_COMPAT, 'UTF-8'); ?>"/>
		<?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>
