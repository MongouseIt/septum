<?php
/**
 * @package      Joomproject
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
use Joomla\CMS\Uri\Uri;

// Load the tooltip bootstrap.
HTMLHelper::_('bootstrap.tooltip');

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate');

$this->useCoreUI = true;

HTMLHelper::_('jphtml.script.form');


$repo_enabled = JPApplicationHelper::enabled('com_jprepo');
?>
<script type="text/javascript">
    $(document).ready(function () {
        JPform.accessAction();
    })

</script>
<div id="joomproject">
    <form action="<?php echo Route::_('index.php?option=com_jpprojects&view=project&id=' . (int) $this->item->id); ?>"
          method="post" name="adminForm" id="item-form" class="form-validate" enctype="multipart/form-data">

		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'general', 'recall' => true, 'breakpoint' => 768]); ?>
		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', '<i class="fas fa-briefcase"></i> ' . Text::_('COM_JOOMPROJECT_FIELDSET_PROJECT')); ?>
        <div class="row">
            <div class="col-md-9">
                <fieldset class="adminform">
                    <div class="form-group">
						<?php echo $this->form->renderField('title'); ?>
                    </div>
                    <div class="form-group">
						<?php echo $this->form->renderField('description'); ?>
                    </div>

                </fieldset>
            </div>
            <div class="col-md-3">
                <div class="form-group">
					<?php echo $this->form->renderField('state'); ?>
                </div>
                <div class="form-group">
					<?php echo $this->form->renderField('catid'); ?>
                </div>
            </div>
        </div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', '<i class="fas fa-info-circle"></i> ' . Text::_('COM_JOOMPROJECT_FIELDSET_DETAILS')); ?>
		<?php echo $this->form->renderFieldset('details') ?>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>


		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'currency', '<i class="fas fa-dollar-sign"></i> ' . Text::_('COM_JOOMPROJECT_FIELDSET_CURRENCY')); ?>
		<?php echo $this->form->renderFieldset('currency') ?>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'display', '<i class="fas fa-eye"></i> ' . Text::_('COM_JOOMPROJECT_FIELDSET_DISPLAY')); ?>
		<?php echo $this->form->renderFieldset('display') ?>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>



		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'publishing', '<i class="fas fa-edit"></i> ' . Text::_('COM_JOOMPROJECT_FIELDSET_PUBLISHING')); ?>

        <div class="form-group">
			<?php echo $this->form->getLabel('created_by'); ?>
			<?php echo $this->form->getInput('created_by'); ?>
        </div>
        <div class="form-group">
			<?php echo $this->form->getLabel('start_date'); ?>
			<?php echo $this->form->getInput('start_date'); ?>
        </div>
        <div class="form-group">
			<?php echo $this->form->getLabel('end_date'); ?>
			<?php echo $this->form->getInput('end_date'); ?>
        </div>

		<?php if ($this->item->modified_by) : ?>
            <div class="form-group">
				<?php echo $this->form->getLabel('modified_by'); ?>
				<?php echo $this->form->getInput('modified_by'); ?>
            </div>
            <div class="form-group">
				<?php echo $this->form->getLabel('modified'); ?>
				<?php echo $this->form->getInput('modified'); ?>
            </div>

		<?php endif; ?>


		<?php echo HTMLHelper::_('uitab.endTab'); ?>



		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'labels', '<i class="fas fa-tags"></i> ' . Text::_('COM_JOOMPROJECT_FIELDSET_LABELS')); ?>
        <fieldset>
			<?php echo $this->form->getInput('labels'); ?>
        </fieldset>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php if ($this->item->id > 0 && $repo_enabled) : ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'attachements', '<i class="fas fa-paperclip"></i> ' . Text::_('COM_JOOMPROJECT_FIELDSET_ATTACHMENTS')); ?>
            <fieldset>
				<?php echo $this->form->getInput('attachment'); ?>
            </fieldset>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php endif; ?>

	    <?php if(\Joomla\CMS\Component\ComponentHelper::getParams('com_jpprojects')->get('enable_project_gallery',0)): ?>

		    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'gallery', '<i class="fas fa-images"></i> '.Text::_('COM_JOOMPROJECT_FIELD_GALLERY')); ?>
		    <?php echo $this->form->getInput('gallery') ?>
		    <?php echo HTMLHelper::_('uitab.endTab'); ?>

	    <?php endif; ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'permissions', '<i class="fas fa-lock"></i> ' . Text::_('COM_JOOMPROJECT_FIELDSET_RULES_ACCESS')); ?>
        <fieldset>
            <p class="alert alert-info"><?php echo Text::_('COM_JOOMPROJECT_RULES_LABEL'); ?></p>
            <p class="alert alert-warning"><?php echo Text::_('COM_JOOMPROJECT_RULES_NOTE'); ?></p>
            <div id="jform_rules_element">
                <div id="jform_rules_reload">
					<?php echo $this->form->getInput('rules'); ?>
                </div>
            </div>
        </fieldset>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>


		<?php echo HTMLHelper::_('uitab.endTab'); ?>





		<?php $this->ignore_fieldsets = array('project', 'currency', 'display', 'details', 'basic', 'attribs', 'general', 'publishing', 'labels', 'attachements', 'permissions'); ?>

		<?php echo HTMLHelper::_('uitab.endTabSet'); ?>

		<?php
		echo $this->form->getInput('alias');
		echo $this->form->getInput('created');
		echo $this->form->getInput('elements');
		echo $this->form->getInput('access');
		?>
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="view"
               value="<?php echo htmlspecialchars($this->get('Name'), ENT_COMPAT, 'UTF-8'); ?>"/>
        <input type="hidden" name="return"
               value="<?php echo \Joomla\CMS\Factory::getApplication()->input->getCmd('return'); ?>"/>
		<?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>
<input id="baseUrl" value="<?php echo Uri::base(); ?>" type="hidden">