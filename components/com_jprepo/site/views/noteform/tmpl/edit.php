<?php
/**
 * @package      Joomproject
 * @subpackage   Repository
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2006-2012 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate');

$this->useCoreUI = true;

HTMLHelper::_('jphtml.script.form');

// Create shortcut to parameters.
$params = $this->state->get('params');
$user   = Factory::getApplication()->getIdentity();
?>

<div id="joomproject" class="edit item-page<?php echo $this->pageclass_sfx; ?>">

	<?php
	// load internal navigation
	echo JPhtmlNav::loadMain();
	?>

	<?php
	// load header
	echo JPhtmlNav::loadHeader($this->params);
	?>

	<?php
	// load project internal navigation
	echo JPhtmlNav::loadProject();
	?>

<form action="<?php echo Route::_('index.php?option=com_jprepo&view=noteform&id=' . (int) $this->item->id . '&layout=edit'); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
    <div class="formelm-buttons btn-toolbar mb-4">
        <?php echo $this->toolbar; ?>
    </div>

    <nav>
        <div class="nav nav-tabs  mt-4" id="nav-tab" role="tablist">
            <a class="nav-item nav-link active" id="nav-home-tab" data-bs-toggle="tab" href="#nav-home" role="tab" aria-controls="nav-home" aria-selected="true">Home</a>
            <?php $fieldsets = $this->form->getFieldsets('attribs');
            if (count($fieldsets)) : ?>
                <a class="nav-item nav-link" id="nav-note-options-tab" data-bs-toggle="tab" href="#nav-note-options" role="tab" aria-controls="nav-note-options" aria-selected="false"><?php echo Text::_('COM_JOOMPROJECT_DETAILS_FIELDSET'); ?></a>
            <?php endif; ?>
            <?php if ($user->authorise('core.admin', 'com_jprepo') || $user->authorise('core.manage', 'com_jprepo')) : ?>
                <a class="nav-item nav-link" id="nav-note-permissions-tab" data-bs-toggle="tab" href="#nav-note-permissions" role="tab" aria-controls="nav-note-permissions" aria-selected="false"><?php echo Text::_('COM_JOOMPROJECT_FIELDSET_RULES'); ?></a>
            <?php endif; ?>

        </div>
    </nav>


    <div class="tab-content py-4" id="nav-tabContent">
        <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
            <fieldset>
                <div class="formelm form-group">
                    <?php echo $this->form->getLabel('dir_id'); ?>
                    <?php echo $this->form->getInput('dir_id'); ?>
                </div>
                <div class="formelm form-group">
                    <?php echo $this->form->getLabel('title'); ?>
                    <?php echo $this->form->getInput('title'); ?>
                </div>
                <div class="formelm form-group">
                    <?php echo $this->form->getLabel('labels'); ?>
                    <div id="jform_labels_reload">
                        <?php echo $this->form->getInput('labels'); ?>
                    </div>
                </div>
                <div class="formelm form-group">
                    <?php echo $this->form->getLabel('description'); ?>
                    <?php echo $this->form->getInput('description'); ?>
                </div>
                <?php if ($this->item->modified_by) : ?>
                    <div class="formelm form-group">
                        <?php echo $this->form->getLabel('modified_by'); ?>
                        <?php echo $this->form->getInput('modified_by'); ?>
                    </div>
                    <div class="formelm form-group">
                        <?php echo $this->form->getLabel('modified'); ?>
                        <?php echo $this->form->getInput('modified'); ?>
                    </div>
                <?php endif; ?>
            </fieldset>
        </div>

        <?php
        $fieldsets = $this->form->getFieldsets('attribs');
        if (count($fieldsets)) : ?>
            <div class="tab-pane fade" id="nav-note-options" role="tabpanel" aria-labelledby="nav-note-options-tab">
                <?php foreach ($fieldsets as $name => $fieldset) :
                    ?>
                    <fieldset>
                        <?php foreach ($this->form->getFieldset($name) as $field) : ?>
                            <div class="formelm form-group">
                                <?php echo $field->label; ?>
                                <?php echo $field->input; ?>
                            </div>
                        <?php endforeach; ?>
                    </fieldset>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>



        <?php if ($user->authorise('core.admin', 'com_jprepo') || $user->authorise('core.manage', 'com_jprepo')) : ?>
            <div class="tab-pane fade" id="nav-note-permissions" role="tabpanel" aria-labelledby="nav-note-permissions-tab">
                <fieldset>
                    <p><?php echo Text::_('COM_JOOMPROJECT_RULES_LABEL'); ?></p>
                    <p><?php echo Text::_('COM_JOOMPROJECT_RULES_NOTE'); ?></p>
                    <div class="formlm" id="jform_rules_element">
                        <div id="jform_rules_reload">
                            <?php echo $this->form->getInput('rules'); ?>
                        </div>
                    </div>
                </fieldset>
            </div>
        <?php endif; ?>
    </div>


    <div id="jform_access_element">
        <div id="jform_access_reload"><?php echo $this->form->getInput('access'); ?></div>
    </div>

    <?php
        echo $this->form->getInput('project_id');
        echo $this->form->getInput('created');
        echo $this->form->getInput('id');
        echo $this->form->getInput('asset_id');
        echo $this->form->getInput('elements');
    ?>
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="return" value="<?php echo $this->return_page;?>" />
    <input type="hidden" name="view" value="<?php echo htmlspecialchars($this->get('Name'), ENT_COMPAT, 'UTF-8');?>" />
    <input type="hidden" name="filter_parent_id" value="<?php echo intval($this->form->getValue('dir_id'));?>" />
    <?php echo HTMLHelper::_( 'form.token' ); ?>
</form>
</div>
