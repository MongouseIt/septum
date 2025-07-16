<?php
/**
 * @package      Joomproject
 * @subpackage   Tasks
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

namespace JoomProject\Tasks;

use JLoader;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use JoomprojectHelperRoute;
use JPStatsHelper;

defined('_JEXEC') or die();

class ListCounts{

    /**
     * Get task counts for a specific task list
     *
     * @param   int  $listId  The task list ID
     *
     * @return  object  Object containing various task counts
     */
    public static function getTaskCounts($listId,$displayLabels = true)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        // Base query to count all tasks for this list
        $query->select('state, COUNT(*) as count')
            ->from($db->quoteName('#__jp_tasks'))
            ->where($db->quoteName('list_id') . ' = ' . (int) $listId)
            ->group('state');

        $db->setQuery($query);
        $results = $db->loadObjectList();

        // Initialize counts
        $counts = new \stdClass();
        $counts->total = 0;
        $counts->published = 0;    // state = 1
        $counts->unpublished = 0;  // state = 0
        $counts->archived = 0;     // state = 2
        $counts->trashed = 0;      // state = -2

        // Process results
        foreach ($results as $result) {

            $counts->total += $result->count;

            switch ($result->state) {
                case 1:
                    $counts->published = $result->count;
                    break;
                case 0:
                    $counts->unpublished = $result->count;
                    break;
                case 2:
                    $counts->archived = $result->count;
                    break;
                case -2:
                    $counts->trashed = $result->count;
                    break;
            }
        }

        return $counts;
    }

}