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

use Joomla\CMS\Date\Date;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;


// Base this model on the backend version.
JLoader::register('JPtasksModelTask', JPATH_ADMINISTRATOR . '/components/com_jptasks/models/task.php');


/**
 * Joomproject Component Task Form Model
 *
 */
class JPtasksModelTaskForm extends JPtasksModelTask
{


    /**
     * Method to get item data.
     *
     * @param     integer    $pk      The id of the item.
     * @return    mixed      $item    Item data object on success, false on failure.
     */
    public function getItem($pk = null)
    {
        // Get the record from the parent class method
        $item = parent::getItem($pk);

        if ($item === false) return false;

        // Compute selected asset permissions.
        $user   = Factory::getApplication()->getIdentity();
        $uid    = $user->get('id');
        $access = JPtasksHelper::getActions($item->id);

        $view_access = true;

        if ($item->access && !$user->authorise('core.admin')) {
            $view_access = in_array($item->access, $user->getAuthorisedViewLevels());
        }

        $item->params->set('access-view', $view_access);

        if (!$view_access) {
            $item->params->set('access-edit', false);
            $item->params->set('access-change', false);
        }
        else {
            // Check general edit permission first.
            if ($access->get('core.edit')) {
                $item->params->set('access-edit', true);
            }
            elseif (!empty($uid) &&  $access->get('core.edit.own')) {
                // Check for a valid user and that they are the owner.
                if ($uid == $item->created_by) {
                    $item->params->set('access-edit', true);
                }
            }

            // Check edit state permission.
            $item->params->set('access-change', $access->get('core.edit.state'));
        }

        return $item;
    }





	/**
     * Method to save the priority of one or more tasks
     *
     * @param     array    $ids     An array of primary key ids.
     * @param     array    $pids    An array of priority values.
     * @return    mixed             True on success, otherwise false
     */
    public function savePriority($pks = null, $priority = null)
    {
        // Initialise variables.
        $table = $this->getTable();
        $conditions = array();

        if (empty($pks)) {
            Factory::getApplication()->enqueueMessage( Text::_($this->text_prefix . '_ERROR_NO_ITEMS_SELECTED'),'warning');
            return false;
        }

        // Include the content plugins for the on save events.
        PluginHelper::importPlugin('content');
        $dispatcher = Factory::getApplication();

        // update priority values
        foreach ($pks as $i => $pk)
        {
            $table->load((int) $pk);

            // Access checks.
            if (!$this->canEditState($table)) {
                // Prune items that you can't change.
                unset($pks[$i]);
                Factory::getApplication()->enqueueMessage( Text::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'),'warning');
            }
            elseif ($table->priority != $priority[$pk]) {
                $table->priority = $priority[$pk];

                // Trigger the onContentBeforeSave event.
                $result = $dispatcher->triggerEvent($this->event_before_save, array($this->option . '.' . $this->name, &$table, false,false));

                if (!$table->store()) {
                    $this->setError($table->getError());
                    return false;
                }

                // Trigger the onContentBeforeSave event.
                $result = $dispatcher->triggerEvent($this->event_after_save, array($this->option . '.' . $this->name, &$table, false,false));
            }
        }

        // Clear the component's cache
        $this->cleanCache();

        return true;
    }


    /**
     * Method to assign a user to one or more tasks
     *
     * @param     array    $ids     An array of primary key ids.
     * @param     array    $uids    An array of user id values.
     * @return    mixed             True on success, otherwise false
     */
    public function addUsers($pks = null, $uids = null)
    {
        // Initialise variables.
        $table = $this->getTable();
        $conditions = array();

        if (empty($pks)) {
            return Factory::getApplication()->enqueueMessage( Text::_($this->text_prefix . '_ERROR_NO_ITEMS_SELECTED'),'warning');
        }

        foreach ($pks as $i => $pk)
        {
            $table->load((int) $pk);

            // Access checks.
            if (!$this->canEditState($table)) {
                // Prune items that you can't change.
                unset($pks[$i]);
                Factory::getApplication()->enqueueMessage( Text::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'),'warning');
            }

            $refs = $this->getInstance('UserRefs', 'JPusersModel', array('ignore_request' => true));

            if (!$refs->store($uids, 'com_jptasks.task', $pk)) {
                return false;
            }

            // Send notification
            if(\Joomla\CMS\Component\ComponentHelper::getParams('com_jptasks')->get('notify_assigned_users',0))
                $this->notifyAssignedUsers($uids, $pk);
        }

        // Clear the component's cache
        $this->cleanCache();

        return true;
    }


    /**
     * Method to set the completion state of tasks
     *
     * @param     array    $pks    An array of primary key ids.
     *
     * @return    mixed            True on success, otherwise false
     */
    public function complete($pks = null, $state = null)
    {
        // Initialise variables.
        $table = $this->getTable();
        $uid   = Factory::getApplication()->getIdentity()->get('id');
        $date  = new Date();
        $now   = $date->toSql();
        $ndate = Factory::getDbo()->getNullDate();

        if (empty($pks)) {
            Factory::getApplication()->enqueueMessage( Text::_($this->text_prefix . '_ERROR_NO_ITEMS_SELECTED'),'warning');
            return false;
        }

        // Get milestone ids
        JLoader::register('JPmilestonesModelMilestone', JPATH_ADMINISTRATOR . '/components/com_jpmilestones/models/milestone.php');

        $milestones = array_unique(array_values($this->getMilestoneIds($pks)));

        // Get milestone progress before the save
        $ms_model = BaseDatabaseModel::getInstance('Milestone', 'JPmilestonesModel', array('ignore_request' => true));
        $progress_before = $ms_model->getProgress($milestones);


        // Include the content plugins for the on save events.
        PluginHelper::importPlugin('content');
        $dispatcher = Factory::getApplication();

        // Update values
        foreach ($pks as $i => $pk)
        {
            $table->load((int) $pk);

            // Access checks.
            if (!$this->canEditState($table)) {
                // Prune items that you can't change.
                unset($pks[$i]);
                Factory::getApplication()->enqueueMessage( Text::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'),'warning');
            }

            if (is_null($state)) {
                if ($table->complete == '1') {
                    $table->complete = '0';
                    $table->completed = $ndate;
                    $table->completed_by = '0';
                }
                else {
                    $table->complete = '1';
                    $table->completed = $now;
                    $table->completed_by = $uid;
                }
            }
            else {
                $table->complete = (int) $state;

                if ($table->complete == 1) {
                    $table->complete = '0';
                    $table->completed = $ndate;
                    $table->completed_by = '0';
                }
                else {
                    $table->complete = 0;
                    $table->completed = $now;
                    $table->completed_by = $uid;
                }
            }

            // Trigger the onContentBeforeSave event.
            $result = $dispatcher->triggerEvent($this->event_before_save, array($this->option . '.' . $this->name, &$table, false,false));

            if (!$table->store()) {
                $this->setError($table->getError());
                return false;
            }

            // Trigger the onContentBeforeSave event.
            $result = $dispatcher->triggerEvent($this->event_after_save, array($this->option . '.' . $this->name, &$table, false,false));
        }

        // Clear the component's cache
        $this->cleanCache();

        // Get progress after the save
        $progress_after = $ms_model->getProgress($milestones);

        // Trigger event for completed milestones
        $completed_ms = array();

        foreach ($progress_before AS $id => $progress)
        {
            if ($progress == 100 || !array_key_exists($id, $progress_after) || $progress_after[$id] != 100) {
                continue;
            }

            // This milestone was just completed!
            $completed_ms[] = $id;
        }

        if (count($completed_ms)) {
            $dispatcher->triggerEvent('onJoomprojectComplete', array('com_jpmilestones.milestone', $completed_ms));
        }

        return true;
    }


	/**
	 * Method to set the not applicable state of one or more records.
	 *
	 * @param   array    $pks     An array of primary key ids.
	 * @param   integer  $state   The not applicable state.
	 *
	 * @return  boolean  True on success.
	 */
	public function setNotApplicable(&$pks, $state = 1)
	{
		// Initialise variables.
		$pks = (array) $pks;
		$table = $this->getTable();
		$user = Factory::getApplication()->getIdentity();

		// Check if user has permission to change state
		if (!$user->authorise('core.edit.state', 'com_jptasks')) {
			$this->setError(Text::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
			return false;
		}

		try {
			// Update record(s)
			foreach ($pks as $pk) {
				if ($table->load($pk)) {

					// Check if the current user can edit this task
					if (!$user->authorise('core.edit.state', 'com_jptasks.task.' . $pk) &&
						!($user->authorise('core.edit.own', 'com_jptasks.task.' . $pk) && $table->created_by == $user->id)) {

						$this->setError(Text::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
						return false;
					}

					$table->not_applicable = (int) $state;

					// If marking as not applicable, also reset complete status
					if ($state) {
						$table->complete = 0;
						$table->completed = null;
						$table->completed_by = 0;
					}

					// Update modified info
					$table->modified = Factory::getDate()->toSql();
					$table->modified_by = $user->get('id');

					if (!$table->store()) {
						$this->setError($table->getError());
						return false;
					}

					// Clear the table for next iteration
					$table->reset();
				} else {
					$this->setError($table->getError());
					return false;
				}
			}
		} catch (Exception $e) {
			$this->setError($e->getMessage());
			return false;
		}

		// Clean the cache
		$this->cleanCache();

		return true;
	}


    /**
     * Get the return URL.
     *
     * @return    string    The return URL.
     */
    public function getReturnPage()
    {
        return base64_encode($this->getState('return_page'));
    }


    /**
     * Method to auto-populate the model state.
     * Note. Calling getState in this method will result in recursion.
     *
     * @return    void
     */
    protected function populateState()
    {
        // Load state from the request.
        $pk = Factory::getApplication()->input->getInt('id');
        $this->setState($this->getName() . '.id', $pk);

        $return = Factory::getApplication()->input->get('return', null, 'base64');
        $this->setState('return_page', base64_decode($return));

        // Load the parameters.
        $params = Factory::getApplication()->getParams();
        $this->setState('params', $params);

        $this->setState('layout', Factory::getApplication()->input->getCmd('layout'));
    }
}
