<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_joomproject
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
 * Joomproject Component Dashboard Model
 *
 */
class JoomprojectModelDashboard extends ItemModel
{
    /**
     * Model context string.
     *
     * @var    string
     */
    protected $_context = 'com_joomproject.dashboard';


    /**
     * Method to get the data of a project.
     *
     * @param     integer    The id of the item.
     *
     * @return    mixed      Item data object on success, false on failure.
     */
    public function &getItem($pk = null)
    {
        // Initialise variables.
        $pk = (!empty($pk)) ? $pk : (int) $this->getState('filter.project');

        if ($this->_item === null) $this->_item = array();

        if (!$pk) {
           $this->_item[$pk] = null;
           return $this->_item[$pk];
        }

        if (!isset($this->_item[$pk])) {

            try {
                $query = $this->_db->getQuery(true);

                $query->select($this->getState(
                        'item.select',
                        'a.id, a.asset_id, a.title, a.alias, a.description AS text, '
                        . 'a.created, a.created_by, a.modified_by, a.checked_out, a.checked_out_time, '
                        . 'a.attribs, a.access, a.state, a.start_date, a.end_date, a.gallery_items'
                    )
                );

                $query->from('#__jp_projects AS a');

                // Join on user table.
                $query->select('u.name AS author')
                      ->join('LEFT', '#__users AS u on u.id = a.created_by')
                      ->where('a.id = ' . (int) $pk);

                $this->_db->setQuery($query);

                try
                {
                    $data = $this->_db->loadObject();
                }
                catch (RuntimeException $e)
                {
                    throw new Exception($e->getMessage());
                }



                if (empty($data)) {
                    if (JPApplicationHelper::getActiveProjectId() == $pk) {
                        JPApplicationHelper::setActiveProject(0);
                        $this->_item[$pk] = null;
                        return $this->_item[$pk];
                    }

                    return Factory::getApplication()->enqueueMessage( Text::_('COM_JOOMPROJECT_ERROR_PROJECT_NOT_FOUND'),'error');
                }

                // Convert parameter fields to objects.
                $registry = new Registry;
                $registry->loadString((string)$data->attribs);

                $data->params = clone $this->getState('params');
                $data->params->merge($registry);

                // Get the attachments
                if (JPApplicationHelper::exists('com_jprepo')) {
                    $attachments = $this->getInstance('Attachments', 'JPrepoModel');
                    $data->attachments = $attachments->getItems('com_jpprojects.project', $data->id);
                }
                else {
                    $data->attachments = array();
                }

                // Compute selected asset permissions.
                $user = Factory::getApplication()->getIdentity();

                // Technically guest could edit the item, but lets not check that to improve performance a little.
                if (!$user->get('guest')) {
                    $uid    = $user->get('id');
                    $access = JPprojectsHelper::getActions($data->id);

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
                    $user   = Factory::getApplication()->getIdentity();
                    $groups = $user->getAuthorisedViewLevels();

                    $data->params->set('access-view', in_array($data->access, $groups));
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
     * Get the return URL.
     *
     * @return    string    The return URL.
     */
    public function getReturnPage()
    {
        return base64_encode($this->getState('return_page'));
    }


    /**
     * Method to auto-populate the model state.
     * Note. Calling getState in this method will result in recursion.
     *
     * @return    void
     */
    protected function populateState()
    {
        $app = Factory::getApplication();

        $id     = Factory::getApplication()->input->get('id', null);
        $filter = Factory::getApplication()->input->get('filter_project', null);

        if (!is_null($filter)) {
            $project = JPApplicationHelper::getActiveProjectId('filter_project');
        }
        elseif (!is_null($id)) {
            $project = JPApplicationHelper::getActiveProjectId('id');
            $this->setState('project.request', true);
        }
        else {
            $project = JPApplicationHelper::getActiveProjectId();
        }

        $this->setState('filter.project', $project);

        $return = Factory::getApplication()->input->get('return', null, 'base64');
        $this->setState('return_page', base64_decode((string)$return));

        // Load the parameters.
        $params = $app->getParams();
        $this->setState('params', $params);

        $this->setState('layout', Factory::getApplication()->input->getCmd('layout'));
    }
}
