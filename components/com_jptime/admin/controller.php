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

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;


jimport('joomla.application.component.controller');


class JPtimeController extends BaseController
{
    /**
     * The default view
     *
     * @var    string
     */
    protected $default_view = 'timesheet';


    public function display($cachable = false, $urlparams = false)
    {
        JPtimeHelper::addSubmenu(Factory::getApplication()->input->get('view', $this->default_view));
        parent::display();

        return $this;
    }
}
