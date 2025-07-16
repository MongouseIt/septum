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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\Controller\FormController;


jimport('joomla.application.component.controllerform');


/**
 * Design Revision Form Controller
 *
 */
class JPdesignsControllerRevisionForm extends FormController
{
    /**
     * Default item view
     *
     * @var    string
     */
    protected $view_item = 'revisionform';

    /**
     * Default list view
     *
     * @var    string
     */
    protected $view_list = 'designs';

    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     */
    protected $text_prefix = "COM_JOOMPROJECT_DESIGN_REVISION";


    /**
     * Constructor
     *
     */
    public function __construct($config = array())
    {
        parent::__construct($config);

        // Register additional tasks
        $this->registerTask('upload', 'save');
        $this->registerTask('decline', 'approve');
    }


    /**
     * Method to add a new record.
     *
     * @return    boolean    True if the article can be added, false if not.
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
     * @param     string    $key    The name of the primary key of the URL variable.
     *
     * @return    void
     */
    public function cancel($key = 'id')
    {
        parent::cancel($key);

        // Redirect to the return page.
        $this->setRedirect($this->getReturnPage());
    }


    /**
     * Method to save a record.
     *
     * @param     string     $key       The name of the primary key of the URL variable.
     * @param     string     $urlVar    The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
     *
     * @return    boolean               True if successful, false otherwise.
     */
    public function save($key = null, $urlVar = null)
    {
        // Check for request forgeries.
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        // Initialise variables.
        $app     = Factory::getApplication();
        $model   = $this->getModel();
        $table   = $model->getTable();
        $task    = $this->getTask();
        $data    = \Joomla\CMS\Factory::getApplication()->input->post->get('jform',[],'array');
        $context = $this->option . ".edit." . $this->context;
        $layout  = \Joomla\CMS\Factory::getApplication()->input->get('layout');
        $files   = Factory::getApplication()->input->files->get('jform');

        // Determine the name of the primary key for the data.
        if (empty($key)) $key = $table->getKeyName();

        // To avoid data collisions the urlVar may be different from the primary key.
        if (empty($urlVar)) $urlVar = $key;

        $record_id = \Joomla\CMS\Factory::getApplication()->input->getUInt($urlVar);

        if (!$this->checkEditId($context, $record_id)) {
            // Somehow the person just went to the form and tried to save it. We don't allow that.
            \Joomla\CMS\Factory::getApplication()->enqueueMessage(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $record_id),'error');

            $this->setRedirect(
                Route::_(
                    'index.php?option=' . $this->option . '&view=' . $this->view_list
                    . $this->getRedirectToListAppend(), false
                )
            );

            return false;
        }

        // Some adjustments for the quick-upload
        if ($task == 'upload') {
            if (!isset($data['project_id'])) {
                // Get the parent design item
                $design_model = $this->getModel('DesignForm');
                $design = $design_model->getItem((int) $data['parent_id']);

                if ($design->getError()) {
                    \Joomla\CMS\Factory::getApplication()->enqueueMessage($design->getError(),'error');
                    $this->setRedirect(
                        Route::_(
                            'index.php?option=' . $this->option . '&view=' . $this->view_list
                            . $this->getRedirectToListAppend(), false
                        )
                    );

                    return false;
                }

                $data['project_id'] = (int) $design->project_id;
                JPApplicationHelper::setActiveProject($data['project_id']);
            }
        }

        // Upload the file first
        if (isset($files['file']) && !empty($files['file']['tmp_name'])) {
            $result = $model->upload($files['file'], (isset($data['project_id']) ? $data['project_id'] : JPApplicationHelper::getActiveProjectId()));

            if (is_array($result)) {
                $data['file'] = $result;
            }
            else {
                \Joomla\CMS\Factory::getApplication()->enqueueMessage($model->getError(),'error');
                // Save the data in the session.
                $app->setUserState($context . '.data', $data);

                $this->setRedirect(
                    Route::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($record_id), false)
                );

                return false;
            }
        }

        if (version_compare(JVERSION, '3.0.0', 'ge')) {
            $this->input->post->set('jform', $data);
        }
        else {
            \Joomla\CMS\Factory::getApplication()->input->set('jform', $data, 'post');
        }

        return parent::save($key, $urlVar);
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
    public function &getModel($name = 'RevisionForm', $prefix = 'JPdesignsModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }


    /**
     * Method to set the approval state of a record.
     *
     * @param     string     $key       The name of the primary key of the URL variable.
     * @param     string     $urlVar    The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
     *
     * @return    boolean               True if successful, false otherwise.
     */
    public function approve($key = null, $urlVar = null)
    {
        // Check for request forgeries.
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        // Initialise variables.
        $app   = Factory::getApplication();
        $user  = Factory::getApplication()->getIdentity();
        $model = $this->getModel();
        $table = $model->getTable();

        // Determine the name of the primary key for the data.
        if (empty($key)) $key = $table->getKeyName();

        // To avoid data collisions the urlVar may be different from the primary key.
        if (empty($urlVar)) $urlVar = $key;

        $record_id = \Joomla\CMS\Factory::getApplication()->input->getUInt($urlVar);
        $parent_id = \Joomla\CMS\Factory::getApplication()->input->getUInt('filter_parent_id');

        if (!$this->allowApprove($record_id, $parent_id)) {
            // Access denied
            \Joomla\CMS\Factory::getApplication()->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'),'error');
            $this->setRedirect(
                Route::_(
                    'index.php?option=' . $this->option . '&view=' . $this->view_list
                    . $this->getRedirectToListAppend(), false
                )
            );

            return false;
        }

        $state = ($this->getTask() == 'approve' ? '1': '0');

        if (!$model->approve($record_id, $parent_id, $user->get('id'), $state)) {
            \Joomla\CMS\Factory::getApplication()->enqueueMessage($model->getError(),'error');


            $this->setRedirect(
                Route::_(
                    'index.php?option=' . $this->option . '&view=' . $this->view_list
                    . $this->getRedirectToListAppend(), false
                )
            );
        }

        $text = ($state == 1 ? Text::_('COM_JOOMPROJECT_DESIGN_REVISION_ITEM_APPROVED') : Text::_('COM_JOOMPROJECT_DESIGN_REVISION_ITEM_DECLINED'));
        $this->setMessage($text);

        // Redirect to the list screen.
        $this->setRedirect($this->getReturnPage());

        return true;
    }


    /**
     * Method to check if you can approve a record.
     *
     * @param     integer    $id    The revision ID
     *
     * @return    boolean
     */
    protected function allowApprove($id = null, $parent = null)
    {
        if (!$id || !$parent) {
            return false;
        }

        $user   = Factory::getApplication()->getIdentity();
        $db     = Factory::getDbo();
        $query  = $db->getQuery(true);
        $access = true;

        // Check if the user has access to the design
        if (!$user->authorise('core.admin', 'com_jpdesigns')) {
            if ($project) {
                $query->select('access')
                      ->from('#__jp_design_revisions')
                      ->where('id = ' . $db->quote((int) $id))
                      ->where('parent_id = ' . $db->quote((int) $parent));

                $db->setQuery($query);
                $access = in_array((int) $db->loadResult(), $user->getAuthorisedViewLevels());
            }

            if ($access) {
                $access = $user->authorise('core.approve', 'com_jpdesigns.revision.' . (int) $id);
            }
        }

        return $access;
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
        $user  = Factory::getApplication()->getIdentity();
        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        $access  = true;

        $levels  = $user->getAuthorisedViewLevels();
        $project = isset($data['project_id']) ? (int) $data['project_id'] : JPApplicationHelper::getActiveProjectId();
        $parent  = (isset($data['parent_id'])  ? (int) $data['parent_id'] : \Joomla\CMS\Factory::getApplication()->input->getUInt('filter_parent_id'));
        $asset   = 'com_jpdesigns.design.' . $parent;

        // Parent design is required
        if (!$parent) {
            \Joomla\CMS\Factory::getApplication()->enqueueMessage(Text::_('COM_JOOMPROJECT_ERROR_DESIGN_NOT_FOUND'),'ERROR');
            return false;
        }

        // Check if the user has access to the design
        if (!$user->authorise('core.admin')) {
            if ($parent) {
                $query->clear();
                $query->select('access')
                      ->from('#__jp_designs')
                      ->where('id = ' . $db->quote((int) $parent));

                $db->setQuery($query);
                $access = in_array((int) $db->loadResult(), $levels);
            }
        }

        // Check if the user has access to the project
        if (!$user->authorise('core.admin')) {
            if ($project && $access) {
                $query->clear();
                $query->select('access')
                      ->from('#__jp_projects')
                      ->where('id = ' . $db->quote((int) $project));

                $db->setQuery($query);
                $access = in_array((int) $db->loadResult(), $levels);
            }
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
        // Initialise variables.
        $id     = (int) isset($data[$key]) ? $data[$key] : 0;
        $uid    = Factory::getApplication()->getIdentity()->get('id');
        $access = JPdesignsHelper::getRevisionActions($id);

        // Check general edit permission first.
        if ($access->get('core.edit')) {
            return true;
        }

        // Fallback on edit.own.
        // First test if the permission is available.
        if ($access->get('core.edit.own')) {
            // Now test the owner is the user.
            $owner = (int) isset($data['created_by']) ? $data['created_by'] : 0;

            if (empty($owner) && $id) {
                // Need to do a lookup from the model.
                $record = $this->getModel()->getItem($id);

                if (empty($record)) return false;

                $owner = $record->created_by;
            }

            // If the owner matches 'me' then do the test.
            if ($owner == $uid) return true;
        }

        // Since there is no asset tracking, revert to the component permissions.
        return parent::allowEdit($data, $key);
    }


    /**
     * Gets the URL arguments to append to a list redirect.
     *
     * @return    string    The arguments to append to the redirect URL.
     */
    protected function getRedirectToListAppend()
    {
        $tmpl   = \Joomla\CMS\Factory::getApplication()->input->getCmd('tmpl');
        $return = $this->getReturnPage();
        $append = '';

        // Setup redirect info.
        if ($tmpl)   $append .= '&tmpl=' . $tmpl;
        if ($return) $append .= '&return=' . base64_encode($return);

        return $append;
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
        $project = \Joomla\CMS\Factory::getApplication()->input->getUint('filter_project', 0);
        $parent  = \Joomla\CMS\Factory::getApplication()->input->getUint('filter_parent_id', 0);
        $return  = $this->getReturnPage();
        $append  = '';

        // Setup redirect info.
        if ($tmpl) $append .= '&tmpl=' . $tmpl;

        $append .= '&layout=edit';
        if ($id)      $append .= '&' . $url_var . '=' . $id;
        if ($project) $append .= '&filter_project=' . $project;
        if ($parent)  $append .= '&filter_parent_id=' . $parent;
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
            return Route::_(JPdesignsHelperRoute::getDesignsRoute(), false);
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

        switch($task)
        {
            case 'save2copy':
            case 'save2new':
                // No redirect because its already set
                break;

            default:
                $this->setRedirect($this->getReturnPage() . ($task == 'upload' ? '#_' . $data['parent_id'] : ''));
                break;
        }
    }
}
