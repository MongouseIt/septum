<?php
/**
 * @package      Joomproject
 * @subpackage   Projects
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2006-2012 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;


HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.calendar');
HTMLHelper::_('jphtml.script.form');

$params = $this->state->get('params');
$user   = Factory::getApplication()->getIdentity();
?>
<script type="text/javascript">
    Joomla.submitbutton = function (task) {
        if (task == 'form.cancel' || document.getElementById('jform_title').value != '') {
			<?php echo $this->form->getField('description')->save(); ?>
            Joomla.submitform(task, document.getElementById('item-form'));
        } else {
            alert('<?php echo $this->escape(Text::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
        }
    }
</script>
<div class="edit item-page<?php echo $this->pageclass_sfx; ?>">

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

    <form action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>" method="post" name="adminForm"
          id="item-form" enctype="multipart/form-data">
        <fieldset>
            <div class="mb-4">
				<?php echo $this->toolbar; ?>
            </div>
            <div class="formelm form-group">
				<?php echo $this->form->getLabel('title'); ?>
				<?php echo $this->form->getInput('title'); ?>
            </div>
            <div class="form-group">
				<?php echo $this->form->getInput('description'); ?>
            </div>
        </fieldset>

        <hr/>

		<?php echo HTMLHelper::_('tabs.start', 'projectform', array('useCookie' => 'true')); ?>
		<?php echo HTMLHelper::_('tabs.panel', Text::_('COM_JOOMPROJECT_FIELDSET_PUBLISHING'), 'project-publishing'); ?>
        <fieldset>
            <div class="formelm form-group">
				<?php echo $this->form->getLabel('state'); ?>
				<?php echo $this->form->getInput('state'); ?>
            </div>
            <div class="formelm form-group">
				<?php echo $this->form->getLabel('catid'); ?>
				<?php echo $this->form->getInput('catid'); ?>
            </div>
            <div class="formelm form-group">
				<?php echo $this->form->getLabel('start_date'); ?>
				<?php echo $this->form->getInput('start_date'); ?>
            </div>
            <div class="formelm form-group">
				<?php echo $this->form->getLabel('end_date'); ?>
				<?php echo $this->form->getInput('end_date'); ?>
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

		<?php echo HTMLHelper::_('tabs.panel', Text::_('COM_JOOMPROJECT_FIELDSET_LABELS'), 'project-labels'); ?>
        <fieldset>
            <div class="formelm form-group">
				<?php echo $this->form->getInput('labels'); ?>
            </div>
        </fieldset>

		<?php if ($this->item->id && JPApplicationHelper::enabled('com_jprepo')) : ?>
			<?php echo HTMLHelper::_('tabs.panel', Text::_('COM_JOOMPROJECT_FIELDSET_ATTACHMENTS'), 'project-attachments'); ?>
            <fieldset>
                <div class="formelm form-group">
					<?php echo $this->form->getInput('attachment'); ?>
                </div>
            </fieldset>
		<?php endif; ?>

		<?php
		$fieldsets = $this->form->getFieldsets('attribs');
		if (count($fieldsets)) :
			echo HTMLHelper::_('tabs.panel', Text::_('COM_JOOMPROJECT_DETAILS_FIELDSET'), 'project-options');
			foreach ($fieldsets as $name => $fieldset) :
				?>
                <fieldset>
					<?php foreach ($this->form->getFieldset($name) as $field) : ?>
                        <div class="formelm form-group">
							<?php echo $field->label; ?>
							<?php echo $field->input; ?>
                        </div>
					<?php endforeach; ?>
                </fieldset>
			<?php endforeach; ?>
		<?php endif; ?>

		<?php if ($user->authorise('core.admin', 'com_jpprojects')) : ?>
			<?php echo HTMLHelper::_('tabs.panel', Text::_('COM_JOOMPROJECT_FIELDSET_RULES'), 'project-permissions'); ?>
            <fieldset>
                <p><?php echo Text::_('COM_JOOMPROJECT_RULES_LABEL'); ?></p>
                <p><?php echo Text::_('COM_JOOMPROJECT_RULES_NOTE'); ?></p>
                <div class="formlm" id="jform_rules_element">
                    <div id="jform_rules_reload">
						<?php echo $this->form->getInput('rules'); ?>
                    </div>
                </div>
            </fieldset>
		<?php endif; ?>

		<?php echo HTMLHelper::_('tabs.end'); ?>

		<?php
		echo $this->form->getInput('alias');
		echo $this->form->getInput('created');
		echo $this->form->getInput('id');
		echo $this->form->getInput('asset_id');
		echo $this->form->getInput('elements');
		?>

        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="return" value="<?php echo $this->return_page; ?>"/>
        <input type="hidden" name="view"
               value="<?php echo htmlspecialchars($this->get('Name'), ENT_COMPAT, 'UTF-8'); ?>"/>
		<?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>
