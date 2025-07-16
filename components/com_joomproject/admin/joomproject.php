<?php
/**
 * @package      Joomproject
 * @subpackage   Dashboard
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\Controller\BaseController;


// Access check
if (!Factory::getApplication()->getIdentity()->authorise('core.manage')) {
	return \Joomla\CMS\Factory::getApplication()->enqueueMessage( Text::_('JERROR_ALERTNOAUTHOR'),'warning');
}

// Include dependencies
jimport('joomla.application.component.controller');
jimport('joomla.application.component.helper');
jimport('joomproject.framework');
HTMLHelper::_('jquery.framework');

// Register classes to autoload
JLoader::register('JoomprojectHelper', JPATH_ADMINISTRATOR . '/components/com_joomproject/helpers/joomproject.php');
JLoader::register('JoomprojectHelperVersion', JPATH_ADMINISTRATOR . '/components/com_joomproject/helpers/version.php');
JLoader::register('JoomprojectHelperAccess',JPATH_SITE.'/components/com_joomproject/helpers/access.php');

Factory::getDocument()->addScript(Uri::root().'media/com_joomproject/joomproject/js/iziModal.min.js');
Factory::getDocument()->addStyleSheet(Uri::root().'media/com_joomproject/joomproject/css/iziModal.css');


$controller = BaseController::getInstance('Joomproject');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();
