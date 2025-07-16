<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_joomproject
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;


class JPusersModelRoles extends ListModel
{

    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'title', 'a.title',
                'state', 'a.state',
                'ordering', 'a.ordering',
                'created', 'a.created',
                'modified', 'a.modified',

            );
        }

        parent::__construct($config);
    }



    protected function populateState($ordering = 'a.id', $direction = 'asc')
    {
        $app = Factory::getApplication('administrator');

        // Adjust the context to support modal layouts.
        if ($layout = $app->input->get('layout', 'default', 'cmd')) {
            $this->context .= '.' . $layout;
        }

        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);



        // Load the parameters.
        $params = ComponentHelper::getParams('com_joomproject');
        $this->setState('params', $params);

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
     * @param   string  $id  A prefix for the store id.
     *
     * @return  string  A store id.
     *
     * @since   1.6
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.published');


        return parent::getStoreId($id);
    }


    protected function getListQuery()
    {
        // Create a new query object.
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $input = Factory::getApplication()->input;

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.*'
            )
        );
        $query->from('#__jp_users_roles AS a');
        // Join over the users for the checked out user.
        $query->select('uc.name AS editor')
            ->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

        // Join over the asset groups.
        $query->select('ag.title AS access_level')
            ->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');
        // Join over the users for the author.
        $query->select('ua.name AS author_name')
            ->join('LEFT', '#__users AS ua ON ua.id = a.created_by');
        // Add the list ordering clause.
        $query->order($db->qn($db->escape($this->getState('list.ordering', 'a.id'))) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));


        // Filter by published state
        $published = $this->getState('filter.published');

        if (is_numeric($published))
        {
            $query->where('a.state = ' . (int) $published);
        }
        elseif ($published === '')
        {
            $query->where('(a.state = 0 OR a.state = 1)');
        }


        // Filter by search in title.
        $search = $this->getState('filter.search');

        if (!empty($search))
        {
            if (stripos($search, 'id:') === 0)
            {
                $query->where('a.id = ' . (int) substr($search, 3));
            }
            elseif (stripos($search, 'content:') === 0)
            {
                $search = $db->quote('%' . $db->escape(substr($search, 8), true) . '%');
                $query->where('(a.title LIKE ' . $search . ' )');
            }
            else
            {
                $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
                $query->where('(a.title like ' . $search . ' )');
            }
        }

        return $query;

    }
}
