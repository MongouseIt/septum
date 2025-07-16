<?php
/**
 * @package      Joomproject
 * @subpackage   Projects
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


use Joomla\CMS\MVC\Controller\AdminController;



jimport('joomla.application.component.controlleradmin');


/**
 * Project list controller class.
 *
 */
class JPprojectsControllerProjects extends AdminController
{
    /**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 */
    protected $text_prefix = "COM_JOOMPROJECT_PROJECTS";


    /**
     * Constructor.
     *
     * @param    array          $config    An optional associative array of configuration settings

     * @see      jcontroller
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
    }

    /**
     * Proxy for getModel.
     *
     * @param     string    $name      The name of the model.
     * @param     string    $prefix    The prefix for the PHP class name.
     * @return    jmodel
     */
    public function getModel($name = 'Project', $prefix = 'JPprojectsModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }
}
