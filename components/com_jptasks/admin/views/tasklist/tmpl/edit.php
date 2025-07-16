<?php
/**
 * @package      Joomproject
 * @subpackage   Tasks
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
<form action="<?php echo Route::_('index.php?option=com_jptasks&view=tasklist&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
    <h3 class="mb-4"><?php echo empty($this->item->id) ? Text::_('COM_JOOMPROJECT_NEW_TASKLIST') : Text::_('COM_JOOMPROJECT_EDIT_TASKLIST'); ?></h3>



    <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'general', 'recall' => true, 'breakpoint' => 768]); ?>
    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', '<i class="fas fa-copy"></i> '.Text::_('COM_JOOMPROJECT_TASKLIST')); ?>
                <div class="form-group">
                    <?php echo $this->form->getLabel('project_id');?>
                    <?php echo  $this->form->getInput('project_id'); ?>
                </div>
                <div class="form-group">
                    <?php echo $this->form->getLabel('milestone_id'); ?>
                    <?php echo $this->form->getInput('milestone_id'); ?>
                </div>
                <div class="form-group">
                    <?php echo $this->form->getLabel('title'); ?>
                    <?php echo $this->form->getInput('title'); ?>
                </div>
                <div class="form-group">
                    <?php echo $this->form->getLabel('description') ?>
                    <?php echo $this->form->getInput('description'); ?>
                </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>


    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'publishing-details', '<i class="fas fa-edit"></i> '.Text::_('COM_JOOMPROJECT_FIELDSET_PUBLISHING')); ?>
        <div class="form-group">
            <?php echo $this->form->getLabel('created_by');?>
            <?php echo $this->form->getInput('created_by'); ?>
        </div>
        <div class="form-group">
            <?php echo $this->form->getLabel('state') ?>
            <?php echo $this->form->getInput('state'); ?>
        </div>
        <?php if ($this->item->modified_by) : ?>
        <div class="form-group">
            <?php echo $this->form->getLabel('modified_by') ?>
            <?php echo $this->form->getInput('modified_by'); ?>
        </div>
            <div class="form-group">
                <?php echo $this->form->getLabel('modified');?>
                <?php echo $this->form->getInput('modified'); ?>
            </div>
        <?php endif; ?>
<?php $fieldsets = $this->form->getFieldsets('attribs'); ?>
<?php foreach ($fieldsets as $name => $fieldset) : ?>
    <?php echo HTMLHelper::_('sliders.panel', Text::_($fieldset->label), $name . '-options'); ?>
    <?php if (isset($fieldset->description) && trim($fieldset->description)) : ?>
        <p><?php echo $this->escape(Text::_($fieldset->description));?></p>
    <?php endif; ?>
    <fieldset class="panelform">
            <?php foreach ($this->form->getFieldset($name) as $field) : ?>
            <div class="form-group">
                <?php echo $field->label;?>
                <?php $field->input; ?>
            </div>

            <?php endforeach; ?>
    </fieldset>
<?php endforeach; ?>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>


    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'access-rules', '<i class="fas fa-lock"></i> '.Text::_('COM_JOOMPROJECT_FIELDSET_RULES')); ?>

    <?php if ($user->authorise('core.admin', 'com_jptasks')) : ?>
    			<fieldset class="panelform">
                    <p><?php echo Text::_('COM_JOOMPROJECT_RULES_LABEL'); ?></p>
                    <p><?php echo Text::_('COM_JOOMPROJECT_RULES_NOTE'); ?></p>
    				<div id="jform_rules_element">
                        <div id="jform_rules_reload" style="clear: both;">
                            <?php echo $this->form->getInput('rules'); ?>
                        </div>
                    </div>
    			</fieldset>
        <?php endif; ?>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>
    <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

        <div id="jform_access_element">
            <div id="jform_access_reload">
                <?php echo $this->form->getInput('access'); ?>
            </div>
        </div>
        <?php
            echo $this->form->getInput('alias');
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