<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jpprojects
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Table\Table;
use Joomla\Database\ParameterType;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;


jimport('joomla.application.component.modellist');


/**
 * Methods supporting a list of project records.
 *
 */
class JPprojectsModelProjects extends ListModel
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
                'id', 'a.id',
                'title', 'a.title',
                'alias', 'a.alias',
                'catid', 'a.catid', 'category_title',
                'access', 'a.access', 'access_level',
                'created', 'a.created',
                'a.created_by', 'author_name',
                'a.modified',
                'a.modified_by',
                'a.access', 'access_level',
                'a.start_date',
                'a.end_date',
                'state', 'a.state',
                'author_id',
                'category_id',

            );
        }

        parent::__construct($config);
    }


    /**
     * Build a list of authors
     *
     * @return    array    The author list
     */
    public function getAuthors()
    {
        $query = $this->_db->getQuery(true);

        // Construct the query
        $query->select('u.id AS value, u.name AS text')
              ->from('#__users AS u')
              ->join('INNER', '#__jp_projects AS a ON a.created_by = u.id')
              ->group('u.id')
              ->order('u.name ASC');

        // Return the result
        $this->_db->setQuery($query, 0, 50);
        return (array) $this->_db->loadObjectList();
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
    protected function populateState($ordering = 'a.id', $direction = 'asc')
    {
        // Initialise variables.
        $app = Factory::getApplication();

        // Adjust the context to support modal layouts.
        if ($layout = Factory::getApplication()->input->get('layout')) $this->context .= '.' . $layout;


        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');


        $this->setState('filter.published', $published);

        $level = $this->getUserStateFromRequest($this->context . '.filter.level', 'filter_level');
        $this->setState('filter.level', $level);

        $formSubmitted = $app->input->post->get('form_submitted');

        // Gets the value of a user state variable and sets it in the session
        $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access');
        $this->getUserStateFromRequest($this->context . '.filter.author_id', 'filter_author_id');
        $this->getUserStateFromRequest($this->context . '.filter.category_id', 'filter_category_id');

        if ($formSubmitted)
        {
            $access = $app->input->post->get('access');
            $this->setState('filter.access', $access);

            $authorId = $app->input->post->get('author_id');
            $this->setState('filter.author_id', $authorId);

            $categoryId = $app->input->post->get('category_id');
            $this->setState('filter.category_id', $categoryId);

        }

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
        $id .= ':' . serialize($this->getState('filter.access'));
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . serialize($this->getState('filter.category_id'));
        $id .= ':' . serialize($this->getState('filter.author_id'));


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
                'a.id, a.title, a.alias, a.catid, a.description, a.checked_out, a.checked_out_time,'
                . 'a.state, a.access, a.created, a.created_by,'
                . 'a.start_date, a.end_date'
            )
        );

        $query->from('#__jp_projects AS a');

        // Join over the users for the checked out user.
        $query->select('uc.name AS editor')
              ->join('LEFT', '#__users AS uc ON uc.id = a.checked_out');

        // Join over the asset groups.
        $query->select('ag.title AS access_level')
              ->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

        // Join over the users for the author name.
        $query->select('ua.name AS manager_name, ua.name AS author_name')
              ->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

        // Join over the categories.
        $query->select('c.title AS category_title, c.created_user_id AS category_uid, c.level AS category_level')
            ->join('LEFT', '#__categories AS c ON c.id = a.catid');
        // Join over the parent categories.
        $query->select('parent.title AS parent_category_title, parent.id AS parent_category_id,
								parent.created_user_id AS parent_category_uid, parent.level AS parent_category_level')
            ->join('LEFT', '#__categories AS parent ON parent.id = c.parent_id');
        // Filter by access level.
        $access = $this->getState('filter.access');

        if (is_numeric($access))
        {
            $access = (int) $access;
            $query->where($db->quoteName('a.access') . ' = :access')
                ->bind(':access', $access, ParameterType::INTEGER);
        }
        elseif (is_array($access))
        {
            $access = ArrayHelper::toInteger($access);
            $query->whereIn($db->quoteName('a.access'), $access);
        }

        // Filter by access level on categories.
		if (!$user->authorise('core.admin'))
    {
    $groups = $user->getAuthorisedViewLevels();
			$query->whereIn($db->quoteName('a.access'), $groups);
			$query->whereIn($db->quoteName('c.access'), $groups);
		}

	    /* Implement View Level Access - deprecated we will use another method
		if (!$user->authorise('core.admin')) {
			$levels = implode(',', $user->getAuthorisedViewLevels());

			$query->where('a.access IN (' . $levels . ')');
		}*/



	    /* the new method of access level */
	    JPUserHelper::filterByViewAccess($query,'project','projects','projects');


	    // Filter by published state
        $published = (string) $this->getState('filter.published');


        if ($published !== '*')
        {
            if (is_numeric($published))
            {
                $state = (int) $published;
                $query->where($db->quoteName('a.state') . ' = :state')
                    ->bind(':state', $state, ParameterType::INTEGER);
            }
            else
            {
                $query->whereIn(
                    $db->quoteName('a.state'),
                    [
                        ContentComponent::CONDITION_PUBLISHED,
                        ContentComponent::CONDITION_UNPUBLISHED,
                    ]
                );
            }
        }

        // Filter by categories and by level
        // Filter by categories and by level
        $categoryId = $this->getState('filter.category_id', array());
        $level = $this->getState('filter.level');

        if (!is_array($categoryId))
        {
            $categoryId = $categoryId ? array($categoryId) : array();
        }

        // Case: Using both categories filter and by level filter
        if (count($categoryId))
        {
            $categoryId = ArrayHelper::toInteger($categoryId);
            $categoryTable = Table::getInstance('Category', 'JTable');
            $subCatItemsWhere = array();

            foreach ($categoryId as $filter_catid)
            {
                $categoryTable->load($filter_catid);
                $subCatItemsWhere[] = '(' .
                    ($level ? 'c.level <= ' . ((int) $level + (int) $categoryTable->level - 1) . ' AND ' : '') .
                    'c.lft >= ' . (int) $categoryTable->lft . ' AND ' .
                    'c.rgt <= ' . (int) $categoryTable->rgt . ')';
            }

            $query->where('(' . implode(' OR ', $subCatItemsWhere) . ')');
        }

        // Case: Using only the by level filter
        elseif ($level)
        {
            $query->where('c.level <= ' . (int) $level);
        }



        // Filter by author
        $authorId = $this->getState('filter.author_id');

        if (is_numeric($authorId))
        {
            $authorId = (int) $authorId;
            $type = $this->getState('filter.author_id.include', true) ? ' = ' : ' <> ';
            $query->where($db->quoteName('a.created_by') . $type . ':authorId')
                ->bind(':authorId', $authorId, ParameterType::INTEGER);
        }
        elseif (is_array($authorId))
        {
            // Check to see if by_me is in the array
            if (\in_array('by_me', $authorId))

                // Replace by_me with the current user id in the array
            {
                $authorId['by_me'] = $user->id;
            }

            $authorId = ArrayHelper::toInteger($authorId);
            $query->whereIn($db->quoteName('a.created_by'), $authorId);
        }

        // Filter by search in title.
        // Filter by search in title.
        $search = $this->getState('filter.search');

        if (!empty($search))
        {
            if (stripos($search, 'id:') === 0)
            {
                $query->where('a.id = ' . (int) substr($search, 3));
            }
            elseif (stripos($search, 'author:') === 0)
            {
                $search = $db->quote('%' . $db->escape(substr($search, 7), true) . '%');
                $query->where('(ua.name LIKE ' . $search . ' OR ua.username LIKE ' . $search . ')');
            }
            elseif (stripos($search, 'content:') === 0)
            {
                $search = $db->quote('%' . $db->escape(substr($search, 8), true) . '%');
                $query->where('(a.description LIKE ' . $search . ')');
            }
            else
            {
                $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
                $query->where('(a.title LIKE ' . $search . ' OR a.alias LIKE ' . $search . ' OR a.description LIKE ' . $search . ')');
            }
        }


        // Add the list ordering clause.
        $orderCol  = $this->state->get('list.ordering', 'a.id');
        $orderDirn = $this->state->get('list.direction', 'DESC');

        if ( $orderCol === 'category_title')
        {
            $ordering = [
                $db->quoteName('c.title') . ' ' . $db->escape($orderDirn),
            ];
        }
        else
        {
            $ordering = $db->escape($orderCol) . ' ' . $db->escape($orderDirn);
        }

        $query->order($ordering);
        // Group by ID
        $query->group('a.id');

        return $query;
    }
}
