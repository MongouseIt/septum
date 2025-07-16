<?php
/**
 * @package      Joomproject
 * @subpackage   Projects
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Uri\Uri;

class JoomprojectGalleryHelper
{
	/**
	 * Duplicates files.
	 * 
	 * @param   array  $value
	 * 
	 * @return  void
	 */
	public static function duplicateFiles(&$value = [])
	{
		if (!is_array($value) || !count($value))
		{
			return;
		}

		if (!isset($value['items']) || !is_array($value['items']) || !count($value['items']))
		{
			return;
		}

		foreach ($value['items'] as &$item)
		{
			if ($newSourcePath = self::duplicateGalleryItemFile('source', $item))
			{
				$item['source'] = $newSourcePath;
			}

			if ($newImagePath = self::duplicateGalleryItemFile('image', $item))
			{
				$item['image'] = $newImagePath;
			}

			if ($newThumbnailPath = self::duplicateGalleryItemFile('thumbnail', $item))
			{
				$item['thumbnail'] = $newThumbnailPath;
			}

			if ($newSlideshowPath = self::duplicateGalleryItemFile('slideshow', $item))
			{
				$item['slideshow'] = $newSlideshowPath;
			}
		}
	}

	/**
	 * Duplicates files item.
	 * 
	 * @param   array  $value
	 * 
	 * @return  void
	 */
	private static function duplicateGalleryItemFile($key, $item)
	{
		// Duplicate the source image
		if (isset($item[$key]) && !empty($item[$key]))
		{
			// Original file path
			$path = implode(DIRECTORY_SEPARATOR, [JPATH_SITE, $item[$key]]);

			if (!file_exists($path))
			{
				return;
			}

			// New file path
			$newPath = JoomprojectFile::copy($path, $path);

			return str_replace([JPATH_SITE, JPATH_ROOT], '', $newPath);
		}
	}
	
	/**
	 * Parses and returns the style.
	 * 
	 * @param   string  $style
	 * 
	 * @return  string
	 */
	public static function getStyle($style)
	{
		// Remove 'z' character from "zjustified" style
		// This is done for previewing purposes to display it at the end.
		return ltrim($style, 'z');
	}
	
	/**
	 * Prepares the Gallery Manager Widget uploaded files prior to being passed
	 * to the Gallery Widget to display the Gallery on the front-end.
	 * 
	 * @param   array  $items
	 * 
	 * @return  array
	 */
	public static function prepareItems($items)
	{
		$tagsHelper = new TagsHelper();
		
		$parsedTagIds = [];
		
		foreach ($items as $key => &$item)
		{
			// Skip items that have not saved properly(items were still uploading and we saved the item)
			if ($key === 'ITEM_ID')
			{
				unset($items[$key]);
				continue;
			}

			// Get tag names from stored IDs
			$itemTags = [];

			$tags = isset($item['tags']) ? $item['tags'] : [];
			if (is_array($tags) && count($tags))
			{
				foreach ($tags as $tagId)
				{
					if (isset($parsedTagIds[$tagId]))
					{
						$itemTags[] = $parsedTagIds[$tagId];
					}
					else
					{
						if (!$tag = $tagsHelper->getTagNames([$tagId]))
						{
							continue;
						}
						
						$itemTags[] = $tag[0];
						$parsedTagIds[$tagId] = $tag[0];
					}
				}
			}

			$item = array_merge($item, [
				'url' =>  Uri::root() . $item['image'],
				'thumbnail_url' => Uri::root() . $item['thumbnail'],
				'tags' => $itemTags
			]);
		}

		return $items;
	}
}

