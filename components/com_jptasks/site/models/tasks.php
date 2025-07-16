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

use Joomla\CMS\Pagination\Pagination;
use Joomla\Registry\Registry;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Utilities\ArrayHelper;

jimport('joomla.application.component.modellist');
jimport('joomla.application.component.helper');


/**
 * This models supports retrieving lists of tasks.
 *
 */
class JPtasksModelTasks extends ListModel
{
    /**
     * Constructor.
     *
     * @param    array          An optional associative array of configuration settings.
     * @see      jcontroller
     */
    public function __construct($config = array())
    {
        // Set field filter
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'a.id', 'a.title', 'a.created',
                'a.modified', 'a.checked_out',
                'a.checked_out_time', 'a.state',
                'a.priority', 'a.complete', 'a.start_date',
                'a.end_date', 'a.ordering', 'a.parentid', 'author_name', 'editor',
                'access_level', 'project_title', 'milestone_title',
                'list_title', 'ordering', 'assigned'
            );
        }

        parent::__construct($config);
    }


    /**
     * Get the master query for retrieving a list of items subject to the model state.
     *
     * @return    jdatabasequery
     */
    public function getListQuery()
    {
        // Create a new query object.
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $user   = Factory::getApplication()->getIdentity();
        $access = JPtasksHelper::getActions();

        // Select the required fields from the table.
	    $query->select(
		    $this->getState(
			    'list.select',
			    'a.id, a.project_id, a.list_id, a.milestone_id, a.title, '
			    . 'a.description, a.alias, a.checked_out, a.attribs, a.priority, '
			    . 'a.checked_out_time, a.state, a.access, a.created, a.created_by, '
			    . 'a.start_date, a.end_date, a.ordering, a.complete, a.completed, a.completed_by, a.modified, a.modified_by, '
			    . 'a.not_applicable'
		    )
	    );

        $query->from('#__jp_tasks AS a');

        // Join over the users for the checked out user.
        $query->select('uc.name AS editor');
        $query->join('LEFT', '#__users AS uc ON uc.id = a.checked_out');

        // Join over the asset groups.
        $query->select('ag.title AS access_level');
        $query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

        // Join over the users for the author.
        $query->select('ua.name AS author_name, ua.email AS author_email');
        $query->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

        // Join over the projects for the project title and alias.
        $query->select('p.title AS project_title, p.alias AS project_alias');
        $query->join('LEFT', '#__jp_projects AS p ON p.id = a.project_id');

        // Join over the task lists for the task list title, description, checked out, author
        $query->select('tl.title AS list_title, tl.alias AS list_alias, tl.description AS list_description, '
                       . 'tl.checked_out AS checked_out_list, tl.created_by AS list_created_by');
        $query->join('LEFT', '#__jp_task_lists AS tl ON tl.id = a.list_id');

        // Join over the milestones for the milestone title and alias.
        $query->select('m.title AS milestone_title, m.alias AS milestone_alias');
        $query->join('LEFT', '#__jp_milestones AS m ON m.id = a.milestone_id');

        // Join over the label refs for label count
        $query->select('COUNT(DISTINCT lbl.id) AS label_count');
        $query->join('LEFT', '#__jp_ref_labels AS lbl ON (lbl.item_id = a.id AND lbl.item_type = ' . $db->quote('com_jptasks.task') . ')');

        // Join over the observer table for email notification status
        if ($user->get('id') > 0) {
            $query->select('COUNT(DISTINCT obs.user_id) AS watching');
            $query->join('LEFT', '#__jp_ref_observer AS obs ON (obs.item_type = '
                               . $db->quote('com_jptasks.task') . ' AND obs.item_id = a.id AND obs.user_id = '
                               . $db->quote($user->get('id')) . ')'
                        );
        }

        // Join over the attachments for attachment count
        $query->select('COUNT(DISTINCT at.id) AS attachments');
        $query->join('LEFT', '#__jp_ref_attachments AS at ON (at.item_type = '
              . $db->quote('com_jptasks.task') . ' AND at.item_id = a.id)');

        // Join over the comments for comment count
        $query->select('COUNT(DISTINCT co.id) AS comments');
        $query->join('LEFT', '#__jp_comments AS co ON (co.context = ' . $db->quote('com_jptasks.task')
                           . ' AND co.item_id = a.id AND co.state = 1)');


	    if (ComponentHelper::getParams('com_jptasks')->get('task_show_last_comment_by', 0)) {
		    $query->select('(SELECT created_by FROM #__jp_comments WHERE item_id = a.id AND context = ' . $db->quote('com_jptasks.task') . ' AND state = 1 ORDER BY created DESC LIMIT 1) AS last_comment_by');
		    $query->select('(SELECT created FROM #__jp_comments WHERE item_id = a.id AND context = ' . $db->quote('com_jptasks.task') . ' AND state = 1 ORDER BY created DESC LIMIT 1) AS last_comment_date');
	    }

        // Join over the task refs for dependencies
        $query->select('COUNT(d.id) AS dependency_count');
        $query->join('LEFT', '#__jp_ref_tasks AS d ON (d.task_id = a.id)');

        // Implement View Level Access
        /*if (!$user->authorise('core.admin')) {
            $levels = implode(',', $user->getAuthorisedViewLevels());

            $query->where('a.access IN (' . $levels . ')');
        }*/

	    /* the new method of access level */
	    JPUserHelper::filterByViewAccess($query,'project','tasks');

        // Filter by assigned user
        $assigned = $this->getState('filter.assigned');
        if (is_numeric($assigned)) {
            $assigned = (int) $assigned;

            if ($assigned) {
                $query->join('INNER',
                '#__jp_ref_users AS ru ON (ru.item_type = ' . $this->_db->quote('com_jptasks.task') . ' AND ru.item_id = a.id)'
                );

                $query->where('ru.user_id = '. (int) $assigned);
            }
            else {
                $query->select('COUNT(ru.id) AS assignee_count')
                      ->join('left', '#__jp_ref_users AS ru ON (ru.item_type = ' . $this->_db->quote('com_jptasks.task') . ' AND ru.item_id = a.id)')
                      ->having('assignee_count = 0')
                      ->group('a.id');
            }
        }

        // Filter labels
        if (count((array) $this->getState('filter.labels'))) {
            $labels = $this->getState('filter.labels');

            ArrayHelper::toInteger($labels);

            if (count($labels) > 1) {
                $labels = implode(', ', $labels);
                $query->where('lbl.label_id IN (' . $labels . ')');
            }
            else {
                $labels = implode(', ', $labels);
                $query->where('lbl.label_id = ' . $db->quote((int) $labels));
            }
        }

        // Filter fields
        $filters = array();
        $filters['a.state']        = array('STATE',       $this->getState('filter.published'));
        $filters['a.project_id']   = array('INT-NOTZERO', $this->getState('filter.project'));
        $filters['a.milestone_id'] = array('INT-NOTZERO', $this->getState('filter.milestone'));
        $filters['a.list_id']      = array('INT-NOTZERO', $this->getState('filter.tasklist'));
        $filters['a.created_by']   = array('INT-NOTZERO', $this->getState('filter.author'));
        $filters['a.priority']     = array('INT',         $this->getState('filter.priority'));
        $filters['a.complete']     = array('INT',         $this->getState('filter.complete'));
        $filters['a']              = array('SEARCH',      $this->getState('filter.search'));

        // Apply Filter
        JPQueryHelper::buildFilter($query, $filters);

        // Add the list ordering clause.
        $order_col = $this->state->get('list.ordering', 'a.title');
        $order_dir = $this->state->get('list.direction', 'asc');

        if ($order_col == '') {
            $order_col = 'a.title';
        }

        if ($order_dir == '') {
            $order_dir = 'asc';
        }

        if ($order_col == 'a.title') {
            $order_col = 'tl.title, p.title, a.ordering ' . $order_dir . ', ' . $order_col;
        }
        if ($order_col == 'a.ordering' || $order_col == 'a.title') {
            $order_col = 'tl.title, p.title, m.title ' . $order_dir . ', ' . $order_col;
        }
        if ($order_col == 'project_title') {
            $order_col = 'tl.title, m.title, a.title ' . $order_dir . ', p.title';
        }
        if ($order_col == 'milestone_title') {
            $order_col = 'p.title ' . $order_dir . ', m.title';
        }
        if ($order_col == 'tasklist_title') {
            $order_col = 'p.title, m.title ' . $order_dir . ', tl.title';
        }

        $query->order($db->escape($order_col . ' ' . $order_dir));
        $query->group('a.id');

        return $query;
    }


    /**
     * Method to get a list of items.
     * Overriden to inject convert the attribs field into a JParameter object.
     *
     * @return    mixed    An array of objects on success, false on failure.
     */
    public function getItems()
    {
        // Get a storage key.
        $store = $this->getStoreId();

        // Try to load the data from internal storage.
        if (!isset($this->cache[$store])) {
            // Load the list items.
            $limit = ($this->getState('filter.project') ? 0 : $this->getState('list.limit'));

            try
            {
                $query = $this->_getListQuery();
            }
            catch (RuntimeException $e)
            {
                $this->setError($e->getMessage());
                return false;
            }
            $items = $this->_getList($query, $this->getStart(), $limit);

            // Add the items to the internal cache.
            $this->cache[$store] = $items;

        }

        $items  = $this->cache[$store];
        $ref    = BaseDatabaseModel::getInstance('UserRefs', 'JPusersModel');
        $tref   = BaseDatabaseModel::getInstance('TaskRefs', 'JPtasksModel');
        $labels = $this->getInstance('Labels', 'JPModel');


        // Get the global params
        $global_params = ComponentHelper::getParams('com_jptasks', true);

        foreach ($items as $i => &$item)
        {
            $params = new Registry;
            $params->loadString((string)$item->attribs);

	        // get project attribs
	        $project_attribs = new Registry;
	        $project_attribs = $project_attribs->loadString((string)$this->getProjectAttribs($items[$i]->project_id));

	        // merge global params with milestone params
	        $mstateParams = clone $this->getState('params');
	        $items[$i]->params = $mstateParams->merge(clone $params);

	        // override task list color with the one defined in project
	        if(!empty($project_attribs->get('tasklist_color','')) && $items[$i]->list_id > 0){
		        $items[$i]->params->set('tasklist_color',$project_attribs->get('tasklist_color',''));
		        $items[$i]->params->set('task_color',$project_attribs->get('tasklist_color',''));
	        }else{
		        // override task color with the one defined in project of tasklist color not defined
		        if(!empty($project_attribs->get('task_color',''))){
			        $items[$i]->params->set('task_color',$project_attribs->get('task_color',''));
		        }
	        }

            // Get assigned users
            $items[$i]->users = $ref->getItems('com_jptasks.task', $items[$i]->id);

            // Create item slugs
            $items[$i]->slug           = $items[$i]->alias           ? ($items[$i]->id . ':' . $items[$i]->alias)                     : $items[$i]->id;
            $items[$i]->project_slug   = $items[$i]->project_alias   ? ($items[$i]->project_id . ':' . $items[$i]->project_alias)     : $items[$i]->project_id;
            $items[$i]->milestone_slug = $items[$i]->milestone_alias ? ($items[$i]->milestone_id . ':' . $items[$i]->milestone_alias) : $items[$i]->milestone_id;
            $items[$i]->list_slug      = $items[$i]->list_alias      ? ($items[$i]->list_id . ':' . $items[$i]->list_alias)           : $items[$i]->list_id;

            // Get the labels
            if ($items[$i]->label_count > 0) {
                $items[$i]->labels = $labels->getConnections('com_jptasks.task', $items[$i]->id);
            }
            else {
                $items[$i]->labels = array();
            }

            // Get the dependencies
            if ($items[$i]->dependency_count > 0) {
                $items[$i]->dependencies = $tref->getItems($items[$i]->id);
            }
            else {
                $items[$i]->dependencies = array();
            }
        }

        return $items;
    }


    /**
     * Method to calculate the aggregated progress of parent projects, milestones or lists
     *
     * @param     array     $pks      The parent item primary keys
     * @param     string    $field    The parent table field
     *
     * @return    array               Assoc array with the pk and progress value
     */
    public function getAggregatedProgress($pks, $field = 'project_id')
    {
        $fields = array('project_id', 'milestone_id', 'list_id');

        if (!is_array($pks) || count($pks) == 0 || !in_array($field, $fields)) {
            return array();
        }

        $completed = $this->getAggregatedTotal($pks, $field, 1, true);
        $total     = $this->getAggregatedTotal($pks, $field, null, true);
        $items     = array();

        foreach ($pks AS $pk)
        {
            $count_complete = (int) (isset($completed[$pk]) ? $completed[$pk] : 0);
            $count_total    = (int) (isset($total[$pk])     ? $total[$pk]   : 0);

            if (!$count_total || !$count_complete) {
                $progress = 0;
            }
            elseif ($count_complete == $count_total) {
                $progress = 100;
            }
            else {
                $progress = round($count_complete * (100 / $count_total));
            }

            $items[$pk] = $progress;
        }

        return $items;
    }

    /**
     * Method to get tasks/taskslist column of parent projects, milestones or lists
     *
     * @param     array     $pks         The parent item primary keys
     * @param     string    $field       The parent table field
     * @param     mixed     $complete    Optionally filter by completition state
     *
     * @return    array                  Assoc array with the pk and total value
     */
    public function getTasksColumnList($pks, $field = 'project_id', $complete = null,$column='title',$type = 'tasks')
    {
        static $cache = array();

        $fields = array('project_id', 'milestone_id', 'list_id');

        if (!is_array($pks) || count($pks) == 0 || !in_array($field, $fields)) {
            return array();
        }

        // Check the cache
        $key = md5($field . $complete . implode('.', $pks).$column.$type);
        //if (isset($cache[$key])) return $cache[$key];

        $query = $this->_db->getQuery(true);
        $query->select("$column")
            ->from('#__jp_'.$type)
            ->where($field . ' IN(' . implode(',', $pks) . ') ')
            ->where('state != -2');

        if ($complete === 0) {
            $query->where('complete = 0');
        }
        elseif ($complete === 1) {
            $query->where('complete = 1');
        }

        $query->order('id ASC');


        $this->_db->setQuery($query);
        $cache[$key] = $this->_db->loadColumn();

        if (!is_array($cache[$key])) $cache[$key] = array();

        return $cache[$key];
    }


    /**
     * Method to get the aggregated total tasks of parent projects, milestones or lists
     *
     * @param     array     $pks         The parent item primary keys
     * @param     string    $field       The parent table field
     * @param     mixed     $complete    Optionally filter by completition state
     *
     * @return    array                  Assoc array with the pk and total value
     */
    public function getAggregatedTotal($pks, $field = 'project_id', $complete = null)
    {
        static $cache = array();

        $fields = array('project_id', 'milestone_id', 'list_id');

        if (!is_array($pks) || count($pks) == 0 || !in_array($field, $fields)) {
            return array();
        }

        // Check the cache
        $key = md5($field . $complete . implode('.', $pks));
        if (isset($cache[$key])) return $cache[$key];

        $query = $this->_db->getQuery(true);
        $query->select($field . ', COUNT(id) AS total')
              ->from('#__jp_tasks')
              ->where($field . ' IN(' . implode(',', $pks) . ') ')
              ->where('state != -2');

        if ($complete === 0) {
            $query->where('complete = 0');
        }
        elseif ($complete === 1) {
            $query->where('complete = 1');
        }

        $query->group($field)
              ->order('id ASC');

        $this->_db->setQuery($query);
        $cache[$key] = $this->_db->loadAssocList($field, 'total');

        if (!is_array($cache[$key])) $cache[$key] = array();

        return $cache[$key];
    }


    /**
     * Method to get a JPagination object for the data set.
     *
     * @return    jpagination    A JPagination object for the data set.
     */
    public function getPagination()
    {
        // Get a storage key.
        $store = $this->getStoreId('getPagination');

        // Try to load the data from internal storage.
        if (isset($this->cache[$store])) {
            return $this->cache[$store];
        }

        // Create the pagination object.
        jimport('joomla.html.pagination');
        $limit = (int) ($this->getState('filter.project') ? 0 : $this->getState('list.limit')) - (int) $this->getState('list.links');
        $page  = new Pagination($this->getTotal(), $this->getStart(), $limit);

        // Add the object to the internal cache.
        $this->cache[$store] = $page;

        return $this->cache[$store];
    }


    /**
     * Build a list of project authors
     *
     * @return    jdatabasequery
     */
    public function getAuthors()
    {
        $db     = $this->getDbo();
        $query  = $db->getQuery(true);
        $user   = Factory::getApplication()->getIdentity();
        $access = JPtasksHelper::getActions();

        // Return empty array if no project is select
        $project = (int) $this->getState('filter.project');

        if ($project <= 0) {
            // Make an exception if we are logged in...
            if ($user->id) {
                $item = new stdClass();
                $item->value = $user->id;
                $item->text  = $user->name;

                $items = array($item);

                return $items;
            }

            return array();
        }

        // Construct the query
        $query->select('u.id AS value, u.name AS text');
        $query->from('#__users AS u');
        $query->join('INNER', '#__jp_tasks AS a ON a.created_by = u.id');

        // Implement View Level Access
        if (!$user->authorise('core.admin', 'com_jptasks')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('a.access IN (' . $groups . ')');
        }

        // Filter fields
        $filters = array();
        $filters['a.project_id'] = array('INT-NOTZERO', $this->getState('filter.project'));

        if (!$access->get('core.edit.state') && !$access->get('core.edit')) {
            $filters['a.state'] = array('STATE', '1');
        }

        // Apply Filter
        JPQueryHelper::buildFilter($query, $filters);

        // Group and order
        $query->group('u.id');
        $query->order('u.name ASC');

        // Get the results
        $db->setQuery((string) $query);
        $items = (array) $db->loadObjectList();

        // Return the items
        return $items;
    }


    /**
     * Build a list of milestones
     *
     * @return    jdatabasequery
     */
    public function getMilestones()
    {
        $db     = $this->getDbo();
        $query  = $db->getQuery(true);
        $user   = Factory::getApplication()->getIdentity();
        $access = JPtasksHelper::getActions();

        // Return empty array if no project is select
        $project = (int) $this->getState('filter.project');

        if ($project < 0) {
            return array();
        }

        // Construct the query
        $query->select('a.id AS value, a.title AS text');
        $query->from('#__jp_milestones AS a');
        // $query->join('LEFT', '#__jp_tasks AS a ON a.milestone_id = m.id');

        // Implement View Level Access
        if (!$user->authorise('core.admin', 'com_jptasks')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('a.access IN (' . $groups . ')');
        }

        // Filter fields
        $filters = array();
        $filters['a.project_id'] = array('INT-NOTZERO', $this->getState('filter.project'));

        if (!$access->get('core.edit.state') && !$access->get('core.edit')) {
            $filters['a.state'] = array('STATE', '1');
        }

        // Apply Filter
        JPQueryHelper::buildFilter($query, $filters);

        // Group and order
        // $query->group('a.id');
        $query->order('a.title ASC');

        // Get results
        $db->setQuery((string) $query);
        $items = (array) $db->loadObjectList();

        // Return the items
        return $items;
    }


    /**
     * Build a list of task lists
     *
     * @return    jdatabasequery
     */
    public function getTaskLists()
    {
        $db     = $this->getDbo();
        $query  = $db->getQuery(true);
        $user   = Factory::getApplication()->getIdentity();
        $access = JPtasksHelper::getActions();

        // Return empty array if no project is select
        $project = (int) $this->getState('filter.project');
        if ($project < 0) {
            return array();
        }

        // Construct the query
        $query->select('a.id AS value, a.title AS text');
        $query->from('#__jp_task_lists AS a');
        // $query->join('LEFT', '#__jp_tasks AS a ON a.list_id = t.id');

        // Implement View Level Access
        if (!$user->authorise('core.admin', 'com_jptasks')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('a.access IN (' . $groups . ')');
        }

        // Filter fields
        $filters = array();
        $filters['a.project_id'] = array('INT-NOTZERO', $this->getState('filter.project'));

        if (!$access->get('core.edit.state') && !$access->get('core.edit')) {
            $filters['a.state'] = array('STATE', '1');
        }

        // Apply Filter
        JPQueryHelper::buildFilter($query, $filters);

        // Group and order
        // $query->group('a.id');
        $query->order('a.title ASC');

        // Get results
        $db->setQuery((string) $query);
        $items = (array) $db->loadObjectList();

        // Return the items
        return $items;
    }


    /**
     * Build a list of assigned users
     *
     * @return    array
     */
    public function getAssignedUsers()
    {
        $user    = Factory::getApplication()->getIdentity();
        $query   = $this->_db->getQuery(true);
        $project = (int) $this->getState('filter.project');

        $items = array();

        $item = new stdClass();
        $item->value = '0';
        $item->text  = '* ' . Text::_('COM_JPTASKS_UNASSIGNED') . ' *';

        $items[] = $item;

        // Return empty array if no project is select
        if ($project <= 0) {
            // Make an exception if we are logged in...
            if ($user->id) {
                $item = new stdClass();
                $item->value = $user->id;
                $item->text  = $user->name;

                $items[] = $item;
            }

            return $items;
        }

        // Construct the query
        $query->select('u.id AS value, u.name AS text')
              ->from('#__users AS u')
              ->join('INNER', '#__jp_ref_users AS a ON a.user_id = u.id')
              ->join('INNER', '#__jp_tasks AS t ON t.id = a.item_id')
              ->where('a.item_type = ' . $this->_db->quote('com_jptasks.task'));

        // Implement View Level Access
        if (!$user->authorise('core.admin', 'com_jptasks')) {
            $levels = implode(',', $user->getAuthorisedViewLevels());
            $query->where('t.access IN (' . $levels . ')');
        }

        // Filter - State
        if (!$user->authorise('core.edit.state', 'com_jptasks')) {
            $query->where('t.state = 1');
        }

        // Filter - Project
        $query->where('t.project_id = ' . (int) $project);

        // Group and Order by clause
        $query->group('u.id')
              ->order('u.name ASC');

        // Get results
        $this->_db->setQuery($query);

        // Return the items
        return array_merge($items, (array) $this->_db->loadObjectList());
    }


    /**
     * Method to auto-populate the model state.
     * Note. Calling getState in this method will result in recursion.
     *
     * @return    void
     */
    protected function populateState($ordering = 'a.title', $direction = 'ASC')
    {
        $app    = Factory::getApplication();
        $access = JPtasksHelper::getActions();
        $user   = Factory::getApplication()->getIdentity();
        $session = Factory::getSession();

        // Check for clearFilter in URL or session
        $clearFilter = $app->input->get('clearFilter', 0, 'int');
        $sessionClearFilter = $session->get('com_jptasks.clearFilter', 0);

        // If clearFilter is in URL, save it to session and redirect
        if ($clearFilter) {
            $session->set('com_jptasks.clearFilter', 1);

            // Get current URL and remove clearFilter parameter
            $uri = \Joomla\CMS\Uri\Uri::getInstance();
            $uri->delVar('clearFilter');

            // Redirect to the same page without clearFilter parameter
            $app->redirect($uri->toString());
            return;
        }

        // If we have clearFilter in session, reset all inputs
        if ($sessionClearFilter) {

            $app->input->set('filter_published', '');
            $app->input->set('filter_search', '');
            $app->input->set('filter_milestone', '');
            $app->input->set('filter_tasklist', '');
            $app->input->set('filter_author', '');
            $app->input->set('filter_assigned', '');
            $app->input->set('filter_priority', '');
            $app->input->set('filter_complete', '');
            $app->input->set('filter_label', array());

            $session->set('com_jptasks.clearFilter', 0);

        }


        // Adjust the context to support modal layouts.
        $layout = \Joomla\CMS\Factory::getApplication()->input->getCmd('layout');
        $params  = $app->getParams();
        $itemid  = $app->input->get('Itemid', 0, 'int');
        $menu    = $app->getMenu()->getActive();

        // Merge app params with menu item params
		if ($menu) {
		    $menu_params = new Registry();

			$menu_params->loadString((string)$menu->getParams());
            $clone_params = clone $menu_params;
            $clone_params->merge($params);

            if (!$itemid) {
                $itemid = (int) $menu->id;
            }
		}

        // View Layout
        $this->setState('layout', $layout);
        if ($layout && $layout != 'print') $this->context .= '.' . $layout;

        $this->context .= '.' . $itemid;

        // Set Params
        $this->setState('params', $params);

        // State
        $state = $app->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', $params->get('filter_published'));
        $this->setState('filter.published', $state);

        // Filter on published for those who do not have edit or edit.state rights.
        if (!$access->get('core.edit.state') && !$access->get('core.edit')){
            $this->setState('filter.published', 1);
            $state = '';
        }

        // Filter - Project
        $project = JPApplicationHelper::getActiveProjectId('filter_project');
        $this->setState('filter.project', $project);

        // Filter - Search
        $search = \Joomla\CMS\Factory::getApplication()->input->getString('filter_search', '');
        $this->setState('filter.search', $search);

        $milestone = $app->getUserStateFromRequest($this->context . '.filter.milestone', 'filter_milestone', '','int');
        $this->setState('filter.milestone', 37);

        // Filter - Task list
        $list = $app->getUserStateFromRequest($this->context . '.filter.tasklist', 'filter_tasklist', '','int');
        $this->setState('filter.tasklist', $list);

        // Filter - Author
        $author = $app->getUserStateFromRequest($this->context . '.filter.author', 'filter_author', '');
        $this->setState('filter.author', $author);

        // Filter - Assigned User
        $assigned = $app->getUserStateFromRequest($this->context . '.filter.assigned', 'filter_assigned', '','int');
        $this->setState('filter.assigned', $assigned);

        // Filter - Priority
        $priority = $app->getUserStateFromRequest($this->context . '.filter.priority', 'filter_priority', '');
        $this->setState('filter.priority', $priority);

        // Filter - Complete
        $complete = $app->getUserStateFromRequest($this->context . '.filter.complete', 'filter_complete', '');
        $this->setState('filter.complete', $complete);

        // Filter - Labels
        $labels = \Joomla\CMS\Factory::getApplication()->input->get('filter_label', array());
        $this->setState('filter.labels', $labels);


        // Do not allow some filters if no project is selected
        if (!is_numeric($project) || intval($project) == 0) {
            $this->setState('filter.tasklist', '');
            $this->setState('filter.milestone', '');
            $this->setState('filter.labels', array());

            $milestone = '';
            $list      = '';
            $labels    = array();

            if ($author != $user->id) {
                $this->setState('filter.author', '');
                $author = '';
            }

            if ($assigned != $user->id) {
                $this->setState('filter.assigned', '');
                $assigned = '';
            }
        }

        if (!is_array($labels)) {
            $labels = array();
        }

        // Filter - Is set
        $this->setState('filter.isset',
            (is_numeric($state) || !empty($search) || is_numeric($author) ||
            is_numeric($assigned) || (is_numeric($list) && $list > 0) || (is_numeric($milestone) && $milestone > 0) ||
            count($labels) || is_numeric($complete))
        );

        // Set list limit
        $cfg   = Factory::getConfig();
        $limit = $app->getUserStateFromRequest($this->context . '.list.limit', 'limit', $params->get('display_num', $cfg->get('list_limit')), 'uint');
        $this->setState('list.limit', $limit);
        $app->set('list_limit', $limit);
        \Joomla\CMS\Factory::getApplication()->input->set('list_limit', $limit);

        // Set sorting order
        $ordering = $app->getUserStateFromRequest($this->context . '.list.ordering', 'filter_order', $params->get('filter_order'));
        $this->setState('list.ordering', $ordering);
        $app->set('filter_order', $ordering);
        \Joomla\CMS\Factory::getApplication()->input->set('filter_order', $ordering);

        // Set order direction
        $direction = $app->getUserStateFromRequest($this->context . '.list.direction', 'filter_order_Dir', $params->get('filter_order_Dir'));
        $this->setState('list.direction', $direction);
        $app->set('filter_order_Dir', $direction);
        \Joomla\CMS\Factory::getApplication()->input->set('filter_order_Dir', $direction);

        // Call parent method
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
     * @return    string           A store id.
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.project');
        $id .= ':' . $this->getState('filter.milestone');
        $id .= ':' . $this->getState('filter.tasklist');
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.assigned');
        $id .= ':' . $this->getState('filter.priority');
        $id .= ':' . $this->getState('filter.complete');
        $id .= ':' . $this->getState('filter.author');
        $id .= ':' . $this->getState('filter.search');

        return parent::getStoreId($id);
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
