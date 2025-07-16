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
defined( '_JEXEC' ) or die ;

use Joomla\CMS\Language\Text;

extract($displayData);

?>
<div class='px-4' data-quicklog="true">
	<div class="mb-1"><?php echo Text::_('COM_JOOMPROJECT_ADD_LOG_TIME') ?></div>
	<div class="input-group mb-1">
		<input type="number" min="1" max="1440" class="form-control form-control-sm" value="1">
			<button class="btn btn-sm btn-outline-secondary" data-tasktitle="<?php echo $task->title ?>" data-taskid="<?php echo $task->id ?>" type="button"><i class="fas fa-plus"></i></button>
	</div>
	<small class="text-muted"><?php echo Text::_('COM_JOOMPROJECT_IN_MINUTES') ?></small>
</div>



