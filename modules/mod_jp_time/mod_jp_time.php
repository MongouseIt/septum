<?php
/**
* @package      Joomproject Timesheet Module
*
* @author       ANGEK DESIGN (Kon Angelopoulos)
* @copyright    Copyright (C) 2013 - 2015 ANGEK DESIGN. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Helper\ModuleHelper;

// load joomproject library
require_once JPATH_ADMINISTRATOR.'/components/com_joomproject/libraries/vendor/autoload.php';

LayoutHelper::$defaultBasePath = JPATH_ADMINISTRATOR.'/components/com_joomproject/layouts';

if (!jimport('joomproject.framework')) {
    echo Text::_('MOD_JP_TIME_JOOMPROJECT_LIB_NOT_INSTALLED');
    return;
}

if (!JPApplicationHelper::exists('com_joomproject')) {
    echo Text::_('MOD_JP_TIME_JOOMPROJECT_NOT_INSTALLED');
    return;
}

require_once dirname(__FILE__) . '/helper.php';
//Factory::getDocument()->addScript(Uri::base().'media/com_joomproject/joomproject/js/joomproject.js');
$doc = Factory::getDocument();


// Include layout
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx',''));
require \Joomla\CMS\Helper\ModuleHelper::getLayoutPath('mod_jp_time', $params->get('layout', 'default'));
