<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_joomproject
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2019 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */
# No Permission
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

defined( '_JEXEC' ) or die ;

extract($displayData);

// no need to display if task has not author
if(empty($item->created_by))
    return;

?>

<div class="text-success hasTooltip" title="<?php echo Text::_('COM_JPTASKS_CREATED_BY') ?> <?php echo Factory::getUser($item->created_by)->name ?> (<?php echo Factory::getUser($item->created_by)->username ?>) <?php echo Text::_('COM_JPTASKS_AT_DATE') ?> <?php echo HTMLHelper::_('date', $item->created, Text::_('DATE_FORMAT_LC5')); ?>">
    <small class="me-3">
        <i class="fas fa-user-plus"></i> <?php echo Factory::getUser($item->created_by)->name ?>
    </small>
    <small>
        <i class="fas fa-calendar-plus"></i> <?php echo HTMLHelper::_('date', $item->created, Text::_('DATE_FORMAT_LC5')); ?></small>
</div>