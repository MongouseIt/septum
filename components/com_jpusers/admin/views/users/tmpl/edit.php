<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_joomproject
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

// Include the component HTML helpers.
HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('formbehavior.chosen', 'select');

$this->configFieldsets  = array('editorConfig');
$this->hiddenFieldsets  = array('basic-limited');



$app = Factory::getApplication();
$input = $app->input;

$assoc = Associations::isEnabled();

Factory::getDocument()->addScriptDeclaration('
	Joomla.submitbutton = function(task)
	{
		if (task == "role.cancel" || document.formvalidator.isValid(document.getElementById("item-form")))
		{
			Joomla.submitform(task, document.getElementById("item-form"));

			// @deprecated 4.0  The following js is not needed since 3.7.0.
			if (task !== "role.apply")
			{
				window.parent.jQuery("#roleEdit' . (int) $this->item->id . 'Modal").modal("hide");
			}
		}
	};
');

// In case of modal
$isModal = $input->get('layout') == 'modal' ? true : false;
$layout  = $isModal ? 'modal' : 'edit';
$tmpl    = $isModal || $input->get('tmpl', '', 'cmd') === 'component' ? '&tmpl=component' : '';


?>
<div>
    <form action="<?php echo Route::_('index.php?option=com_jpusers    &layout=' . $layout . $tmpl . '&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
        <div class="form-horizontal">
            <?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', ['active' => 'site', 'recall' => true, 'breakpoint' => 768]); ?>

            <?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'general', Text::_('COM_JOOMPROJECT_GENERAL_FIELDSET_LABEL')); ?>
            <div class="row-fluid">
                <div class="span9">
                    <fieldset class="adminform">
                        <?php echo $this->form->renderField('id'); ?>
                        <?php echo $this->form->renderField('title'); ?>
                        <?php echo $this->form->renderField('permissions'); ?>
                    </fieldset>
                </div>
                <div class="span3">
                    <?php echo LayoutHelper::render('joomla.edit.global', $this); ?>
                </div>
            </div>
            <?php echo HTMLHelper::_('bootstrap.endTab'); ?>

            <?php // Do not show the publishing options if the edit form is configured not to. ?>
            <?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'publishing', Text::_('COM_JOOMPROJECT_FIELDSET_PUBLISHING')); ?>
            <div class="row-fluid form-horizontal-desktop">
                <?php echo LayoutHelper::render('joomla.edit.publishingdata', $this); ?>
            </div>
            <?php echo HTMLHelper::_('bootstrap.endTab'); ?>

            <?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>

            <input type="hidden" name="task" value="" />
            <input type="hidden" name="return" value="<?php echo $input->get('return', null, 'BASE64'); ?>" />
            <input type="hidden" name="forcedLanguage" value="<?php echo $input->get('forcedLanguage', '', 'cmd'); ?>" />
            <?php echo HTMLHelper::_('form.token'); ?>
        </div>
    </form>
</div>



