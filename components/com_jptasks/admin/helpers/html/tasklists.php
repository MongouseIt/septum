<?php
/**
 * @package      Joomproject
 * @subpackage   Tasks
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;

jimport('joomla.application.component.model');


abstract class JHtmlTasklists{

	static public function options(){

		$db = Factory::getDbo();
		$user = Factory::getApplication()->getIdentity();

		$query   = $db->getQuery(true);

		$query->select('id AS value, title AS text')
			->from('#__jp_projects');

		// Implement View Level Access
		if (!$user->authorise('core.admin')) {
			$levels = implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN (' . $levels . ')');
		}

		// Return the result
		$db->setQuery($query);
		return $db->loadObjectList();


	}
}

