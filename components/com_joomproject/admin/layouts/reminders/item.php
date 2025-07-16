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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

extract($displayData);

$dataSite = Factory::getApplication()->isClient('site') ? 'site' : 'admin';
$viewType = Factory::getApplication()->isClient('site') ? 'form' : 'reminder';

$users_extract = implode(',', (array) json_decode($reminder->assigned_users, true));
$users = explode(',', $users_extract);
$link = Uri::base()."index.php?option=com_jpreminders&view=$viewType&layout=edit&id=$reminder->id&tmpl=component&task_id=$reminder->task_id";

$reminderDateModel = BaseDatabaseModel::getInstance('Reminder', 'JPremindersModel', array('ignore_request' => true));

?>


<div id="reminder-<?php echo $reminder->id ?>" class="col-md-3 reminderItem mb-3">
    <div class="card">
        <div class="btn-group">
            <a class="btn btn-light btn-sm btn-izimodal" data-op-icon="fas fa-edit" data-op-modal="edit"
               title="<?php echo Text::_('COM_JPREMINDERS_EDIT') ?>" href="<?php echo $link; ?>"
               id="editReminder"><i class="fas fa-edit"></i></a>
            <a class="btn btn-light btn-sm text-danger deleteReminder" data-site="<?php echo $dataSite ?>"
               id="deleteReminder-<?php echo $reminder->id ?>"
               href="#"
            >
                <i class="fas fa-times"></i>
            </a>
        </div>
        <div class="card-body">
            <p class="card-text">
				<?php echo $reminder->description ?>
            </p>
        </div>
        <table class="table mb-0">
            <tr>
                <td><i class="fas fa-calendar"></i> <?php echo Text::_('COM_JPTAKS_START_DATE'); ?></td>
                <td><?php echo $reminder->start_date ?></td>
            </tr>
            <tr>
                <td><i class="fas fa-redo"></i> <?php echo Text::_('COM_JPTAKS_REPEATS'); ?></td>
                <td>
                    <span class="badge bg-primary bg-pill"><?php echo $reminderDateModel->reminderDate($reminder->id)->total ?></span>
                </td>
            </tr>
            <tr>
                <td><i class="fas fa-users"></i> <?php echo Text::_('COM_JPTAKS_ASSIGNED_TO'); ?></td>
                <td>
					<?php if (is_null($users_extract) or empty($users_extract)) : ?>
                        <span class="badge bg-dark p-2"><?php echo Text::_('COM_JPREMINDER_ALL_USERS'); ?></span>
					<?php else : ?>
						<?php foreach ($users as $user) : ?>
                            <span class="badge bg-dark p-2"><?php echo Factory::getUser($user)->name ?></span>
						<?php endforeach; ?>
					<?php endif; ?>
                </td>
            </tr>
        </table>
    </div>
</div>
