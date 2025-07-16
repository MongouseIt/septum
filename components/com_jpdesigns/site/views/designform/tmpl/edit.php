<?php
/**
 * @package      Joomproject Pro
 * @subpackage   Designs
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2021 JoomBoost. All rights reserved.
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
HTMLHelper::_('bootstrap.dropdown');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('jphtml.script.form');

// Create shortcut to parameters.
$params = $this->state->get('params');
$user   = Factory::getApplication()->getIdentity();
?>
<?php echo LayoutHelper::render('form.pdfpreview') ?>

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

    <form action="<?php echo Route::_('index.php?option=com_jpdesigns&view=designform&id=' . (int) $this->item->id . '&layout=edit'); ?>"
          method="post" name="adminForm" id="item-form" class="form-validate" enctype="multipart/form-data">
        <div class="mb-4">
			<?php echo $this->toolbar; ?>
        </div>
        <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'general']); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('COM_JOOMPROJECT_FIELDSET_DESIGNS')); ?>
        <fieldset>
            <?php if ($this->item->id <= 0) : ?>
                <div class="formelm form-group">
                    <?php echo $this->form->getLabel('project_id'); ?>
                    <?php echo $this->form->getInput('project_id'); ?>
                </div>
            <?php endif; ?>
            <div class="formelm form-group">
                <div>
                    <?php echo $this->form->getLabel('file'); ?>
                    <?php echo $this->form->getInput('file'); ?>
                </div>
            </div>
            <?php if(ComponentHelper::getParams('com_jpdesigns')->get('enable_custom_thumbnail',0)): ?>
            <?php echo $this->form->renderField('thumbnail'); ?>
            <?php endif; ?>
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
                <?php echo $this->form->getInput('description'); ?>
            </div>
        </fieldset>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'publishing', Text::_('COM_JOOMPROJECT_FIELDSET_PUBLISHING')); ?>
        <fieldset>
            <div class="formelm form-group">
                <?php echo $this->form->getLabel('album_id'); ?>
                <div id="jform_album_id_reload">
                    <?php echo $this->form->getInput('album_id'); ?>
                </div>
            </div>
            <div class="formelm form-group">
                <?php echo $this->form->getLabel('state'); ?>
                <?php echo $this->form->getInput('state'); ?>
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
        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'options', Text::_('COM_JOOMPROJECT_DETAILS_FIELDSET')); ?>
            <?php foreach ($fieldsets as $name => $fieldset) : ?>
                <fieldset>
                    <?php foreach ($this->form->getFieldset($name) as $field) : ?>
                        <div class="formelm form-group">
                            <?php echo $field->label; ?>
                        </div>
                        <div>
                            <?php echo $field->input; ?>
                        </div>

                    <?php endforeach; ?>
                </fieldset>
            <?php endforeach; ?>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php endif; ?>
        <?php if ($user->authorise('core.admin', 'com_jpdesigns')) : ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'permissions', Text::_('COM_JOOMPROJECT_FIELDSET_RULES')); ?>
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
        <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

<div id="jform_access_element">
    <div id="jform_access_reload"><?php echo $this->form->getInput('access'); ?></div>
</div>

<?php
echo $this->form->getInput('alias');
echo $this->form->getInput('created');
echo $this->form->getInput('elements');
?>

<input type="hidden" name="task" value=""/>

<input type="hidden" name="return" value="<?php echo $this->return_page; ?>"/>
<input type="hidden" name="view" value="<?php echo htmlspecialchars($this->get('Name'), ENT_COMPAT, 'UTF-8'); ?>"/>
<?php echo HTMLHelper::_('form.token'); ?>
</form>
</div>
