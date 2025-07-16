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

use Joomla\Database\ParameterType;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;

jimport('joomla.application.component.modellist');


/**
 * Methods supporting a list of design album records.
 *
 */
class JPdesignsModelAlbums extends ListModel
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
                'a.id', 'a.title',  'a.alias', 'a.created', 'a.created_by', 'a.modified',
                'a.modified_by', 'a.checked_out', 'a.checked_out_time',
                'a.attribs', 'a.access', 'access_level',
                'a.state', 'a.ordering', 'album_count',
                'a.project_id', 'project_title',
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
              ->join('INNER', '#__jp_design_albums AS a ON a.created_by = u.id')
              ->group('u.id')
              ->order('u.name');

        // Return the result
        $db->setQuery((string) $query);
        return (array) $db->loadObjectList();
    }


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

        $manager_id = $app->getUserStateFromRequest($this->context . '.filter.author_id', 'filter_author_id');
        $this->setState('filter.author_id', $manager_id);

        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        $access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', '');
        $this->setState('filter.access', $access);

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

        $filter_project = $this->getState('filter.project');

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.project_id, a.title, a.alias, a.checked_out, a.checked_out_time,'
                . 'a.state, a.access, a.created, a.created_by, a.ordering'
            )
        );
        $query->from('#__jp_design_albums AS a');

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

        // Count designs
        $query->select('COUNT(DISTINCT d.id) AS design_count')
              ->join('LEFT', '#__jp_designs AS d ON d.album_id = a.id');

        // Implement View Level Access
        if (!$user->authorise('core.admin')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('a.access IN (' . $groups . ')');
        }

        // Filter by project
        if (is_numeric($filter_project) && $filter_project > 0) {
            $query->where('a.project_id = ' . (int) $filter_project);
        }

        // Filter by published state
        $published = (string) $this->getState('filter.published');

        if ($published !== '*') {
            if (is_numeric($published)) {
                $state = (int)$published;
                $query->where($db->quoteName('a.state') . ' = :state')
                    ->bind(':state', $state, ParameterType::INTEGER);
            }else{

                $query->whereIn(
                    $db->quoteName('a.state'),
                    [
                        ContentComponent::CONDITION_PUBLISHED,
                        ContentComponent::CONDITION_UNPUBLISHED,
                    ]
                );
            }
        }


        // Filter by access level.
        if ($access = $this->getState('filter.access')) {
            $query->where('a.access = ' . (int) $access);
        }

        // Implement View Level Access
       /* if (!$user->authorise('core.admin')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('a.access IN (' . $groups . ')');
        }*/

	    /* the new method of access level */
	    JPUserHelper::filterByViewAccess($query,'project','designs');

        // Filter by author
        $author = (int) $this->getState('filter.author_id');
        if (is_numeric($author) && $access > 0) {
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

        if ($order_col == 'a.ordering') {
			$order_col = 'a.project_id ' . $order_dir . ', a.ordering';
		}

        $query->group('a.id');
        $query->order($db->escape($order_col . ' ' . $order_dir));

        return $query;
    }
}
