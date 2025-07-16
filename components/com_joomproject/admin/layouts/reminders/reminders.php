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
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Uri\Uri;

// init
$reminders         = $displayData['reminders'];
$item              = $displayData['item'];
$id = Factory::getApplication()->input->get('id',0,'int');

$dataSite = Factory::getApplication()->isClient('site') ? 'site' : 'admin';
$viewType = Factory::getApplication()->isClient('site') ? 'form' : 'reminder';

?>
<?php if(!$id): ?>
    <p class="m-0 alert alert-warning"><i class="fas fa-exclamation-triangle"></i> <?php echo Text::_('COM_JOOMPROJECT_SAVE_TASK_TO_USE_REMINDERS') ?></p>
<?php else: ?>
    <div class="row">
		<?php if (!empty($reminders)): ?>
			<?php foreach ($reminders as $reminder) : ?>

				<?php echo LayoutHelper::render('reminders.item',['reminder' => $reminder]) ?>

			<?php endforeach; ?>
		<?php endif; ?>
    </div>
	<?php
	$item->id = $item->id > 0 ? $item->id : 0;
	$link     = Uri::base()."index.php?option=com_jpreminders&view=$viewType&layout=edit&tmpl=component&task_id=$item->id";
	?>
    <div class="form-group mt-4">
        <a href="<?php echo $link; ?>" data-op-icon="fas fa-plus" data-op-modal="add"
           title="<?php echo Text::_('COM_JPREMINDER_FIELD_ADD_REMINDER') ?>"
           class="btn btn-success btn-sm btn-reminder btn-izimodal"><i
                    class="fas fa-plus"></i> <?php echo Text::_('COM_JOOMPROJECT_FIELD_ADD_REMINDER') ?></a>
    </div>
<?php endif; ?>

