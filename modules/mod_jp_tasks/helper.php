<?php
/**
 * @package      Joomproject Tasks
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 **/

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;


/**
 * Module helper class
 *
 */
abstract class modJPtasksHelper
{
	/**
	 * Method to get a list of tasks
	 *
	 * @return    array    $items    The tasks
	 */
	public static function getItems($params)
	{
		JLoader::register('JPtasksModelTasks', JPATH_SITE . '/components/com_jptasks/models/tasks.php');

		$model = BaseDatabaseModel::getInstance('Tasks', 'JPtasksModel', array('ignore_request' => true));

		// Set application parameters in model
		$app       = Factory::getApplication();
		$appParams = $app->getParams();
		$model->setState('params', $appParams);

		// Set the filters based on the module params
		$model->setState('list.start', 0);
		$model->setState('list.limit', (int) $params->get('count', 10));
		$model->setState('filter.published', 1);

		// show tasks by assignment and author
		switch ($params->get('show_tasks', 0))
		{
			case 0:  // Show tasks assigned to me only
				$model->setState('filter.assigned', Factory::getApplication()->getIdentity()->id);
				break;
			case 1: // show tasks created by me only
				$model->setState('filter.author', Factory::getApplication()->getIdentity()->id);
				break;
			case 2: // show tasks created by me and assigned to me
				$model->setState('filter.assigned', Factory::getApplication()->getIdentity()->id);
				$model->setState('filter.author', Factory::getApplication()->getIdentity()->id);
			case 3:
				break;

		}

		// Set project filter
		if (!(int) $params->get('tasks_of'))
		{
			$model->setState('filter.project', JPApplicationHelper::getActiveProjectId());
		}
		else
		{
			$project = (int) $params->get('project');
			if ($project)
			{
				$model->setState('filter.project', $project);
			}
			else
			{
				$model->setState('filter.project', JPApplicationHelper::getActiveProjectId());
			}
		}

		// Set completition filter
		$model->setState('filter.complete', $params->get('filter_complete'));

		// Sort and order
		$model->setState('list.ordering', $params->get('sort'));
		$model->setState('list.direction', $params->get('order'));

		$items = $model->getItems();

		return $items;
	}
}
