<?php
/**
 * @package      Joomproject.Library
 * @subpackage   Controller
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 20012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\Utilities\ArrayHelper;

jimport('joomla.application.component.controlleradmin');


/**
 * JSON Admin List Controller
 *
 */
class JPControllerAdminJson extends AdminController
{
    public function __construct($config = array())
    {
        parent::__construct($config);

        // Define standard task mappings.
        $this->registerTask('unwatch', 'watch');
    }


    /**
     * Method to publish a list of items
     *
     * @return    void
     */
    public function publish()
    {
        $rdata = array();
        $rdata['success']  = "true";
        $rdata['messages'] = array();
        $rdata['data']     = array();

        // Check for request forgeries
        if (!Session::checkToken()) {
            $rdata['success']    = false;
            $rdata['messages'][] = Text::_('JINVALID_TOKEN');

            $this->sendResponse($rdata);
        }

        // Get items to publish from the request.
        $cid   = \Joomla\CMS\Factory::getApplication()->input->get('cid', array(), '', 'array');
        $data  = array('publish' => 1, 'unpublish' => 0, 'archive' => 2, 'trash' => -2, 'report' => -3);
        $task  = $this->getTask();
        $value = \Joomla\Utilities\ArrayHelper::getValue($data, $task, 0, 'int');

        if (empty($cid)) {
            $rdata['success']    = "false";
            $rdata['messages'][] = Text::_($this->text_prefix . '_NO_ITEM_SELECTED');
        }
        else {
            // Get the model.
            $model = $this->getModel();

            // Make sure the item ids are integers
            \Joomla\Utilities\ArrayHelper::toInteger($cid);

            // Publish the items.
            if (!$model->publish($cid, $value)) {
                 $rdata['success']    = "false";
                 $rdata['messages'][] = $model->getError();
            }
            else {
                if ($value == 1) {
                    $ntext = $this->text_prefix . '_N_ITEMS_PUBLISHED';
                }
                elseif ($value == 0) {
                    $ntext = $this->text_prefix . '_N_ITEMS_UNPUBLISHED';
                }
                elseif ($value == 2) {
                    $ntext = $this->text_prefix . '_N_ITEMS_ARCHIVED';
                }
                else {
                    $ntext = $this->text_prefix . '_N_ITEMS_TRASHED';
                }

                $rdata['success']    = "true";
                $rdata['messages'][] = Text::plural($ntext, count($cid));
            }
        }

        $this->sendResponse($rdata);
    }


    public function watch()
    {
        $rdata = array();
        $rdata['success']  = "true";
        $rdata['messages'] = array();
        $rdata['data']     = array();

        // Check for request forgeries
        if (!Session::checkToken()) {
            $rdata['success']    = "false";
            $rdata['messages'][] = Text::_('JINVALID_TOKEN');

            $this->sendResponse($rdata);
        }

        // Make sure the user is logged in
        if (Factory::getApplication()->getIdentity()->get('id') == 0) {
            $rdata['success']    = "false";
            $rdata['messages'][] = Text::_('JERROR_ALERTNOAUTHOR');

            $this->sendResponse($rdata);
        }

        $cid   = \Joomla\CMS\Factory::getApplication()->input->get('cid', array(), '', 'array');
        $task  = $this->getTask();
        $data  = array('watch' => 1, 'unwatch' => 0);
        $value = \Joomla\Utilities\ArrayHelper::getValue($data, $task, 0, 'int');

        if (empty($cid)) {
            $rdata['success']    = "false";
            $rdata['messages'][] = Text::_($this->text_prefix . '_NO_ITEM_SELECTED');
        }
        else {
            // Get the model.
            $model = $this->getModel();

            // Make sure the item ids are integers
            \Joomla\Utilities\ArrayHelper::toInteger($cid);

            // Publish the items.
            if (!$model->watch($cid, $value)) {
                 $rdata['success']    = "false";
                 $rdata['messages'][] = $model->getError();
            }
            else {
                if ($value == 1) {
                    $ntext = $this->text_prefix . '_N_ITEMS_WATCHED';
                }
                else {
                    $ntext = $this->text_prefix . '_N_ITEMS_UNWATCHED';
                }

                $rdata['success']    = "true";
                $rdata['messages'][] = Text::plural($ntext, count($cid));
            }
        }

        $this->sendResponse($rdata);
    }


    /**
     * Method to save the submitted ordering values for records.
     *
     */
    public function saveorder()
    {
        $data = array();
        $data['success'] = "true";
        $data['messages'] = array();
        $data['data'] = array();

        // Check for request forgeries
        if (!Session::checkToken()) {
            $data['success']    = false;
            $data['messages'][] = Text::_('JINVALID_TOKEN');

            $this->sendResponse($data);
        }

        // Get the input
        $pks   = \Joomla\CMS\Factory::getApplication()->input->get('cid', null, 'post', 'array');
        $order = \Joomla\CMS\Factory::getApplication()->input->get('order', null, 'post', 'array');

        // Sanitize the input
        \Joomla\Utilities\ArrayHelper::toInteger($pks);
        \Joomla\Utilities\ArrayHelper::toInteger($order);

        // Get the model
        $model = $this->getModel();

        // Save the ordering
        $return = $model->saveorder($pks, $order);

        if ($return === false) {
            // Reorder failed
            $data['success']    = "false";
            $data['messages'][] = Text::sprintf('JLIB_APPLICATION_ERROR_REORDER_FAILED', $model->getError());
        }
        else {
            // Reorder succeeded.
            $data['success']    = "true";
            $data['messages'][] = Text::_('JLIB_APPLICATION_SUCCESS_ORDERING_SAVED');
        }

        $this->sendResponse($data);
    }


    /**
     * Removes an item.
     *
     * @return    void
     */
    public function delete()
    {
        $data = array();
        $data['success'] = "true";
        $data['messages'] = array();
        $data['data'] = array();

        // Check for request forgeries
        if (!Session::checkToken()) {
            $data['success']    = false;
            $data['messages'][] = Text::_('JINVALID_TOKEN');

            $this->sendResponse($data);
        }

        // Get items to remove from the request.
        $cid = \Joomla\CMS\Factory::getApplication()->input->get('cid', array(), '', 'array');

        if (!is_array($cid) || count($cid) < 1) {
            $data['success']    = "false";
            $data['messages'][] = Text::_($this->text_prefix . '_NO_ITEM_SELECTED');
        }
        else {
            // Get the model.
            $model = $this->getModel();

            // Make sure the item ids are integers
            jimport('joomla.utilities.arrayhelper');
            \Joomla\Utilities\ArrayHelper::toInteger($cid);

            // Remove the items.
            if ($model->delete($cid)) {
                $data['success']    = "true";
                $data['messages'][] = Text::plural($this->text_prefix . '_N_ITEMS_DELETED', count($cid));
            }
            else {
                $data['success']    = "false";
                $data['messages'][] = $model->getError();
            }
        }

        $this->sendResponse($data);
    }


    /**
     * Check in of one or more records.
     *
     * @return    boolean    True on success
     */
    public function checkin()
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

        // Initialise variables.
        $ids = \Joomla\CMS\Factory::getApplication()->input->get('cid', null, 'post', 'array');

        $model  = $this->getModel();
        $return = $model->checkin($ids);

        if ($return === false) {
            // Checkin failed.
            $data['success']    = "false";
            $data['messages'][] = Text::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError());
        }
        else {
            // Checkin succeeded.
            $data['success']    = "true";
            $data['messages'][] = Text::plural($this->text_prefix . '_N_ITEMS_CHECKED_IN', count($ids));
        }

        $this->sendResponse($data);
    }


    /**
     * Sends a JSON response to the browser
     *
     * @param     array    $data    The data to send
     *
     * @return    void
     **/
    protected function sendResponse($data)
    {
        // Set the MIME type for JSON output.
        Factory::getDocument()->setMimeEncoding('application/json');

        // Change the suggested filename.
        \Joomla\CMS\Factory::getApplication()->setHeader('Content-Disposition', 'attachment;filename="' . $this->view_list . '.json"');

        foreach($data AS $key => $value)
        {
            if (is_array($value)) {
                if (count($value) == 0) {
                    unset($data[$key]);
                }
            }
        }

        // Output the JSON data.
        echo json_encode($data);

        jexit();
    }
}
