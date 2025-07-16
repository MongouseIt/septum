<?php

/**
 * @package      Joomproject
 * @subpackage   Projects
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

extract($displayData);

if (!isset($choices) || !is_array($choices) || !count($choices))
{
	return;
}

$mode = isset($mode) ? $mode : 'svg';
$gap = isset($gap) && !empty($gap) ? $gap : 'inherit';
$class = isset($class) ? ' ' . $class : '';
$columns = isset($columns) ? $columns : null;
$id = isset($id) ? $id : null;

$class .= ' ' . $id;

if ($columns)
{
	Factory::getDocument()->addStyleDeclaration('
		.tf-choiceselector-control.' . $id . ' {
			--columns: ' . $columns . ';
			--gap: ' . $gap . ';
		}'
	);
}

HTMLHelper::stylesheet('com_joomproject/choiceselector.css', ['relative' => true, 'version' => 'auto']);
?>
<div class="tf-choiceselector-control mode-<?php echo $mode; ?><?php echo $class; ?>">
	<?php
		$i = 0;
		foreach ($choices as $key => $_value)
		{
			$_value = !is_string($_value) ? (array) $_value : $_value;
			
			$id = $name . '_' . (empty($item_id) ? $key : $item_id);
			
			$image = isset($_value['image']) ? $_value['image'] : false;
			$icon = isset($_value['icon']) ? $_value['icon'] : false;
			$label = isset($_value['label']) ? $_value['label'] : $_value;
			$pro = isset($_value['pro']) ? (bool) $_value['pro'] : false;
			?>
			<div class="tf-choiceselector-control--item<?php echo $pro ? ' pro' : ''; ?>"<?php echo $pro ? ' data-pro-only="' . Text::_($label) . '"' : ''; ?>>
					<?php echo $pro ? '<span class="pro">' . Text::_('COM_JOOMPROJECT_GALLERY_PRO') . '</span>' : ''; ?>
					
					<input type="radio" id="fpf-control-input-item_<?php echo $id; ?>" name="<?php echo $name; ?>" value="<?php echo $key; ?>"<?php echo $value == $key ? ' checked="checked"' : ''; ?> />
					<label for="fpf-control-input-item_<?php echo $id; ?>">
						<?php echo $mode == 'svg' && !empty($icon) ? $icon : ''; ?>
						<span class="text"><?php echo Text::_($label); ?></span>
					</label>
			</div>
			<?php
			$i++;
		}
	?>
</div>