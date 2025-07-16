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

$user = Factory::getApplication()->getIdentity();
?>

<div id="joomproject">
<form action="<?php echo Route::_('index.php?option=com_jprepo&view=directory&id=' . (int) $this->item->id . '&layout=edit'); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
    <h3 class="mb-4 pb-4 border-bottom"><?php echo empty($this->item->id) ? Text::_('COM_JOOMPROJECT_NEW_DIRECTORY') : Text::_('COM_JOOMPROJECT_EDIT_DIRECTORY'); ?></h3>

   <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'general', 'recall' => true, 'breakpoint' => 768]); ?>
    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', '<i class="fas fa-briefcase"></i> '.Text::_('COM_JOOMPROJECT_FIELDSET_DIRECTORY')); ?>
        <fieldset class="adminform">
                <div class="form-group">
                    <?php echo $this->form->renderField('parent_id'); ?>
                </div>
            <div class="form-group">
                <?php echo $this->form->renderField('title'); ?>
            </div>
            <div class="form-group">
                <?php echo $this->form->renderField('description'); ?>
            </div>
        </fieldset>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>


        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'publishing-details', '<i class="fas fa-edit"></i> '.Text::_('COM_JOOMPROJECT_FIELDSET_PUBLISHING')); ?>
            <fieldset class="panelform">
                <div class="form-group">
                    <?php echo $this->form->renderField('created_by'); ?>
                </div>
                    <?php if ($this->item->modified_by) : ?>
                        <div class="form-group">
                            <?php echo $this->form->renderField('modified_by'); ?>
                        </div>
                        <div class="form-group">
                            <?php echo $this->form->renderField('modified'); ?>
                        </div>
                    <?php endif; ?>
            </fieldset>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'labels', '<i class="fas fa-tags"></i> '.Text::_('COM_JOOMPROJECT_FIELDSET_LABELS')); ?>
            <fieldset class="panelform">
                <div class="form-group" id="jform_labels_element">
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
                    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', $name.'-options', '<i class="fas fa-edit"></i> '.Text::_($fieldset->label)); ?>

                    <?php if (isset($fieldset->description) && trim($fieldset->description)) : ?>
                        <p class="tip"><?php echo $this->escape(Text::_($fieldset->description));?></p>
                    <?php endif; ?>
                    <fieldset class="panelform">
                            <?php foreach ($this->form->getFieldset($name) as $field) : ?>
                        <div class="form-group">
                               <?php echo $field->label;?>
                        </div>
                        <div class="form-group">
                                <?php echo $field->input; ?>
                        </div>
                            <?php endforeach; ?>
                    </fieldset>
                <?php
                endforeach;
                echo HTMLHelper::_('uitab.endTab');
            endif;
            ?>

    <?php if ($user->authorise('core.admin', 'com_jprepo')) : ?>
          <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'permissions', '<i class="fas fa-lock"></i> '.Text::_('COM_JOOMPROJECT_FIELDSET_RULES')); ?>
                <fieldset class="panelform">
                    <p><?php echo Text::_('COM_JOOMPROJECT_RULES_LABEL'); ?></p>
                    <p><?php echo Text::_('COM_JOOMPROJECT_RULES_NOTE'); ?></p>
                    <div class="form-group" id="jform_rules_element">
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
            echo $this->form->getInput('created');
            echo $this->form->getInput('project_id');
            echo $this->form->getInput('id');
            echo $this->form->getInput('elements');
        ?>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="return" value="<?php echo \Joomla\CMS\Factory::getApplication()->input->getCmd('return');?>" />
        <input type="hidden" name="view" value="<?php echo htmlspecialchars($this->get('Name'), ENT_COMPAT, 'UTF-8');?>" />
        <input type="hidden" name="filter_parent_id" value="<?php echo intval($this->form->getValue('parent_id'));?>" />
        <?php echo HTMLHelper::_('form.token'); ?>
    </div>
</form>
</div>
<input id="baseUrl" value="<?php echo Uri::base(); ?>" type="hidden">