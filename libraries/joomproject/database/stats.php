<?php
/**
 * @package      Joomproject.Library
 * @subpackage   Database
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 20012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Joomproject Stats Helper
 *
 * @static
 */
class JPStatsHelper
{

    public static function getUsersCount($id){


        BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jpusers/models', 'JPusersModel');

        $model = BaseDatabaseModel::getInstance('Users', 'JPusersModel', array('ignore_request' => false));

        return $model->getTotal();



    }

	public static  function getCount($id, $table, $pk = 'id',$extraWhere = false,$onlyPublished = true){

		$db = Factory::getDbo();

		$query = $db->getQuery(true);

		// Join over the milestones for milestone count
		$query->select('COUNT(DISTINCT '.$pk.')')
			->from($table)
			->where('project_id = '. (int)$id);

        if($onlyPublished)
            $query->where('state = 1');

        // add extra where
        if($extraWhere)
            $query->where($extraWhere);

		$db->setQuery($query);

		$result = $db->loadResult();

		return is_null($result) ? 0 : (int) $result;

	}

}