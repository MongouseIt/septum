<?php
/**
* @package      pkg_joomproject
* @subpackage   com_jprepo
*
* @author       JoomBoost (eaxs)
* @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\Utilities\ArrayHelper;
jimport('joomproject.framework');
jimport('joomproject.controller.form.json');


/**
 * Joomproject File Form JSON Controller
 *
 */
class JPrepoControllerFileForm extends JPControllerFormJson
{
    public function save($key = null, $urlVar = null)
    {
        $rdata = array();
        $rdata['success']  = true;
        $rdata['messages'] = array();
        $rdata['data']     = array();
        $rdata['file']     = '';

        $files_data = Factory::getApplication()->input->get('qqfile', null, 'files');
        $get_data   = Factory::getApplication()->input->get('qqfile', null, 'get');
        $dir        = Factory::getApplication()->input->getUInt('filter_parent_id', Factory::getApplication()->input->getUInt('dir_id'));
        $project    = Factory::getApplication()->input->getUInt('filter_project', JPApplicationHelper::getActiveProjectId());
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
        if (!$this->allowSave($d = array()) || defined('JPDEMO')) {
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
        $name  = File::makeSafe($file['name']);

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
        $project = ArrayHelper::getValue($data, 'project_id', Factory::getApplication()->input->getInt('filter_project'), 'int');
        $dir_id  = ArrayHelper::getValue($data, 'dir_id', Factory::getApplication()->input->getInt('filter_parent_id'), 'int');

        // Demo mode check
        if (defined('JPDEMO')) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_JOOMPROJECT_WARNING_CREATE_FILE_DENIED'),'error');
            return false;
        }

        // Make sure the directory exists
        $model    = $this->getModel('Directory', 'JPrepoModel');
        $item_dir = $model->getItem($dir_id);

        if (empty($item_dir) || !$dir_id) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_JOOMPROJECT_WARNING_DIRECTORY_NOT_FOUND'),'error');
        }

        // Check super admin permission
        if ($user->authorise('core.admin')) {
            return true;
        }

        // Check if the user has viewing access when not a super admin
        if (!in_array($item_dir->access, $user->getAuthorisedViewLevels())) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_JOOMPROJECT_WARNING_DIRECTORY_ACCESS_DENIED'),'error');
            return false;
        }

        // Check create permission
        if (!$user->authorise('core.create', 'com_jprepo.directory.' . $dir_id)) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_JOOMPROJECT_WARNING_DIRECTORY_CREATE_FILE_DENIED'),'error');
            return false;
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
}
