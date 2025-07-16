<?php
/**
 * @package      pkg_jpactivities
 * @subpackage   com_jpactivities
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2018 JoomBoost.com. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Controller\BaseController;


// load joomproject library
require_once JPATH_ADMINISTRATOR.'/components/com_joomproject/libraries/vendor/autoload.php';

HTMLHelper::_('jquery.framework');
require_once JPATH_SITE . '/components/com_jpactivities/helpers/route.php';
BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jpactivities/models', 'JPactivitiesModel');
HTMLHelper::_('stylesheet', 'com_jpactivities/site.css');

$controller = BaseController::getInstance('JPactivities');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();
