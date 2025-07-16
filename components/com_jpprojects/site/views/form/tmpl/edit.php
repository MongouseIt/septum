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
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;


/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate');
$this->useCoreUI = true;
HTMLHelper::_('bootstrap.dropdown');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('jphtml.script.form');

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

    <form action="<?php echo Route::_('index.php?option=com_jpprojects&view=form&id=' . (int) $this->item->id . '&layout=edit'); ?>"
          method="post" name="adminForm" id="adminForm" class="" enctype="multipart/form-data">
        <div class="mb-4">
			<?php echo $this->toolbar; ?>
        </div>
		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'site', 'recall' => true, 'breakpoint' => 768]); ?>
		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', '<i class="fas fa-briefcase"></i> ' . Text::_('COM_JOOMPROJECT_FIELDSET_PROJECT')); ?>
        <fieldset>
            <div class="formelm form-group">
				<?php echo $this->form->getLabel('title'); ?>
				<?php echo $this->form->getInput('title'); ?>
            </div>
            <div class="form-group">
				<?php echo $this->form->getInput('description'); ?>
            </div>
        </fieldset>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', '<i class="fas fa-info-circle"></i> '.Text::_('COM_JOOMPROJECT_FIELDSET_DETAILS')); ?>
        <?php echo $this->form->renderFieldset('details') ?>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>


        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'currency', '<i class="fas fa-dollar-sign"></i> '.Text::_('COM_JOOMPROJECT_FIELDSET_CURRENCY')); ?>
        <?php echo $this->form->renderFieldset('currency') ?>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'display', '<i class="fas fa-eye"></i> '.Text::_('COM_JOOMPROJECT_FIELDSET_DISPLAY')); ?>
        <?php echo $this->form->renderFieldset('display') ?>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>


		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'publishing', '<i class="fas fa-edit"></i> ' . Text::_('COM_JOOMPROJECT_FIELDSET_PUBLISHING')); ?>
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

		<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'labels', '<i class="fas fa-tags"></i> ' . Text::_('COM_JOOMPROJECT_FIELDSET_LABELS')); ?>
        <fieldset>
            <div class="formelm form-group">
				<?php echo $this->form->getInput('labels'); ?>
            </div>
        </fieldset>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'attachements', '<i class="fas fa-paperclip"></i> ' . Text::_('COM_JOOMPROJECT_FIELDSET_ATTACHMENTS')); ?>

        <fieldset>
            <div class="formelm form-group">
				<?php echo $this->form->getInput('attachment'); ?>
            </div>
        </fieldset>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

	    <?php if(\Joomla\CMS\Component\ComponentHelper::getParams('com_jpprojects')->get('enable_project_gallery',0)): ?>
		    <?php //echo HTMLHelper::_('uitab.addTab', 'myTab', 'gallery', '<i class="fas fa-images"></i> '.Text::_('COM_JOOMPROJECT_FIELD_GALLERY')); ?>
		    <?php // echo $this->form->getInput('gallery') ?>
		    <?php // echo HTMLHelper::_('uitab.endTab'); ?>
	    <?php endif; ?>

		<?php if (JoomprojectHelperAccess::canChangePermissions(null,$this->item)) : ?>
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
		<?php $this->ignore_fieldsets = array('project','details','currency','display', 'basic', 'attribs', 'general', 'publishing', 'labels', 'attachements', 'permissions'); ?>
		<?php echo LayoutHelper::render('joomla.edit.params', $this); ?>
		<?php echo HTMLHelper::_('uitab.endTabSet'); ?>
		<?php
		echo $this->form->getInput('alias');
		echo $this->form->getInput('created');
		echo $this->form->getInput('id');
		echo $this->form->getInput('asset_id');
		echo $this->form->getInput('elements');
		?>

        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="return" value="<?php echo $this->return_page; ?>"/>
        <input type="hidden" id="baseUrl" name="baseUrl" value="<?php echo Uri::base() ?>"/>
        <input type="hidden" name="view"
               value="<?php echo htmlspecialchars($this->get('Name'), ENT_COMPAT, 'UTF-8'); ?>"/>
		<?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>
