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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
jimport('joomla.application.component.modellist');
jimport('joomla.application.component.helper');


/**
 * Design Album List Model
 *
 */
class JPdesignsModelAlbums extends ListModel
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
                'a.id', 'a.title', 'a.ordering', 'a.created', 'a.modified',
                'a.state', 'author_name', 'editor', 'access_level',
                'a.project_id', 'project_title'
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
                'a.id, a.asset_id, a.project_id, a.title, a.alias, a.description, a.created, '
                . 'a.created_by, a.modified, a.modified_by, a.checked_out, '
                . 'a.checked_out_time, a.attribs, a.access, a.state, a.ordering'
            )
        );

        $query->from('#__jp_design_albums AS a');

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

        // Join over the designs for design count
        $query->select('COUNT(DISTINCT d.id) AS design_count');
        $query->join('LEFT', '#__jp_designs AS d ON d.album_id = a.id');

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
        $filters['a.project_id'] = array('INT-NOTZERO', $this->getState('filter.project'));
        $filters['a']            = array('SEARCH',      $this->getState('filter.search'));

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
        $items = parent::getItems();
        $design_model = $this->getInstance('Designs', 'JPdesignsModel');

        $design_model->getState('list.limit');

        $cfg  = ComponentHelper::getParams('com_jpdesigns', true);
        $user = Factory::getApplication()->getIdentity();

        list($p_w, $p_h) = explode('x', $cfg->get('img_preview_size', '400x300'), 2);
        list($c_w, $c_h) = explode('x', $cfg->get('img_cover_size', '770x300'), 2);

        // Iterate through each revision to add some additional stuff
        foreach ($items as $i => &$item)
        {
            $params = new Registry;
            $params->loadString((string)$item->attribs);

            // Convert the parameter fields into objects.
            $items[$i]->params = clone $this->getState('params');

            // Create slugs
            $item->slug         = $item->alias          ? ($item->id . ':' . $item->alias)                 : $item->id;
            $item->project_slug = $item->project_alias  ? ($item->project_id . ':' . $item->project_alias) : $item->project_id;

            // Get the x latest designs
            $cols  = (int) $item->params->get('album_row_limit', 1);
            $limit = (int) $item->params->get('album_row_items', 8);
            $sort  = (int) $item->params->get('album_items_sort', 2);
            $fill  = (int) $item->params->get('album_row_fill', 1);
            $upl   = (int) $item->params->get('album_row_upload', 1);
            $total = ($cols * $limit) + 1;

            $design_model->setState('list.limit',     $total);
            $design_model->setState('filter.project', $item->project_id);
            $design_model->setState('filter.album',   $item->id);
            $design_model->setState('load_revisions', false);

            switch ($sort)
            {
                case 0:
                    $design_model->setState('list.ordering', 'a.ordering');
                    $design_model->setState('list.direction', 'asc');
                    break;

                case 1:
                    $design_model->setState('list.ordering', 'a.created');
                    $design_model->setState('list.direction', 'asc');
                    break;

                case 2:
                default:
                    $design_model->setState('list.ordering', 'a.created');
                    $design_model->setState('list.direction', 'desc');
                    break;
            }

            $item->designs = (($item->design_count > 0) ? $design_model->getItems() : array());

            // Add placeholders
            if (count($item->designs) == 0 && $fill == 0) {
                // We need at least a placeholder for the cover
                $item->designs[0] = new stdClass();
                $item->designs[0]->placeholder = true;
                $item->designs[0]->slug = "0:original";
                $item->designs[0]->preview_source = HTMLHelper::_('image', 'com_jpdesigns/preview-placeholder-' . intval($p_w) . 'x' . intval($p_h) . '.jpg', 'placeholder', null, true, true);
                $item->designs[0]->full_source    = HTMLHelper::_('image', 'com_jpdesigns/full-placeholder.jpg', 'placeholder', null, true, true);
                $item->designs[0]->cover_source   = HTMLHelper::_('image', 'com_jpdesigns/cover-placeholder-' . intval($c_w) . 'x' . intval($c_h) . '.jpg', 'placeholder', null, true, true);
            }

            if (count($item->designs) < $total && $fill) {
                $x = 0;

                while ($total > $x)
                {
                    if (!isset($item->designs[$x])) {
                        $item->designs[$x] = new stdClass();
                        $item->designs[$x]->placeholder = true;
                        $item->designs[$x]->slug = "0:original";
                        $item->designs[$x]->preview_source = HTMLHelper::_('image', 'com_jpdesigns/preview-placeholder-' . intval($p_w) . 'x' . intval($p_h) . '.jpg', 'placeholder', null, true, true);
                        $item->designs[$x]->full_source    = HTMLHelper::_('image', 'com_jpdesigns/full-placeholder.jpg', 'placeholder', null, true, true);
                        $item->designs[$x]->cover_source   = HTMLHelper::_('image', 'com_jpdesigns/cover-placeholder-' . intval($c_w) . 'x' . intval($c_h) . '.jpg', 'placeholder', null, true, true);
                    }

                    $x++;
                }
            }

            // Add quick-upload placeholder?
            if ($upl && $user->authorise('core.create', 'com_jpdesigns.album.' . $item->id)) {
                // Pop the last one out to make room for the placeholder
                if (count($item->designs) == $total) {
                    array_pop($item->designs);
                    reset($item->designs);
                }

                $x = count($item->designs);

                $item->designs[$x] = new stdClass();
                $item->designs[$x]->placeholder = true;
                $item->designs[$x]->slug = "0:original";
                $item->designs[$x]->preview_source = HTMLHelper::_('image', 'com_jpdesigns/add-placeholder-' . intval($p_w) . 'x' . intval($p_h) . '.jpg', 'placeholder', null, true, true);
                $item->designs[$x]->full_source    = HTMLHelper::_('image', 'com_jpdesigns/full-placeholder.jpg', 'placeholder', null, true, true);
                $item->designs[$x]->cover_source   = HTMLHelper::_('image', 'com_jpdesigns/cover-placeholder-' . intval($c_w) . 'x' . intval($c_h) . '.jpg', 'placeholder', null, true, true);
            }
        }

        return $items;
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
              ->join('INNER', '#__jp_design_albums AS a ON a.created_by = u.id');

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
     * Method to auto-populate the model state.
     * Note. Calling getState in this method will result in recursion.
     *
     * @return    void
     */
    protected function populateState($ordering = 'a.ordering', $direction = 'ASC')
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
        $access = JPdesignsHelper::getAlbumActions();
        if (!$access->get('core.edit.state') && !$access->get('core.edit')) {
            $this->setState('filter.published', 1);
            $state = '';
        }

        // Filter - Search
        $search = Factory::getApplication()->input->getString('filter_search', '');
        $this->setState('filter.search', $search);

        // Filter - Project
        $project = JPApplicationHelper::getActiveProjectId('filter_project');
        $this->setState('filter.project', $project);

        // Filter - Author
        $author = $app->getUserStateFromRequest($this->context . '.filter.author', 'filter_author', '');
        $this->setState('filter.author', $author);

        // Do not allow some filters if no project is selected
        if (!is_numeric($project) || intval($project) == 0) {
            $this->setState('filter.author', '');

            $author = '';
        }

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
        $id .= ':' . $this->getState('filter.project_id');
        $id .= ':' . $this->getState('filter.search');

        return parent::getStoreId($id);
    }
}
