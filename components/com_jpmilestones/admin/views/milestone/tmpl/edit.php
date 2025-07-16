<?php
/**
 * @package      Joomproject
 * @subpackage   Milestones
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


// Load the tooltip behavior.
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

// Load the tooltip uitab.
HTMLHelper::_('bootstrap.tooltip');

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('jquery')
    ->useScript('form.validate');
HTMLHelper::_('jphtml.script.form');

?>

<div id="joomproject">
    <form action="<?php echo Route::_('index.php?option=com_jpmilestones&view=milestone&id=' . (int)$this->item->id); ?>"
          method="post" name="adminForm" id="item-form" class="form-validate">
        <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'site', 'recall' => true, 'breakpoint' => 768]); ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', '<i class="fas fa-flag"></i> ' . Text::_('COM_JOOMPROJECT_FIELDSET_MILESTONE')); ?>
        <div class="row">
            <div class="col-md-9">
                <?php echo $this->form->renderField('project_id'); ?>
                <?php echo $this->form->renderField('title'); ?>
                <?php echo $this->form->renderField('description'); ?>
            </div>
            <div class="col-md-3">
                <?php echo $this->form->renderField('state'); ?>
                <fieldset class="panelform">
                    <div id="jform_labels_element">
                        <div id="jform_labels_reload">
                            <?php echo $this->form->renderField('labels'); ?>
                        </div>
                    </div>
                </fieldset>
                <div class="form-group">
                    <?php echo $this->form->renderField('catid'); ?>
                </div>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'publishing', '<i class="fas fa-edit"></i> ' . Text::_('COM_JOOMPROJECT_FIELDSET_PUBLISHING')); ?>
        <?php echo $this->form->renderField('created_by'); ?>

        <div class="control-group">
            <div class="control-label">
                <?php echo $this->form->getLabel('start_date') ?>
            </div>
            <div class="controls">
                        <span id="jform_start_date_reload">
                            <?php echo $this->form->getInput('start_date') ?>
                        </span>
            </div>
        </div>

        <div class="control-group">
            <div class="control-label">
                <?php echo $this->form->getLabel('end_date') ?>
            </div>
            <div class="controls">
                <span id="jform_end_date_reload">
                    <?php echo $this->form->getInput('end_date') ?>
                </span>
            </div>
        </div>

        <?php if ($this->item->modified_by) : ?>
            <?php echo $this->form->renderField('modified_by'); ?>
            <?php echo $this->form->renderField('modified'); ?>
        <?php endif; ?>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php if (JPApplicationHelper::enabled('com_jprepo')) : ?>
            <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'attachements', '<i class="fas fa-paperclip"></i> ' . Text::_('COM_JOOMPROJECT_FIELDSET_ATTACHMENTS')); ?>
            <fieldset class="panelform">
                <ul class="adminformlist unstyled" id="jform_attachment_element">
                    <li id="jform_attachment_reload">
                        <?php echo $this->form->getInput('attachment'); ?>
                    </li>
                </ul>
            </fieldset>
            <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php endif; ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'permissions', '<i class="fas fa-lock"></i> ' . Text::_('COM_JOOMPROJECT_FIELDSET_RULES_ACCESS')); ?>
        <p class="alert alert-info"><?php echo Text::_('COM_JOOMPROJECT_RULES_LABEL'); ?></p>
        <p class="alert alert-warning"><?php echo Text::_('COM_JOOMPROJECT_RULES_NOTE'); ?></p>
        <div id="jform_rules_element">
            <div id="jform_rules_reload" style="clear: both;">
                <?php echo $this->form->getInput('rules'); ?>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php $this->ignore_fieldsets = array('project', 'basic', 'attribs', 'general', 'publishing', 'labels', 'attachements', 'permissions'); ?>
        <?php echo LayoutHelper::render('joomla.edit.params', $this); ?>
        <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

        <div class="clr"></div>
        <div>
            <div id="jform_access_element" style="display: none;">
                <div id="jform_access_reload">
                    <?php echo $this->form->getInput('access'); ?>
                </div>
            </div>
            <?php
            echo $this->form->getInput('alias');
            echo $this->form->getInput('asset_id');
            echo $this->form->getInput('created');
            echo $this->form->getInput('id');
            echo $this->form->getInput('elements');
            ?>
            <input type="hidden" name="task" value=""/>
            <input type="hidden" name="return"
                   value="<?php echo \Joomla\CMS\Factory::getApplication()->input->getCmd('return'); ?>"/>
            <input type="hidden" name="view"
                   value="<?php echo htmlspecialchars($this->get('Name'), ENT_COMPAT, 'UTF-8'); ?>"/>
            <?php echo HTMLHelper::_('form.token'); ?>
        </div>
    </form>
    <input id="baseUrl" value="<?php echo Uri::base(); ?>" type="hidden">
</div>