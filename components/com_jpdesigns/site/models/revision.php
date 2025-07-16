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
use Joomla\CMS\MVC\Model\ItemModel;

jimport('joomla.application.component.modelitem');
JLoader::register('JPdesignsHelper' , JPATH_ADMINISTRATOR . '/components/com_jpdesigns/helpers/jpdesigns.php');

/**
 * Design Revision Item Model
 *
 */
class JPdesignsModelRevision extends ItemModel
{
    /**
     * Model context string.
     *
     * @var    string
     */
    protected $_context = 'com_jpdesigns.revision';


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
                        'a.id, a.asset_id, a.project_id, a.parent_id, a.title, a.alias, a.description AS text, '
                        . 'a.created, a.created_by, a.modified_by, a.checked_out, a.checked_out_time, '
                        . 'a.attribs, a.access, a.state, a.file_name, a.file_extension, a.file_size,'
                        . 'a.ordering'
                    )
                );
                $query->from('#__jp_design_revisions AS a');

                // Join on project table.
                $query->select('p.title AS project_title, p.alias AS project_alias');
                $query->join('LEFT', '#__jp_projects AS p on p.id = a.project_id');

                // Join on user table.
                $query->select('u.name AS author_name');
                $query->join('LEFT', '#__users AS u on u.id = a.created_by');

                $query->where('a.id = ' . (int) $pk);

                // Filter by published state.
                $published = $this->getState('filter.published');
                $archived  = $this->getState('filter.archived');

                if (is_numeric($published)) {
                    $query->where('(a.state = ' . (int) $published . ' OR a.state =' . (int) $archived . ')');
                }

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
                    return Factory::getApplication()->enqueueMessage( Text::_('COM_JOOMPROJECT_ERROR_DESIGN_NOT_FOUND'),'error');
                }

                // Check for published state if filter set.
                if (((is_numeric($published)) || (is_numeric($archived))) && (($data->state != $published) && ($data->state != $archived))) {
                    return Factory::getApplication()->enqueueMessage( Text::_('COM_JOOMPROJECT_ERROR_DESIGN_NOT_FOUND'),'error');
                }

                // Generate slugs
                $data->slug         = $data->alias           ? ($data->id . ':' . $data->alias)                     : $data->id;
                $data->project_slug = $data->project_alias   ? ($data->project_id . ':' . $data->project_alias)     : $data->project_id;

                // Convert parameter fields to objects.
                $registry = new Registry;
                $registry->loadString( (string) $data->attribs);

                $data->params = clone $this->getState('params');
                $data->params->merge($registry);

                // Compute selected asset permissions.
                // Technically guest could edit an item, but lets not check that to improve performance a little.
                if (!Factory::getApplication()->getIdentity()->get('guest')) {
                    $uid    = Factory::getApplication()->getIdentity()->get('id');
                    $access = JPdesignsHelper::getRevisionActions($data->id);

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

                // Compute view access permissions.
                if ($access = $this->getState('filter.access')) {
                    // If the access filter has been set, we already know this user can view.
                    $data->params->set('access-view', true);
                }
                else {
                    // If no access filter is set, the layout takes some responsibility for display of limited information.
                    $access = JPdesignsHelper::getRevisionActions($data->id);

                    if ($access->get('core.admin')) {
                        $data->params->set('access-view', true);
                    }
                    else {
                        $groups = Factory::getApplication()->getIdentity()->getAuthorisedViewLevels();
                        $data->params->set('access-view', in_array($data->access, $groups));
                    }
                }

                // Get the thumbnails
                JPdesignsHelper::getThumbnails($data, 'revision');

                // Get the approval list
                $data->approved = array();
                $data->declined = array();

                $approvals = $this->getItemApprovals($data->id);

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
     * Method to the list of users who approved or declined the revision
     *
     * @param    integer  $id The revision id
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

        $query->where('a.revision_id = ' . $db->quote((int) $id))
              ->order('a.created ASC');

        $db->setQuery($query);
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
        $pk = Factory::getApplication()->input->getUInt('id');
        $this->setState($this->getName() . '.id', $pk);

        $offset = Factory::getApplication()->input->getUInt('limitstart');
        $this->setState('list.offset', $offset);

        // Load the parameters.
        $params = Factory::getApplication('site')->getParams();
        $this->setState('params', $params);

        $access = JPdesignsHelper::getRevisionActions($pk);
        if (!$access->get('core.edit.state') && !$access->get('core.edit')) {
            $this->setState('filter.published', 1);
            $this->setState('filter.archived', 2);
        }
    }
}
