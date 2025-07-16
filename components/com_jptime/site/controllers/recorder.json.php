<?php
/**
 * @package      Joomproject
 * @subpackage   Timetracking
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Session\Session;
use Joomla\Utilities\ArrayHelper;


jimport('joomla.application.component.controlleradmin');


/**
 * Joomproject Time Recorder JSON Controller
 *
 */
class JPtimeControllerRecorder extends JPControllerAdminJson
{
    /**
     * The default view
     *
     * @var    string
     */
    protected $view_list = 'recorder';


    /**
     * Method to get a model object, loading it if required.
     *
     * @param     string    $name      The model name. Optional.
     * @param     string    $prefix    The class prefix. Optional.
     * @param     array     $config    Configuration array for model. Optional.
     *
     * @return    object               The model.
     */
    public function &getModel($name = 'Recorder', $prefix = 'JPtimeModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }


    /**
     * Method to toggle the pause state of one or more items in the recorder
     *
     * @return    void
     */
    public function pause()
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
        $pks = \Joomla\CMS\Factory::getApplication()->input->get('cid', null, 'post', 'array');

        if (empty($pks)) {
            $data['success']    = "false";
            $data['messages'][] = Text::_($this->text_prefix . '_NO_ITEM_SELECTED');
        }
        else {
            // Get the model.
            $model = $this->getModel();

            // Make sure the item ids are integers
            ArrayHelper::toInteger($pks);

            // Pause the items.
            if (!$model->pause($pks)) {
                 $data['success'] = "false";
                 $data['messages'][] = $model->getError();
            }
            else {
                $data['success'] = "true";
            }
        }

        $this->sendResponse($data);
    }

	/**
	 * Method to punch-in item in the recorder
	 *
	 * @return    json
	 */
    public function quickLog(){

	    Session::checkToken( 'get' ) or die( 'Invalid Token' );

	    $data = array();
	    $data['success']  = "true";
	    $data['messages'] = '';
	    $input = Factory::getApplication()->input;

	    // get request data
	    $taskid = $input->get('taskid',0,'int');
	    $tasktitle = $input->get('tasktitle',0,'string');
	    $logtime = $input->get('logtime',0,'int');



	    // check request data
	    if($taskid <= 0 || $logtime <= 0){
		    $data['success']    = "false";
		    $data['messages'] = LayoutHelper::render(
		    	'common.floatingalert',
			        [
			        	'type' => 'warning',
				        'id' => $taskid,
				        'message' => Text::sprintf('COM_JOOMPROJECT_LOG_TIME_NOT_ADDED',$logtime, $tasktitle)
			        ],
			    null,
			    ['component' => 'com_joomproject','client' => 'admin']
		    );

		    $this->sendResponse($data);
	    }

	    // Get the model.
	    $model = $this->getModel();

	    // Punch-in new log item.
	    if (!$model->quickLog($taskid,$logtime)) {
		    $data['success'] = "false";
		    $data['messages'] = $model->getError();
		    $this->sendResponse($data);
	    }

	    $data['success'] = "true";
	    $data['messages'] = LayoutHelper::render(
		    'common.floatingalert',
		    [
			    'type' => 'success',
			    'id' => $taskid,
			    'message' => Text::sprintf('COM_JOOMPROJECT_LOG_TIME_ADDED',$logtime, $tasktitle)
		    ],
		    null,
			['component' => 'com_joomproject','client' => 'admin']
	    );

	    $this->sendResponse($data);
    }




    /**
     * Method to punch-in items in the recorder
     *
     * @return    void
     */
    public function punch()
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

        // Get the model.
        $model = $this->getModel();

        // Punch-in items.
        if (!$model->punch()) {
             $data['success'] = "false";
             $data['messages'][] = $model->getError();
             $this->sendResponse($data);
        }

        $data['success'] = "true";

        $app   = Factory::getApplication();
        $items = $app->getUserState('com_jptime.recorder.data');

        // Make sure we have items
        if (!is_array($items) || count($items) == 0) {
            $this->sendResponse($data);
        }

        foreach ($items AS $rec)
        {
            $id   = $rec['id'];
            $time = $rec['time'];

            $data['data'][$id] = HTMLHelper::_('time.format', $time);
        }

        $this->sendResponse($data);
    }


    /**
     * Method to update records details in the recorder
     *
     * @return    void
     */
    public function save()
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
        $pks = \Joomla\CMS\Factory::getApplication()->input->get('cid', null, 'post', 'array');

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
            if (!$model->save($pks)) {
                 $data['success'] = "false";
                 $data['messages'][] = $model->getError();
            }
            else {
                $data['success'] = "true";
            }
        }

        $this->sendResponse($data);
    }


    /**
     * Method to remove items from the recorder
     *
     * @return    void
     */
    public function delete()
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
        $pks = \Joomla\CMS\Factory::getApplication()->input->get('cid', null, 'post', 'array');
        $c   = \Joomla\CMS\Factory::getApplication()->input->getUint('complete');

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
            if (!$model->delete($pks, $c)) {
                 $data['success'] = "false";
                 $data['messages'][] = $model->getError();
            }
            else {
                $data['success'] = "true";
            }
        }

        $this->sendResponse($data);
    }
}
