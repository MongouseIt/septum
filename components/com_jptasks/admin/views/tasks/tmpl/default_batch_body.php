<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
$published = $this->state->get('filter.published');



// Create the copy/move options.
$options = array(
    HTMLHelper::_('select.option', 'c', Text::_('JLIB_HTML_BATCH_COPY')),
    HTMLHelper::_('select.option', 'm', Text::_('JLIB_HTML_BATCH_MOVE'))
);

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->useScript('joomla.batch-copymove');

// Create the calendar input field
$endDateField = HTMLHelper::calendar('','batch[mass_action][end_date]','batch-end-date','',['showTime' => true]);


?>

<div class="p-3">
    <div class="row">
        <?php if ($published >= 0 || empty($published)) : ?>
            <div class="form-group col-md-6">
                <div class="controls">
                    <label id="batch-choose-action-lbl" for="batch-category-id">
                        <?php echo Text::_('JLIB_HTML_BATCH_MENU_LABEL'); ?>
                    </label>
                    <div id="batch-choose-action" class="control-group">
                        <select name="batch[project_id]" class="form-select" id="batch-category-id">
                            <option value=""><?php echo Text::_('JLIB_HTML_BATCH_NO_CATEGORY'); ?></option>
                            <?php echo HTMLHelper::_('select.options', HTMLHelper::_('jphtml.project.options')); ?>
                        </select>
                    </div>
                    <div id="batch-copy-move" class="control-group radio">
                        <fieldset id="batch-copy-move-id">
                            <legend>
                                <?php echo Text::_('JLIB_HTML_BATCH_MOVE_QUESTION'); ?>
                            </legend>
                            <?php echo HTMLHelper::_('select.radiolist', $options, 'batch[move_copy]', '', 'value', 'text', 'm'); ?>
                        </fieldset>
                    </div>
                </div>
            </div>

            <div class="form-group col-md-6">
                <div id="batch-choose-action" class="controls">
                    <label id="batch-choose-action-lbl" for="batch-category-id">
                        <?php echo Text::_('COM_JOOMPROJECT_FIELD_PRIORITY_LABEL'); ?>
                    </label>
                    <div id="batch-choose-priority" class="control-group">
                        <select name="batch[mass_action][priority]" class="form-select" id="batch-priority-id">
                            <option value=""><?php echo Text::_('JOPTION_SELECT_PRIORITY'); ?></option>
                            <option value="1"><?php echo Text::_('COM_JOOMPROJECT_PRIORITY_VERY_LOW'); ?></option>
                            <option value="2"><?php echo Text::_('COM_JOOMPROJECT_PRIORITY_LOW'); ?></option>
                            <option value="3"><?php echo Text::_('COM_JOOMPROJECT_PRIORITY_MEDIUM'); ?></option>
                            <option value="4"><?php echo Text::_('COM_JOOMPROJECT_PRIORITY_HIGH'); ?></option>
                            <option value="5"><?php echo Text::_('COM_JOOMPROJECT_PRIORITY_VERY_HIGH'); ?></option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group col-md-6">
                <div class="controls">
                    <label for="batch-start-date">End Date</label>
                    <?php echo $endDateField ?>
                </div>
            </div>

        <?php endif; ?>
    </div>
</div>

<div class="p-3 d-flex justify-content-end border-top">
    <?php echo $this->loadTemplate('batch_footer'); ?>
</div>


