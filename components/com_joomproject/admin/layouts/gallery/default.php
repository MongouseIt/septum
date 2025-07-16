<?php

/**
 * @package      Joomproject
 * @subpackage   Projects
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

extract($displayData);

if (!$items || !is_array($items) || !count($items))
{
	return;
}
/*
if (!$readonly && !$disabled)
{

}
*/

$load_stylesheet = 1;
$load_css_vars = true;
$css_class=" lightbox";
$gallery_items_css= $style;
$atts="";
$atts = [];


$atts[] = 'data-id="' . $id . '"';

if ($style === 'justified' && $justified_item_height)
{
	$atts[] = 'data-item-height="' . $justified_item_height . '"';
}

$atts  = implode(' ', $atts);


HTMLHelper::script(Uri::root().'media/com_joomproject/js/gallery.js', ['relative' => true, 'version' => 'auto']);
if ($load_stylesheet)
{
	HTMLHelper::stylesheet(Uri::root().'media/com_joomproject/css/gallery.css', ['relative' => true, 'version' => 'auto']);
}

if ($style === 'justified')
{
    HTMLHelper::script('com_joomproject/justified.layout.min.js', ['relative' => true, 'version' => 'auto']);
    HTMLHelper::script('com_joomproject/justified.js', ['relative' => true, 'version' => 'auto']);
}

if ($load_css_vars && !empty($custom_css))
{
	Factory::getDocument()->addStyleDeclaration($custom_css);
}

// Add global CSS vars
$global_css = '.nrf-widget.tf-gallery-wrapper.' . $id . ' {
    --mobile-tags-default-style: ' . ($tags_mobile === 'show' ? 'flex' : 'none') . ';
    --mobile-tags-dropdown-style: ' . ($tags_mobile === 'dropdown' ? 'flex' : 'none') . ';
}';
Factory::getDocument()->addStyleDeclaration($global_css);
?>

<div class="jp-gallery-container my-4">
    <div class="nrf-widget tf-gallery-wrapper <?php echo $id.' '.$css_class; ?>" <?php echo $atts; ?>>
		<?php if ($tags_position === 'above'): ?>
			<?php echo $this->sublayout('tags', $displayData); ?>
		<?php endif; ?>

        <div class="gallery-items <?php echo $gallery_items_css; ?>">
			<?php
			foreach ($items as $index => $item)
			{
				// If its an invalid image path, show a warning and continue
				if (isset($item['invalid']) && $show_warnings)
				{
					echo '<div><strong>Warning:</strong> ' . sprintf(Text::_('COM_JOOMPROJECT_GALLERY_INVALID_IMAGE_PATH'), $item['path']) . '</div>';
					continue;
				}

				$item['index'] = $index;
				$displayData['item'] = $item;
				echo $this->sublayout('item', $displayData);
			}
			?>
        </div>

		<?php if ($tags_position === 'below'): ?>
			<?php echo $this->sublayout('tags', $displayData); ?>
		<?php endif; ?>

		<?php
		if ($lightbox)
		{
			echo $this->sublayout('glightbox', $displayData);
		}
		?>
    </div>
</div>
