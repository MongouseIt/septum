<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jpreminders
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;



/**
 * Methods supporting a list of milestone records.
 *
 */
class JPremindersModelReminders extends ListModel
{
    /**
     * Constructor
     *
     * @param    array    An optional associative array of configuration settings.
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 't.id',
                'title', 't.title',
                'task_id', 'r.task_id',
                'state', 'r.state',
                'created', 'r.created',
                'modified', 'r.modified',
                'created_by', 'r.created_by',
                'ordering', 'r.ordering',
                'created_by', 'r.created_by',
                'start_date','r.start_date',

            );
        }

        parent::__construct($config);
    }


    /**
     * Method to auto-populate the model state.
     * Note: Calling getState in this method will result in recursion.
     *
     * @param     string    $ordering     Default field to sort the items by
     * @param     string    $direction    Default list sorting direction
     *
     * @return    void
     */
    protected function populateState($ordering = 't.title', $direction = 'asc')
    {
        // Initialise variables.
        $app = Factory::getApplication();

        // Adjust the context to support modal layouts.
        if ($layout = \Joomla\CMS\Factory::getApplication()->input->get('layout')) $this->context .= '.' . $layout;

        // Filter - Search
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);


        // List state information.
        parent::populateState($ordering, $direction);
    }


    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param     string    $id    A prefix for the store id.
     *
     * @return    string           A store id.
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');


        return parent::getStoreId($id);
    }


    /**
     * Build an SQL query to load the list data.
     *
     * @return    jdatabasequery
     */
    protected function getListQuery()
    {
        $query = $this->_db->getQuery(true);
        $user  = Factory::getApplication()->getIdentity();

        // Get possible filters
        $filter_state   = $this->getState('filter.published');
        $filter_project = $this->getState('filter.project');
        $filter_access  = $this->getState('filter.access');
        $filter_author  = $this->getState('filter.author_id');
        $filter_search  = $this->getState('filter.search');
        $filter_task  = $this->getState('filter.task_id');

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'r.id,r.task_id,t.title,t.alias,r.types,r.assigned_users,u.name,u.username,r.created_by,r.description,r.start_date, r.amount,r.state,r.repeats'
            )
        );

        $query->from('#__jp_reminders AS r')
        ->join('LEFT', '#__jp_tasks AS t ON t.id = r.task_id')
        ->join('LEFT', '#__users AS u ON u.id = r.created_by');

        if (isset($filter_task) && !empty($filter_task)) {
            $query->where('r.task_id = ' . (int) $filter_task);
        }

        // Filter by published state

        if (is_numeric($filter_state)) {
            $query->where('r.state = ' . (int) $filter_state);
        }
        elseif ($filter_state === '') {
            $query->where('(r.state = 0 OR r.state = 1)');
        }
/*
        // Filter by project
        if (is_numeric($filter_project) && $filter_project > 0) {
            $query->where('a.project_id = ' . (int) $filter_project);
        }

        // Filter by author
        if (is_numeric($filter_author)) {
            $type = $this->getState('filter.author_id.include', true) ? '= ' : '<>';
            $query->where('r.created_by ' . $type . (int) $filter_author);
        }*/

        // Filter by search in title.
        if (!empty($filter_search)) {
            if (stripos($filter_search, 't.id:') === 0) {
                $query->where('t.id = '. (int) substr($filter_search, 3));
            }
            elseif (stripos($filter_search, 'author:') === 0) {
                $search = $this->_db->quote('%' . $this->_db->escape(substr($filter_search, 7), true) . '%');
                $query->where('(u.name LIKE ' . $search . ' OR u.username LIKE ' . $search . ')');
            }
            else {
                $search = $this->_db->quote('%' . $this->_db->escape($filter_search, true) . '%');
                $query->where('(t.title LIKE ' . $search . ' OR t.alias LIKE ' . $search . ')');
            }
        }

        // Add the list ordering clause.
        $order_col = $this->state->get('list.ordering', 'r.id');
        $order_dir = $this->state->get('list.direction', 'asc');

        $query->order($this->_db->escape($order_col . ' ' . $order_dir));

        return $query;
    }
}
