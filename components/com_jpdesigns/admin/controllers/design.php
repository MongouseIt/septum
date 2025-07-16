<?php
/**
 * @package      Joomproject Pro
 * @subpackage   Designs
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
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Controller\FormController;


jimport('joomla.application.component.controllerform');


class JPdesignsControllerDesign extends FormController
{
    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     */
    protected $text_prefix = "COM_JOOMPROJECT_DESIGN";

    /**
     * The URL view list variable.
     *
     * @var    string
     */
    protected $view_list = 'designs';

    /**
     * The URL view item variable.
     *
     * @var    string
     */
    protected $view_item = 'design';


    /**
     * Class constructor.
     *
     * @param    array    $config    A named array of configuration variables
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
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
    public function getModel($name = 'Design', $prefix = 'JPdesignsModel', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
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
        $app       = Factory::getApplication();
        $model     = $this->getModel();
        $table     = $model->getTable();
        $data      = Factory::getApplication()->input->post->get('jform',[],'array');
        $context   = $this->option . ".edit." . $this->context;
        $layout    = Factory::getApplication()->input->get('layout');
        $file_form = Factory::getApplication()->input->files->get('jform',[],'array');
        $files     = array();
        $customThumbEnabled = ComponentHelper::getParams('com_jpdesigns')->get('enable_custom_thumbnail',0);


        // Determine the name of the primary key for the data.
		if (empty($key)) $key = $table->getKeyName();

		// To avoid data collisions the urlVar may be different from the primary key.
        if (empty($urlVar)) $urlVar = $key;

        // Setup redirect links
        $record_id = Factory::getApplication()->input->getInt($urlVar);
        $link_base = 'index.php?option=' . $this->option . '&view=';
        $link_list = $link_base . $this->view_list . $this->getRedirectToListAppend();
        $link_item = $link_base . $this->view_item . $this->getRedirectToItemAppend($record_id, $urlVar);

        // Check edit id
        if (!$this->checkEditId($context, $record_id)) {
            // Somehow the person just went to the form and tried to save it. We don't allow that.

            Factory::getApplication()->enqueueMessage(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $record_id),'warning');
           // $this->setMessage($this->getError(), 'error');

            $this->setRedirect(Route::_(($layout != 'modal' ? $link_item : $link_list), false));

            return false;
        }

        // Get file info
        //$files = $this->getFormFiles($file_form);

        // Check for upload errors
        if (!$this->checkFileError($file_form, $record_id)) {
            $this->setRedirect(Route::_(($layout != 'modal' ? $link_item : $link_list), false));
            return false;
        }



        // Upload file if we have any
        if (
            count($file_form) &&
            !empty($file_form['file']['tmp_name'])
        ) {
            $file = $file_form['file'];


            // Upload the file
            $result = $model->upload($file, (isset($data['project_id']) ? $data['project_id'] : JPApplicationHelper::getActiveProjectId()));

            if (is_array($result)) {
                $data['file'] = $result;
            }
            else {
                $error = $model->getError();
                Factory::getApplication()->enqueueMessage($error, 'error');

                $this->setRedirect(Route::_(($layout != 'modal' ? $link_item : $link_list), false));
                return false;
            }
        }

        // upload custom thumbnail
        if(
            $customThumbEnabled &&
            count($file_form) &&
            !empty($file_form['thumbnail']['tmp_name'])
        ){

            $thumb = $file_form['thumbnail'];


            // Upload the file
            $result = $model->uploadThumb($thumb, (isset($data['project_id']) ? $data['project_id'] : JPApplicationHelper::getActiveProjectId()));

            if (is_string($result)) { // add thumb to data
                $data['thumbnail'] = $result;
            }
            elseif($result == false){ // display error message if upload failed
                $error = $model->getError();
                Factory::getApplication()->enqueueMessage($error, 'error');
            }

        }


        $this->input->post->set('jform', $data);

        return parent::save($key, $urlVar);
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
        $tmpl    = Factory::getApplication()->input->getCmd('tmpl');
        $layout  = Factory::getApplication()->input->getCmd('layout', 'edit');
        $project = Factory::getApplication()->input->getUint('filter_project', 0);
        $append  = '';

        // Setup redirect info.
        if ($tmpl) {
            $append .= '&tmpl=' . $tmpl;
        }

        if ($layout) {
            $append .= '&layout=' . $layout;
        }

        if ($id) {
            $append .= '&' . $url_var . '=' . $id;
        }

        if ($project) {
            $append .= '&filter_project=' . $project;
        }

        return $append;
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

        foreach ($files AS &$file)
        {

            // Uploading a file is not required when updating an existing record
            if ($file['error'] == 4 && $record_id > 0) {
                $file['error'] = 0;
            }

            if ($file['error']) {
                $error = JPdesignsHelper::getFileErrorMsg($file['error'], $file['name']);
                //$this->setError($error);
                Factory::getApplication()->enqueueMessage($error, 'error');

                return false;
            }
        }

        return true;
    }
}
