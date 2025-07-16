<?php
/**
 * @package      Joomproject
 * @subpackage   Comments
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate');

$this->useCoreUI = true;
// Load the tooltip behavior.
HTMLHelper::_('bootstrap.tooltip');
?>
<form action="<?php echo Route::_('index.php?option=com_jpcomments&view=comment&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
    <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'site', 'recall' => true, 'breakpoint' => 768]); ?>
    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', '<i class="fas fa-comment"></i> '.empty($this->item->id) ? Text::_('COM_JOOMPROJECT_NEW_COMMENT') : Text::_('COM_JOOMPROJECT_EDIT_COMMENT')); ?>
        <fieldset class="adminform">
            <legend><?php echo empty($this->item->id) ? Text::_('COM_JOOMPROJECT_NEW_COMMENT') : Text::_('COM_JOOMPROJECT_EDIT_COMMENT'); ?></legend>
            <?php echo $this->form->renderField('description') ?>
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
                echo HTMLHelper::_('uitab.endTab'); ?>
            <?php
            endforeach;
        endif;
        ?>

        <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

    <div>
		<?php
            echo $this->form->getInput('title');
            echo $this->form->getInput('created');
            echo $this->form->getInput('project_id');
            echo $this->form->getInput('parent_id');
            echo $this->form->getInput('item_id');
            echo $this->form->getInput('context');
            echo $this->form->getInput('id');
        ?>
        <input type="hidden" name="task" value="" />
		<input type="hidden" name="return" value="<?php echo \Joomla\CMS\Factory::getApplication()->input->getCmd('return');?>" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
<input id="baseUrl" value="<?php echo Uri::base(); ?>" type="hidden">