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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\Controller\FormController;


jimport('joomla.application.component.controllerform');


/**
 * Joomproject Directory Form Controller
 *
 */
class JPrepoControllerDirectoryForm extends FormController
{
    /**
     * The default item view
     *
     * @var    string
     */
    protected $view_item = 'directoryform';

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
    public function &getModel($name = 'DirectoryForm', $prefix = '', $config = array('ignore_request' => true))
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
        $dir = isset($data['parent_id'])  ? (int) $data['parent_id']  : \Joomla\CMS\Factory::getApplication()->input->getUint('filter_parent_id');

        $user   = Factory::getApplication()->getIdentity();
        $asset  = 'com_jprepo.directory.' . $dir;
        $access = true;

        // Deny if no parent directory is given
        if (!$dir) {
            \Joomla\CMS\Factory::getApplication()->enqueueMessage(Text::_('COM_JOOMPROJECT_WARNING_DIRECTORY_NOT_FOUND'),'error');
            return false;
        }

        // Check if the user has viewing access when not a super admin
        if (!$user->authorise('core.create')) {
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
        $id = (int) isset($data[$key]) ? $data[$key] : 0;

        $user   = Factory::getApplication()->getIdentity();
        $uid    = $user->get('id');
        $asset  = 'com_jprepo.directory.' . $id;
        $access = true;

        // Check if the user has viewing access when not a super admin
        if (!$user->authorise('core.admin')) {
            $db    = Factory::getDbo();
            $query = $db->getQuery(true);

            $query->select('access')
                  ->from('#__jp_repo_dirs')
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
        $return  = $this->getReturnPage($parent, $project);
        $append  = '&layout=edit';

        // Setup redirect info.
        if ($project) $append .= '&filter_project=' . $project;
        if ($parent)  $append .= '&filter_parent_id=' . $parent;
        if ($id)      $append .= '&' . $url_var . '=' . $id;
        if ($item_id) $append .= '&Itemid=' . $item_id;
        if ($tmpl)    $append .= '&tmpl=' . $tmpl;
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
        // Need to override the parent method completely.
        $tmpl    = \Joomla\CMS\Factory::getApplication()->input->getCmd('tmpl');
        $project = \Joomla\CMS\Factory::getApplication()->input->getUint('filter_project', 0);
        $parent  = \Joomla\CMS\Factory::getApplication()->input->getUint('filter_parent_id', 0);
        $return  = $this->getReturnPage();
        $append  = '';

        // Setup redirect info.
        if ($project) $append .= '&filter_project=' . $project;
        if ($parent)  $append .= '&filter_parent_id=' . $parent;
        if ($tmpl)    $append .= '&tmpl=' . $tmpl;
        if ($return)  $append .= '&return=' . $return;

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
        $return  = \Joomla\CMS\Factory::getApplication()->input->get('return', null, 'base64');
        $parent  = \Joomla\CMS\Factory::getApplication()->input->getUint('filter_parent_id');
        $project = \Joomla\CMS\Factory::getApplication()->input->getUint('filter_project');
        $append  = '';

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
