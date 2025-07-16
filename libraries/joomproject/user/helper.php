<?php
/**
 * @package      pkg_joomproject
 * @subpackage   lib_joomproject
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2006-2013 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;


/**
 * Joomproject User Helper Class
 *
 */
abstract class JPUserHelper
{
	/**
	 * Projects the user is allowed to access
	 *
	 * @var    array
	 */
	protected static $allowed_projects;


	/**
	 * Returns a list of project id's the user is allowed to access
	 *
	 * @param integer $uid Optional user id
	 *
	 * @return    array
	 */
	protected static $routes;

	public static function getAuthorisedProjects($uid = null)
	{
		$cache_key = (int) $uid;

		// Check the cache
		if (isset(self::$allowed_projects[$cache_key]))
		{
			return self::$allowed_projects[$cache_key];
		}

		// Not yet cached
		$levels = Factory::getUser($uid)->getAuthorisedViewLevels();
		$db     = Factory::getDbo();
		$query  = $db->getQuery(true);

		$query->select('id')
			->from('#__jp_projects')
			->where('access IN(' . implode(', ', $levels) . ')')
			->order('id ASC');

		$db->setQuery($query);
		$result = $db->loadColumn();

		if (empty($result)) $result = array();

		self::$allowed_projects[$cache_key] = $result;

		return self::$allowed_projects[$cache_key];
	}

	/*
	 * Check if current user allow to view this project
	 */
	static public function canView($itemId, $ownerId)
	{

		$user = Factory::getApplication()->getIdentity();

		// get item actions as object
		$actions = JPprojectsHelper::getActions($itemId);

		// if user authorized admin can view everything.
		if ($user->authorise('core.admin')) return true;

		// if user is the owner of this item, allow him always to see it.
		if ($user->id == $ownerId) return true;

		// if user group is allowed in ACL action "view" allow him to see it.
		if ($actions->get('core.view')) return true;


		return false;


	}

	static public function filterByViewAccess($query, $type, $component, $view = false)
	{

		$user = Factory::getApplication()->getIdentity();
        $userGroups = implode(',', $user->getAuthorisedGroups());

		if ($user->authorise('core.admin')) return $query;

        if($type == 'project' && $component == 'projects'){ // execption for projects should not depend on view or anything else

            $query->join('LEFT', '#__jp_groups_view_action AS gva ON a.id = gva.itemid');
            $query->where("((gva.groupid IN ($userGroups) AND type = 'project' AND component = 'projects') OR (a.created_by = $user->id))");

        }else{ // others
            $currentProjectID = JPApplicationHelper::getActiveProjectId('filter_project');

            $view = ($view == false) ? Factory::getApplication()->input->get('view', '', 'word') : $view;

            $projectColumn = $view != 'projects' ? 'project_id' : 'id';

            $itemid = $currentProjectID > 0 && $view != 'projects' ? 'AND a.project_id = ' . $currentProjectID : '';

            $query->join('LEFT', '#__jp_groups_view_action AS gva ON a.' . $projectColumn . ' = gva.itemid');
            $query->where("((gva.groupid IN ($userGroups) AND type = '$type' AND component = '$component' $itemid) OR (a.created_by = $user->id $itemid))");
        }




		return $query;

	}

    static public function cleanRules($rules){

        // make sure rules are empty

        if(is_null($rules)) // make is not null
            return false;

        if(count($rules) == 0) // check if empty
            return false;

        // remove empty rules
        foreach ($rules as $rulek => $rulev){

            if(empty($rulev))
                unset($rules[$rulek]);

        }

        if(count($rules) == 0) // make sure again is not empty
            return false;

        return $rules;



    }


	/*
	 * This method update groups item that has core.view action allowed
	 */
	static public function updateGroupsViewAction($type, $rules, $itemId)
	{


        $rules = self::cleanRules($rules);

        if(!$rules)
            return true; // no rule to update

		// delete all old rows
		self::removeOldViewGroups($type, $itemId);

		foreach ($rules as $component => $crules)
		{

			$component = str_replace('com_jp', '', $component);

			// if core view doesn't exist skip
			if (!isset($crules['core.view'])) continue;

			// insert new view groups
			foreach ($crules['core.view'] as $groupId => $status)
			{

				// if view action false remove it
				if ($status == false) continue;

				// else add group with action view to the table
				self::addViewGroup($type, $groupId, $itemId, $component);

			}

		}

		return true;
	}

	static public function getViewGroupsViewAction($type, $itemId)
	{

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$conditions = array(
			$db->quoteName('type') . ' = ' . $db->quote($type),
			$db->quoteName('itemid') . ' = ' . $itemId
		);

		$query->select('*')->from('#__jp_groups_view_action')->where($conditions);

		$db->setQuery($query);

		$result = $db->loadObjectList();

		return is_null($result) ? [] : $result;

	}


    public static function deleteActions($type,$id){

            self::removeOldViewGroups($type,$id);
    }


	protected static function removeOldViewGroups($type, $itemId)
	{

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$conditions = array(
			$db->quoteName('type') . ' = ' . $db->quote($type),
			$db->quoteName('itemid') . ' = ' . $itemId
		);

		$query->delete($db->quoteName('#__jp_groups_view_action'));
		$query->where($conditions);

		$db->setQuery($query);

		$db->execute();

	}

	protected static function addViewGroup($type, $groupId, $itemId, $component)
	{

		$group            = new stdClass();
		$group->itemid    = $itemId;
		$group->type      = $type;
		$group->groupid   = $groupId;
		$group->component = $component;

		Factory::getDbo()->insertObject('#__jp_groups_view_action', $group);

	}


}
