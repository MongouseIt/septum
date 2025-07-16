<?php
/**
 * @package      Joomproject
 * @subpackage   Forum
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

// Load the tooltip bootstrap.
HTMLHelper::_('bootstrap.tooltip');

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('keepalive')
    ->useScript('form.validate');

$this->useCoreUI = true;
HTMLHelper::_('jphtml.script.form');

$user = Factory::getApplication()->getIdentity();
?>

<div id="joomproject">
    <h3 class="mb-4"><?php echo empty($this->item->id) ? Text::_('COM_JOOMPROJECT_NEW_TOPIC') : Text::_('COM_JOOMPROJECT_EDIT_TOPIC'); ?></h3>
<form action="<?php echo Route::_('index.php?option=com_jpforum&view=topic&id=' .(int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
    <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'site', 'recall' => true, 'breakpoint' => 768]); ?>
    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', '<i class="fas fa-briefcase"></i> '.Text::_('COM_JOOMPROJECT_FIELDSET_FORUM')); ?>
        <fieldset class="adminform">

                <?php if ($this->item->id <= 0) : ?>
                <div class="form-group">
                    <?php echo $this->form->getLabel('project_id') ?>
                    <?php echo $this->form->getInput('project_id'); ?>
                </div>
                <?php endif; ?>
            <div class="form-group">
                <?php echo $this->form->getLabel('title')?>
                <?php echo $this->form->getInput('title'); ?>
            </div>
            <div class="form-group">
            <?php echo $this->form->getLabel('description'); ?>
            <?php echo $this->form->getInput('description'); ?>
            </div>
        </fieldset>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'publishing', '<i class="fas fa-edit"></i> '.Text::_('COM_JOOMPROJECT_FIELDSET_PUBLISHING')); ?>
            <fieldset class="panelform">
                <div class="form-group">
                    <?php echo $this->form->getLabel('created_by')?>
                    <?php echo $this->form->getInput('created_by'); ?>
                </div>
                <div class="form-group">
                    <?php echo $this->form->getLabel('state') . $this->form->getInput('state'); ?>
                </div>
                    <?php if ($this->item->modified_by) : ?>
                <div class="form-group">
                        <?php echo $this->form->getLabel('modified_by')?>
                        <?php echo $this->form->getInput('modified_by'); ?>
                </div>
                <div class="form-group">
                        <?php echo $this->form->getLabel('modified') ?>
                        <?php echo $this->form->getInput('modified'); ?>
                </div>
                <?php endif; ?>
            </fieldset>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>
    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'labels', '<i class="fas fa-tags"></i> '.Text::_('COM_JOOMPROJECT_FIELDSET_LABELS')); ?>
            <fieldset class="panelform">
                <div id="jform_labels_element">
                    <div id="jform_labels_reload">
				        <?php echo $this->form->getInput('labels'); ?>
                    </div>
                </div>
            </fieldset>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>
            <?php if (JPApplicationHelper::enabled('com_jprepo')) : ?>
                <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'attachments', '<i class="fas fa-paperclip"></i> '.Text::_('COM_JOOMPROJECT_FIELDSET_ATTACHMENTS')); ?>
                <fieldset class="panelform">
    				<?php echo $this->form->getInput('attachment'); ?>
                </fieldset>
                <?php echo HTMLHelper::_('uitab.endTab'); ?>
            <?php endif; ?>


            <?php $fieldsets = $this->form->getFieldsets('attribs'); ?>
			<?php foreach ($fieldsets as $name => $fieldset) : ?>
                <?php echo HTMLHelper::_('uitab.addTab', 'myTab',$name . '-options', '<i class="fas fa-list"></i> '.Text::_($fieldset->label)); ?>

				<?php if (isset($fieldset->description) && trim($fieldset->description)) : ?>
					<p><?php echo $this->escape(Text::_($fieldset->description));?></p>
				<?php endif; ?>
				<fieldset class="panelform">
					    <?php foreach ($this->form->getFieldset($name) as $field) : ?>
                        <div class="form-group">
                            <?php echo $field->label; ?>
                            <?php echo $field->input; ?>
                        </div>

					    <?php endforeach; ?>
				</fieldset>
                <?php echo HTMLHelper::_('uitab.endTab'); ?>
			<?php endforeach; ?>



    <?php if ($user->authorise('core.admin', 'com_jpforum')) : ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'access-rules', '<i class="fas fa-lock"></i> '.Text::_('COM_JOOMPROJECT_FIELDSET_RULES')); ?>
    			<fieldset class="panelform">
                    <p><?php echo Text::_('COM_JOOMPROJECT_RULES_LABEL'); ?></p>
                    <p><?php echo Text::_('COM_JOOMPROJECT_RULES_NOTE'); ?></p>
    				<div id="jform_rules_element">
                        <div id="jform_rules_reload" style="clear: both;">
                            <?php echo $this->form->getInput('rules'); ?>
                        </div>
                    </div>
    			</fieldset>
            <?php echo HTMLHelper::_('uitab.endTab'); ?>
    <?php endif; ?>
    <?php $this->ignore_fieldsets = array('project','basic','attribs','general','publishing','labels', 'attachements', 'permissions'); ?>
    <?php echo LayoutHelper::render('joomla.edit.params', $this); ?>

    <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

    <div>
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