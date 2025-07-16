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

use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Model\ItemModel;

jimport('joomla.application.component.modelitem');


/**
 * Joomproject Component Task Model
 *
 */
class JPtasksModelTask extends ItemModel
{
    /**
     * Model context string.
     *
     * @var    string
     */
    protected $_context = 'com_jptasks.task';



    /**
     * Method to get item data.
     *
     * @param     integer    The id of the item.
     * @return    mixed      Menu item data object on success, false on failure.
     */
    public function &getItem($pk = null)
    {
        // Initialise variables.
        $pk = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');

        if ($this->_item === null) {
            $this->_item = array();
        }

        // Check if cached
        if (isset($this->_item[$pk])) {
            return $this->_item[$pk];
        }

        try {
            $db    = $this->getDbo();
            $query = $db->getQuery(true);

	        $query->select($this->getState(
		        'item.select',
		        'a.id, a.asset_id, a.complete, a.project_id, a.milestone_id, a.list_id, a.title, a.alias, a.description AS text, '
		        . 'a.created, a.created_by, a.modified_by, a.checked_out, a.checked_out_time, '
		        . 'a.attribs, a.access, a.state, a.ordering, a.start_date, a.end_date, a.not_applicable'
	        ));

            $query->from('#__jp_tasks AS a');

            // Join on project table.
            $query->select('p.title AS project_title, p.alias AS project_alias');
            $query->join('LEFT', '#__jp_projects AS p on p.id = a.project_id');

            // Join on milestone table.
            $query->select('m.title AS milestone_title, m.alias AS milestone_alias');
            $query->join('LEFT', '#__jp_milestones AS m on m.id = a.milestone_id');

            // Join on task lists table.
            $query->select('l.title AS list_title, l.alias AS list_alias');
            $query->join('LEFT', '#__jp_task_lists AS l on l.id = a.list_id');

            // Join on user table.
            $query->select('u.name AS author');
            $query->join('LEFT', '#__users AS u on u.id = a.created_by');

            $query->where('a.id = ' . (int) $pk);

            // Filter by published state.
            $published = $this->getState('filter.published');
            $archived  = $this->getState('filter.archived');

            if (is_numeric($published)) {
                $query->where('(a.state = ' . (int) $published . ' OR a.state =' . (int) $archived . ')');
            }

            $db->setQuery($query);

            // Check for error
            try
            {
                $item = $db->loadObject();
            }
            catch (RuntimeException $e)
            {
                throw new Exception($e->getMessage());

            }
            // Check if we have a record
            if (empty($item)) {

                $this->setError(Text::_('COM_JOOMPROJECT_ERROR_TASK_NOT_FOUND'));

                $item = false;
                return $item;
            }

			// get tasks' labels
			$item->labels = null;
			$model_labels = $this->getInstance('Labels', 'JPModel');
			$item->labels = $model_labels->getConnections('com_jptasks.task', $item->id);
			
            // Check for published state if filter set.
            if (((is_numeric($published)) || (is_numeric($archived))) && (($item->state != $published) && ($item->state != $archived))) {

                Factory::getApplication()->enqueueMessage( Text::_('COM_JOOMPROJECT_ERROR_TASK_NOT_FOUND'),'error');

                $item = false;

                return $item;
            }

            // Convert parameter fields to objects.
            $registry = new Registry;
            $registry->loadString((string)$item->attribs);

            $item->params = $this->getState('params');

            if(!is_null($item->params))
                $item->params->merge($registry);
            
            // Get assigned users
            $ref = BaseDatabaseModel::getInstance('UserRefs', 'JPusersModel');

            $item->users = $ref->getItems('com_jptasks.task', $item->id);


            // Get the attachments
            if (JPApplicationHelper::exists('com_jprepo')) {
                $attachments = $this->getInstance('Attachments', 'JPrepoModel');
                $item->attachments = $attachments->getItems('com_jptasks.task', $item->id);
                $item->attachment  = $item->attachments;
            }
            else {
                $item->attachments = array();
                $item->attachment  = array();
            }


            // Generate slugs
            $item->slug           = $item->alias           ? ($item->id . ':' . $item->alias)                     : $item->id;
            $item->project_slug   = $item->project_alias   ? ($item->project_id . ':' . $item->project_alias)     : $item->project_id;
            $item->milestone_slug = $item->milestone_alias ? ($item->milestone_id . ':' . $item->milestone_alias) : $item->milestone_id;
            $item->list_slug      = $item->list_alias      ? ($item->list_id . ':' . $item->list_alias)           : $item->list_id;

            // Compute selected asset permissions.
            $user   = Factory::getApplication()->getIdentity();
            $uid    = $user->get('id');
            $access = JPtasksHelper::getActions($item->id);

            $view_access = true;

            if ($item->access && !$user->authorise('core.admin')) {
                $view_access = in_array($item->access, $user->getAuthorisedViewLevels());
            }

            if(!is_null($item->params)){
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
            }



	        // get project attribs
	        $project_attribs = new Registry;
	        $project_attribs = $project_attribs->loadString((string)$this->getProjectAttribs($item->project_id));

	        // override task list color with the one defined in project
            if(!is_null($item->params)){
                if(!empty($project_attribs->get('tasklist_color','')) && $item->list_id > 0){
                    $item->params->set('tasklist_color',$project_attribs->get('tasklist_color',''));
                    $item->params->set('task_color',$project_attribs->get('tasklist_color',''));
                }else{
                    // override task color with the one defined in project of tasklist color not defined
                    if(!empty($project_attribs->get('task_color',''))){
                        $item->params->set('task_color',$project_attribs->get('task_color',''));
                    }
                }
            }



            $this->_item[$pk] = $item;
        }
        catch (Exception $e)
        {
            if ($e->getCode() == 404) {
                // Need to go thru the error handler to allow Redirect to work.
                Factory::getApplication()->enqueueMessage( $e->getMessage(),'error');
            }
            else {
                $this->setError($e);
                $this->_item[$pk] = false;
            }
        }

        return $this->_item[$pk];
    }

	protected function getProjectAttribs($project_id){

		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('attribs');
		$query->from($db->quoteName('#__jp_projects'));
		$query->where($db->quoteName('id')." = ".$db->quote($project_id));
		$db->setQuery($query);
		$result = $db->loadResult();

		return $result;
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

        $offset = Factory::getApplication()->input->getUInt('limitstart');
        $this->setState('list.offset', $offset);

        // Load the parameters.
        $params = Factory::getApplication('site')->getParams();
        $this->setState('params', $params);

        $access = JPtasksHelper::getActions();
        if (!$access->get('core.edit.state') && !$access->get('core.edit')) {
            $this->setState('filter.published', 1);
            $this->setState('filter.archived', 2);
        }
    }
}
