<?php
/**
 * @package      Joomproject
 * @subpackage   Comments
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2006-2012 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Controller\BaseController;

// load joomproject library
require_once JPATH_ADMINISTRATOR.'/components/com_joomproject/libraries/vendor/autoload.php';

// Inits
$params = ComponentHelper::getParams('com_joomproject');
$app = Factory::getApplication();

// Include dependancies
jimport('joomla.application.component.controller');
jimport('joomla.application.component.helper');
jimport('joomproject.framework');
HTMLHelper::_('jquery.framework');

JLoader::register('JPCommentsHelperComments',__DIR__.'/helpers/comments.php');


// load full screen mode
if ($params->get('fullscreen_mode',0) && $app->input->get('format','','word') != 'json')
{
    //$app->input->set('tmpl', 'component');
}

HTMLHelper::_('script', 'com_joomproject/joomproject/iziModal.min.js', array('version' => 'auto', 'relative' => true));
HTMLHelper::_('stylesheet', 'com_joomproject/joomproject/iziModal.css');
$controller = BaseController::getInstance('JPcomments');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();