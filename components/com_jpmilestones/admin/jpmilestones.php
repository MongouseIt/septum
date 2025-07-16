<?php
/**
 * @package      Joomproject
 * @subpackage   Milestones
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

// Access check.
if (!Factory::getApplication()->getIdentity()->authorise('core.manage', 'com_jpmilestones')) {
	return Factory::getApplication()->enqueueMessage( Text::_('JERROR_ALERTNOAUTHOR'),'warning');
}

// Include dependencies
jimport('joomla.application.component.controller');
jimport('joomla.application.component.helper');
jimport('joomproject.framework');
HTMLHelper::_('jquery.framework');

// Register classes to autoload
JLoader::register('JPmilestonesHelper', JPATH_ADMINISTRATOR . '/components/com_jpmilestones/helpers/jpmilestones.php');
LayoutHelper::$defaultBasePath = JPATH_ROOT.'/administrator/components/com_joomproject/layouts';

$globalParams = ComponentHelper::getComponent('com_joomproject')->getParams();
$jinput = Factory::getApplication()->input;


HTMLHelper::_('script', 'com_joomproject/joomproject/iziModal.min.js', array('version' => 'auto', 'relative' => true));
HTMLHelper::_('stylesheet', 'com_joomproject/joomproject/iziModal.css');

$controller = BaseController::getInstance('JPmilestones');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();
