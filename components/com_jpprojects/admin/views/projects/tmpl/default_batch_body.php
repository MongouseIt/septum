<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

/** @var \Joomla\Component\Content\Administrator\View\Articles\HtmlView $this */

$params = ComponentHelper::getParams('com_jpprojects');

$published = (int) $this->state->get('filter.published');

$user = $this->getCurrentUser();


// Create the calendar input field
$startDateField = HTMLHelper::calendar('','batch[start_date]','batch-start-date','',['showTime' => true]);


?>

<div class="p-3">
    <div class="row">
        <?php if (Multilanguage::isEnabled()) : ?>
            <div class="form-group col-md-6">
                <div class="controls">
                    <?php echo LayoutHelper::render('joomla.html.batch.language', []); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <div class="row mb-5">
        <?php if ($published >= 0) : ?>
        <div class="form-group col-md-6">
            <div class="controls">
                <?php echo LayoutHelper::render('joomla.html.batch.item', ['extension' => 'com_jpprojects']); ?>
            </div>
        </div>
        <?php endif; ?>
        <div class="form-group col-md-6">
            <div class="controls">
                <label for="batch-start-date">Start Date</label>
                <?php echo $startDateField ?>
                <small class="text-muted">Leave it empty if you want to use original project start date</small>
            </div>

        </div>
    </div>
</div>
<div class="btn-toolbar p-3">
    <joomla-toolbar-button task="project.batch" class="ms-auto">
        <button type="button" class="btn btn-success"><?php echo Text::_('JGLOBAL_BATCH_PROCESS'); ?></button>
    </joomla-toolbar-button>
</div>
