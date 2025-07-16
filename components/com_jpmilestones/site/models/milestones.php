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

use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Utilities\ArrayHelper;


jimport('joomla.application.component.modellist');
jimport('joomla.application.component.helper');


/**
 * This models supports retrieving lists of milestones.
 *
 */
class JPmilestonesModelMilestones extends ListModel
{

    /**
     * Constructor.
     *
     * @param    array          $config    An optional associative array of configuration settings.
     * @see      jcontroller
     */
    public function __construct($config = array())
    {
        // Set field filter
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'a.id', 'project_title, a.title', 'a.created', 'a.modified',
                'a.checked_out', 'a.checked_out_time',
                'a.state', 'a.start_date', 'a.end_date',
                'author_name', 'editor', 'access_level',
                'project_title', 'tasklists', 'tasks'
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
        $db    = $this->getDbo();
        $query = $db->getQuery(true);
        $user  = Factory::getApplication()->getIdentity();

        // Get possible filters
        $filter_cat    = $this->getState('filter.category');

        // Select the required fields from the table.
        $query->select(
            $this->getState('list.select',
                'a.id, a.asset_id, a.catid, a.project_id, a.title, a.alias, a.description, a.created,'
                . 'a.created_by, a.modified, a.modified_by, a.checked_out,'
                . 'a.checked_out_time, a.attribs, a.access, a.state, a.start_date,'
                . 'a.end_date'
            )
        );

        $query->from('#__jp_milestones AS a');

        // Join over the users for the checked out user
        $query->select('uc.name AS editor');
        $query->join('LEFT', '#__users AS uc ON uc.id = a.checked_out');

        // Join over the categories.
        $query->select('c.title AS category_title')
            ->join('LEFT', '#__categories AS c ON c.id = a.catid');

        // Join over the asset groups
        $query->select('ag.title AS access_level');
        $query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

        // Join over the users for the owner
        $query->select('ua.name AS author_name, ua.email AS author_email');
        $query->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

        // Join over the projects for project title and alias
        $query->select('p.title AS project_title, p.alias AS project_alias');
        $query->join('LEFT', '#__jp_projects AS p ON p.id = a.project_id');

        // Join over the task lists for task list count
        $query->select('COUNT(DISTINCT tl.id) AS tasklists');
        $query->join('LEFT', '#__jp_task_lists AS tl ON tl.milestone_id = a.id');

        // Join over the label refs for label count
        $query->select('COUNT(DISTINCT lbl.id) AS label_count');
        $query->join('LEFT', '#__jp_ref_labels AS lbl ON (lbl.item_id = a.id AND lbl.item_type = ' . $db->quote('com_jpmilestones.milestone') . ')');

        // Join over the observer table for email notification status
        if ($user->get('id') > 0) {
            $query->select('COUNT(DISTINCT obs.user_id) AS watching');
            $query->join('LEFT', '#__jp_ref_observer AS obs ON (obs.item_type = ' . $db->quote('com_jpmilestones.milestone')
                               . ' AND obs.item_id = a.id AND obs.user_id = '
                               . $db->quote($user->get('id')) . ')'
                        );
        }

        // Join over the attachments for attachment count
        $query->select('COUNT(DISTINCT at.id) AS attachments');
        $query->join('LEFT', '#__jp_ref_attachments AS at ON (at.item_type = '
              . $db->quote('com_jpmilestones.milestone') . ' AND at.item_id = a.id)');

        // Join over the comments for comment count
        $query->select('COUNT(DISTINCT co.id) AS comments');
        $query->join('LEFT', '#__jp_comments AS co ON (co.context = '
              . $db->quote('com_jpmilestones.milestone') . ' AND co.item_id = a.id)');

	    JPUserHelper::filterByViewAccess($query,'project','milestones');

        // Filter by a single or group of categories.
        $baselevel = 1;
        if (is_numeric($filter_cat)) {
            $filter_cat = (int) $filter_cat;
            $cat_tbl    = Table::getInstance('Category', 'JTable');

            if ($cat_tbl) {
                if ($cat_tbl->load($filter_cat)) {
                    $rgt       = $cat_tbl->rgt;
                    $lft       = $cat_tbl->lft;
                    $baselevel = (int) $cat_tbl->level;

                    $query->where('c.lft >= ' . (int) $lft);
                    $query->where('c.rgt <= ' . (int) $rgt);
                }
            }
        }
        elseif (is_array($filter_cat)) {
            ArrayHelper::toInteger($filter_cat);

            $filter_cat = implode(',', $filter_cat);
            $query->where('a.catid IN (' . $filter_cat . ')');
        }

        // Filter labels
        if (count($this->getState('filter.labels'))) {
            $labels = $this->getState('filter.labels');

            \Joomla\Utilities\ArrayHelper::toInteger($labels);

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
        $filters['a.state']      = array('STATE',       $this->getState('filter.published'));
        $filters['a.project_id'] = array('INT-NOTZERO', $this->getState('filter.project'));
        $filters['a.created_by'] = array('INT-NOTZERO', $this->getState('filter.author'));
        $filters['a']            = array('SEARCH',      $this->getState('filter.search'));

        // Apply Filter
        JPQueryHelper::buildFilter($query, $filters);

        // Group by ID
        $query->group('a.id');

        // Add the list ordering clause.
        $project = (int) $this->getState('filter.project');
        $order   = $this->getState('list.ordering', 'a.title');

        if ($order == '') {
            $order = 'a.title';
        }

        if ($project <= 0) {
            if ($order != 'project_title') {
                $order = 'project_title ASC, ' . $order;
            }
        }

        $query->order($order . ' ' . $this->getState('list.direction', 'ASC'));

        return $query;
    }


    /**
     * Method to get a list of items.
     * Overriden to inject convert the attribs field into a JParameter object.
     *
     * @return    mixed    $items    An array of objects on success, false on failure.
     */
    public function getItems()
    {
        $items  = parent::getItems();

        if(!$items){
            return false;
        }

        $labels = $this->getInstance('Labels', 'JPModel');

        $tasks_exists = JPApplicationHelper::enabled('com_jptasks');

        $pks = \Joomla\Utilities\ArrayHelper::getColumn($items, 'id');

        // Get aggregate data
        $progress        = array();
        $total_tasks     = array();
        $completed_tasks = array();

        if ($tasks_exists) {
            JLoader::register('JPtasksModelTasks', JPATH_SITE . '/components/com_jptasks/models/tasks.php');

            $tmodel             = BaseDatabaseModel::getInstance('Tasks', 'JPtasksModel', array('ignore_request' => true));
            $progress           = $tmodel->getAggregatedProgress($pks, 'milestone_id');
            $total_tasks        = $tmodel->getAggregatedTotal($pks, 'milestone_id');
            $completed_tasks    = $tmodel->getAggregatedTotal($pks, 'milestone_id', 1);
        }

        foreach ($items as $i => &$item)
        {
            // Convert the parameter fields into objects.
	        $params = new Registry;
            $params->loadString( (string) $item->attribs);

            // get project attribs
	        $project_attribs = new Registry;
	        $project_attribs = $project_attribs->loadString( (string) $this->getProjectAttribs($items[$i]->project_id));

            // if no category selected (catid: 0)
            $items[$i]->category_title = empty($items[$i]->category_title) ? 'ROOT' : $items[$i]->category_title;

	        // merge global params with milestone params
	        $mstateParams = clone $this->getState('params');
	        $items[$i]->params = $mstateParams->merge(clone $params);

	        // override milestone color with the one defined in project
	        if(!empty($project_attribs->get('milestone_color',''))){
		        $items[$i]->params->set('milestone_color',$project_attribs->get('milestone_color',''));
	        }

            // Create slugs
            $items[$i]->slug         = $items[$i]->alias ? ($items[$i]->id . ':' . $items[$i]->alias) : $items[$i]->id;
            $items[$i]->project_slug = $items[$i]->project_alias ? ($items[$i]->project_id . ':' . $items[$i]->project_alias) : $items[$i]->project_id;

            // Get the labels
            if ($items[$i]->label_count > 0) {
                $items[$i]->labels = $labels->getConnections('com_jpmilestones.milestone', $items[$i]->id);
            }

            if (!isset($items[$i]->watching)) {
                $items[$i]->watching = 0;
            }

            // Inject task count
            $items[$i]->tasks = (isset($total_tasks[$item->id]) ? $total_tasks[$item->id] : 0);

            // Inject completed task count
            $items[$i]->completed_tasks = (isset($completed_tasks[$item->id]) ? $completed_tasks[$item->id] : 0);

            // Inject progress
            $items[$i]->progress = (isset($progress[$item->id]) ? $progress[$item->id] : 0);
        }

        return $items;
    }


    /**
     * Build a list of authors
     *
     * @return    array
     */
    public function getAuthors()
    {
        $db     = $this->getDbo();
        $query  = $db->getQuery(true);
        $user   = $user = Factory::getApplication()->getIdentity();
        $access = JPmilestonesHelper::getActions();

        // Return empty array if no project is select
        $project = (int) $this->getState('filter.project');

        if ($project <= 0) {
            return array();
        }

        // Construct the query
        $query->select('u.id AS value, u.name AS text');
        $query->from('#__users AS u');
        $query->join('INNER', '#__jp_milestones AS a ON a.created_by = u.id');

        // Implement View Level Access
        if (!$access->get('core.admin', 'com_jpmilestones')) {
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

        $db->setQuery((string) $query);
        $items = (array) $db->loadObjectList();

        // Return the items
        return $items;
    }


    /**
     * Method to auto-populate the model state.
     * Note. Calling getState in this method will result in recursion.
     *
     * @return    void
     */
    protected function populateState($ordering = 'project_title, a.title', $direction = 'ASC')
    {
        $app    = Factory::getApplication();
        $access = JPmilestonesHelper::getActions();

        // Adjust the context to support modal layouts.
        $layout  = \Joomla\CMS\Factory::getApplication()->input->getCmd('layout');
        $params  = $app->getParams();
        $itemid  = $app->input->get('Itemid', 0, 'int');
        $menu    = $app->getMenu()->getActive();

        // Merge app params with menu item params
		if ($menu) {

		    $menu_params = new Registry();

			$menu_params->loadString( (string) $menu->getParams());
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

        // Params
        $value = $app->getParams();
        $this->setState('params', $value);

        // State
        $state = $app->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', $params->get('filter_published'));
        $this->setState('filter.published', $state);

        // Filter on published for those who do not have edit or edit.state rights.
        if (!$access->get('core.edit.state') && !$access->get('core.edit')){
            $this->setState('filter.published', 1);
            $state = '';
        }

        // Filter - Search
        $search = \Joomla\CMS\Factory::getApplication()->input->getString('filter_search', '');
        $this->setState('filter.search', $search);

        // Filter - Project
        $project = JPApplicationHelper::getActiveProjectId('filter_project');
        $this->setState('filter.project', $project);

        // Filter - Category
        $cat = $app->getUserStateFromRequest($this->context . '.filter.category', 'filter_category', $params->get('filter_category'));
        $this->setState('filter.category', $cat);

        // Filter - Author
        $author = $app->getUserStateFromRequest($this->context . '.filter.author', 'filter_author', '');
        $this->setState('filter.author', $author);

        // Filter - Labels
        $labels = \Joomla\CMS\Factory::getApplication()->input->get('filter_label', array());
        $this->setState('filter.labels', $labels);

        // Do not allow to filter by author if no project is selected
        if (!is_numeric($project) || intval($project) == 0) {
            $this->setState('filter.author', '');
            $this->setState('filter.labels', array());
            $author = '';
            $labels = array();
        }

        if (!is_array($labels)) {
            $labels = array();
        }

        // Filter - Is set
        $this->setState('filter.isset', (is_numeric($state) || !empty($search) || is_numeric($author) || count($labels)));

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
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.project');
        $id .= ':' . $this->getState('filter.category');
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
