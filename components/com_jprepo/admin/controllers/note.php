<?php
/**
 * @package      Joomproject
 * @subpackage   Repository
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\FormController;

jimport('joomla.application.component.controllerform');


class JPrepoControllerNote extends FormController
{
    /**
     * The URL view list variable.
     *
     * @var    string
     */
    protected $view_list = 'repository';


    /**
     * Class constructor.
     *
     * @param    array    $config    A named array of configuration variables
     */
    public function __construct($config = array())
    {
        $id   = \Joomla\CMS\Factory::getApplication()->input->getUint('id');
        $rev  = \Joomla\CMS\Factory::getApplication()->input->getUint('rev');
        $task = \Joomla\CMS\Factory::getApplication()->input->get('task');

        if ($task == 'cancel' && $id && $rev) {
            $this->view_list = 'noterevisions';
        }

        // auto-checking feature
        if(\Joomla\CMS\Component\ComponentHelper::getParams('com_jprepo')->get('note_autochecking',0))
            JoomprojectHelperFrontend::autoCheckin('jp_repo_notes',Factory::getApplication()->input->getInt('id'));

        parent::__construct($config);
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
        $user = Factory::getApplication()->getIdentity();

        $dir_id = (int) \Joomla\CMS\Factory::getApplication()->input->getUInt('filter_parent_id', 0);
        $access = true;

        if (isset($data['dir_id'])) {
            $dir_id = (int) $data['dir_id'];
        }

        // Verify directory access
        if ($dir_id) {
            $model = $this->getModel('Directory', 'JPrepoModel');
            $item  = $model->getItem($dir_id);

            if (!empty($item)) {
                $access = JPrepoHelper::getActions('directory', $item->id);

                if (!$user->authorise('core.admin')) {
                    if (!in_array($item->access, $user->getAuthorisedViewLevels())) {
                        \Joomla\CMS\Factory::getApplication()->enqueueMessage(Text::_('COM_JOOMPROJECT_WARNING_DIRECTORY_ACCESS_DENIED'),'error');
                        $access = false;
                    }
                    elseif (!$access->get('core.create')) {
                        \Joomla\CMS\Factory::getApplication()->enqueueMessage(Text::_('COM_JOOMPROJECT_WARNING_DIRECTORY_CREATE_NOTE_DENIED'),'error');
                        $access = false;
                    }
                }
            }
            else {
                \Joomla\CMS\Factory::getApplication()->enqueueMessage(Text::_('COM_JOOMPROJECT_WARNING_DIRECTORY_NOT_FOUND'),'error');
                $access = false;
            }
        }
        else {
            $access = JPrepoHelper::getActions();

            if (!$access->get('core.create')) {
                \Joomla\CMS\Factory::getApplication()->enqueueMessage(Text::_('COM_JOOMPROJECT_WARNING_CREATE_NOTE_DENIED'),'error');
                $access = false;
            }
        }

        return ($access && ($dir_id > 0));
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
        $rev     = \Joomla\CMS\Factory::getApplication()->input->getUint('rev', 0);
        $append  = '';

        // Setup redirect info.
        if ($project) {
            $append .= '&filter_project=' . $project;
        }

        if ($parent) {
            $append .= '&filter_parent_id=' . $parent;
        }

        if ($id) {
            $append .= '&' . $url_var . '=' . $id;
        }

        if ($rev) {
            $append .= '&rev=' . $rev;
        }

        if ($tmpl) {
            $append .= '&tmpl=' . $tmpl;
        }

        if ($layout) {
            $append .= '&layout=' . $layout;
        }



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
        $id      = \Joomla\CMS\Factory::getApplication()->input->getUint('id');
        $rev     = \Joomla\CMS\Factory::getApplication()->input->getUint('rev');
        $append  = '';

        // Setup redirect info.
        if ($project) {
            $append .= '&filter_project=' . $project;
        }

        if ($parent) {
            $append .= '&filter_parent_id=' . $parent;
        }

        if ($id && $rev) {
            $append .= '&id=' . $id;
        }

        if ($tmpl) {
            $append .= '&tmpl=' . $tmpl;
        }

        return $append;
    }
}
