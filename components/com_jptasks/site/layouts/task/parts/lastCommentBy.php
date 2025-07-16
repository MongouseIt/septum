<?php
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

extract($displayData);

// Check if data exists
if (empty($item->last_comment_by) || !isset($item->last_comment_by)) {
	return;
}
?>

<div class="text-success hasTooltip" title="<?php echo Text::_('COM_JPTASKS_LAST_COMMENT_BY') ?> <?php echo Factory::getUser($item->last_comment_by)->name ?> (<?php echo Factory::getUser($item->last_comment_by)->username ?>) <?php echo Text::_('COM_JPTASKS_AT_DATE') ?> <?php echo HTMLHelper::_('date', $item->last_comment_date, Text::_('DATE_FORMAT_LC5')); ?>">
	<small class="me-3">
		<i class="fas fa-comment-dots"></i> <?php echo Factory::getUser($item->last_comment_by)->name ?>
	</small>
	<small>
		<i class="fas fa-clock"></i> <?php echo HTMLHelper::_('date', $item->last_comment_date, Text::_('DATE_FORMAT_LC5')); ?>
	</small>
</div>