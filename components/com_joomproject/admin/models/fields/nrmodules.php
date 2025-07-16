<?php

/**
 * @package      Joomproject
 * @subpackage   Projects
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;


class JFormFieldNRModules extends \Joomla\CMS\Form\Field\ListField
{
	/**
	 * Provide a list of all published modules.
	 *
	 * @return   array   An array of options.
	 */
	protected function getOptions()
	{
		// Get modules
		$modules = $this->getModules();

		// get all position options
		$options = [];

		$options[] = HTMLHelper::_('select.option', '',  Text::_('COM_JOOMPROJECT_GALLERY_NONE_SELECTED'));
		foreach ($modules as $module) {
			$options[] = HTMLHelper::_('select.option', $module->id, $module->title . ' (' . $module->id . ')');
		}

		return array_merge(parent::getOptions(), $options);
	}

	/**
	 * Returns all enabled modules.
	 *
	 * @return  object
	 */
	private function getModules()
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true);
		$query->select('id, title');
		$query->from('#__modules');
		$query->where('published = 1');
		$query->where('client_id = 0');
		$db->setQuery($query);

		return $db->loadObjectList();
	}
}