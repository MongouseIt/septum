<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jptasks
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;


JLoader::register('JPtasksControllerTask', JPATH_ADMINISTRATOR . '/components/com_jptasks/controllers/task.json.php');


/**
 * Joomproject Task Form Controller
 *
 */
class JPtasksControllerTaskForm extends JPtasksControllerTask
{
    /**
     * Method to check if you can add a new record.
     *
     * @param     array      $data    An array of input data.
     *
     * @return    boolean
     */
    protected function allowAdd($data = array())
    {
        // Get form input
        $project = isset($data['project_id'])   ? (int) $data['project_id']   : JPApplicationHelper::getActiveProjectId();
        $ms      = isset($data['milestone_id']) ? (int) $data['milestone_id'] : 0;
        $list    = isset($data['list_id'])      ? (int) $data['list_id']      : 0;

        $user   = Factory::getApplication()->getIdentity();
        $db     = Factory::getDbo();
        $is_sa  = $user->authorise('core.admin');
        $levels = $user->getAuthorisedViewLevels();
        $query  = $db->getQuery(true);
        $asset  = 'com_jptasks';
        $access = true;

        // Check if the user has access to the project
        if ($project) {
            // Check if in allowed projects when not a super admin
            if (!$is_sa) {
                $access = in_array($project, JPUserHelper::getAuthorisedProjects());
            }

            // Change the asset name
            $asset  .= '.project.' . $project;
        }

        // Check if the user can access the selected milestone when not a super admin
        if (!$is_sa && $ms && $access) {
            $query->select('access')
                  ->from('#__jp_milestones')
                  ->where('id = ' . $db->quote((int) $ms));

            $db->setQuery($query);
            $lvl = $db->loadResult();

            $access = in_array($lvl, $levels);
        }

        // Check if the user can access the selected task list when not a super admin
        if (!$is_sa && $list && $access) {
            $query->clear()
                  ->select('access')
                  ->from('#__jp_task_lists')
                  ->where('id = ' . $list);

            $db->setQuery($query);
            $lvl = $db->loadResult();

            $access = in_array($lvl, $levels);

            // Change asset to list
            $asset = 'com_jptasks.tasklist.' . $list;
        }

        return ($user->authorise('core.create', $asset) && $access);
    }


    /**
     * Method override to check if you can edit an existing record.
     *
     * @param     array      $data    An array of input data.
     * @param     string     $key     The name of the key for the primary key.
     *
     * @return    boolean
     */
    protected function allowEdit($data = array(), $key = 'id')
    {
        // Get form input
        $id = (int) isset($data[$key]) ? $data[$key] : 0;

        $user  = Factory::getApplication()->getIdentity();
        $uid   = $user->get('id');
        $asset = 'com_jptasks.task.' . $id;

        // Check if the user has viewing access when not a super admin
        if (!$user->authorise('core.admin')) {
            $db    = Factory::getDbo();
            $query = $db->getQuery(true);

            $query->select('access')
                  ->from('#__jp_tasks')
                  ->where('id = ' . $id);

            $db->setQuery($query);
            $lvl = $db->loadResult();

            if (!in_array($lvl, $user->getAuthorisedViewLevels())) {
                return false;
            }
        }

        // Check edit permission first
        if ($user->authorise('core.edit', $asset)) {
            return true;
        }

        // Fallback on edit.own.
        // First test if the permission is available.
        if (!$user->authorise('core.edit.own', $asset)) {
            return false;
        }

        // Load the item
        $record = $this->getModel()->getItem($id);

        // Abort if not found
        if (empty($record)) return false;

        // Now test the owner is the user.
        $owner = (int) isset($data['created_by']) ? (int) $data['created_by'] : $record->created_by;

        // If the owner matches 'me' then do the test.
        return ($owner == $uid && $uid > 0);
    }
}
