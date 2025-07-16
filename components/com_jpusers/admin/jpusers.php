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

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\Controller\BaseController;


// Access check
if (!Factory::getApplication()->getIdentity()->authorise('core.manage', 'com_jpusers')) {
	return \Joomla\CMS\Factory::getApplication()->enqueueMessage( Text::_('JERROR_ALERTNOAUTHOR'),'warning');
}

$format = Factory::getApplication()->input->get('format', 'html','word');
if ($format == 'html' && !ComponentHelper::getParams('com_jpprojects')->get('permissions_type',0)) {
    Factory::getApplication()->redirect('index.php?option=com_users&view=users');
    jexit();
}


// Include dependencies
jimport('joomla.application.component.controller');
jimport('joomla.application.component.helper');
jimport('joomproject.framework');
HTMLHelper::_('jquery.framework');

$globalParams = ComponentHelper::getComponent('com_joomproject')->getParams();


Factory::getDocument()->addScript(Uri::root().'media/com_joomproject/joomproject/js/iziModal.min.js');
Factory::getDocument()->addStyleSheet(Uri::root().'media/com_joomproject/joomproject/css/iziModal.css');


$controller = BaseController::getInstance('JPusers');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();

