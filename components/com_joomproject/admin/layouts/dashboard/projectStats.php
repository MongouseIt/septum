<?php
/**
 * @package        JoomProject
 * @copyright      2013-2019 JoomBoost, joomboost.com
 * @license        GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
extract($displayData);

// add badges to counts
$pstats = JoomprojectHelper::addBadges($pstats);

?>

<table class="m-0 table table-striped">
	<tr>
		<td class="w-50"><?php echo Text::_('COM_JOOMPROJECT_TODAY') ?></td>
		<td><?php echo $pstats['today'] ?></td>
	</tr>
	<tr>
		<td ><?php echo Text::_('COM_JOOMPROJECT_YESTERDAY') ?></td>
		<td><?php echo $pstats['yesterday'] ?></td>
	</tr>
	<tr>
		<td ><?php echo Text::_('COM_JOOMPROJECT_THISMONTH') ?></td>
		<td><?php echo $pstats['thismonth'] ?></td>
	</tr>
	<tr>
		<td ><?php echo Text::_('COM_JOOMPROJECT_LASTMONTH') ?></td>
		<td><?php echo $pstats['lastmonth'] ?></td>
	</tr>
	<tr>
		<td ><?php echo Text::_('COM_JOOMPROJECT_THISYEAR') ?></td>
		<td><?php echo $pstats['thisyear'] ?></td>
	</tr>
	<tr>
		<td ><?php echo Text::_('COM_JOOMPROJECT_LASTYEAR') ?></td>
		<td><?php echo $pstats['lastyear'] ?></td>
	</tr>
	<tr>
		<td ><?php echo Text::_('COM_JOOMPROJECT_TOTAL') ?></td>
		<td><?php echo $pstats['total'] ?></td>
	</tr>
</table>

