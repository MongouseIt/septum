<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_joomproject
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2019 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;

$JoomprojectParams 	= ComponentHelper::getParams('com_joomproject');
$layoutField = $JoomprojectParams->get('style_field_layout',0);

if (!key_exists('field', $displayData))
{
	return;
}

$field = $displayData['field'];
$label = Text::_($field->label);
$value = $field->value;
$showLabel = $field->params->get('showlabel');
$labelClass = $field->params->get('label_render_class');

if ($value == '')
{
	return;
}

?>
<?php if ($layoutField == 0) :?>
<?php if ($showLabel == 1) : ?>
	<span class="field-label <?php echo $labelClass; ?>">
        <?php echo htmlentities($label, ENT_QUOTES | ENT_IGNORE, 'UTF-8'); ?>:
    </span>
<?php endif; ?>
<span class="field-value">
    <?php echo $value; ?>
</span>
<?php else : ?>
    <?php if ($showLabel == 1) : ?>
    <td width="10%"><span class="field-label <?php echo $labelClass; ?>">
        <?php echo htmlentities($label, ENT_QUOTES | ENT_IGNORE, 'UTF-8'); ?>:
    </span></td>
<?php endif; ?>
<td><span class="field-value">
    <?php echo $value; ?>
</span> </td>
 <?php endif; ?>
