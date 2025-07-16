<?php
/**
 * @package      Joomproject Pro
 * @subpackage   Designs
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;


/**
 * Methods supporting a list of project records.
 *
 */
class JPdesignsModelRevisions extends ListModel
{
    /**
     * Constructor
     *
     * @param    array          An optional associative array of configuration settings.
     * @see      jcontroller
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'a.id', 'a.title', 'a.alias', 'a.created', 'a.created_by', 'a.modified',
                'a.modified_by', 'a.checked_out', 'a.checked_out_time',
                'a.attribs', 'a.access', 'access_level',
                'a.state', 'a.ordering', 'a.project_id', 'project_title',
            );
        }

        parent::__construct($config);
    }


    /**
     * Build a list of project authors
     *
     * @return    jdatabasequery
     */
    public function getAuthors()
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        // Construct the query
        $query->select('u.id AS value, u.name AS text')
              ->from('#__users AS u')
              ->join('INNER', '#__jp_design_revisions AS a ON (a.created_by = u.id AND a.parent_id = '  . $db->quote((int) $this->getState('filter.parent_id')) . ')')
              ->group('u.id')
              ->order('u.name');

        // Return the result
        $db->setQuery((string) $query);
        return (array) $db->loadObjectList();
    }


    /**
     * Method to get the title of the current design
     *
     * @return    jdatabasequery
     */
    public function getDesignTitle()
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('title')
              ->from('#__jp_designs')
              ->where('id = ' . $db->quote((int) $this->getState('filter.parent_id')));

        $db->setQuery($query);
        $title = $db->loadResult();

        return $title;
    }


    /**
     * Method to get an array of data items.
     *
     * @return    mixed    An array of data items on success, false on failure.
     */
    /*
    public function getItems()
    {
        static $access_allowed = null;

        if (is_null($access_allowed)) {
            $access_allowed = $this->checkDesign();
        }

        if (!$access_allowed) {
            return false;
        }

        return parent::getItems();
    }
   */


    /**
     * Method to auto-populate the model state.
     * Note: Calling getState in this method will result in recursion.
     *
     * @return    void
     */
    protected function populateState($ordering = 'a.ordering', $direction = 'asc')
    {
        // Initialise variables.
        $app = Factory::getApplication();

        // Adjust the context to support modal layouts.
        if ($layout = \Joomla\CMS\Factory::getApplication()->input->get('layout')) $this->context .= '.' . $layout;

        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $author = $app->getUserStateFromRequest($this->context . '.filter.author_id', 'filter_author_id');
        $this->setState('filter.author_id', $author);

        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        $access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', '');
        $this->setState('filter.access', $access);

        $parent = $this->getUserStateFromRequest($this->context . '.filter.parent_id', 'filter_parent_id', '');
        $this->setState('filter.parent_id', $parent);

        $project = JPApplicationHelper::getActiveProjectId('filter_project');
        $this->setState('filter.project', $project);

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
     * @return    string           A store id.
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.access');
        $id .= ':' . $this->getState('filter.author_id');
        $id .= ':' . $this->getState('filter.parent_id');
        $id .= ':' . $this->getState('filter.project');

        return parent::getStoreId($id);
    }


    /**
     * Build an SQL query to load the list data.
     *
     * @return    jdatabasequery
     */
    protected function getListQuery()
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);
        $user  = Factory::getApplication()->getIdentity();

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.project_id, a.parent_id, a.title, a.alias, a.checked_out, a.checked_out_time,'
                . 'a.state, a.access, a.created, a.created_by, a.ordering'
            )
        );
        $query->from('#__jp_design_revisions AS a');

        // Join over the users for the checked out user.
        $query->select('uc.name AS editor')
              ->join('LEFT', '#__users AS uc ON uc.id = a.checked_out');

        // Join over the asset groups.
        $query->select('ag.title AS access_level')
              ->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

        // Join over the users for the author name.
        $query->select('ua.name AS author_name')
              ->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

        // Join over the projects.
        $query->select('p.title AS project_title')
              ->join('LEFT', '#__jp_projects AS p ON p.id = a.project_id');

        // Join over the designs.
        $query->select('d.title AS design_title')
              ->join('LEFT', '#__jp_designs AS d ON d.id = a.parent_id');

        // Implement View Level Access
        /*if (!$user->authorise('core.admin')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('a.access IN (' . $groups . ')');
        }*/

	    /* the new method of access level */
	    JPUserHelper::filterByViewAccess($query,'project','designs');

        // Filter by parent id
        if( $this->getState('filter.parent_id')){
            $query->where('a.parent_id = ' . $db->quote((int) $this->getState('filter.parent_id')));
        }


        // Filter by published state
        $published = $this->getState('filter.published');
        if (is_numeric($published)) {
            $query->where('a.state = ' . (int) $published);
        }
        elseif ($published === '') {
            $query->where('(a.state = 0 OR a.state = 1)');
        }

        // Filter by access level.
        if ($access = $this->getState('filter.access')) {
            $query->where('a.access = ' . (int) $access);
        }

        // Implement View Level Access
        if (!$user->authorise('core.admin')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('a.access IN (' . $groups . ')');
        }

        // Filter by author
        $author = $this->getState('filter.author_id');
        if (is_numeric($author)) {
            $type = $this->getState('filter.author_id.include', true) ? '= ' : '<>';
            $query->where('a.created_by ' . $type . (int) $author);
        }

        // Filter by search in title.
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = '. (int) substr($search, 3));
            }
            elseif (stripos($search, 'author:') === 0) {
                $search = $db->Quote('%' . $db->escape(substr($search, 7), true) . '%');
                $query->where('(ua.name LIKE ' . $search . ' OR ua.username LIKE ' . $search . ')');
            }
            else {
                $search = $db->Quote('%' . $db->escape($search, true) . '%');
                $query->where('(a.title LIKE ' . $search . ' OR a.alias LIKE ' . $search . ')');
            }
        }

        // Add the list ordering clause.
        $order_col = $this->state->get('list.ordering', 'a.ordering');
        $order_dir = $this->state->get('list.direction', 'asc');

        $query->order($db->escape($order_col . ' ' . $order_dir));
        $query->group('a.id');

        return $query;
    }


    /**
     * Method to check if the user is allowed to access the design revisions
     *
     * @return    boolean    True if allowed, False if not
     */
    protected function checkDesign()
    {
        $parent_id = (int) $this->getState('filter.parent_id');
        $db        = $this->getDbo();
        $query     = $db->getQuery(true);
        $user      = Factory::getApplication()->getIdentity();




        if ($parent_id <= 0) {
            $this->setError(Text::_('COM_JOOMPROJECT_ERROR_DESIGN_NOT_FOUND'));
            return false;
        }

        $query->select('access')
              ->from('#__jp_designs')
              ->where('id = ' . $db->quote($parent_id));

        $db->setQuery($query);
        $access = (int) $db->loadResult();


        if ($access <= 0) {
            $this->setError(Text::_('COM_JOOMPROJECT_ERROR_DESIGN_NOT_FOUND'));
            return false;
        }

        if (!$user->authorise('core.admin')) {
            $levels = implode(',', $user->getAuthorisedViewLevels());

            if (!in_array($access, $levels)) {
                $this->setError(Text::_('COM_JOOMPROJECT_ERROR_DESIGN_ACCESS_DENIED'));
                return false;
            }
        }

        return true;
    }
}
