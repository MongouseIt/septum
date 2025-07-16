<?php
/**
 * @package      Joomproject
 * @subpackage   Repository
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;


$project = (int) $this->state->get('filter.project');
$dir     = (int) $this->state->get('filter.parent_id');

if ($project && $dir) :
?>
    <fieldset class="batch">
    	<legend><?php echo Text::_('COM_JOOMPROJECT_BATCH_OPTIONS');?></legend>
    	<?php echo HTMLHelper::_('jprepo.batchItem', $project, $dir);?>
    	<button type="submit" class="btn btn-primary" onclick="Joomla.submitbutton('repository.batch');">
    		<?php echo Text::_('JGLOBAL_BATCH_PROCESS'); ?>
    	</button>
    	<button type="button" class="btn" onclick="document.getElementById('batch-category-id').value='';document.getElementById('batch-access').value='';">
    		<?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>
    	</button>
    </fieldset>
<?php
endif;