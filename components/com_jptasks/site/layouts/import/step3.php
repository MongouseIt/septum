<?php
/**
 * by JoomBoost
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: http://www.joomboost.com/
 * @copyright Copyright (C) 2012 JoomBoost (http://www.mintjoomla.com). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die();

use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

extract($displayData);

array_unshift($current->heads, Text::_('COM_JOOMPROJECT_IMPORTSELECTCOLUMN'));


// Load the tooltip uitab.
HTMLHelper::_('bootstrap.tooltip');

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = Factory::getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate');

HTMLHelper::_('jphtml.script.form');

$jinput = Factory::getApplication()->input;
$globalParams = ComponentHelper::getParams('com_joomcrm');

$typeId = 1; // tasks are type with id 1

$InsertType = [
    0 => Text::_('COM_JOOMPROJECT_SKIP_ITEM'),
    1 => Text::_('COM_JOOMPROJECT_UPDATE_ITEM'),
    2 => Text::_('COM_JOOMPROJECT_DUPLICATED_ITEM')
];

$selectInsertType = !empty(Factory::getSession()->get("messageInsertType")) ? Factory::getSession()->get("messageInsertType") : array_keys($InsertType)[0];

$form = Factory::getContainer()->get(FormFactoryInterface::class)->createForm("import", ['control' => 'jform']);
$form->loadFile(JPATH_ADMINISTRATOR . '/components/com_jptasks/models/forms/import.xml');

// load data
$preset = Factory::getApplication()->getInput()->get('preset');

if($current->preset) {
    $form->bind($current->preset->params);
}

$taskViewName = Factory::getApplication()->isClient('administrator') ? 'task' : 'taskform';


?>

<div class="nav nav-tabs mb-3" id="nav-tab" role="tablist">
    <a class="nav-link nav-link"
       href="<?php echo Route::_('index.php?option=com_jptasks&view=import'); ?>"><?php echo Text::_('COM_JOOMPROJECT_IMPORTUPLOAD') ?>
    </a>
    <a class="nav-link nav-link active"><?php echo Text::_('COM_JOOMPROJECT_IMPORTCONFIG') ?></a>
    <a class="nav-link nav-link"><?php echo Text::_('COM_JOOMPROJECT_IMPORTFINISH') ?></a>
</div>

<form action="<?php echo Route::_('index.php?option=com_jptasks&view=import'); ?>"
      method="post" name="adminForm" id="item-form" class="form-validate">

    <div id="preset-form">
        <div class="card my-4">
            <div class="card-header border-bottom">
                <h3 class="m-0"><i class="fa fa-cog"></i> <?php echo Text::_('COM_JOOMPROJECT_IMPORTPARAMS') ?></h3>
            </div>
            <div class="card-body">
                <?php if ($jinput->input->get('type') != 2) : ?>
                    <div class="control-group">
                        <div class="control-label">
                            <label>
                                <?php echo Text::_('COM_JOOMPROJECT_ITEM_ALREADY_EXIST') ?>
                            </label>
                        </div>
                        <div class="control-group">
                            <?php
                            echo HTMLHelper::_('select.genericlist', $InsertType, 'InsertType', 'class="form-select"', 'value', 'text', $selectInsertType); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php echo $form->renderField('name'); ?>

                <?php echo $form->renderField('project_id'); ?>

                <?php if (JPApplicationHelper::enabled('com_jpmilestones')) : ?>
                    <div class="control-group">
                        <div class="control-label">
                            <?php echo $form->getLabel('milestone_id'); ?>
                        </div>
                        <div class="controls">
                            <div id="jform_milestone_id_reload">
                                <?php echo $form->getInput('milestone_id'); ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="control-group">
                    <div class="control-label">
                        <?php echo $form->getLabel('list_id'); ?>
                    </div>
                    <div class="controls">
                        <div id="jform_list_id_reload">
                            <?php echo $form->getInput('list_id'); ?>
                        </div>
                    </div>
                </div>
                <?php echo $form->getInput('elements'); ?>
                <?php echo HTMLHelper::_('form.token'); ?>

            </div>
        </div>

        <div class="card">
            <div class="card-header border-bottom">
                <h3 class="m-0"><i class="fa fa-columns"></i> <?php echo Text::_('COM_JOOMPROJECT_IMPORTFIELDASSOC') ?></h3>
            </div>
            <div class="card-body">
                <div class="row">

                    <?php

                    foreach ($current->columns as $field):

                        $required = $field->required ? 'required' : null;
                        $default = isset($current->selectedFields[$field->name]) ? $current->selectedFields[$field->name] : 0;

                        ?>

                        <div class="col-md-4">
                            <div class="form-group <?php echo $required; ?>">
                                <label for="type">
                                    <?php echo Text::_($field->label); ?>
                                    <?php if ($required): ?>
                                        <span class="float-end" rel="tooltip"
                                              data-original-title="<?php echo Text::_('CREQUIRED') ?>">
                                    *
                                </span>
                                    <?php endif; ?>
                                </label>
                                <?php
                                $add_name = "";
                                echo HTMLHelper::_(
                                    'select.genericlist',
                                    $current->heads,
                                    "jform[field][$field->name]",
                                    'class="form-control" id="jform_field_' . $field->name . '" ' . $required,
                                    'text',
                                    'value',
                                    Factory::getApplication()->input->get('preset', '') == "new" ? 0 : $default
                                );
                                ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                </div>
            </div>
        </div>

    </div>

    <input type="hidden" id="taskInput" name="task" value=""/>
    <input type="hidden" id="viewInput" name="view" value="<?php echo $taskViewName ?>"/>
    <input type="hidden" name="step" value="4">
    <input type="hidden" name="preset" value="<?php echo Factory::getApplication()->getInput()->get('preset') ?>">
    <input type="hidden" name="type" value="<?php echo $typeId ?>">
    <input type="hidden" name="key" value="<?php echo Factory::getApplication()->input->get('key'); ?>">

</form>


<div class="d-flex justify-content-between w-100 mt-3">
    <a class="btn btn-light border bg-light" href="<?php echo Route::_('index.php?option=com_jptasks&view=import&step=2') ?>">
        <i class="fas fa-arrow-left"></i> <?php echo Text::_('COM_JOOMPROJECT_BACK'); ?>
    </a>
    <button class="btn btn-primary" type="button" id="next-step"><?php echo Text::_('COM_JOOMPROJECT_NEXT') ?> <i
                class="fas fa-arrow-right"></i> (<?php echo Text::_('COM_JOOMPROJECT_IMPORTFINISH') ?>)
    </button>
</div>

<script>
    (function ($) {

        // next step to import
        $('#next-step').bind('click', function (event) {

            var submit = true;

            // make sure import name not empty
            if ($('#jform_importname').val() == 0) {
                alert('<?php echo Text::_('COM_JOOMPROJECT_ENTER_PRESET_NAME') ?>');
                submit = false;
                return false;
            }

            // make sure project selected cuz required
            if ($('#jform_project_id_id').val() == 0) {
                alert('<?php echo Text::_('COM_JPTASKS_IMPORT_SELECT_PROJECT') ?>');
                submit = false;
                return false;
            }

            // check required association fields
            $.each($('div.required select'), function() {
                if($(this).val() == 0) {
                    alert('<?php echo Text::_('COM_JOOMPROJECT_REQUIRED_FIELDS_NOT_SET') ?>');
                    submit = false;
                    return false;
                }
            });

            // submit form if all good
            if (submit) {

                $('#item-form #viewInput').val('import')
                $('#item-form #taskInput').val('import.import')

                $('#item-form').submit();
            }
        });

    }(jQuery))
</script>

