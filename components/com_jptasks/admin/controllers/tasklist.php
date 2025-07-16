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
use Joomla\CMS\MVC\Controller\FormController;


jimport('joomla.application.component.controllerform');


class JPtasksControllerTasklist extends FormController
{
    /**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 */
    protected $text_prefix = "COM_JOOMPROJECT_TASKLIST";


    /**
     * Class constructor.
     *
     * @param     array              $config    A named array of configuration variables
     * @return    jcontrollerform
     */
    public function __construct($config = array())
    {

        // auto-checking feature
        if(\Joomla\CMS\Component\ComponentHelper::getParams('com_jptasks')->get('tasklist_autochecking',0))
            JoomprojectHelperFrontend::autoCheckin('jp_task_lists',Factory::getApplication()->input->getInt('id'));

        parent::__construct($config);
    }


    /**
     * Method to get a model object, loading it if required.
     *
     * @param     string    $name      The model name. Optional.
     * @param     string    $prefix    The class prefix. Optional.
     * @param     array     $config    Configuration array for model. Optional.
     *
     * @return    object               The model.
     */
    public function getModel($name = 'Tasklist', $prefix = 'JPtasksModel', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }
}
