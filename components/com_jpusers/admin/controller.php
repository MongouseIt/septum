<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jpusers
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();
use Joomla\CMS\MVC\Controller\BaseController;

jimport('joomla.application.component.controller');


class JPusersController extends BaseController
{
    /**
     * The default view
     *
     * @var    string
     */
    protected $default_view = 'users';
}
