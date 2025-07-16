<?php
/**
 * @package      Joomproject
 * @subpackage   Projects
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2006-2012 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Controller\BaseController;

HTMLHelper::_('jquery.framework');

// load joomproject library
require_once JPATH_ADMINISTRATOR.'/components/com_joomproject/libraries/vendor/autoload.php';

// Inits
$params = ComponentHelper::getParams('com_joomproject');
$app = Factory::getApplication();

// load full screen mode
if ($params->get('fullscreen_mode',0))
{
	$app->input->set('tmpl', 'component');
}

// Include dependancies
jimport('joomla.application.component.controller');
jimport('joomla.application.component.helper');
jimport('joomproject.framework');
HTMLHelper::_('jquery.framework');

JLoader::register('JoomprojectHelperFrontend',JPATH_ROOT.'/components/com_joomproject/helpers/joomproject.php');
JLoader::register('JoomprojectHelperColor',JPATH_SITE.'/components/com_joomproject/helpers/color.php');
JLoader::register('JoomprojectHelperAccess',JPATH_SITE.'/components/com_joomproject/helpers/access.php');
JLoader::register('JPprojectsHelperProject',JPATH_SITE.'/components/com_jpprojects/helpers/project.php');


LayoutHelper::$defaultBasePath = JPATH_ROOT.'/administrator/components/com_joomproject/layouts';

// force bootstrap load from CDN
$globalParams = ComponentHelper::getComponent('com_joomproject');


HTMLHelper::_('script', 'com_joomproject/joomproject/iziModal.min.js', array('version' => 'auto', 'relative' => true));
HTMLHelper::_('stylesheet', 'com_joomproject/joomproject/iziModal.css');

$controller = BaseController::getInstance('JPprojects');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();