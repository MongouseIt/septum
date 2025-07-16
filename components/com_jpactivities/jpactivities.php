<?php
/**
 * @package      pkg_jpactivities
 * @subpackage   com_jpactivities
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;


// load joomproject library
require_once JPATH_ADMINISTRATOR.'/components/com_joomproject/libraries/vendor/autoload.php';

// Access check
if (!Factory::getApplication()->getIdentity()->authorise('core.manage', 'com_jpactivities')) {

Factory::getApplication()->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'),'error');

    return false;
}

// Include dependencies
jimport('joomla.application.component.controller');
jimport('joomla.application.component.helper');


// Register classes to autoload
JLoader::register('JPactivitiesHelper', JPATH_ADMINISTRATOR . '/components/com_jpactivities/helpers/jpactivities.php');
JLoader::register('JoomprojectHelper', JPATH_ADMINISTRATOR . '/components/com_joomproject/helpers/joomproject.php');


$controller = BaseController::getInstance('JPactivities');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();
