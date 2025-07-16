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

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\Controller\FormController;


jimport('joomla.application.component.controllerform');


/**
 * Joomproject Note Form Controller
 *
 */
class JPrepoControllerNoteForm extends FormController
{
    /**
     * The default item view
     *
     * @var    string
     */
    protected $view_item = 'noteform';

    /**
     * The default list view
     *
     * @var    string
     */
    protected $view_list = 'repository';


    /**
     * Method to get a model object, loading it if required.
     *
     * @param     string    $name      The model name. Optional.
     * @param     string    $prefix    The class prefix. Optional.
     * @param     array     $config    Configuration array for model. Optional.
     *
     * @return    object               The model.
     */
    public function &getModel($name = 'NoteForm', $prefix = 'JPrepoModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }


    /**
     * Method to add a new record.
     *
     * @return    boolean    True if the item can be added, false if not.
     */
    public function add()
    {
        if (!parent::add()) {
            // Redirect to the return page.
            $this->setRedirect($this->getReturnPage());
            return false;
        }

        return true;
    }


    /**
     * Method to cancel an edit.
     *
     * @param     string     $key    The name of the primary key of the URL variable.
     *
     * @return    boolean            True if access level checks pass, false otherwise.
     */
    public function cancel($key = 'id')
    {
        $result = parent::cancel($key);
        $parent  = Factory::getApplication()->input->getUint('filter_parent_id', 0);
        $project = Factory::getApplication()->input->getUint('filter_project', 0);
        // Redirect to the return page.
        $this->setRedirect($this->getReturnPage($parent,$project));

        return $result;
    }


    /**
     * Method to save a record.
     *
     * @param     string     $key        The name of the primary key of the URL variable.
     * @param     string     $url_var    The name of the URL variable if different from the primary key.
     *
     * @return    boolean                True if successful, false otherwise.
     */
    public function save($key = null, $url_var = 'id')
    {
        $result  = parent::save($key, $url_var);
        $project = Factory::getApplication()->input->getUint('filter_project', 0);
        $parent  = Factory::getApplication()->input->getUint('filter_parent_id', 0);

        // If ok, redirect to the return page.
        if ($result) $this->setRedirect($this->getReturnPage($parent, $project));

        return $result;
    }


    /**
     * Method to check if you can add a new record.
     *
     * @param     array      $data    An array of input data.
     *
     * @return    boolean
     */
    protected function allowAdd($data = array())
    {
        // Get form input
        $dir = isset($data['parent_id'])  ? (int) $data['parent_id']  : Factory::getApplication()->input->getUint('filter_parent_id');

        $user   = Factory::getApplication()->getIdentity();
        $asset  = 'com_jprepo.directory.' . $dir;
        $access = true;

        // Deny if no parent directory is given
        if (!$dir) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_JOOMPROJECT_WARNING_DIRECTORY_NOT_FOUND'),'error');
            return false;
        }

        // Check if the user has viewing access when not a super admin
        if (!$user->authorise('core.admin')) {
            $db    = Factory::getDbo();
            $query = $db->getQuery(true);

            $query->select('access')
                  ->from('#__jp_repo_dirs')
                  ->where('id = ' . $dir);

            $db->setQuery($query);
            $lvl = $db->loadResult();

            $access = in_array($lvl, $user->getAuthorisedViewLevels());
        }

        return ($user->authorise('core.create', $asset) && $access);
    }


    /**
     * Method override to check if you can edit an existing record.
     *
     * @param     array      $data    An array of input data.
     * @param     string     $key     The name of the key for the primary key.
     *
     * @return    boolean
     */
    protected function allowEdit($data = array(), $key = 'id')
    {
        // Get form input
        $id = (int) (isset($data[$key]) ? $data[$key] : 0);

        $user   = Factory::getApplication()->getIdentity();
        $uid    = Factory::getApplication()->getIdentity()->get('id');
        $asset  = 'com_jprepo.file.' . $id;
        $access = true;

        // Check if the user has viewing access when not a super admin
        if (!$user->authorise('core.admin')) {
            $db    = Factory::getDbo();
            $query = $db->getQuery(true);

            $query->select('access')
                  ->from('#__jp_repo_notes')
                  ->where('id = ' . $id);

            $db->setQuery($query);
            $lvl = $db->loadResult();

            if (!in_array($lvl, $user->getAuthorisedViewLevels())) {
                return false;
            }
        }

        // Check general edit permission first.
        if ($user->authorise('core.edit', $asset)) {
            return true;
        }

        // Fallback on edit.own.
        // First test if the permission is available.
        if (!$user->authorise('core.edit.own', $asset)) {
            return false;
        }

        // Load the item
        $record = $this->getModel()->getItem($id);

        // Abort if not found
        if (empty($record)) return false;

        // Now test the owner is the user.
        $owner = (int) isset($data['created_by']) ? (int) $data['created_by'] : $record->created_by;

        // If the owner matches 'me' then do the test.
        return ($owner == $uid && $uid > 0);
    }


    /**
     * Gets the URL arguments to append to an item redirect.
     *
     * @param     int       $id         The primary key id for the item.
     * @param     string    $url_var    The name of the URL variable for the id.
     *
     * @return    string                The arguments to append to the redirect URL.
     */
    protected function getRedirectToItemAppend($id = null, $url_var = 'id')
    {
        // Need to override the parent method completely.
        $tmpl    = Factory::getApplication()->input->getCmd('tmpl');
        $layout  = Factory::getApplication()->input->getCmd('layout', 'edit');
        $item_id = Factory::getApplication()->input->getUInt('Itemid');
        $project = Factory::getApplication()->input->getUint('filter_project', 0);
        $parent  = Factory::getApplication()->input->getUint('filter_parent_id', 0);
        $return  = $this->getReturnPage($parent, $project);
        $append  = '';


        // Setup redirect info.
        if ($tmpl) $append .= '&tmpl=' . $tmpl;

        $append .= '&layout=edit';
        if ($project) $append .= '&filter_project=' . $project;
        if ($parent)  $append .= '&filter_parent_id=' . $parent;
        if ($id)      $append .= '&' . $url_var . '=' . $id;
        if ($item_id) $append .= '&Itemid=' . $item_id;
        if ($return)  $append .= '&return='.base64_encode($return);

        return $append;
    }


    /**
     * Gets the URL arguments to append to a list redirect.
     *
     * @return    string    The arguments to append to the redirect URL.
     */
    protected function getRedirectToListAppend()
    {
        // Need to override the parent method completely.
        $tmpl    = Factory::getApplication()->input->getCmd('tmpl');
        $project = Factory::getApplication()->input->getUint('filter_project', 0);
        $parent  = Factory::getApplication()->input->getUint('filter_parent_id', 0);
        $append  = '';


        // Setup redirect info.
        if ($project) $append .= '&filter_project=' . $project;
        if ($parent)  $append .= '&filter_parent_id=' . $parent;
        if ($tmpl)    $append .= '&tmpl=' . $tmpl;

        return $append;
    }


    /**
     * Get the return URL.
     * If a "return" variable has been passed in the request
     *
     * @return    string    The return URL.
     */
    protected function getReturnPage($parent, $project = 0)
    {
        $return = Factory::getApplication()->input->get('return', null, 'base64');
        $append = '';

        if ($project) $append .= '&filter_project=' . $project;
        if ($parent)   $append .= '&filter_parent_id=' . $parent;

        if (empty($return) || !Uri::isInternal(base64_decode($return))) {
            return Route::_('index.php?option=com_jprepo&view=' . $this->view_list . $append, false);
        }
        else {
            return base64_decode($return);
        }
    }


    /**
     * Function that allows child controller access to model data after the data has been saved.
     *
     * @param     jmodel    $model    The data model object.
     * @param     array     $data     The validated data.
     *
     * @return    void
     */
    protected function postSaveHook(Joomla\CMS\MVC\Model\BaseDatabaseModel $model, $validData = [])
    {
        $task = $this->getTask();

        if ($task == 'save') {
            $this->setRedirect(Route::_('index.php?option=com_jprepo&view=' . $this->view_list, false));
        }
    }
}
