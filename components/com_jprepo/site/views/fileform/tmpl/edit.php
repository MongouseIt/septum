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
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Component\ComponentHelper;


/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate');
$this->useCoreUI = true;
\Joomla\CMS\HTML\HTMLHelper::_('bootstrap.dropdown');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('jphtml.script.form');

// Create shortcut to parameters.
$params = $this->state->get('params');
$user   = Factory::getApplication()->getIdentity();

$allowed      = JPrepoHelper::getAllowedFileExtensions();
$config       = ComponentHelper::getParams('com_jprepo');
$filter_admin = $config->get('filter_ext_admin');
$is_admin     = $user->authorise('core.admin');

// Restrict file extensions?
$txt_upload = '';

if ($is_admin && !$filter_admin) $allowed = array();

if (count($allowed)) {
    $txt_upload = Text::_('COM_JOOMPROJECT_UPLOAD_ALLOWED_EXT') . ' ' . implode(', ', $allowed);
}
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
	// load internal project nav
	echo JPhtmlNav::loadProject();
	?>

<form action="<?php echo Route::_('index.php?option=com_jprepo&view=fileform&id=' . (int) $this->item->id . '&layout=edit'); ?>" method="post" name="adminForm" id="item-form" class="form-validate" enctype="multipart/form-data">
    <div class="mb-4">
        <?php echo $this->toolbar; ?>
    </div>

        <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'general', 'recall' => true, 'breakpoint' => 768]); ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', '<i class="fas fa-file"></i> '.Text::_('COM_JOOMPROJECT_FIELDSET_FILE')); ?>
            <fieldset>
                <div class="formelm form-group">
                    <?php echo $this->form->getLabel('dir_id'); ?>
                    <?php echo $this->form->getInput('dir_id'); ?>
                </div>
                <div class="formelm form-group">
                    <?php echo $this->form->getLabel('file'); ?>
                    <?php echo $this->form->getInput('file'); ?>
                </div>
                <?php if (count($allowed)) : ?>
                    <div class="formelm form-group small"><?php echo $txt_upload; ?></div>
                <?php endif; ?>
                <div class="formelm form-group">
                    <?php echo $this->form->getLabel('title'); ?>
                    <?php echo $this->form->getInput('title'); ?>
                </div>
                <div class="formelm form-group">
                    <?php echo $this->form->getLabel('description'); ?>
                    <?php echo $this->form->getInput('description'); ?>
                </div>
                <div class="formelm form-group">
                    <?php echo $this->form->getLabel('labels'); ?>
                    <div id="jform_labels_reload">
                        <?php echo $this->form->getInput('labels'); ?>
                    </div>
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
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php
        $fieldsets = $this->form->getFieldsets('attribs');
        if (count($fieldsets)) : ?>
           <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', '<i class="fas fa-list"></i> '.Text::_('COM_JOOMPROJECT_FIELDSET_DETAILS')); ?>
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
            <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php endif; ?>



        <?php if ($user->authorise('core.admin', 'com_jprepo') || $user->authorise('core.manage', 'com_jprepo')) : ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'permissions', '<i class="fas fa-lock"></i> '.Text::_('COM_JOOMPROJECT_FIELDSET_RULES')); ?>
                <fieldset>
                    <p><?php echo Text::_('COM_JOOMPROJECT_RULES_LABEL'); ?></p>
                    <p><?php echo Text::_('COM_JOOMPROJECT_RULES_NOTE'); ?></p>
                    <div class="formlm" id="jform_rules_element">
                        <div id="jform_rules_reload">
                            <?php echo $this->form->getInput('rules'); ?>
                        </div>
                    </div>
                </fieldset>
            <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php endif; ?>
        <?php $this->ignore_fieldsets = array('project','basic','attribs','general','publishing','labels', 'attachements', 'permissions'); ?>
        <?php echo LayoutHelper::render('joomla.edit.params', $this); ?>
        <?php echo HTMLHelper::_('uitab.endTabSet'); ?>




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
