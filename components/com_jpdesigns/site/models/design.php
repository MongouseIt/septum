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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Model\ItemModel;

jimport('joomla.application.component.modelitem');


/**
 * Design Item Model
 *
 */
class JPdesignsModelDesign extends ItemModel
{
    /**
     * Model context string.
     *
     * @var    string
     */
    protected $_context = 'com_jpdesigns.design';


    /**
     * Method to get item data.
     *
     * @param     integer    The id of the item.
     * @return    mixed      Menu item data object on success, false on failure.
     */
    public function &getItem($pk = null)
    {
        // Initialise variables.
        $pk = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');

        if ($this->_item === null) $this->_item = array();

        if (!isset($this->_item[$pk])) {
            try {
                $db    = $this->getDbo();
                $query = $db->getQuery(true);

                $query->select($this->getState(
                        'item.select',
                        'a.id, a.asset_id, a.project_id, a.album_id, a.title, a.alias, a.description AS text, '
                        . 'a.created, a.created_by, a.modified_by, a.checked_out, a.checked_out_time, '
                        . 'a.attribs, a.access, a.state, a.file_name, a.file_extension, a.file_size, a.thumbnail'
                    )
                );
                $query->from('#__jp_designs AS a');

                // Join on project table.
                $query->select('p.title AS project_title, p.alias AS project_alias');
                $query->join('LEFT', '#__jp_projects AS p on p.id = a.project_id');

                // Join on album table.
                $query->select('c.title AS album_title, c.alias AS album_alias');
                $query->join('LEFT', '#__jp_design_albums AS c on c.id = a.album_id');

                // Join on user table.
                $query->select('u.name AS author_name');
                $query->join('LEFT', '#__users AS u on u.id = a.created_by');

                $query->where('a.id = ' . (int) $pk);

                $db->setQuery($query);

                try
                {
                    $data = $db->loadObject();
                }
                catch (RuntimeException $e)
                {
                    throw new Exception($e->getMessage());
                }

                if (empty($data)) {
                    $this->setError(Text::_('COM_JOOMPROJECT_ERROR_DESIGN_NOT_FOUND'));
                    $item = false;
                    return $item;
                }

                // Generate slugs
                $data->slug         = $data->alias         ? ($data->id . ':' . $data->alias)                 : $data->id;
                $data->project_slug = $data->project_alias ? ($data->project_id . ':' . $data->project_alias) : $data->project_id;
                $data->album_slug   = $data->album_alias   ? ($data->album_id . ':' . $data->album_alias)     : $data->album_id;

                // Convert parameter fields to objects.
                $registry = new Registry;
                $registry->loadString((string)$data->attribs);

                if(!is_null($this->getState('params'))){
                    $data->params = clone $this->getState('params');
                    $data->params->merge($registry);
                }


                // Compute selected asset permissions.
                // Technically guest could edit an article, but lets not check that to improve performance a little.
                if (!Factory::getApplication()->getIdentity()->get('guest')) {
                    $uid    = Factory::getApplication()->getIdentity()->get('id');
                    $access = JPdesignsHelper::getActions($data->id);

                    if(isset($data->params)){
                        // Check general edit permission first.
                        if ($access->get('core.edit')) {
                            $data->params->set('access-edit', true);
                        }
                        // Now check if edit.own is available.
                        elseif (!empty($uid) && $access->get('core.edit.own')) {
                            // Check for a valid user and that they are the owner.
                            if ($uid == $data->created_by) {
                                $data->params->set('access-edit', true);
                            }
                        }
                    }

                }

                if(isset($data->params)){
                    // Compute view access permissions.
                    if ($access = $this->getState('filter.access')) {
                        // If the access filter has been set, we already know this user can view.
                        $data->params->set('access-view', true);
                    }
                    else {
                        // If no access filter is set, the layout takes some responsibility for display of limited information.
                        $access = JPdesignsHelper::getActions($data->id);

                        if ($access->get('core.admin')) {
                            $data->params->set('access-view', true);
                        }
                        else {
                            $groups = Factory::getApplication()->getIdentity()->getAuthorisedViewLevels();
                            $data->params->set('access-view', in_array($data->access, $groups));
                        }
                    }
                }


                // Get the thumbnails
                JPdesignsHelper::getThumbnails($data);

                // Get the requested revision
                $data->revision = null;
                $rev_id = (int) $this->getState($this->getName() . '.revision');

                if ($rev_id) {
                    $query->clear();
                    $query->select('parent_id')
                          ->from('#__jp_design_revisions')
                          ->where('id = ' . $db->quote($rev_id));

                    $db->setQuery($query);
                    $rev_parent = (int) $db->loadResult();

                    if ($rev_parent == $data->id && $rev_parent > 0) {
                        $rev_model = BaseDatabaseModel::getInstance('Revision', 'JPdesignsModel');
                        $data->revision = $rev_model->getItem($rev_id);
                    }
                }

                // Get the approval list
                $data->approved = array();
                $data->declined = array();

                $approvals = $this->getItemApprovals($data->id, $data->state);

                foreach ($approvals AS $approval)
                {
                    $app_uid  = (int) $approval->created_by;
                    $app_data = array('created' => $approval->created, 'author' => $approval->author_name);

                    if ($approval->state == '1') {
                        $data->approved[$app_uid] = $app_data;
                    }
                    else {
                        $data->declined[$app_uid] = $app_data;
                    }
                }

                // Get the x latest revisions
                if(isset($data->params)){

                    $data->recent = $this->getItemRecentRevisions($data->id, 0, $data->params->get('recent_limit', 5));

                    // Iterate through the revisions
                    foreach ($data->recent AS $x => &$rev)
                    {
                        // Create slug
                        $rev->slug = $rev->alias ? ($rev->id . ':' . $rev->alias) : $rev->id;

                        // Get thumbnails
                        JPdesignsHelper::getThumbnails($rev, 'revision');
                    }
                }


                $this->_item[$pk] = $data;
            }
            catch (Exception $e)
            {
                if ($e->getCode() == 404) {
                    // Need to go thru the error handler to allow Redirect to work.
                    Factory::getApplication()->enqueueMessage( $e->getMessage(),'error');
                }
                else {
                    $this->setError($e);
                    $this->_item[$pk] = false;
                }
            }
        }

        return $this->_item[$pk];
    }


    /**
     * Method to the list of users who approved or declined the design
     *
     * @param    integer  $id The design id
     *
     * @return    array $items The list of users
     */
    public function getItemApprovals($id)
    {
        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select('a.created, a.created_by, a.state')
              ->from('#__jp_designs_approved AS a');

        // Join on user table.
        $query->select('u.name AS author_name');
        $query->join('LEFT', '#__users AS u on u.id = a.created_by');

        $query->where('a.id = ' . $db->quote((int) $id))
              ->where('a.revision_id = 0')
              ->order('a.created ASC');

        $db->setQuery($query);
        $items = (array) $db->loadObjectList();

        return $items;
    }


    protected function getItemRecentRevisions($id, $state = 0, $limit = 5)
    {
        // Create a new query object.
        $db    = $this->getDbo();
        $query = $db->getQuery(true);
        $user  = Factory::getApplication()->getIdentity();

        // Select the required fields from the table.
        $query->select(
            'a.id, a.asset_id, a.project_id, a.title, a.alias, a.description, '
            . 'a.file_name, a.file_extension, a.file_size, a.created, '
            . 'a.created_by, a.modified, a.modified_by, a.checked_out, '
            . 'a.checked_out_time, a.attribs, a.access, a.state, a.ordering'
        );

        $query->from('#__jp_design_revisions AS a');

        // Filter on parent id
        $query->where('a.parent_id = ' . $db->quote((int) $id));

        if ($state == 1) {
            $query->where('a.state = ' . $db->quote((int) $state));
        }

        // Implement View Level Access
        if (!$user->authorise('core.admin', 'com_jpdesigns')) {
            $levels = implode(',', $user->getAuthorisedViewLevels());
            $query->where('a.access IN (' . $levels . ')');
        }

        // Join over the approved table for approved/declined count
        $query->select('COUNT(DISTINCT ap.created_by) AS approved_count');
        $query->join('LEFT', '#__jp_designs_approved AS ap ON (ap.revision_id = a.id AND ap.state = 1)');

        $query->select('COUNT(de.created_by) AS declined_count');
        $query->join('LEFT', '#__jp_designs_approved AS de ON (de.revision_id = a.id AND de.state = 0)');

        // Add the list ordering clause.
        $query->order('a.ordering DESC');
        $query->group('a.id');

        $db->setQuery($query, 0, (int) $limit);
        $items = (array) $db->loadObjectList();

        return $items;

    }


    /**
     * Method to auto-populate the model state.
     * Note. Calling getState in this method will result in recursion.
     *
     * @return    void
     */
    protected function populateState()
    {
        // Load state from the request.
        $pk = Factory::getApplication()->input->getInt('id');
        $this->setState($this->getName() . '.id', $pk);

        $offset = Factory::getApplication()->input->getUInt('limitstart');
        $this->setState('list.offset', $offset);

        $revision = Factory::getApplication()->input->getUInt('revision');
        $this->setState($this->getName() . '.revision', $revision);

        // Load the parameters.
        $params = Factory::getApplication('site')->getParams();
        $this->setState('params', $params);

        $access = JPdesignsHelper::getActions();
        if (!$access->get('core.edit.state') && !$access->get('core.edit')) {
            $this->setState('filter.published', 1);
            $this->setState('filter.archived', 2);
        }
    }
}
