<?php
/**
 * @package      Joomproject
 * @subpackage   Comments
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;;
use Joomla\CMS\MVC\Controller\BaseController;



class JPcommentsController extends BaseController
{
    /**
     * The default view
     *
     * @var    string
     */
    protected $default_view = 'comments';


    public function display($cachable = false, $urlparams = false)
    {
        JPcommentsHelper::addSubmenu(Factory::getApplication()->input->get('view', $this->default_view));
        parent::display();

        return $this;
    }
}
