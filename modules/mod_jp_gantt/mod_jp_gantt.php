<?php
/**
* @package      mod_jp_gantt
*
* @author       JoomBoost
* @copyright    Copyright (C) 2006-2018 JoomBoost. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Helper\ModuleHelper;


// load joomproject library
require_once JPATH_ADMINISTRATOR.'/components/com_joomproject/libraries/vendor/autoload.php';

JLoader::register('JoomProjectHelperWebasset', JPATH_ADMINISTRATOR . '/components/com_joomproject/helpers/webasset.php');

JoomProjectHelperWebasset::init();


if (!jimport('joomproject.framework')) {
    echo Text::_('MOD_JP_GANTT_JOOMPROJECT_LIB_NOT_INSTALLED');
    return;
}

if (!JPApplicationHelper::exists('com_joomproject')) {
    echo Text::_('MOD_JP_GANTT_JOOMPROJECT_NOT_INSTALLED');
    return;
}

// Do nothing if the module is set to hide the overview chart
if ((int) $params->get('show_overview', 1) == 0 && JPApplicationHelper::getActiveProjectId() == 0) {
    return '';
}

// Get the helper class
require_once dirname(__FILE__) . '/helper.php';

modJPganttHelper::init($params, $module->id);
$items = modJPganttHelper::getItems();

// Include layout
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx',''));
require ModuleHelper::getLayoutPath('mod_jp_gantt', $params->get('layout', 'default'));
