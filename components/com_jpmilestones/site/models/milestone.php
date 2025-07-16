<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jpmilestones
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ItemModel;

jimport('joomla.application.component.modelitem');


/**
 * Joomproject Milestone Item Model
 *
 */
class JPmilestonesModelMilestone extends ItemModel
{
    /**
     * Model context string.
     *
     * @var    string
     */
    protected $_context = 'com_jpmilestones.milestone';


    /**
     * Method to get item data.
     *
     * @param     integer $pk    The id of the item.
     *
     * @return    mixed   $item   Item data object on success, false on failure.
     */
    public function &getItem($pk = null)
    {
        // Initialise variables.
        $pk = (!empty($pk)) ? $pk : (int) $this->getState('milestone.id');

        if ($this->_item === null) {
            $this->_item = array();
        }

        // Check if cached
        if (isset($this->_item[$pk])) {
            return $this->_item[$pk];
        }

        // Load item
        try {
            $db    = $this->getDbo();
            $query = $db->getQuery(true);

            $query->select($this->getState(
                    'item.select',
                    'a.id, a.asset_id, a.project_id, a.title, a.alias, a.description AS text, '
                    . 'a.created, a.created_by, a.modified_by, a.checked_out, a.checked_out_time, '
                    . 'a.attribs, a.access, a.state, a.ordering, a.start_date, a.end_date'
                )
            );

            $query->from('#__jp_milestones AS a');

            // Join on project table.
            $query->select('p.title AS project_title, p.alias AS project_alias');
            $query->join('LEFT', '#__jp_projects AS p on p.id = a.project_id');

            // Join on tasks table.
            $query->select('COUNT(DISTINCT t.id) AS tasks');
            $query->join('LEFT', '#__jp_tasks AS t on t.milestone_id = a.id');

            // Join on task lists table.
            $query->select('COUNT(DISTINCT l.id) AS lists');
            $query->join('LEFT', '#__jp_task_lists AS l on l.milestone_id = a.id');

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

                $this->setError(Text::_('COM_JOOMPROJECT_ERROR_MILESTONE_NOT_FOUND'));
                $item = false;
                return $item;

            }
			// get milestones' labels
			$item->labels = null;
			$model_labels = $this->getInstance('Labels', 'JPModel');
			$item->labels = $model_labels->getConnections('com_jpmilestones.milestone', $item->id);

            // Check for published state if filter set.
            if (((is_numeric($published)) || (is_numeric($archived))) && (($item->state != $published) && ($item->state != $archived))) {
                $emessage = Factory::getApplication()->enqueueMessage(Text::_('COM_JOOMPROJECT_ERROR_MILESTONE_NOT_FOUND'),'error');
                return $emessage;
            }

            // Convert parameter fields to objects.
            $registry = new Registry;
            $registry->loadString((string)$item->attribs);

	        // get project attribs
	        $project_attribs = new Registry;
	        $project_attribs = $project_attribs->loadString((string)$this->getProjectAttribs($item->project_id));

	        // merge global params with milestone params
	        $mstateParams = $this->getState('params');
            if(!is_null($mstateParams))
	            $item->params = $mstateParams->merge(clone $registry);

            $item->params = !isset($item->params) ? new \Joomla\Registry\Registry() : $item->params;



	        // override milestone color with the one defined in project
	        if(!empty($project_attribs->get('milestone_color',''))){
		        $item->params->set('milestone_color',$project_attribs->get('milestone_color',''));
	        }

            // Get the attachments
            if (JPApplicationHelper::exists('com_jprepo')) {
                $attachments = $this->getInstance('Attachments', 'JPrepoModel');
                $item->attachments = $attachments->getItems('com_jpmilestones.milestone', $item->id);
                $item->attachment  = $item->attachments;
            }
            else {
                $item->attachments = array();
                $item->attachment  = array();
            }

            // Generate slugs
            $item->slug         = $item->id . ':' . $item->alias;
            $item->project_slug = $item->project_id . ':' . $item->project_alias;

            // Compute selected asset permissions.
            $user   = Factory::getApplication()->getIdentity();
            $uid    = $user->get('id');
            $access = JPmilestonesHelper::getActions($item->id);

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

            // Cache the result
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


    /**
     * Method to auto-populate the model state.
     * Note. Calling getState in this method will result in recursion.
     *
     * @return    void
     */
    protected function populateState()
    {
        $app = Factory::getApplication('site');

        // Load state from the request.
        $pk = Factory::getApplication()->input->getInt('id');
        $this->setState('milestone.id', $pk);

        // Load the parameters.
        $params = $app->getParams();
        $this->setState('params', $params);

        // Adjust the state filter based on permissions
        $access = JPmilestonesHelper::getActions();

        if (!$access->get('core.edit.state') && !$access->get('core.edit')) {
            $this->setState('filter.published', 1);
            $this->setState('filter.archived', 2);
        }

        // Set the layout
        $this->setState('layout', Factory::getApplication()->input->getCmd('layout'));
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

}
