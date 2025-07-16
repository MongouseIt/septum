<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_reminders
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */


defined('_JEXEC') or die();

use Joomla\CMS\MVC\Controller\AdminController;


jimport('joomla.application.component.controlleradmin');


/**
 * Directories list controller class.
 *
 */
class JPremindersControllerReminders extends AdminController
{
    /**
     * The URL view list variable.
     *
     * @var    string
     */
    protected $view_list = 'reminders';


    /**
     * Proxy for getModel.
     *
     * @param     string    $name      The name of the model.
     * @param     string    $prefix    The prefix for the PHP class name.
     * @return    jmodel
     */

    public function __construct($config = array())
    {
        parent::__construct($config);
    }



    public function getModel($name = 'Reminder', $prefix = 'JPremindersModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }


}
