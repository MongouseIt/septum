<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jprepo
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


/**
 * Joomproject Component Note Model
 *
 */
class JPrepoModelNote extends ItemModel
{
    /**
     * Model context string.
     *
     * @var    string
     */
    protected $_context = 'com_jprepo.note';


    /**
     * Method to get item data.
     *
     * @param     integer    The id of the item.
     * @return    mixed      Menu item data object on success, false on failure.
     */
    public function getItem($pk = null)
    {
        // Initialise variables.
        $pk = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');

        if ($this->_item === null) {
            $this->_item = array();
        }

        if (isset($this->_item[$pk])) {
            return $this->_item[$pk];
        }

        try {
            $db    = $this->getDbo();
            $query = $db->getQuery(true);

            $query->select($this->getState(
                    'item.select',
                    'a.id, a.asset_id, a.project_id, a.dir_id, a.title, a.alias, a.description AS text, '
                    . 'a.created, a.created_by, a.modified, a.modified_by, a.checked_out, a.checked_out_time, '
                    . 'a.attribs, a.access'
                )
            );

            $query->from('#__jp_repo_notes AS a');

            // Join on project table.
            $query->select('p.title AS project_title, p.alias AS project_alias');
            $query->join('LEFT', '#__jp_projects AS p on p.id = a.project_id');

            // Join on directories table.
            $query->select('d.title AS dir_title, d.alias AS dir_alias, d.path');
            $query->join('LEFT', '#__jp_repo_dirs AS d on d.id = a.dir_id');

            // Join on user table.
            $query->select('u.name AS author');
            $query->join('LEFT', '#__users AS u on u.id = a.created_by');

            $query->where('a.id = ' . (int) $pk);

            $db->setQuery($query);

            try
            {
                $item = $db->loadObject();
            }
            catch (RuntimeException $e)
            {
                throw new Exception($e->getMessage());
            }


            if (empty($item)) {
                $this->setError(Text::_('COM_JOOMPROJECT_ERROR_NOTE_NOT_FOUND'));
                $item = false;
                return $item;

            }

            // Convert parameter fields to objects.
            $registry = new Registry;
            $registry->loadString((string)$item->attribs);

            $params = $this->getState('params');

            if ($params) {
                $item->params = clone $this->getState('params');
                $item->params->merge($registry);
            }
            else {
                $item->params = $registry;
            }

            // Generate slugs
            $item->slug         = $item->alias         ? ($item->id . ':' . $item->alias)                 : $item->id;
            $item->project_slug = $item->project_alias ? ($item->project_id . ':' . $item->project_alias) : $item->project_id;
            $item->dir_slug     = $item->dir_alias     ? ($item->dir_id . ':' . $item->dir_alias)         : $item->dir_id;

            // Compute selected asset permissions.
            $user   = Factory::getApplication()->getIdentity();
            $uid    = $user->get('id');
            $access = JPrepoHelper::getActions('note', $item->id);

            $view_access = true;

            if ($item->access && !$user->authorise('core.admin')) {
                $view_access = in_array($item->access, $user->getAuthorisedViewLevels());
            }

            $item->params->set('access-view', $view_access);

            if (!$view_access) {
                $item->params->set('access-edit', false);
                $item->params->set('access-change', false);
            }
            else {
                // Check general edit permission first.
                if ($access->get('core.edit')) {
                    $item->params->set('access-edit', true);
                }
                elseif (!empty($uid) &&  $access->get('core.edit.own')) {
                    // Check for a valid user and that they are the owner.
                    if ($uid == $item->created_by) {
                        $item->params->set('access-edit', true);
                    }
                }

                // Check edit state permission.
                $item->params->set('access-change', $access->get('core.edit.state'));
            }

            // Get the revision if requested
            $rev = (int) $this->getState($this->getName() . '.rev');

            if ($rev) {
                $cfg       = array('ignore_request' => true);
                $rev_model = $this->getInstance('NoteRevision', 'JPrepoModel', $cfg);
                $rev_item  = $rev_model->getItem($rev);

                // Check for error
                if ($error = $rev_model->getError()) throw new Exception($error);

                if (empty($rev_item)) {
                    return Factory::getApplication()->enqueueMessage( Text::_('COM_JOOMPROJECT_ERROR_NOTE_NOT_FOUND'),'error');
                }

                if (!$rev_item || $rev_item->parent_id != $item->id) {
                    $item->params->set('access-view', false);
                }
                else {
                    // Override properties of item
                    $props = array('title', 'description', 'created', 'created_by');

                    foreach ($props AS $prop)
                    {
                        $item->$prop = $rev_item->$prop;
                    }

                    $item->text = $rev_item->description;
                }
            }

            $this->_item[$pk] = $item;
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

        return $this->_item[$pk];
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
        $pk  = Factory::getApplication()->input->getUInt('id');
        $rev = Factory::getApplication()->input->getUInt('rev');

        $this->setState($this->getName() . '.id', $pk);
        $this->setState($this->getName() . '.rev', $rev);

        // Load the parameters.
        $params = Factory::getApplication('site')->getParams();
        $this->setState('params', $params);
    }
}
