<?php
/**
 * @package      Joomproject
 * @subpackage   Timetracking
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
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

$user = Factory::getApplication()->getIdentity();
?>

<div id="joomproject">
<form action="<?php echo Route::_('index.php?option=com_jptime&view=time&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
    <h3 class="mb-4 border-bottom pb-4"><?php echo empty($this->item->id) ? Text::_('COM_JOOMPROJECT_NEW_TIME') : Text::_('COM_JOOMPROJECT_EDIT_TIME'); ?></h3>
    <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'general', 'recall' => true, 'breakpoint' => 768]); ?>
    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', '<i class="fas fa-copy"></i> '.Text::_('COM_JOOMPROJECT_FIELDSET_TIME')); ?>
<div class="row">
    <div class="col-md-8">

                <?php if ($this->item->id <= 0) : ?>
            <?php echo $this->form->renderField('project_id'); ?>s
                        <?php echo $this->form->getLabel('task_id'); ?>
                        <div id="jform_task_id_reload">
                            <?php echo $this->form->getInput('task_id'); ?>
                        </div>
                <?php endif; ?>
                <?php echo $this->form->renderField('description') ?>
                <?php echo $this->form->renderField('log_date') ?>
                <?php echo $this->form->renderField('log_time') ?>
                <?php echo $this->form->renderField('billable') ?>
                    <?php echo $this->form->getLabel('rate');?>
                    <div id="jform_rate_reload">
                        <?php echo $this->form->getInput('rate'); ?>
                    </div>


        </div>
        <div class="col-md-4">
            <?php echo $this->form->renderField('created_by') ?>
            <?php echo $this->form->renderField('state') ?>
            <?php if ($this->item->modified_by) {
                echo $this->form->renderField('modified_by');
                echo $this->form->renderField('modified');
              }
             ?>
            <?php $fieldsets = $this->form->getFieldsets('attribs'); ?>
			<?php foreach ($fieldsets as $name => $fieldset) : ?>
                <?php echo HTMLHelper::_('uitab.addTab', 'myTab', $name . '-options', '<i class="fas fa-list"></i> '.Text::_($fieldset->label)); ?>
				<?php if (isset($fieldset->description) && trim($fieldset->description)) : ?>
					<p><?php echo $this->escape(Text::_($fieldset->description));?></p>
				<?php endif; ?>

					<ul class="adminformlist unstyled">
					    <?php foreach ($this->form->getFieldset($name) as $field) : ?>
						    <li><?php echo $field->label . $field->input; ?></li>
					    <?php endforeach; ?>
					</ul>
                <?php echo HTMLHelper::_('uitab.endTab'); ?>
			<?php endforeach; ?>
        </div>
    </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>


    <?php if ($user->authorise('core.admin', 'com_jptime')) : ?>
            <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'permissions', '<i class="fas fa-lock"></i> '.Text::_('COM_JOOMPROJECT_FIELDSET_RULES')); ?>
                    <p><?php echo Text::_('COM_JOOMPROJECT_RULES_LABEL'); ?></p>
                    <p><?php echo Text::_('COM_JOOMPROJECT_RULES_NOTE'); ?></p>
    				<div id="jform_rules_element">
                        <div id="jform_rules_reload" style="clear: both;">
                            <?php echo $this->form->getInput('rules'); ?>
                        </div>
                    </div>

    	 <?php echo HTMLHelper::_('uitab.endTab'); ?>
    <?php endif; ?>
    <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
    <div>
        <div id="jform_access_element">
            <div id="jform_access_reload">
                <?php echo $this->form->getInput('access'); ?>
            </div>
        </div>
        <?php
            echo $this->form->getInput('created');
            echo $this->form->getInput('id');
            echo $this->form->getInput('asset_id');
            echo $this->form->getInput('elements');
        ?>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="return" value="<?php echo \Joomla\CMS\Factory::getApplication()->input->getCmd('return');?>" />
        <input type="hidden" name="view" value="<?php echo htmlspecialchars($this->get('Name'), ENT_COMPAT, 'UTF-8');?>" />
        <?php echo HTMLHelper::_('form.token'); ?>
    </div>
</form>
<input id="baseUrl" value="<?php echo Uri::base(); ?>" type="hidden">
</div>
