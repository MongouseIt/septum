<?php
/**
 * @package      pkg_jpactivities
 * @subpackage   mod_jpactivities_site
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2013 JoomBoost.com. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Helper\ModuleHelper;


JLoader::register('JoomprojectHelperRoute',JPATH_SITE.'/components/com_joomproject/helpers/route.php');

// load joomproject library
require_once JPATH_ADMINISTRATOR.'/components/com_joomproject/libraries/vendor/autoload.php';

HTMLHelper::_('jquery.framework');

// Include the helper class.
jimport('joomla.application.module.helper');
require_once JPATH_SITE . '/modules/mod_jpactivities_site/helper.php';

LayoutHelper::$defaultBasePath = __DIR__.'/layouts';




$globalParams = ComponentHelper::getComponent('com_jpactivities');

HTMLHelper::_('stylesheet', 'com_jpactivities/site.css');
// Prepare model config
$config = array('ignore_request' => true);

if (is_numeric($params->get('group_activity'))) {
    $config['group_activity'] = (int) $params->get('group_activity');
}

// Get module data.
$model = modJPactivitiesSiteHelper::getModel($config);
$data  = modJPactivitiesSiteHelper::getItems($params);

// Render the module
require ModuleHelper::getLayoutPath('mod_jpactivities_site', $params->get('layout', 'default'));
