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
use Joomla\CMS\Router\Route;


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

<form action="<?php echo Route::_('index.php?option=com_jpforum&view=reply&id=' . (int)$this->item->id); ?>"
      method="post" name="adminForm" id="item-form" class="form-validate">
    <div class="main-card">
        <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'general']); ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', '<i class="fas fa-comment"></i> ' . Text::_('COM_JOOMPROJECT_NEW_NOTE')); ?>
        <div class="mb-3">
            <h3><?php echo empty($this->item->id) ? Text::_('COM_JOOMPROJECT_NEW_REPLY') : Text::_('COM_JOOMPROJECT_EDIT_REPLY'); ?></h3>
            <?php echo $this->form->renderField('description'); ?>

        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'publishing', '<i class="fas fa-edit"></i> ' . Text::_('COM_JOOMPROJECT_FIELDSET_PUBLISHING')); ?>
        <fieldset class="panelform">
            <?php echo $this->form->renderField('created_by') ?>
            <?php echo $this->form->renderField('state') ?>
            <?php if ($this->item->modified_by) : ?>
                <?php echo $this->form->renderField('modified_by') ?>
                <?php echo $this->form->renderField('modified') ?>
            <?php endif; ?>
        </fieldset>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php if (JPApplicationHelper::enabled('com_jprepo')) : ?>
            <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'attachments', '<i class="fas fa-edit"></i> ' . Text::_('COM_JOOMPROJECT_FIELDSET_ATTACHMENTS')); ?>
            <fieldset class="panelform">
                <?php echo $this->form->getInput('attachment'); ?>
            </fieldset>
            <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php endif; ?>

        <?php if ($user->authorise('core.admin', 'com_jpforum')) : ?>
            <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'permissions', '<i class="fas fa-lock"></i> ' . Text::_('COM_JOOMPROJECT_FIELDSET_RULES')); ?>
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
        <div id="jform_access_element">
            <div id="jform_access_reload">
                <?php echo $this->form->getInput('access'); ?>
            </div>
        </div>

        <div>
            <?php
            echo $this->form->getInput('project_id');
            echo $this->form->getInput('topic_id');
            echo $this->form->getInput('created');
            echo $this->form->getInput('id');
            echo $this->form->getInput('asset_id');
            echo $this->form->getInput('elements');
            ?>
            <input type="hidden" name="task" value=""/>
            <input type="hidden" name="return"
                   value="<?php echo \Joomla\CMS\Factory::getApplication()->input->getCmd('return'); ?>"/>
            <input type="hidden" name="view"
                   value="<?php echo htmlspecialchars($this->get('Name'), ENT_COMPAT, 'UTF-8'); ?>"/>
            <?php echo HTMLHelper::_('form.token'); ?>
        </div>
    </div>
</form>
