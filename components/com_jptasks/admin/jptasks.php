<?php
/**
 * @package      Joomproject
 * @subpackage   Tasks
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Component\ComponentHelper;

// Access check
if (!Factory::getApplication()->getIdentity()->authorise('core.manage', 'com_jptasks')) {
	return \Joomla\CMS\Factory::getApplication()->enqueueMessage( Text::_('JERROR_ALERTNOAUTHOR'),'warning');
}

// load the new joomproject library
require_once JPATH_ADMINISTRATOR.'/components/com_joomproject/libraries/vendor/autoload.php';

// Include dependencies
jimport('joomla.application.component.controller');
jimport('joomla.application.component.helper');
jimport('joomproject.framework');
HTMLHelper::addIncludePath(JPATH_ROOT.'/components/com_joomproject/helpers/html');
$jinput = Factory::getApplication()->input;
HTMLHelper::_('jquery.framework');
// Register classes to autoload
JLoader::register('JPtasksHelper', JPATH_ADMINISTRATOR . '/components/com_jptasks/helpers/jptasks.php');
LayoutHelper::$defaultBasePath = JPATH_ROOT.'/administrator/components/com_joomproject/layouts';

$globalParams = ComponentHelper::getComponent('com_joomproject')->getParams();

$jinput = Factory::getApplication()->input;

$controller = BaseController::getInstance('JPtasks');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();
