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
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
// Base this on the backend users model
use \Joomla\Component\Users\Administrator\Model\UserModel;
/**
 * Joomproject User Model
 * Extends on the backend version of com_users
 *
 */
class JPusersModelUser extends UserModel
{
    /**
     * Method to find all projects a user has access to
     *
     * @param              $pk    The user id
     * @return    array           The project IDs
     */
    public function getProjects($pk = NULL)
    {
        $user  = Factory::getUser($pk);
        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        $access = JPprojectsHelper::getActions();
        $groups = implode(',', $user->getAuthorisedViewLevels());

        $query->select('id')
              ->from('#__jp_projects')
              ->where('access IN(' . $groups . ')');

        if (!$access->get('core.edit.state') && !$access->get('core.edit')) {
            $query->where('state = 1');
        }

        $db->setQuery((string) $query);
        $projects = (array) $db->loadColumn();

        return $projects;
    }


    public function deleteAvatar($pk)
    {
        $base_path = JPATH_ROOT . '/media/com_joomproject/repo/0/avatar';
        $img_path  = NULL;

        if (File::exists($base_path . '/' . $pk . '.jpg')) {
            $img_path = $base_path . '/' . $pk . '.jpg';
        }
        elseif (File::exists($base_path . '/' . $pk . '.jpeg')) {
            $img_path = $base_path . '/' . $pk . '.jpeg';
        }
        elseif (File::exists($base_path . '/' . $pk . '.png')) {
            $img_path = $base_path . '/' . $pk . '.png';
        }
        elseif (File::exists($base_path . '/' . $pk . '.gif')) {
            $img_path = $base_path . '/' . $pk . '.gif';
        }

        // No image found
        if (!$img_path) {
            return true;
        }

        if (File::delete($img_path) !== true) {
            return false;
        }

        return true;
    }


    public function saveAvatar($pk, $file)
    {
        if (!JPImage::isValid($file['name'], $file['tmp_name'])) {
            $this->setError(Text::_('COM_JOOMPROJECT_WARNING_NOT_AN_IMAGE'));
            return false;
        }

        // Delete any previous avatar
        if (!$this->deleteAvatar($pk)) {
            return false;
        }

        if ($file['error']) {
            $error = JPrepoHelper::getFileErrorMsg($file['error'], $file['name']);
            $this->setError($error);
            return false;
        }

        $uploadpath = JPATH_ROOT . '/media/com_joomproject/repo/0/avatar';
        $name = $pk . '.' . strtolower(File::getExt($file['name']));

        if (File::upload($file['tmp_name'], $uploadpath . '/' . $name) === true) {
            return true;
        }

        return false;
    }


    /**
	 * Method to auto-populate the model state.
	 *
	 * @return  void
	 */
	protected function populateState()
	{
		// Initialise variables.
		$table = $this->getTable();
		$key   = $table->getKeyName();

		// Get the pk of the record from the request.
		$pk = Factory::getApplication()->input->getInt($key, Factory::getApplication()->getIdentity()->get('id'));

		$this->setState($this->getName() . '.id', $pk);

		// Load the parameters.
		$value = ComponentHelper::getParams($this->option);
		$this->setState('params', $value);
	}
}
