<?php
/**
* @package      pkg_joomproject
* @subpackage   com_jprepo
*
* @author       JoomBoost
* @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;

jimport('joomproject.controller.form.json');
jimport('joomla.filesystem.file');


/**
 * Joomproject File Form JSON Controller
 *
 */
class JPrepoControllerFile extends JPControllerFormJson
{
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
        $rdata = array();
        $rdata['success']  = true;
        $rdata['messages'] = array();
        $rdata['data']     = array();
        $rdata['file']     = '';

        $files_data = \Joomla\CMS\Factory::getApplication()->input->get('qqfile', null, 'files');
        $get_data   = \Joomla\CMS\Factory::getApplication()->input->get('qqfile', null, 'get');
        $dir        = \Joomla\CMS\Factory::getApplication()->input->getUInt('filter_parent_id', \Joomla\CMS\Factory::getApplication()->input->getUInt('dir_id'));
        $project    = \Joomla\CMS\Factory::getApplication()->input->getUInt('filter_project', JPApplicationHelper::getActiveProjectId());
        $method     = null;

        // Determine the upload method
        if ($files_data) {
            $method = 'form';
            $file   = $files_data;
        }
        elseif ($get_data) {
            $method = 'xhr';
            $file   = array('name' => $get_data, 'tmp_name' => $get_data, 'error' => 0);
        }
        else {
            $rdata['success'] = false;
            $rdata['messages'][] = Text::_('COM_JOOMPROJECT_WARNING_FILE_UPLOAD_ERROR_4');

            $this->sendResponse($rdata);
        }

        // Access check.
        if (!$this->allowSave($d = array())) {
            $rdata['success'] = false;
            $rdata['messages'][] = Text::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED');

            $this->sendResponse($rdata);
        }

        // Check for upload error
        if ($file['error']) {
            $error = JPrepoHelper::getFileErrorMsg($file['error'], $file['name']);

            $rdata['success'] = false;
            $rdata['messages'][] = $error;

            $this->sendResponse($rdata);
        }

        // Find file with the same name in the same dir
        $db    = Factory::getDbo();
        $query = $db->getQuery(true);
        $name  = \Joomla\CMS\Filesystem\File::makeSafe($file['name']);

        $query->select('id')
              ->from('#__jp_repo_files')
              ->where('dir_id = ' . (int) $dir)
              ->where('file_name = ' . $db->quote($name));

        $db->setQuery($query, 0, 1);
        $parent_id = (int) $db->loadResult();

        $model  = $this->getModel();
        $result = $model->upload($file, $dir, ($method == 'xhr' ? true : false), $parent_id);

        if (!$result) {
            $rdata['success'] = false;
            $rdata['messages'][] = $model->getError();

            $this->sendResponse($rdata);
        }

        // Prepare data for saving
        $data = array();
        $data['project_id'] = $project;
        $data['dir_id']     = $dir;
        $data['file']       = $result;
        $data['title']      = $result['name'];

        if ($parent_id) {
            $data['id'] = $parent_id;
        }

        if (!$model->save($data)) {
            $rdata['success'] = false;
            $rdata['messages'][] = $model->getError();

            $this->sendResponse($rdata);
        }

        $this->sendResponse($rdata);
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
        $user    = Factory::getApplication()->getIdentity();
        $project = \Joomla\Utilities\ArrayHelper::getValue($data, 'project_id', \Joomla\CMS\Factory::getApplication()->input->getInt('filter_project'), 'int');
        $dir_id  = \Joomla\Utilities\ArrayHelper::getValue($data, 'dir_id', \Joomla\CMS\Factory::getApplication()->input->getInt('filter_parent_id'), 'int');

        // Check general access
        if (!$user->authorise('core.create', 'com_jprepo')) {
            \Joomla\CMS\Factory::getApplication()->enqueueMessage(Text::_('COM_JOOMPROJECT_WARNING_CREATE_FILE_DENIED'), 'error');

            return false;
        }

        // Validate directory access
        $model = $this->getModel('Directory', 'JPrepoModel');
        $item  = $model->getItem($dir_id);

        if ($item == false || empty($item->id) || $dir_id <= 1) {
            \Joomla\CMS\Factory::getApplication()->enqueueMessage(Text::_('COM_JOOMPROJECT_WARNING_DIRECTORY_NOT_FOUND'), 'error');
            return false;
        }

        $access = JPrepoHelper::getActions('directory', $item->id);

        if (!$user->authorise('core.admin')) {
            if (!in_array($item->access, $user->getAuthorisedViewLevels())) {
                \Joomla\CMS\Factory::getApplication()->enqueueMessage(Text::_('COM_JOOMPROJECT_WARNING_DIRECTORY_ACCESS_DENIED'), 'error');
                return false;
            }
            elseif (!$access->get('core.create')) {
                \Joomla\CMS\Factory::getApplication()->enqueueMessage(Text::_('COM_JOOMPROJECT_WARNING_DIRECTORY_CREATE_FILE_DENIED'),'error');
                return false;
            }
        }

        return true;
    }
}
