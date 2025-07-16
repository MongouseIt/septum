<?php
/**
 * @package      Joomproject
 * @subpackage   Users
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2006-2012 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;
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
jimport('joomproject.framework');
HTMLHelper::_('jquery.framework');

// load some helpers
JLoader::register('JoomprojectHelperAccess',JPATH_SITE.'/components/com_joomproject/helpers/access.php');


// force bootstrap load from CDN
$globalParams = ComponentHelper::getComponent('com_joomproject');

Factory::getDocument()->addScript(Uri::base().'media/com_joomproject/joomproject/js/iziModal.min.js');
Factory::getDocument()->addStyleSheet(Uri::base().'media/com_joomproject/joomproject/css/iziModal.css');
$controller = BaseController::getInstance('JPusers');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();