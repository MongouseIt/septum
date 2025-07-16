<?php
/**
 * @package      Joomproject
 * @subpackage   Users
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2006-2012 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;


/**
 * Joomproject User Form Controller
 *
 */
class JPusersControllerUser extends FormController
{
    /**
     * Default item view
     *
     * @var    string
     */
    protected $view_item = 'user';

    /**
     * Default list view
     *
     * @var    string
     */
    protected $view_list = 'users';


    /**
     * Constructor
     *
     */
    public function __construct($config = array())
	{
	    parent::__construct($config);
    }


    /**
     * Method to add a new record.
     *
     * @return    boolean    True if the article can be added, false if not.
     */
    public function add()
    {
        return false;
    }


    /**
     * Method to cancel an edit.
     *
     * @param     string    $key    The name of the primary key of the URL variable.
     *
     * @return    void
     */
    public function cancel($key = 'id')
    {
        return false;
    }


    /**
     * Method to edit an existing record.
     *
     * @param     string     $key        The name of the primary key of the URL variable.
     * @param     string     $url_var    The name of the URL variable if different from the primary key.
     *
     * @return    boolean                True if access level check and checkout passes, false otherwise.
     */
    public function edit($key = null, $url_var = 'id')
    {
        return false;
    }


    public function deleteAvatar()
    {
        // Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        $user   = Factory::getApplication()->getIdentity();
        $access = JPusersHelper::getActions();
        $id     = Factory::getApplication()->input->getUInt('id');
        $model  = $this->getModel();

        // Access check
        if ($user->id != $id || defined('JPDEMO')) {
            if (!$access->get('core.admin') || defined('JPDEMO')) {
                Factory::getApplication()->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'),'error');
                $this->setRedirect(
    				Route::_(
    					'index.php?option=' . $this->option . '&view=' . $this->view_item
    					. $this->getRedirectToItemAppend($id), false
    				)
    			);
                return false;
            }
        }

        if (!$id) {
            $this->setRedirect(
				Route::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_item
					. $this->getRedirectToItemAppend($id), false
				)
			);
            return false;
        }

        if (!$model->deleteAvatar($id)) {
            Factory::getApplication()->enqueueMessage($model->getError());

            $this->setRedirect(
				Route::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_item
					. $this->getRedirectToItemAppend($id), false
				)
			);

            return false;
        }

        $this->setRedirect(
			Route::_(
				'index.php?option=' . $this->option . '&view=' . $this->view_item
				. $this->getRedirectToItemAppend($id), false
			)
		);

        return true;
    }


    /**
     * Method to upload or delete a user avatar image
     *
     * @return boolean True on success, False on error
     */
    public function avatar()
    {
        // Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        $user   = Factory::getApplication()->getIdentity();
        $access = JPusersHelper::getActions();
        $file   = Factory::getApplication()->input->files->get('avatar',[],'array');
        $id     = Factory::getApplication()->input->getUInt('id');
        $model  = $this->getModel();

        // Access check
        if ($user->id != $id || defined('JPDEMO')) {
            if (!$access->get('core.admin') || defined('JPDEMO')) {
                Factory::getApplication()->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'),'error');
                $this->setRedirect(
    				Route::_(
    					'index.php?option=' . $this->option . '&view=' . $this->view_item
    					. $this->getRedirectToItemAppend($id), false
    				)
    			);
                return false;
            }
        }

        if (!empty($file['tmp_name'])) {
            if (!$model->saveAvatar($id, $file)) {
                Factory::getApplication()->enqueueMessage($model->getError(),'error');

                $this->setRedirect(
    				Route::_(
    					'index.php?option=' . $this->option . '&view=' . $this->view_item
    					. $this->getRedirectToItemAppend($id), false
    				)
    			);

                return false;
            }
        }

        $this->setRedirect(
			Route::_(
				'index.php?option=' . $this->option . '&view=' . $this->view_item
				. $this->getRedirectToItemAppend($id), false
			)
		);

        return true;
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
    public function &getModel($name = 'User', $prefix = 'JPusersModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
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
        return false;
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
        return false;
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
        $append  = '';

        // Setup redirect info.
        if ($tmpl) $append .= '&tmpl=' . $tmpl;

        if ($id)      $append .= '&' . $url_var . '=' . $id;
        if ($item_id) $append .= '&Itemid=' . $item_id;

        return $append;
    }


    /**
     * Function that allows child controller access to model data after the data has been saved.
     *
     * @param     jmodel    $model    The data model object.
     * @param     array     $data     The validated data.
     *
     * @return    void
     */
    protected function postSaveHook(BaseDatabaseModel $model, $validData = array())
    {
        $task = $this->getTask();


    }
}
