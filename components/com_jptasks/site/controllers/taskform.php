<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jptasks
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

jimport('joomla.application.component.controllerform');


/**
 * Joomproject Task Form Controller
 *
 */
class JPtasksControllerTaskForm extends FormController
{
    /**
     * The default item view
     *
     * @var    string
     */
    protected $view_item = 'taskform';

    /**
     * The default list view
     *
     * @var    string
     */
    protected $view_list = 'tasks';


    /**
     * Constructor
     *
     */
    public function __construct($config = array())
	{
	    parent::__construct($config);


        // Register additional tasks
		$this->registerTask('save2milestone', 'save');
		$this->registerTask('save2tasklist', 'save');
    }

    /**
     * Method to run batch operations.
     *
     * @param   object  $model  The model.
     *
     * @return  boolean   True if successful, false otherwise and internal error is set.
     *
     * @since   1.1
     */
    public function batch($model = null)
    {

        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        // Set the model
        $model = $this->getModel('Taskform', '', array());

        // Preset the redirect
        $this->setRedirect(Route::_('index.php?option=com_jptasks&view=tasks' . $this->getRedirectToListAppend(), false));

        return parent::batch($model);
    }


    /**
     * Method to get a model object, loading it if required.
     *
     * @param     string    $name      The model name. Optional.
     * @param     string    $prefix    The class prefix. Optional.
     * @param     array     $config    Configuration array for model. Optional.
     *
     * @return    object               The model.
     */
    public function &getModel($name = 'TaskForm', $prefix = 'JPtasksModel', $config = array('ignore_request' => true))
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
        }
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

        // Redirect to the return page.
        $this->setRedirect($this->getReturnPage());

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
        $project = isset($data['project_id'])   ? (int) $data['project_id']   : JPApplicationHelper::getActiveProjectId();
        $ms      = isset($data['milestone_id']) ? (int) $data['milestone_id'] : 0;
        $list    = isset($data['list_id'])      ? (int) $data['list_id']      : 0;

        $user   = Factory::getApplication()->getIdentity();
        $db     = Factory::getDbo();
        $is_sa  = $user->authorise('core.admin');
        $levels = $user->getAuthorisedViewLevels();
        $query  = $db->getQuery(true);
        $asset  = 'com_jptasks';
        $access = true;

        // Check if the user has access to the project
        if ($project) {
            // Check if in allowed projects when not a super admin
            if (!$is_sa) {
                $access = in_array($project, JPUserHelper::getAuthorisedProjects());
            }

            // Change the asset name
            $asset  .= '.project.' . $project;
        }

        // Check if the user can access the selected milestone when not a super admin
        if (!$is_sa && $ms && $access) {
            $query->select('access')
                  ->from('#__jp_milestones')
                  ->where('id = ' . $db->quote((int) $ms));

            $db->setQuery($query);
            $lvl = $db->loadResult();

            $access = in_array($lvl, $levels);
        }

        // Check if the user can access the selected task list when not a super admin
        if (!$is_sa && $list && $access) {
            $query->clear()
                  ->select('access')
                  ->from('#__jp_task_lists')
                  ->where('id = ' . $list);

            $db->setQuery($query);
            $lvl = $db->loadResult();

            $access = in_array($lvl, $levels);

            // Change asset to list
            $asset = 'com_jptasks.tasklist.' . $list;
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
        $id = (int) isset($data[$key]) ? $data[$key] : 0;

        $user  = Factory::getApplication()->getIdentity();
        $uid   = $user->get('id');
        $asset = 'com_jptasks.task.' . $id;

        // Check if the user has viewing access when not a super admin
        if (!$user->authorise('core.admin')) {
            $db    = Factory::getDbo();
            $query = $db->getQuery(true);

            $query->select('access')
                  ->from('#__jp_tasks')
                  ->where('id = ' . $id);

            $db->setQuery($query);
            $lvl = $db->loadResult();

            if (!in_array($lvl, $user->getAuthorisedViewLevels())) {
                return false;
            }
        }

        // Check edit permission first
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
        $tmpl    = \Joomla\CMS\Factory::getApplication()->input->getCmd('tmpl');
        $layout  = \Joomla\CMS\Factory::getApplication()->input->getCmd('layout', 'edit');
        $item_id = \Joomla\CMS\Factory::getApplication()->input->getUInt('Itemid');
        $ms_id   = \Joomla\CMS\Factory::getApplication()->input->getUInt('milestone_id');
        $list_id = \Joomla\CMS\Factory::getApplication()->input->getUInt('list_id');
        $return  = $this->getReturnPage();
        $append  = '';

        // Setup redirect info.
        if ($tmpl) $append .= '&tmpl=' . $tmpl;

        $append .= '&layout=edit';
        if ($id)      $append .= '&' . $url_var . '=' . $id;
        if ($ms_id)   $append .= '&milestone_id=' . $ms_id;
        if ($list_id) $append .= '&list_id=' . $list_id;
        if ($item_id) $append .= '&Itemid=' . $item_id;
        if ($return)  $append .= '&return=' . base64_encode($return);

        return $append;
    }


    /**
     * Get the return URL.
     * If a "return" variable has been passed in the request
     *
     * @return    string    The return URL.
     */
    protected function getReturnPage()
    {
        $return = \Joomla\CMS\Factory::getApplication()->input->get('return', null, 'base64');

        if (empty($return) || !Uri::isInternal(base64_decode($return))) {
            $app       = Factory::getApplication();
            $project   = JPApplicationHelper::getActiveProjectId();
            $milestone = (int) $app->getUserStateFromRequest('com_jptasks.tasks.filter.milestone', 'milestone_id', '');
            $list      = (int) $app->getUserStateFromRequest('com_jptasks.tasks.filter.tasklist', 'list_id', '');

            return Route::_(JPtasksHelperRoute::getTasksRoute($project, $milestone, $list), false);
        }
        else {
            return base64_decode($return);
        }
    }


    /**
     * Function that allows child controller access to model data after the data has been saved.
     *
     * @param     jmodel    $model        The data model object.
     * @param     array     $validData    The validated data.
     *
     * @return    void
     */
    protected function postSaveHook(BaseDatabaseModel $model, $validData = array())
    {
        $task = $this->getTask();

        switch($task)
        {
            case 'save2copy':
            case 'save2new':
                // No redirect because its already set
                break;

            case 'save2milestone':
                $link = Route::_(JPmilestonesHelperRoute::getMilestonesRoute() . '&task=form.add');
                $this->setRedirect($link);
                break;

            case 'save2tasklist':
                $link = Route::_(JPtasksHelperRoute::getTasksRoute() . '&task=tasklistform.add');
                $this->setRedirect($link);
                break;

            default:
                $this->setRedirect($this->getReturnPage());
                break;
        }
    }
}
