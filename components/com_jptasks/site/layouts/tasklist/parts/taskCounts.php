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

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;

// extract passed data to layout
extract($displayData);

// no need to continue if task counts display on tasklist header disabled
if(!ComponentHelper::getParams('com_jptasks')->get('show_total_tasks_tasklists',0))
    return;

// get counts info
$counts = JoomProject\Tasks\ListCounts::getTaskCounts($id);

// build tooltip summary of counts
$tooltipText = Text::_('COM_JPTASKS_TOTAL').": " . $counts->total . "<br>";
$tooltipText .= Text::_('COM_JPTASKS_PUBLISHED').": " . $counts->published . "<br>";
$tooltipText .= Text::_('COM_JPTASKS_UNPUBLISHED').": " . $counts->unpublished . "<br>";
$tooltipText .= Text::_('COM_JPTASKS_ARCHIVED').": " . $counts->archived . "<br>";
$tooltipText .= Text::_('COM_JPTASKS_TRASHED').": " . $counts->trashed;

?>
<small class="ms-2 hasTooltip " title="<?php echo $tooltipText ?>" data-bs-toggle="tooltip">
    <b>(<?php echo $counts->total ?>)</b>
</small>

