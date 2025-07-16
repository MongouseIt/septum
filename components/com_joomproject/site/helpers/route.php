<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jpprojects
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2006-2016 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;

class JoomprojectHelperRoute{

	public static function getProjectsRoute(){

		$url   = 'index.php?option=com_jpprojects&view=projects&filter_project=0';

		// get item id
		$url .= self::findMenuItemId(
			[
				['view' => 'projects'],
                ['view' => 'dashboard']
			],
			'com_jpprojects'
		);

		return $url;
	}

	public static function getMilestonesRoute(){

		$url   = 'index.php?option=com_jpmilestones&view=milestones';

		// get item id
		$url .= self::findMenuItemId(
			[
				['view' => 'milestones'],
                ['view' => 'dashboard']
			],
			'com_jpmilestones'
		);

		return $url;
	}

	public static function getTasksRoute($clearFilter = false){

		$url   = 'index.php?option=com_jptasks&view=tasks';

        if($clearFilter)
            $url .= '&clearFilter=1';

		// get item id
		$url .= self::findMenuItemId(
			[
				['view' => 'tasks'],
                ['view' => 'dashboard']
			],
			'com_jptasks'
		);

		return $url;
	}

	public static function getTimeRoute(){

		$url   = 'index.php?option=com_jptime&view=timesheet';

		// get item id
		$url .= self::findMenuItemId(
			[
				['view' => 'timesheet'],
                ['view' => 'dashboard']
			],
			'com_jptime'
		);

		return $url;
	}

	public static function getRepoRoute(){

		$url   = 'index.php?option=com_jprepo&view=repository';

		// get item id
		$url .= self::findMenuItemId(
			[
				['view' => 'repository'],
                ['view' => 'dashboard']
			],
			'com_jprepo'
		);

		return $url;
	}

	public static function getTopicsRoute(){

		$url   = 'index.php?option=com_jpforum&view=topics';

		// get item id
		$url .= self::findMenuItemId(
			[
				['view' => 'topics'],
                ['view' => 'dashboard']
			],
			'com_jpforum'
		);

		return $url;
	}

	public static function getDesignsRoute(){

		$url   = 'index.php?option=com_jpdesigns&view=designs';

		// get item id
		$url .= self::findMenuItemId(
			[
				['view' => 'designs'],
                ['view' => 'dashboard']
			],
			'com_jpdesigns'
		);

		return $url;
	}

	public static function getUsersRoute(){

		$url   = 'index.php?option=com_jpusers&view=users';

		// get item id
		$url .= self::findMenuItemId(
			[
				['view' => 'users'],
                ['view' => 'dashboard']
			],
			'com_jpusers'
		);

		return $url;
	}

    public static function getProjectDashCommentsRoute(){

        $url   = 'index.php?option=com_jpcomments&view=comments&filter_context=com_jpprojects.project&filter_item_id='.JPApplicationHelper::getActiveProjectId().'&filter_project='.JPApplicationHelper::getActiveProjectId();

        // get item id
        $url .= self::findMenuItemId(
            [
                ['view' => 'users'],
                ['view' => 'dashboard']
            ],
            'com_jpusers'
        );

        return $url;
    }

	public static function getDashboardRoute($project_id = false){

        $project_id = $project_id ? '&filter_project='.$project_id : '';

		$url   = 'index.php?option=com_joomproject&view=dashboard'.$project_id;

		// get item id
		$url .= self::findMenuItemId(
			[
				['view' => 'dashboard'],
			],
			'com_joomproject'
		);

		return $url;
	}

	/*
	 * find menu item id from a set of rules
	 */
	private static function findMenuItemId($rules,$component)
	{
		foreach ($rules as $rule)
		{
			$itemid = self::executeRule($rule,$component);

			if ($itemid > 0)
			{
				return "&Itemid=" . $itemid;
			}
		}

        $defaultMenuItem = \Joomla\CMS\Factory::getContainer()->get(SiteApplication::class)->getMenu()->getActive();

		return isset($defaultMenuItem->id) ? "&Itemid=" .$defaultMenuItem->id : '';
	}

	/*
	 * Find menu item id from a given rule
	 */
	private static function executeRule($rule, $component)
	{
		$menus     = \Joomla\CMS\Factory::getContainer()->get(SiteApplication::class)->getMenu()->getMenu();
		$hasId     = isset($rule['id']) ? true : false;
		$hasLayout = isset($rule['layout']) ? true : false;

		$rulesLength = count($rule);

		foreach ($menus as $menu)
		{
			// check if menu item is a component
			if ($menu->component != $component)
			{
				continue;
			}

			// check if menu item has view query
			if (!isset($menu->query['view']))
			{
				continue;
			}

			// rule has view and layout and id
			if ($rulesLength == 3 &&
				$hasLayout &&
				$hasId &&
				$menu->query['view'] == $rule['view'] &&

				isset($menu->query['layout']) && $menu->query['layout'] == $rule['layout'] &&
				isset($menu->query['id']) && $menu->query['id'] == $rule['id'])

			{
				return $menu->id;
			}

			// rule has view and id
			if ($rulesLength == 2 &&
				$hasId && $menu->query['view'] == $rule['view'] &&

				isset($menu->query['id']) && $menu->query['id'] == $rule['id']
			)
			{

				return $menu->id;
			}

			// rule has view and layout
			if ($rulesLength == 2 &&
				$hasLayout &&
				$menu->query['view'] == $rule['view'] &&

				isset($menu->query['layout']) && $menu->query['layout'] == $rule['layout'] //&&
			)
			{
				return $menu->id;
			}

			// rule has view only
			if ($rulesLength == 1 &&
				$menu->query['view'] == $rule['view'] &&
				!$hasId &&
				!$hasLayout
			)
			{
				return $menu->id;
			}

		}

		return 0;

	}
}