<?php
/**
* @package      Joomproject Dashboard Buttons
*
* @author       JoomBoost
* @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;


// load joomproject library
require_once JPATH_ADMINISTRATOR.'/components/com_joomproject/libraries/vendor/autoload.php';

$option = Factory::getApplication()->input->get('option');
$view   = Factory::getApplication()->input->get('view');

// Disable this module on the "user" view if it is positioned on the dashboard
if (stripos($module->position, 'jp-dashboard') !== false && $option == 'com_joomproject' && $view == 'user') {
    return '';
}

// Stop if joomproject is not installed
if (!file_exists(JPATH_SITE . '/components/com_joomproject/joomproject.php')) {
    echo Text::_('MOD_JP_DASH_BUTTONS_JOOMPROJECT_NOT_INSTALLED');
}
else {
    // Include dependencies
    jimport('joomproject.library');

    require_once dirname(__FILE__) . '/helper.php';

    // Get buttons
    $buttons = modJPdashButtonsHelper::getButtons();

    // Include layout
    $moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx',''));
    require \Joomla\CMS\Helper\ModuleHelper::getLayoutPath('mod_jp_dash_buttons', $params->get('layout', 'default'));
}
