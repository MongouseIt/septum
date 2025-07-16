<?php
/**
 * @package      Joomproject Pro
 * @subpackage   Designs
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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Component\ComponentHelper;
jimport('joomproject.framework');
// Access check
if (!Factory::getApplication()->getIdentity()->authorise('core.manage', 'com_jpdesigns')) {
	return Factory::getApplication()->enqueueMessage( Text::_('JERROR_ALERTNOAUTHOR'),'warning');
}

// Include dependencies

HTMLHelper::_('jquery.framework');

// Register classes to autoload
JLoader::register('JPdesignsHelper', JPATH_ADMINISTRATOR . '/components/com_jpdesigns/helpers/jpdesigns.php');
LayoutHelper::$defaultBasePath = JPATH_ROOT.'/administrator/components/com_joomproject/layouts';
$globalParams = ComponentHelper::getComponent('com_joomproject')->getParams();
$jinput = Factory::getApplication()->input;
if($jinput->get('layout') == "edit"){

}

HTMLHelper::_('script', 'com_joomproject/joomproject/iziModal.min.js', array('version' => 'auto', 'relative' => true));
HTMLHelper::_('stylesheet', 'com_joomproject/joomproject/iziModal.css');

Factory::getDocument()->addStyleSheet(Uri::root().'media/com_joomproject/css/admin.style.css');


$controller = BaseController::getInstance('JPdesigns');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();
