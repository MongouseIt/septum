<?php
/**
 * @package      Joomproject
 * @subpackage   Tasks
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2006-2012 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Factory;


jimport('joomla.application.component.controlleradmin');


/**
 * Joomproject Task List Controller
 *
 */
class JPtasksControllerTasks extends AdminController
{
    /**
     * The default list view
     *
     * @var    string
     */
    protected $view_list = 'tasks';

    /**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 */
	protected $text_prefix = 'COM_JOOMPROJECT_TASKS';


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
	 * Method to toggle the not applicable state of tasks
	 *
	 * @return  boolean  True on success, otherwise false
	 */
	public function notApplicable()
	{
		// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		// Initialise variables.
		$ids = Factory::getApplication()->input->get('cid', null, 'post', 'array');
		$state = Factory::getApplication()->input->getInt('not_applicable', 0);

		// Check if at least one item is selected
		if (empty($ids)) {
			$message = Text::_('COM_JPTASKS_ERROR_NO_ITEMS_SELECTED');
			$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false), $message, 'error');
			return false;
		}

		// Get the model
		$model = $this->getModel();

		// Toggle the not applicable state
		$return = $model->setNotApplicable($ids, $state);

		if ($return === false) {
			// Action failed.
			$message = Text::sprintf('COM_JPTASKS_ERROR_NOTAPPLICABLE_FAILED', $model->getError());
			$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false), $message, 'error');
			return false;
		}
		else {
			// Action succeeded.
			$ntext = $state ? 'COM_JPTASKS_SUCCESS_MARKED_NOT_APPLICABLE' : 'COM_JPTASKS_SUCCESS_MARKED_APPLICABLE';
			$message = Text::plural($ntext, count($ids));
			$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false), $message);
			return true;
		}
	}


    /**
     * Method to save the priority of one or more tasks
     *
     * @return    boolean    True on success, otherwise false
     */
    public function savePriority()
    {
        // Check for request forgeries.
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        // Initialise variables.
        $ids  = Factory::getApplication()->input->get('cid', null, 'post', 'array');
        $pids = Factory::getApplication()->input->get('priority', null, 'post', 'array');

        $model  = $this->getModel();
        $return = $model->savePriority($ids, $pids);

        if ($return === false) {
            // Storage failed.
            $message = Text::sprintf('COM_JOOMPROJECT_ERROR_SAVEPRIORITY_FAILED', $model->getError());
            $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false), $message, 'error');
            return false;
        }
        else {
            // Storage succeeded.
            $message = Text::_('COM_JOOMPROJECT_SUCCESS_TASK_SAVEPRIORITY');
            $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false), $message);
            return true;
        }
    }


    /**
     * Method to assign a user to one or more tasks
     *
     * @return    boolean    True on success, otherwise false
     */
    public function addUsers()
    {
        // Check for request forgeries.
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        // Initialise variables.
        $ids  = Factory::getApplication()->input->get('cid', null, 'post', 'array');
        $uids = Factory::getApplication()->input->get('assigned', null, 'post', 'array');

        $model  = $this->getModel();
        $return = $model->addUsers($ids, $uids);

        if ($return === false) {
            // Assigning failed.
            $message = Text::sprintf('COM_JOOMPROJECT_ERROR_ADDUSER_FAILED', $model->getError());
            $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false), $message, 'error');
            return false;
        }
        else {
            // Assigning succeeded.
            $message = Text::_('COM_JOOMPROJECT_SUCCESS_TASK_ADDUSER');
            $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false), $message);
            return true;
        }
    }


    /**
     * Method to remove a user from one or more tasks
     *
     * @return    boolean    True on success, otherwise false
     */
    public function deleteUsers()
    {
        // Check for request forgeries.
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        // Initialise variables.
        $ids  = Factory::getApplication()->input->post->get('cid', null, 'array');
        $uids = Factory::getApplication()->input->post->get('assigned', null, 'post', 'array');

        $model  = $this->getModel();
        $return = $model->deleteUsers($ids, $uids);

        if ($return === false) {
            // Deletion failed.
            $message = Text::sprintf('COM_JOOMPROJECT_ERROR_ADDUSER_FAILED', $model->getError());
            $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false), $message, 'error');
            return false;
        }
        else {
            // Deletion succeeded.
            $message = Text::_('COM_JOOMPROJECT_SUCCESS_TASK_ADDUSER');
            $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false), $message);
            return true;
        }
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
        $layout  = Factory::getApplication()->input->getCmd('layout');
        $item_id = Factory::getApplication()->input->getUInt('Itemid');
        $return  = $this->getReturnPage();
        $append  = '';

        // Setup redirect info.
        if ($tmpl)    $append .= '&tmpl=' . $tmpl;
        if ($layout)  $append .= '&layout=' . $layout;
        if ($id)      $append .= '&' . $url_var . '=' . $id;
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

        $return = Factory::getApplication()->input->get('return', null, 'base64');

        if (empty($return) || !Uri::isInternal(base64_decode($return))) {
            return Uri::base();
        }

        return base64_decode($return);
    }
}
