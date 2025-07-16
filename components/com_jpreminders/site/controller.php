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

use Joomla\CMS\MVC\Controller\BaseController;


jimport('joomla.application.component.controller');


/**
 * Repository Main Controller Class
 *
 */
class JPremindersController extends BaseController
{
    /**
     * The default view
     *
     * @var    string
     */
    protected $default_view = 'reminders';


    /**
     * Method to display a view.
     *
     * @param     boolean        If true, the view output will be cached
     * @param     array          An array of safe url parameters
     *
     * @return    jcontroller    This object to support chaining.
     */

    public function display($cachable = false, $urlparams = array())
    {
        return parent::display();
    }
}
