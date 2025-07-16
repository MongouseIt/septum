<?php
/**
* @package      mod_jp_calendar
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

// Get the helper class
require_once dirname(__FILE__) . '/helper.php';

modJPcalendarHelper::init($params, $module->id);
$items = modJPcalendarHelper::getItems();

$months = array(
    Text::_('JANUARY'), Text::_('FEBRUARY'), Text::_('MARCH'),
    Text::_('APRIL'), Text::_('MAY'), Text::_('JUNE'),
    Text::_('JULY'), Text::_('AUGUST'), Text::_('SEPTEMBER'),
    Text::_('OCTOBER'), Text::_('NOVEMBER'), Text::_('DECEMBER')
);

$days = array(
    Text::_('SUNDAY'), Text::_('MONDAY'), Text::_('TUESDAY'),
    Text::_('WEDNESDAY'), Text::_('THURSDAY'), Text::_('FRIDAY'),
    Text::_('SATURDAY')
);

$days_short = array(
    Text::_('SUN'), Text::_('MON'), Text::_('TUE'),
    Text::_('WED'), Text::_('THU'), Text::_('FRI'),
    Text::_('SAT')
);



// Include layout
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx',''));
require ModuleHelper::getLayoutPath('mod_jp_calendar', $params->get('layout', 'default'));
