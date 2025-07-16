<?php
/**
 * @package      Joomproject Pro
 * @subpackage   Designs
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
use Joomla\CMS\Factory;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate');

$this->useCoreUI = true;
// Load the tooltip behavior.
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('jphtml.script.form');
?>

<form action="<?php echo Route::_('index.php?option=com_jpdesigns&view=album&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
    <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'site', 'recall' => true, 'breakpoint' => 768]); ?>
    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', '<i class="fas fa-eye"></i> '.Text::_('COM_JOOMPROJECT_NEW_NOTE')); ?>
	        <fieldset class="adminform">
				<legend>
                    <?php if (empty($this->item->id)) : ?>
                        <?php echo Text::_('COM_JOOMPROJECT_NEW_DESIGN_ALBUM'); ?>
                    <?php else : ?>
                        <?php echo Text::_('COM_JOOMPROJECT_EDIT_DESIGN_ALBUM'); ?>
                    <?php endif; ?>
                </legend>
                  <?php echo $this->form->renderField('project_id')?>
					<?php echo $this->form->renderField('title') ?>
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

	            <?php $fieldsets = $this->form->getFieldsets('attribs'); ?>
				<?php foreach ($fieldsets as $name => $fieldset) : ?>
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
				<?php endforeach; ?>

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
        <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

    <div>
        <div id="jform_access_element">
            <div id="jform_access_reload">
                <?php echo $this->form->getInput('access'); ?>
            </div>
        </div>
		<?php
            echo $this->form->getInput('created');
            echo $this->form->getInput('elements');
        ?>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="view" value="<?php echo htmlspecialchars($this->get('Name'), ENT_COMPAT, 'UTF-8');?>" />
		<input type="hidden" name="return" value="<?php echo Factory::getApplication()->input->getCmd('return');?>" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
<input id="baseUrl" value="<?php echo Uri::base(); ?>" type="hidden">