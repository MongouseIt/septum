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
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\Controller\FormController;


jimport('joomla.application.component.controllerform');


/**
 * Joomproject File Form Controller
 *
 */
class JPrepoControllerFileForm extends FormController
{
    /**
     * The default item view
     *
     * @var    string
     */
    protected $view_item = 'fileform';

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
    public function &getModel($name = 'FileForm', $prefix = 'JPrepoModel', $config = array('ignore_request' => true))
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
        // Get form input
        $dir = isset($data['parent_id'])  ? (int) $data['parent_id']  : Factory::getApplication()->input->getUint('filter_parent_id');

        $user   = Factory::getApplication()->getIdentity();
        $asset  = 'com_jprepo.directory.' . $dir;
        $access = true;

        // Deny if no parent directory is given
        if (!$dir) {
            $this->setMessage(Text::_('COM_JOOMPROJECT_WARNING_DIRECTORY_NOT_FOUND'),'error');
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
                  ->from('#__jp_repo_files')
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
     * Method to save a record.
     *
     * @param     string     $key       The name of the primary key of the URL variable.
     * @param     string     $urlVar    The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
     *
     * @return    boolean               True if successful, false otherwise.
     */
    public function save($key = 'id', $urlVar = null)
    {
        // Check for request forgeries.
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        // Initialise variables.
        $model     = $this->getModel();
        $data      = Factory::getApplication()->input->post->get('jform',[],'array');
        $file_form = Factory::getApplication()->input->files->get('jform',[],'array');
        $context   = $this->option . ".edit." . $this->context;
        $layout    = Factory::getApplication()->input->get('layout');
        $user      = Factory::getApplication()->getIdentity();
        $files     = array();

        if (empty($urlVar)) $urlVar = $key;

        // Setup redirect links
        $record_id = Factory::getApplication()->input->getInt($urlVar);
        $link_base = 'index.php?option=' . $this->option . '&view=';
        $link_list = $link_base . $this->view_list . $this->getRedirectToListAppend();
        $link_item = $link_base . $this->view_item . $this->getRedirectToItemAppend($record_id, $urlVar);

        // Get project id from directory if missing
        if ((!isset($data['project_id']) || empty($data['project_id'])) && isset($data['dir_id'])) {
            $data['project_id'] = JPrepoHelper::getProjectFromDir($data['dir_id']);
        }

        // Check edit id
        if (!$this->checkEditId($context, $record_id)) {
            // Somehow the person just went to the form and tried to save it. We don't allow that.
            $this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $record_id),'error');
            //$this->setMessage($this->getError(), 'error');

            $this->setRedirect(Route::_(($layout != 'modal' ? $link_item : $link_list), false));

            return false;
        }

        // Check general access
        if (!$user->authorise('core.create', 'com_jprepo') || defined('JPDEMO')) {
            $this->setMessage(Text::_('COM_JOOMPROJECT_WARNING_CREATE_FILE_DENIED'),'error');
            //$this->setMessage($this->getError(), 'error');

            $this->setRedirect(Route::_(($layout != 'modal' ? $link_item : $link_list), false));

            return false;
        }

        // Get file info
        $files = $file_form;

        // Check for upload errors
        if (!$this->checkFileError($files, $record_id)) {
            $this->setRedirect(Route::_(($layout != 'modal' ? $link_item : $link_list), false));
            return false;
        }

        // Upload file if we have any
        if (count($files) && !empty($files['file']['tmp_name'])) {
            $file = $files['file'];

            if ($record_id) {
                // File extension must be the same as the original
                if (!$this->checkFileExtension($record_id, $file['name'])) {
                    $this->setMessage(Text::_('COM_JOOMPROJECT_WARNING_FILE_UPLOAD_ERROR_10'),'error');
                    //$this->setMessage($this->getError(), 'error');

                    $this->setRedirect(Route::_(($layout != 'modal' ? $link_item : $link_list), false));

                    return false;
                }
            }

            // Upload the file
            $result = $model->upload($file, $data['dir_id'], false, $record_id);

            if (is_array($result)) {
                $data['file'] = $result;
            }
            else {
                $error = $model->getError();
                $this->setMessage($error, 'error');

                $this->setRedirect(Route::_(($layout != 'modal' ? $link_item : $link_list), false));
                return false;
            }
        }

        // Inject file info into the form post data
        if (version_compare(JVERSION, '3.0.0', 'ge')) {
            $this->input->post->set('jform', $data);
        }
        else {
            Factory::getApplication()->input->post->set('jform', $data);
        }

        // Store data
        return parent::save($key, $urlVar);
    }


    /**
     * Method the get the file info coming from a form
     *
     * @param     array    $data     The form data
     *
     * @return    array    $files    The file data
     */
    protected function getFormFiles($data)
    {
        $files = array();

        if (!is_array($data)) return $files;

        foreach($data AS $attr => $field)
        {
            $count = count($field);
            $i     = 0;

            while($count > $i)
            {
                foreach($field AS $name => $value)
                {
                    $files[$i][$attr] = $value;
                }

                $i++;
            }
        }

        return $files;
    }


    /**
     * Method to check for upload errors
     *
     * @param     array      $files    The files to check
     * @param     integer $record_id The file id
     *
     * @return    boolean              True if no error
     */
    protected function checkFileError(&$files, $record_id = 0)
    {
        foreach ($files AS &$file)
        {
            // Uploading a file is not required when updating an existing record
            if ($file['error'] == 4 && $record_id > 0) {
                $file['error'] = 0;
            }

            if ($file['error']) {
                $error = JPrepoHelper::getFileErrorMsg($file['error'], $file['name']);
                $this->setMessage($error, 'error');

                return false;
            }
        }

        return true;
    }


    /**
     * Method to check if the file extension is the same the original
     *
     * @param integer $id The file id
     * @param string $file The name of the file to upload
     *
     * @return boolean True if they are the same
     */
    protected function checkFileExtension($id, $file)
    {
        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select('file_extension')
              ->from('#__jp_repo_files')
              ->where('id = ' . (int) $id);

        $db->setQuery($query);
        $original_ext = $db->loadResult();

        return (\Joomla\CMS\Filesystem\File::getExt($file) == $original_ext);
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
        if ($project) $append .= '&filter_project=' . $project;
        if ($parent)  $append .= '&filter_parent_id=' . $parent;
        if ($id)      $append .= '&' . $url_var . '=' . $id;
        if ($item_id) $append .= '&Itemid=' . $item_id;
        if ($layout)  $append .= '&layout=' . $layout;
        if ($tmpl) $append .= '&tmpl=' . $tmpl;
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
        $tmpl    = Factory::getApplication()->input->getCmd('tmpl');
        $project = Factory::getApplication()->input->getUint('filter_project');
        $parent  = Factory::getApplication()->input->getUint('filter_parent_id');
        $func    = Factory::getApplication()->input->getCmd('function');
        $layout  = Factory::getApplication()->input->getCmd('layout');
        $append  = '';

        // Setup redirect info.
        if ($project) $append .= '&filter_project=' . $project;
        if ($parent)  $append .= '&filter_parent_id=' . $parent;
        if ($tmpl)    $append .= '&tmpl=' . $tmpl;
        if ($layout)  $append .= '&layout=' . $layout;
        if ($func)    $append .= '&function=' . $func;

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
        if ($parent)  $append .= '&filter_parent_id=' . $parent;

        if (empty($return) || !Uri::isInternal(base64_decode($return))) {
            return Route::_('index.php?option=com_jprepo&view=' . $this->view_list . $append, false);
        }
        else {
            return base64_decode($return);
        }
    }
}
