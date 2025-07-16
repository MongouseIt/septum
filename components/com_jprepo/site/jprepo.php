<?php
/**
 * @package      Joomproject
 * @subpackage   Repository
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

LayoutHelper::$defaultBasePath = JPATH_ROOT.'/administrator/components/com_joomproject/layouts';

// Register classes to autoload
HTMLHelper::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jprepo/helpers/html');
JLoader::register('JHtmlGrid', JPATH_ROOT . '/components/com_joomproject/helpers/html/grid.php');

JLoader::register('JoomprojectHelperFrontend', JPATH_ROOT . '/components/com_joomproject/helpers/joomproject.php');


HTMLHelper::_('script', 'com_joomproject/joomproject/iziModal.min.js', array('version' => 'auto', 'relative' => true));
HTMLHelper::_('stylesheet', 'com_joomproject/joomproject/iziModal.css');


$controller = BaseController::getInstance('JPrepo');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();