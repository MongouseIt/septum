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
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Utilities\ArrayHelper;

jimport('joomla.application.component.modellist');
jimport('joomla.application.component.helper');
jimport('joomla.filesystem.file');


/**
 * Design List Model
 *
 */
class JPdesignsModelDesigns extends ListModel
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
                'a.state', 'author_name', 'editor', 'access_level',
                'a.ordering', 'album_title'
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
                'a.id, a.asset_id, a.project_id, a.album_id, a.title, a.alias, '
                . 'a.description, a.file_name, a.file_extension, a.file_size, '
                . 'a.created_by, a.created, a.modified, a.modified_by, a.checked_out, '
                . 'a.checked_out_time, a.attribs, a.access, a.state, a.ordering'
            )
        );

        $query->from('#__jp_designs AS a');

        // Implement View Level Access
        /*if (!$user->authorise('core.admin', 'com_jpdesigns')) {
            $levels = implode(',', $user->getAuthorisedViewLevels());

            $query->where('a.access IN (' . $levels . ')');
        }*/


	    /* the new method of access level */
	    JPUserHelper::filterByViewAccess($query,'project','designs');

        // Join over the users for the checked out user.
        $query->select('uc.name AS editor');
        $query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

        // Join over the asset groups.
        $query->select('ag.title AS access_level');
        $query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

        // Join over the users for the owner.
        $query->select('ua.name AS author_name, ua.email AS author_email');
        $query->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

        // Join over the projects for the project title and alias.
        $query->select('p.title AS project_title, p.alias AS project_alias');
        $query->join('LEFT', '#__jp_projects AS p ON p.id = a.project_id');

        // Join over the albums.
        $query->select('c.title AS album_title, c.alias AS album_alias');
        $query->join('LEFT', '#__jp_design_albums AS c ON c.id = a.album_id');

        // Join over the revisions for revision count
        $query->select('COUNT(DISTINCT r.id) AS revision_count');
        $query->join('LEFT', '#__jp_design_revisions AS r ON r.parent_id = a.id');

        // Join over the label refs for label count
        $query->select('COUNT(DISTINCT lbl.id) AS label_count');
        $query->join('LEFT', '#__jp_ref_labels AS lbl ON (lbl.item_id = a.id AND lbl.item_type = ' . $db->quote('com_jpdesigns.design') . ')');

        // Join over the observer table for email notification status
        if ($user->get('id') > 0) {
            $query->select('COUNT(DISTINCT obs.user_id) AS watching');
            $query->join('LEFT', '#__jp_ref_observer AS obs ON (obs.item_type = '
                  . $db->quote('com_jpdesigns.design') . ' AND obs.item_id = a.id AND obs.user_id = '
                  . $db->quote($user->get('id')) . ')'
            );
        }

        // Join over the comments for comment count
        $query->select('COUNT(DISTINCT co.id) AS comments');
        $query->join('LEFT', '#__jp_comments AS co ON (co.context = '
              . $db->quote('com_jpdesigns.design') . ' AND co.item_id = a.id)');

        // Join over the approved table for approved/declined count
        $query->select('COUNT(DISTINCT ap.revision_id) AS approved_count');
        $query->join('LEFT', '#__jp_designs_approved AS ap ON (ap.id = a.id AND ap.state = 1)');

        $query->select('COUNT(DISTINCT de.revision_id) AS declined_count');
        $query->join('LEFT', '#__jp_designs_approved AS de ON (de.id = a.id AND de.state = 0)');

        // Filter labels
        if (count($this->getState('filter.labels'))) {
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
        $filters['a.state']      = array('STATE',       $this->getState('filter.published'));
        $filters['a.created_by'] = array('INT-NOTZERO', $this->getState('filter.author'));
        $filters['a']            = array('SEARCH',      $this->getState('filter.search'));
        $filters['a.project_id'] = array('INT-NOTZERO', $this->getState('filter.project'));
        $filters['a.album_id']   = array('INT-NOTZERO', $this->getState('filter.album'));

        // Apply Filter
        JPQueryHelper::buildFilter($query, $filters);

        // Group by ID
        $query->group('a.id');

        // Add the list ordering clause.
        $query->order($this->getState('list.ordering', 'a.ordering') . ' ' . $this->getState('list.direction', 'ASC'));

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
        // Get the list of designs
        $items  = parent::getItems();
        $labels = $this->getInstance('Labels', 'JPModel');

        $cfg  = ComponentHelper::getParams('com_jpdesigns', true);
        $user = Factory::getApplication()->getIdentity();

        list($p_w, $p_h) = explode('x', $cfg->get('img_preview_size', '300x300'), 2);

        // Iterate through each design to add some additional stuff
        foreach ($items as $i => &$item)
        {
            $params = new Registry;
            $params->loadString( (string) $item->attribs);

            // Convert the parameter fields into objects.
            $items[$i]->params = clone $this->getState('params');

            // Create slugs
            $item->slug         = $item->alias          ? ($item->id . ':' . $item->alias)                 : $item->id;
            $item->project_slug = $item->project_alias  ? ($item->project_id . ':' . $item->project_alias) : $item->project_id;
            $item->album_slug   = $item->album_alias    ? ($item->album_id . ':' . $item->album_alias)     : $item->album_id;

            // Get the labels
            if ($items[$i]->label_count > 0) {
                $item->labels = $labels->getConnections('com_jpdesigns.design', $item->id);
            }
            else {
                $item->labels = array();
            }

            // Get thumbnails
            JPdesignsHelper::getThumbnails($item);

            // Get the latest revision
            $item->revision = $this->getLatestRevision($item->id);

            if (!is_null($item->revision)) {
                JPdesignsHelper::getThumbnails($item->revision, 'revision');
            }
        }

        return $items;
    }


    /**
     * Method to get the latest revision of a design
     *
     * @param     int      $id       The design id
     *
     * @return    object   $item     The revision
     */
    protected function getLatestRevision($id)
    {
        // Create a new query object.
        $db    = $this->getDbo();
        $query = $db->getQuery(true);
        $user  = Factory::getApplication()->getIdentity();

        // Select the required fields from the table.
        $query->select(
            $this->getState('list.select',
                'a.id, a.asset_id, a.project_id, a.title, a.alias, a.description, '
                . 'a.file_name, a.file_extension, a.file_size, a.created, '
                . 'a.created_by, a.modified, a.modified_by, a.checked_out, '
                . 'a.checked_out_time, a.attribs, a.access, a.state, a.ordering'
            )
        );

        $query->from('#__jp_design_revisions AS a');

        // Filter on parent id
        $query->where('a.parent_id = ' . $db->quote((int) $id));

        // Implement View Level Access
        if (!$user->authorise('core.admin', 'com_jpdesigns')) {
            $levels = implode(',', $user->getAuthorisedViewLevels());
            $query->where('a.access IN (' . $levels . ')');
        }

        // Filter fields
        $filters = array();
        $filters['a.state'] = array('STATE', $this->getState('filter.published'));

        // Apply Filter
        JPQueryHelper::buildFilter($query, $filters);

        // Add the list ordering clause.
        $query->order('a.ordering DESC');

        $db->setQuery($query, 0, 1);
        $item = $db->loadObject();

        if (!empty($item)) {
            $item->slug = $item->id . ':' . $item->alias;
        }

        return $item;
    }


    /**
     * Build a list of item authors
     *
     * @return    jdatabasequery
     */
    public function getAuthors()
    {
        // Return empty array if no project is select
        $project = (int) $this->getState('filter.project');
        if ($project <= 0) return array();

        $db     = $this->getDbo();
        $query  = $db->getQuery(true);
        $user   = Factory::getApplication()->getIdentity();
        $access = JPdesignsHelper::getActions();

        // Construct the query
        $query->select('u.id AS value, u.name AS text')
              ->from('#__users AS u')
              ->join('INNER', '#__jp_designs AS a ON a.created_by = u.id');

        // Implement View Level Access
        if (!$user->authorise('core.admin', 'com_jpdesigns')) {
            $levels = implode(',', $user->getAuthorisedViewLevels());
            $query->where('a.access IN (' . $levels . ')');
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

        // Return the result
        $db->setQuery((string) $query);
        return (array) $db->loadObjectList();
    }


    /**
     * Build a list of item albums
     *
     * @return    jdatabasequery
     */
    public function getAlbums()
    {
        // Return empty array if no project is select
        $project = (int) $this->getState('filter.project');
        if ($project <= 0) return array();

        $db     = $this->getDbo();
        $query  = $db->getQuery(true);
        $user   = Factory::getApplication()->getIdentity();
        $access = JPdesignsHelper::getAlbumActions();

        // Construct the query
        $query->select('c.id AS value, c.title AS text')
              ->from('#__jp_design_albums AS c')
              ->join('INNER', '#__jp_designs AS a ON a.album_id = c.id');

        // Implement View Level Access
        if (!$user->authorise('core.admin', 'com_jpdesigns')) {
            $levels = implode(',', $user->getAuthorisedViewLevels());
            $query->where('c.access IN (' . $levels . ')');
        }

        // Filter fields
        $filters = array();
        $filters['c.project_id'] = array('INT-NOTZERO', $this->getState('filter.project'));

        if (!$access->get('core.edit.state') && !$access->get('core.edit')) {
            $filters['c.state'] = array('STATE', '1');
        }

        // Apply Filter
        JPQueryHelper::buildFilter($query, $filters);

        // Group and order
        $query->group('c.id');
        $query->order('c.title ASC');

        // Return the result
        $db->setQuery((string) $query);
        return (array) $db->loadObjectList();
    }


    /**
     * Method to auto-populate the model state.
     * Note. Calling getState in this method will result in recursion.
     *
     * @return    void
     */
    protected function populateState($ordering = 'a.ordering', $direction = 'ASC')
    {
        $app = Factory::getApplication();

        // Adjust the context to support modal layouts.
        $layout = \Joomla\CMS\Factory::getApplication()->input->getCmd('layout');

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
        $access = JPdesignsHelper::getActions();
        if (!$access->get('core.edit.state') && !$access->get('core.edit')) {
            $this->setState('filter.published', 1);
            $state = '';
        }

        // Filter - Search
        $search = \Joomla\CMS\Factory::getApplication()->input->getString('filter_search', '');
        $this->setState('filter.search', $search);

        // Filter - Project
        $project = JPApplicationHelper::getActiveProjectId('filter_project');
        $this->setState('filter.project', $project);

        // Filter - Author
        $author = $app->getUserStateFromRequest($this->context . '.filter.author', 'filter_author', '');
        $this->setState('filter.author', $author);

        // Filter - Category
        $album = $app->getUserStateFromRequest($this->context . '.filter.album', 'filter_album', '');
        $this->setState('filter.album', $album);

        // Filter - Labels
        $labels = \Joomla\CMS\Factory::getApplication()->input->get('filter_label', array());
        $this->setState('filter.labels', $labels);

        // Do not allow some filters if no project is selected
        if (!is_numeric($project) || intval($project) == 0) {
            $this->setState('filter.author', '');
            $this->setState('filter.album', '');
            $this->setState('filter.labels', array());

            $author = '';
            $album  = '';
            $labels = array();
        }

        if (!is_array($labels)) $labels = array();

        // Filter - Is set
        $this->setState('filter.isset', (is_numeric($state) || !empty($search) || is_numeric($author) ||
            (is_numeric($album) && $album > 0) || count($labels))
        );

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
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.project');
        $id .= ':' . $this->getState('filter.album');

        return parent::getStoreId($id);
    }
}
