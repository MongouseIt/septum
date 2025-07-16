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
use Joomla\CMS\Router\Route;

extract($displayData);

$layout = \Joomla\CMS\Factory::getApplication()->isClient('administrator') ? '&layout=import' : '';

$returnLink = base64_decode(Factory::getSession()->get('importReturnLink', ''));

?>
<div class="nav nav-tabs mb-3"  id="nav-tab" role="tablist">
	<a class="nav-link nav-link" href="<?php echo Route::_('index.php?option=com_jptasks&view=import&step=1'); ?>"><?php echo Text::_('COM_JOOMPROJECT_IMPORTUPLOAD')?>
	</a>
	<a class="nav-link nav-link"><?php echo Text::_('COM_JOOMPROJECT_IMPORTCONFIG')?></a>
	<a class="nav-link nav-link active"><?php echo Text::_('COM_JOOMPROJECT_IMPORTFINISH')?></a>
</div>

<?php if (!empty(Factory::getSession()->get('messageState'))) : ?>
	<div class="alert alert-success"><?php echo Factory::getSession()->get('messageState');?></div>
<?php endif; ?>

<table class="table table-bordered table-condensed table-striped">
	<tbody>
	<tr>
		<td><?php echo Text::_('COM_JOOMPROJECT_IMPORT_PROCESSED')  ?></td>
		<td><?php echo $current->statistic->get('total', 0) ?></td>
	</tr>
	<tr>
		<td><?php echo Text::_('COM_JOOMPROJECT_IMPORT_NEW')  ?></td>
		<td><?php echo $current->statistic->get('new', 0)  ?></td>
	</tr>
	<tr>
		<td><?php echo Text::_('COM_JOOMPROJECT_IMPORT_UPDATED')  ?></td>
		<td><?php echo $current->statistic->get('update', 0)  ?></td>
	</tr>
    <tr>
        <td>
            <?php echo Text::_('COM_JOOMPROJECT_IMPORT_SKIPPED') ?>
            <br>
            <small class="text-muted">For empty or already existing items</small>
        </td>
        <td><?php echo $current->statistic->get('exist', 0) + $current->statistic->get('skipped', 0)  ?></td>
    </tr>
	</tbody>
</table>

<div class="rounded p-2 border d-flex justify-content-between bg-light w-100">
    <a  class="btn btn-light border bg-light" href="<?php echo Route::_('index.php?option=com_jptasks&view=import&step=2') ?>">
        <i class="fas fa-arrow-left"></i> <?php echo Text::_('COM_JOOMPROJECT_BACK');?>
    </a>

    <?php if(!empty($returnLink)): ?>
    <a  class="btn btn-light border bg-light" href="<?php echo $returnLink ?>">
        <i class="fas fa-arrow-left"></i> <?php echo Text::_('COM_JOOMPROJECT_FINISH');?>
    </a>
    <?php endif; ?>

</div>