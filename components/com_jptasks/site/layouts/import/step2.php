<?php
/**
 * JoomCRM
 * @author     JoomBoost <support@joomboost.com>
 * @copyright  Copyright (C) 2018 Joomboost.com All Rights Reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Website: https://www.joomboost.com
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

extract($displayData);

// get current selected preset id by type
$selectedPreset = Factory::getSession()->get("selectedPreset");

$typeId = 1; // tasks are type with id 1

if (isset($selectedPreset['type_id']) && $selectedPreset['type_id'] == $typeId) {
    $currentPreset = $selectedPreset['id'];
} else {
    $currentPreset = '';
}

$layout = \Joomla\CMS\Factory::getApplication()->isClient('administrator') ? '&layout=import' : '';


?>
<div class="nav nav-tabs mb-3" id="nav-tab" role="tablist">
    <a class="nav-link nav-link"
       href="<?php echo Route::_('index.php?option=com_jptasks&view=import'); ?>"><?php echo Text::_('COM_JOOMPROJECT_IMPORTUPLOAD') ?>
    </a>
    <a class="nav-link nav-link active "><?php echo Text::_('COM_JOOMPROJECT_IMPORTCONFIG') ?></a>
    <a class="nav-link nav-link"><?php echo Text::_('COM_JOOMPROJECT_IMPORTFINISH') ?></a>
</div>

<div>
    <div class="card">
        <div class="card-header m-0 border-bottom d-flex justify-content-between align-items-center">
            <h3 class="m-0"><i class="fa fa-save"></i> <?php echo Text::_('COM_JOOMPROJECT_PRESETS') ?></h3>
            <a class="btn btn-outline-success" href="<?php echo Route::_('index.php?option=com_jptasks&view=import&step=3&preset=new') ?>"><i class="fas fa-plus"></i> Add New</a>
        </div>

        <?php if (count($current->presets) > 0): ?>
        <ul class="list-group list-group-flush">

                <?php foreach ($current->presets as $preset): ?>
                    <li class="list-group-item d-flex justify-content-between">

                        <div>
                            <a href="<?php echo Route::_('index.php?option=com_jptasks&view=import&step=3&preset='.$preset->value) ?>" class="btn btn-outline-success me-5">Use this preset</a>
                            <span><?php echo $preset->text ?></span>
                        </div>

                        <a      href="<?php echo Route::_('index.php?option=com_jptasks&view=import&task=import.deletePreset&preset='.$preset->value) ?>"
                                class="btn btn-danger"
                                title="<?php echo Text::_('COM_JOOMPROJECT_DELETE_PRESET') ?>"
                                data-bs-toggle="tooltip"
                        >
                            <i class="fas fa-times"></i>
                        </a>

                    </li>
                <?php endforeach; ?>

        </ul>
        <?php else: ?>
            <a class="btn btn-outline-success" href="<?php echo Route::_('index.php?option=com_jptasks&view=import&step=3&preset=new') ?>"><i class="fas fa-plus"></i> Add your first preset</a>
        <?php endif; ?>
    </div>
</div>