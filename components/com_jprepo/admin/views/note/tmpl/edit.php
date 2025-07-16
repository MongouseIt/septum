<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jprepo
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
$view_only  = ($this->item->checked_out && $this->item->checked_out != $user->id);
$txt_action = ($view_only ? 'COM_JOOMPROJECT_VIEW_NOTE' : 'COM_JOOMPROJECT_EDIT_NOTE');
?>

<div id="joomproject">
<form action="<?php echo Route::_('index.php?option=com_jprepo&view=note&id='.(int) $this->item->id . '&layout=edit'); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
    <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'site', 'recall' => true, 'breakpoint' => 768]); ?>
    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', '<i class="fas fa-file"></i> '.Text::_('COM_JOOMPROJECT_NEW_NOTE')); ?>
        <fieldset class="adminform">
            <legend><?php echo empty($this->item->id) ? Text::_('COM_JOOMPROJECT_NEW_NOTE') : Text::_($txt_action); ?></legend>
                <?php if (!$view_only) : ?>
                    <?php echo $this->form->renderField('dir_id') ?>
                <?php endif; ?>
               <?php echo $this->form->renderField('title'); ?>
            <?php echo $this->form->renderField('description'); ?>

        </fieldset>
<?php echo HTMLHelper::_('uitab.endTab'); ?>
    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'publishing', '<i class="fas fa-edit"></i> '.Text::_('COM_JOOMPROJECT_FIELDSET_PUBLISHING')); ?>
        <fieldset class="panelform">
                <?php echo $this->form->renderField('created_by') ?>
               <?php echo $this->form->renderField('state')  ?>
                <?php if ($this->item->modified_by) : ?>
                    <?php echo $this->form->renderField('modified_by') ?>
                    <?php echo $this->form->renderField('modified') ?>
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
        <?php
        $fieldsets = (array) $this->form->getFieldsets('attribs');

        if (count($fieldsets)) :
            foreach ($fieldsets as $name => $fieldset) :
                ?>
                <?php echo HTMLHelper::_('uitab.addTab', 'myTab', $name.'-options', '<i class="fas fa-briefcase"></i> '.Text::_($fieldset->label)); ?>

                <?php if (isset($fieldset->description) && trim($fieldset->description)) : ?>
                <p class="tip"><?php echo $this->escape(Text::_($fieldset->description));?></p>
            <?php endif; ?>
                <fieldset class="panelform">
                        <?php foreach ($this->form->getFieldset($name) as $field) : ?>
                           <?php echo $this->form->renderField($field) ?>
                        <?php endforeach; ?>
                </fieldset>
            <?php
                echo HTMLHelper::_('uitab.endTab');
            endforeach;
        endif;
        ?>


        <div class="clr"></div>


    <?php if ($user->authorise('core.admin', 'com_jprepo') && !$this->item->checked_out) : ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'permissions', '<i class="fas fa-lock"></i> '.Text::_('COM_JOOMPROJECT_FIELDSET_RULES')); ?>
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
    <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
    <div>
        <div id="jform_access_element">
            <div id="jform_access_reload">
                <?php echo $this->form->getInput('access'); ?>
            </div>
        </div>
        <?php
            echo $this->form->getInput('project_id');
            echo $this->form->getInput('created');
            echo $this->form->getInput('alias');
            echo $this->form->getInput('id');
            echo $this->form->getInput('asset_id');
            echo $this->form->getInput('elements');
        ?>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="return" value="<?php echo \Joomla\CMS\Factory::getApplication()->input->getCmd('return');?>" />
        <input type="hidden" name="view" value="<?php echo htmlspecialchars($this->get('Name'), ENT_COMPAT, 'UTF-8');?>" />
        <input type="hidden" name="filter_parent_id" value="<?php echo intval($this->form->getValue('dir_id'));?>" />
        <input type="hidden" name="rev" value="<?php echo intval($this->state->get('note.rev'));?>" />
        <?php echo HTMLHelper::_('form.token'); ?>
    </div>
</form>
    <input id="baseUrl" value="<?php echo Uri::base(); ?>" type="hidden">

</div>
