<?php
/**
 * @package      mod_jp_taskcounter
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2015 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Helper\ModuleHelper;

// load joomproject library
require_once JPATH_ADMINISTRATOR.'/components/com_joomproject/libraries/vendor/autoload.php';

// Check if Joomproject is properly installed
if (!jimport('joomproject.framework')) {
    echo Text::_('MOD_JP_TASKS_JOOMPROJECT_LIB_NOT_INSTALLED');
    return;
}

if (!JPApplicationHelper::exists('com_joomproject')) {
    echo Text::_('MOD_JP_TASKS_JOOMPROJECT_NOT_INSTALLED');
    return;
}


// Include helper file
require_once dirname(__FILE__) . '/helper.php';

// Get task count
$count = modJPtaskcounterHelper::getCount($params);

// Get link to task list
$param_link = $params->get('link', "");

if ($param_link !== "") {
    $link = modJPtaskcounterHelper::getLink($params);
}
else {
    $link = "";
}

// Include layout
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx',''));
require ModuleHelper::getLayoutPath('mod_jp_taskcounter', $params->get('layout', 'default'));
