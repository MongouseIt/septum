<?php
/**
 * @package      Joomproject
 * @subpackage   Tasks
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\Utilities\ArrayHelper;


jimport('joomla.application.component.controlleradmin');


/**
 * Joomproject Tasks (list) controller class.
 *
 */
class JPtasksControllerTasks extends AdminController
{
    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     */
    protected $text_prefix = "COM_JOOMPROJECT_TASKS";


    /**
     * Proxy for getModel.
     *
     * @param     string    $name      The name of the model.
     * @param     string    $prefix    The prefix for the class name.
     * @param     array     $config    Configuration array for model. Optional.
     *
     * @return    object
     */
    public function getModel($name = 'Task', $prefix = 'JPtasksModel', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Method to save the submitted ordering values for records via AJAX.
     *
     * @return    void
     */
    public function saveOrderAjax()
    {
        $pks   = $this->input->post->get('cid', array(), 'array');
        $order = $this->input->post->get('order', array(), 'array');

        // Sanitize the input
        ArrayHelper::toInteger($pks);
        ArrayHelper::toInteger($order);

        if ($this->getModel()->saveorder($pks, $order)) echo "1";

        // Close the application
        Factory::getApplication()->close();
    }
}
