<?php
/**
 * @package      Joomproject
 * @subpackage   Tasks
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2006-2012 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;

jimport('joomla.application.component.controlleradmin');


/**
 * Joomproject Task List Controller
 *
 */
class JPtasksControllerTasks extends JPControllerAdminJson
{
    /**
     * The default view
     *
     */
    protected $view_list = 'tasks';


    /**
     * Method to get a model object, loading it if required.
     *
     * @param     string    $name      The model name. Optional.
     * @param     string    $prefix    The class prefix. Optional.
     * @param     array     $config    Configuration array for model. Optional.
     *
     * @return    object               The model.
     */
    public function &getModel($name = 'TaskForm', $prefix = 'JPtasksModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }


    /**
     * Override for json return response
     *
     * @see       controlleradmin.php
     *
     * @return    string                 JSON encoded response
     */
    public function complete()
    {
        $data = array();
        $data['success']  = "true";
        $data['messages'] = array();
        $data['data']     = array();

        // Check for request forgeries
        if (!Session::checkToken()) {
            $data['success']    = "false";
            $data['messages'][] = Text::_('JINVALID_TOKEN');

            $this->sendResponse($data);
        }

        // Get the input
        $pks = Factory::getApplication()->input->get('cid', null, 'post', 'array');

        if (empty($pks)) {
            $data['success']    = "false";
            $data['messages'][] = Text::_($this->text_prefix . '_NO_ITEM_SELECTED');
        }
        else {
            // Get the model.
            $model = $this->getModel();

            // Make sure the item ids are integers
            ArrayHelper::toInteger($pks);

            // Publish the items.
            if (!$model->complete($pks)) {
                 $data['success']    = "false";
                 $data['messages'][] = $model->getError();
            }
            else {
                $ntext = $this->text_prefix . '_N_ITEMS_UPDATED';

                $data['success']    = "true";
                $data['messages'][] = Text::plural($ntext, count($pks));
            }
        }

        $this->sendResponse($data);
    }


    /**
     * Override for json return response
     *
     * @see       controlleradmin.php
     *
     * @return    string                 JSON encoded response
     */
    public function priority()
    {
        $data = array();
        $data['success']  = "true";
        $data['messages'] = array();
        $data['data']     = array();

        // Check for request forgeries
        if (!Session::checkToken()) {
            $data['success']    = "false";
            $data['messages'][] = Text::_('JINVALID_TOKEN');

            $this->sendResponse($data);
        }

        // Get the input
        $pks   = Factory::getApplication()->input->get('cid', null, 'post', 'array');
        $value = Factory::getApplication()->input->get('priority', null, 'post', 'array');

        if (empty($pks)) {
            $data['success']    = "false";
            $data['messages'][] = Text::_($this->text_prefix . '_NO_ITEM_SELECTED');
        }
        else {
            // Get the model.
            $model = $this->getModel();

            // Make sure the item ids are integers
            ArrayHelper::toInteger($pks);
            ArrayHelper::toInteger($value);

            if (!$model->savePriority($pks, $value)) {
                 $data['success']    = "false";
                 $data['messages'][] = $model->getError();
            }
            else {
                $ntext = $this->text_prefix . '_N_ITEMS_UPDATED';

                $data['success']    = "true";
                $data['messages'][] = Text::plural($ntext, count($pks));
            }
        }

        $this->sendResponse($data);
    }


    /**
     * Override for json return response
     *
     * @see       controlleradmin.php
     *
     * @return    string                 JSON encoded response
     */
    public function addUsers()
    {
        $data = array();
        $data['success']  = "true";
        $data['messages'] = array();
        $data['data']     = array();

        // Check for request forgeries
        if (!Session::checkToken()) {
            $data['success']    = "false";
            $data['messages'][] = Text::_('JINVALID_TOKEN');

            $this->sendResponse($data);
        }

        // Get the input
        $pks   = Factory::getApplication()->input->get('cid', null, 'post', 'array');
        $value = Factory::getApplication()->input->get('assigned', null, 'post', 'array');

        if (empty($pks)) {
            $data['success']    = "false";
            $data['messages'][] = Text::_($this->text_prefix . '_NO_ITEM_SELECTED');
        }
        else {
            // Get the model.
            $model = $this->getModel();

            // Make sure the item ids are integers
            ArrayHelper::toInteger($pks);
            ArrayHelper::toInteger($value);

            if (!$model->addUsers($pks, $value)) {
                 $data['success']    = "false";
                 $data['messages'][] = $model->getError();
            }
            else {
                $ntext = $this->text_prefix . '_N_ITEMS_UPDATED';

                $data['success']    = "true";
                $data['messages'][] = Text::plural($ntext, count($pks));
            }
        }

        $this->sendResponse($data);
    }
}
