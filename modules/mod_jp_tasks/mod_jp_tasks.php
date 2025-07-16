<?php
/**
* @package      Joomproject Tasks
*
* @author       JoomBoost
* @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Helper\ModuleHelper;

// load joomproject library
require_once JPATH_ADMINISTRATOR.'/components/com_joomproject/libraries/vendor/autoload.php';

if (!jimport('joomproject.framework')) {
    echo Text::_('MOD_JP_TASKS_JOOMPROJECT_LIB_NOT_INSTALLED');
    return;
}

if (!JPApplicationHelper::exists('com_joomproject')) {
    echo Text::_('MOD_JP_TASKS_JOOMPROJECT_NOT_INSTALLED');
    return;
}

require_once dirname(__FILE__) . '/helper.php';

$items = modJPtasksHelper::getItems($params);

// Include layout
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx',''));
require \Joomla\CMS\Helper\ModuleHelper::getLayoutPath('mod_jp_tasks', $params->get('layout', 'default'));
