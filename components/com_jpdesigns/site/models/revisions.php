<?php
/**
 * @package      Joomproject Pro
 * @subpackage   Designs
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\Registry\Registry;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;


jimport('joomla.application.component.modellist');
jimport('joomla.application.component.helper');


/**
 * Design Revision List Model
 *
 */
class JPdesignsModelRevisions extends ListModel
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
                'a.id', 'a.title', 'a.created', 'a.modified',
                'a.state', 'a.ordering', 'author_name', 'editor', 'access_level'
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
        $user  = Factory::getApplication()->getIdentity();

        // Select the required fields from the table.
        $query->select(
            $this->getState('list.select',
                'a.id, a.asset_id, a.project_id, a.parent_id, a.title, a.alias, a.description, a.created, '
                . 'a.file_name, a.file_extension, a.file_size, '
                . 'a.created_by, a.modified, a.modified_by, a.checked_out, '
                . 'a.checked_out_time, a.attribs, a.access, a.state, a.ordering'
            )
        );

        $query->from('#__jp_design_revisions AS a');

        // Join over the users for the checked out user.
        $query->select('uc.name AS editor');
        $query->join('LEFT', '#__users AS uc ON uc.id = a.checked_out');

        // Join over the asset groups.
        $query->select('ag.title AS access_level');
        $query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

        // Join over the users for the owner.
        $query->select('ua.name AS author_name, ua.email AS author_email');
        $query->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

        // Join over the projects for the project title and alias.
        $query->select('p.title AS project_title, p.alias AS project_alias');
        $query->join('LEFT', '#__jp_projects AS p ON p.id = a.project_id');

        // Join over the comments for comment count
        $query->select('COUNT(DISTINCT co.id) AS comments');
        $query->join('LEFT', '#__jp_comments AS co ON (co.context = '
              . $db->quote('com_jpdesigns.revision') . ' AND co.item_id = a.id)');

        // Join over the approved table for approved/declined count
        $query->select('COUNT(DISTINCT ap.created_by) AS approved_count');
        $query->join('LEFT', '#__jp_designs_approved AS ap ON (ap.revision_id = a.id AND ap.state = 1)');

        $query->select('COUNT(DISTINCT de.created_by) AS declined_count');
        $query->join('LEFT', '#__jp_designs_approved AS de ON (de.revision_id = a.id AND de.state = 0)');

        // Implement View Level Access
        /*if (!$user->authorise('core.admin', 'com_jpdesigns')) {
            $levels = implode(',', $user->getAuthorisedViewLevels());
            $query->where('a.access IN (' . $levels . ')');
        }*/


	    /* the new method of access level */
	    JPUserHelper::filterByViewAccess($query,'project','designs');

        // Filter fields
        $filters = array();
        $filters['a.state']      = array('STATE',       $this->getState('filter.published'));
        $filters['a.created_by'] = array('INT-NOTZERO', $this->getState('filter.author'));
        $filters['a.parent_id']  = array('INT-NOTZERO', $this->getState('filter.parent_id'));
        $filters['a']            = array('SEARCH',      $this->getState('filter.search'));

        // Apply Filter
        JPQueryHelper::buildFilter($query, $filters);

        // Group by ID
        $query->group('a.id');

        // Add the list ordering clause.
        $query->order($this->getState('list.ordering', 'a.ordering') . ' ' . $this->getState('list.direction', 'DESC'));

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
        // Get the list of revisions
        $items = parent::getItems();

        // Iterate through each revision to add some additional stuff
        foreach ($items as $i => &$item)
        {
            $params = new Registry;
            $params->loadString( (string) $item->attribs);

            // Convert the parameter fields into objects.
            $items[$i]->params = clone $this->getState('params');

            // Create slugs
            $item->slug         = $item->alias          ? ($item->id . ':' . $item->alias)                 : $item->id;
            $item->project_slug = $item->project_alias  ? ($item->project_id . ':' . $item->project_alias) : $item->project_id;

            // Get the thumbnails
            JPdesignsHelper::getThumbnails($item, 'revision');
        }

        return $items;
    }


    /**
     * Method to auto-populate the model state.
     * Note. Calling getState in this method will result in recursion.
     *
     * @return    void
     */
    protected function populateState($ordering = 'a.ordering', $direction = 'DESC')
    {
        $app = Factory::getApplication();

        // Adjust the context to support modal layouts.
        $layout = Factory::getApplication()->input->getCmd('layout');

        // View Layout
        $this->setState('layout', $layout);
        if ($layout) $this->context .= '.' . $layout;

        // Params
        $value = $app->getParams();
        $this->setState('params', $value);

        // State
        $state = $app->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->setState('filter.published', $state);

        // Filter on published for those who do not have edit or edit.state rights.
        $access = JPprojectsHelper::getActions();
        if (!$access->get('core.edit.state') && !$access->get('core.edit')) {
            $this->setState('filter.published', 1);
            $state = '';
        }

        // Filter - Search
        $search = \Joomla\CMS\Factory::getApplication()->input->getString('filter_search', '');
        $this->setState('filter.search', $search);

        // Filter - Author
        $author = $app->getUserStateFromRequest($this->context . '.filter.author', 'filter_author', '');
        $this->setState('filter.author', $author);

        // Filter - Parent id
        $parent = \Joomla\CMS\Factory::getApplication()->input->getUInt('id', '');
        $this->setState('filter.parent_id', $parent);

        // Filter - Is set
        $this->setState('filter.isset', (is_numeric($state) || !empty($search) || is_numeric($author)));

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
     *
     * @return    string           A store id.
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.author');
        $id .= ':' . $this->getState('filter.parent_id');
        $id .= ':' . $this->getState('filter.search');

        return parent::getStoreId($id);
    }
}
