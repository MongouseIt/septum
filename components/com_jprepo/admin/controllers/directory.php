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

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\Utilities\ArrayHelper;


jimport('joomla.application.component.controllerform');


/**
 * Repository Directory Controller Class
 *
 */
class JPrepoControllerDirectory extends FormController
{
    /**
     * The URL view list variable.
     *
     * @var    string
     */
    protected $view_list = 'repository';


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
        $project = ArrayHelper::getValue($data, 'project_id', \Joomla\CMS\Factory::getApplication()->input->getInt('filter_project'), 'int');
        $parent  = ArrayHelper::getValue($data, 'parent_id', \Joomla\CMS\Factory::getApplication()->input->getInt('filter_parent_id'), 'int');

        if (!$project || $parent <= 1) return false;

        // Validate access on the target parent directory
        if (!$user->authorise('core.create', 'com_jprepo.directory.'. $parent)) {
            return false;
        }

        return parent::allowAdd($data);
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
        if ($user->authorise('core.edit', 'com_jprepo.directory.' . $id)) {
            return true;
        }

        // Fallback on edit.own.
        if ($user->authorise('core.edit.own', 'com_jprepo.directory.' . $id)) {
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
        $tmpl    = Factory::getApplication()->input->getCmd('tmpl');
        $layout  = Factory::getApplication()->input->getCmd('layout', 'edit');
        $project = Factory::getApplication()->input->getUint('filter_project');
        $parent  = Factory::getApplication()->input->getUint('filter_parent_id');
        $append  = '';

        // Setup redirect info.
        if ($project) $append .= '&filter_project=' . $project;
        if ($parent)  $append .= '&filter_parent_id=' . $parent;
        if ($id)      $append .= '&' . $url_var . '=' . $id;
        if ($tmpl)    $append .= '&tmpl=' . $tmpl;
        if ($layout)  $append .= '&layout=' . $layout;

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
        $append  = '';

        // Setup redirect info.
        if ($project) $append .= '&filter_project=' . $project;
        if ($parent)  $append .= '&filter_parent_id=' . $parent;
        if ($tmpl)    $append .= '&tmpl=' . $tmpl;

        return $append;
    }
}
