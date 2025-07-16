<?php
/**
 * @package      Joomproject
 * @subpackage   Timetracking
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\MVC\Controller\FormController;


jimport('joomla.application.component.controllerform');


class JPtimeControllerTime extends FormController
{
    /**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 */
    protected $text_prefix = "COM_JOOMPROJECT_TIME";

    /**
     * The URL view list variable.
     *
     * @var    string
     */
    protected $view_list = 'timesheet';


    /**
     * Class constructor.
     *
     * @param    array    $config    A named array of configuration variables
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
    }
}
