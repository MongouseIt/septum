<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_joomproject
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
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

// register layout helper base
LayoutHelper::$defaultBasePath = JPATH_ROOT.'/administrator/components/com_joomproject/layouts';

// register helper classes
JLoader::register('JoomprojectHelperFrontend',JPATH_ROOT.'/components/com_joomproject/helpers/joomproject.php');
JLoader::register('JoomprojectHelperColor',JPATH_SITE.'/components/com_joomproject/helpers/color.php');
JLoader::register('JoomprojectHelperRoute',JPATH_SITE.'/components/com_joomproject/helpers/route.php');


HTMLHelper::_('script', 'com_joomproject/joomproject/iziModal.min.js', array('version' => 'auto', 'relative' => true));
HTMLHelper::_('stylesheet', 'com_joomproject/joomproject/iziModal.css');

// load full screen mode
if ($params->get('fullscreen_mode',0))
{
	$app->input->set('tmpl', 'component');
}

// execute controller
$controller = BaseController::getInstance('Joomproject');
$controller->execute($app->input->get('task'));
$controller->redirect();