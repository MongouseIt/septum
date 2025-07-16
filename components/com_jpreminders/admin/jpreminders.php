<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_reminders
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */


defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Component\ComponentHelper;
JLoader::register('JPremindersHelper', __DIR__ . '/helpers/jpreminders.php');
HTMLHelper::addIncludePath(JPATH_ROOT . '/components/com_joomproject/helpers/html');
HTMLHelper::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_joomproject/helpers/html');
jimport('joomproject.framework');
HTMLHelper::_('jquery.framework');
HTMLHelper::_('jphtml.script.jQuerySelect2');
$jinput       = Factory::getApplication()->input;
$globalParams = ComponentHelper::getComponent('com_joomproject')->getParams();

if ($jinput->get('layout') == "edit")
{

	LayoutHelper::$defaultBasePath = JPATH_ADMINISTRATOR . '/components/com_joomproject/layouts';

}



HTMLHelper::_('script', 'com_joomproject/joomproject/iziModal.min.js', array('version' => 'auto', 'relative' => true));
HTMLHelper::_('stylesheet', 'com_joomproject/joomproject/iziModal.css');

Factory::getDocument()->addStyleSheet(Uri::root() . 'media/com_joomproject/css/icons.css');


$controller = BaseController::getInstance('JPreminders');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();
?>