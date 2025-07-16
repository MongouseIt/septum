<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jprepo
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\Utilities\ArrayHelper;

jimport('joomla.application.component.controllerform');


/**
 * Repository File controller class
 *
 */
class JPrepoControllerFile extends FormController
{
    /**
     * The URL view list variable.
     *
     * @var    string
     */
    protected $view_list = 'repository';


    public function download()
    {
        $id  = Factory::getApplication()->input->getUInt('id');
        $rev = Factory::getApplication()->input->getUInt('rev');

        $link_base = 'index.php?option=' . $this->option . '&view=';
        $link_list = $link_base . $this->view_list . $this->getRedirectToListAppend();

        $user   = Factory::getApplication()->getIdentity();
        $levels = $user->getAuthorisedViewLevels();
        $admin  = $user->authorise('core.admin', 'com_jprepo');

        $file_model = $this->getModel();
        $file       = $file_model->getItem($id);

        if (empty($id) || !$file || empty($file->id)) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_JOOMPROJECT_ERROR_FILE_NOT_FOUND'),'error');
            $this->setRedirect(Route::_($link_list, false));
            return false;
        }

        // Check file access
        if (!$admin && !in_array($file->access, $levels)) {
            Factory::getApplication()->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'),'error');
            $this->setRedirect(Route::_($link_list, false));
            return false;
        }

        if ($rev) {
            $rev_model = $this->getModel('FileRevision');
            $file_rev  = $rev_model->getItem($rev);

            if (!$file_rev || empty($file_rev->id)) {

                Factory::getApplication()->enqueueMessage(Text::_('COM_JOOMPROJECT_ERROR_FILE_NOT_FOUND'),'error');
                $this->setRedirect(Route::_($link_list, false));
                return false;
            }

            // Check access
            if ($file_rev->parent_id != $file->id) {
                Factory::getApplication()->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'),'error');
                $this->setRedirect(Route::_($link_list, false));
                return false;
            }

            $filepath = JPrepoHelper::getBasePath($file->project_id) . '/_revs/file_' . $file->id;
            $filename = $file_rev->file_name;
        }
        else {
            $filepath = JPrepoHelper::getFilePath($file->file_name, $file->dir_id);
            $filename = $file->file_name;
        }

        // Check if the file exists
        if (empty($filepath) || !\Joomla\CMS\Filesystem\File::exists($filepath . '/' . $filename)) {
            \Joomla\CMS\Factory::getApplication()->enqueueMessage(Text::_('COM_JOOMPROJECT_ERROR_FILE_NOT_FOUND'),'error');
            $this->setRedirect(Route::_($link_list, false));
            return false;
        }

        if (headers_sent($f, $line)) {
            \Joomla\CMS\Factory::getApplication()->enqueueMessage(Text::sprintf('COM_JOOMPROJECT_WARNING_FILE_DL_ERROR_HEADERS_SENT', $f, $line),'error');
            $this->setRedirect(Route::_($link_list, false));
            return false;
        }

        while (ob_get_level())
        {
            ob_end_clean();
        }

        header("Content-Type: APPLICATION/OCTET-STREAM");
        header("Content-Length: " . filesize($filepath . '/' . $filename));
        header("Content-Disposition: attachment; filename=\"" . $filename . "\";");
        header("Content-Transfer-Encoding: Binary");

        if (function_exists('readfile')) {
            readfile($filepath . '/' . $filename);
        }
        else {
            echo file_get_contents($filepath . '/' . $filename);
        }

        jexit();
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
        $app = \Joomla\CMS\Factory::getApplication();
        // Check for request forgeries.
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        // Initialise variables.
        $model     = $this->getModel();
        $data      = \Joomla\CMS\Factory::getApplication()->input->post->get('jform',[],'array');
        $file_form = \Joomla\CMS\Factory::getApplication()->input->files->get('jform',[],'array');
        $context   = $this->option . ".edit." . $this->context;
        $layout    = \Joomla\CMS\Factory::getApplication()->input->get('layout');
        $files     = array();

        if (empty($urlVar)) $urlVar = $key;

        // Setup redirect links
        $record_id = \Joomla\CMS\Factory::getApplication()->input->getInt($urlVar);
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
            $app->enqueueMessage(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $record_id),'error');
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
                    $app->enqueueMessage(Text::_('COM_JOOMPROJECT_WARNING_FILE_UPLOAD_ERROR_10'),'error');
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
                $app->enqueueMessage($error,'error');

                $this->setRedirect(Route::_(($layout != 'modal' ? $link_item : $link_list), false));
                return false;
            }
        }

        // Inject file info into the form post data
        if (version_compare(JVERSION, '3.0.0', 'ge')) {
            $this->input->post->set('jform', $data);
        }
        else {
            \Joomla\CMS\Factory::getApplication()->input->post->set('jform', $data);
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
     *
     * @return    boolean              True if no error
     */
    protected function checkFileError(&$files, $record_id = 0)
    {
        $app = \Joomla\CMS\Factory::getApplication();
        foreach ($files AS &$file)
        {

               // Uploading a file is not required when updating an existing record
               if ($file['error'] == 4 && $record_id > 0) {
                   $file['error'] = 0;
               }

               if ($file['error']) {
                   $error = JPrepoHelper::getFileErrorMsg($file['error'], $file['name']);
                   $app->enqueueMessage($error,'error');
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
     * Method to check if you can add a new record.
     *
     * @param     array      $data    An array of input data.
     *
     * @return    boolean
     */
    protected function allowAdd($data = array())
    {
        $app = \Joomla\CMS\Factory::getApplication();
        $user    = Factory::getApplication()->getIdentity();
        $project = \Joomla\Utilities\ArrayHelper::getValue($data, 'project_id', \Joomla\CMS\Factory::getApplication()->input->getUInt('filter_project'), 'int');
        $dir_id  = \Joomla\Utilities\ArrayHelper::getValue($data, 'dir_id', \Joomla\CMS\Factory::getApplication()->input->getUInt('filter_parent_id'), 'int');

        // Check general access
        if (!$user->authorise('core.create', 'com_jprepo')) {
            $app->enqueueMessage(Text::_('COM_JOOMPROJECT_WARNING_CREATE_FILE_DENIED'),'error');
            return false;
        }

        // Validate directory access
        $model = $this->getModel('Directory', 'JPrepoModel');
        $item  = $model->getItem($dir_id);

        if ($item == false || empty($item->id) || $dir_id <= 1) {

            \Joomla\CMS\Factory::getApplication()->enqueueMessage(Text::_('COM_JOOMPROJECT_WARNING_DIRECTORY_NOT_FOUND'),'error');
            return false;
        }

        $access = JPrepoHelper::getActions('directory', $item->id);

        if (!$user->authorise('core.admin')) {
            if (!in_array($item->access, $user->getAuthorisedViewLevels())) {
                $app->enqueueMessage(Text::_('COM_JOOMPROJECT_WARNING_DIRECTORY_ACCESS_DENIED'),'error');
                return false;
            }
            elseif (!$access->get('core.create')) {
                $app->enqueueMessage(Text::_('COM_JOOMPROJECT_WARNING_DIRECTORY_CREATE_FILE_DENIED'),'error');
                return false;
            }
        }

        return true;
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
        $user  = Factory::getApplication()->getIdentity();
        $uid   = $user->get('id');
        $id    = (int) isset($data[$key]) ? $data[$key] : 0;
        $owner = (int) isset($data['created_by']) ? $data['created_by'] : 0;

        // Check general edit permission first.
        if ($user->authorise('core.edit', 'com_jprepo.file.' . $id)) {
            return true;
        }

        // Fallback on edit.own.
        if ($user->authorise('core.edit.own', 'com_jprepo.file.' . $id)) {
            // Now test the owner is the user.
            if (!$owner && $id) {
                $record = $this->getModel()->getItem($id);

                if (empty($record)) return false;

                $owner = $record->created_by;
            }

            if ($owner == $uid) return true;
        }

        // Fall back to the component permissions.
        return parent::allowEdit($data, $key);
    }


    /**
     * Gets the URL arguments to append to an item redirect.
     *
     * @param     integer    $id         The primary key id for the item.
     * @param     string     $url_var    The name of the URL variable for the id.
     *
     * @return    string                 The arguments to append to the redirect URL.
     */
    protected function getRedirectToItemAppend($id = null, $url_var = 'id')
    {
        $tmpl    = \Joomla\CMS\Factory::getApplication()->input->getCmd('tmpl');
        $layout  = \Joomla\CMS\Factory::getApplication()->input->getCmd('layout', 'edit');
        $project = \Joomla\CMS\Factory::getApplication()->input->getUint('filter_project', 0);
        $parent  = \Joomla\CMS\Factory::getApplication()->input->getUint('filter_parent_id', 0);
        $append  = '';

        // Setup redirect info.
        if ($project) $append .= '&filter_project=' . $project;
        if ($parent)  $append .= '&filter_parent_id=' . $parent;
        if ($id)      $append .= '&' . $url_var . '=' . $id;
        if ($layout)  $append .= '&layout=' . $layout;
        if ($tmpl)    $append .= '&tmpl=' . $tmpl;

        return $append;
    }


    /**
     * Gets the URL arguments to append to a list redirect.
     *
     * @return    string    The arguments to append to the redirect URL.
     */
    protected function getRedirectToListAppend()
    {
        $tmpl    = \Joomla\CMS\Factory::getApplication()->input->getCmd('tmpl');
        $project = \Joomla\CMS\Factory::getApplication()->input->getUint('filter_project');
        $parent  = \Joomla\CMS\Factory::getApplication()->input->getUint('filter_parent_id');
        $layout  = \Joomla\CMS\Factory::getApplication()->input->getCmd('layout');
        $func    = \Joomla\CMS\Factory::getApplication()->input->getCmd('function');
        $append  = '';

        // Setup redirect info.
        if ($project) $append .= '&filter_project=' . $project;
        if ($parent)  $append .= '&filter_parent_id=' . $parent;
        if ($tmpl)    $append .= '&tmpl=' . $tmpl;
        if ($layout)  $append .= '&layout=' . $layout;
        if ($func)    $append .= '&function=' . $func;

        return $append;
    }
}
