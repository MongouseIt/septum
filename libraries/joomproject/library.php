<?php
/**
 * @package      pkg_joomproject
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2006-2013 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();
use Joomla\CMS\Table\Table;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Form\Form;


// Make sure the cms libraries are loaded
if (!defined('JPATH_PLATFORM')) {
    require_once dirname(__FILE__) . '/../cms.php';
}

if (!defined('JP_LIBRARY')) {
    define('JP_LIBRARY', 1);
}
else {
    // Make sure we run the code below only once
    return;
}

// Register the joomproject library
JLoader::register('JPAccessHelper', JPATH_PLATFORM . '/joomproject/access/helper.php');
JLoader::register('JPApplicationHelper', JPATH_PLATFORM . '/joomproject/application/helper.php');
JLoader::register('JPControllerAdminJson', JPATH_PLATFORM . '/joomproject/controller/admin/json.php');
JLoader::register('JPControllerFormJson', JPATH_PLATFORM . '/joomproject/controller/admin/json.php');
JLoader::register('JPQueryHelper', JPATH_PLATFORM . '/joomproject/database/query/helper.php');
JLoader::register('JPDate', JPATH_PLATFORM . '/joomproject/date/date.php');
JLoader::register('JPFormHelper', JPATH_PLATFORM . '/joomproject/form/helper.php');
JLoader::register('JPImage', JPATH_PLATFORM . '/joomproject/image/image.php');
JLoader::register('JPInstallerHelper', JPATH_PLATFORM . '/joomproject/installer/helper.php');
JLoader::register('JPMenuContext', JPATH_PLATFORM . '/joomproject/menu/context.php');
JLoader::register('JPObjectHelper', JPATH_PLATFORM . '/joomproject/object/helper.php');
JLoader::register('JPToolbar', JPATH_PLATFORM . '/joomproject/toolbar/toolbar.php');
JLoader::register('JPUserHelper', JPATH_PLATFORM . '/joomproject/user/helper.php');
JLoader::register('JPVersion', JPATH_PLATFORM . '/joomproject/version/version.php');
JLoader::register('JPhtmlNav', JPATH_PLATFORM . '/joomproject/html/nav.php');
JLoader::register('JPhtmlStyle', JPATH_PLATFORM . '/joomproject/html/style.php');
JLoader::register('JPStatsHelper', JPATH_PLATFORM . '/joomproject/database/stats.php');

// register joomproject component helpers
JLoader::register('JoomprojectHelperStats', JPATH_ADMINISTRATOR . '/components/com_joomproject/helpers/stats.php');

// register joomproject component helpers
JLoader::register('JoomProjectHelperWebasset', JPATH_ADMINISTRATOR . '/components/com_joomproject/helpers/webasset.php');

// register joomproject component helpers
JLoader::register('JoomprojectHelperFrontend', JPATH_SITE . '/components/com_joomproject/helpers/joomproject.php');



// Add include paths
HTMLHelper::addIncludePath(JPATH_PLATFORM . '/joomproject/html');
BaseDatabaseModel::addIncludePath(JPATH_PLATFORM . '/joomproject/model', 'JPModel');
Table::addIncludePath(JPATH_PLATFORM . '/joomproject/table', 'JPTable');
Form::addFieldPath(JPATH_PLATFORM . '/joomproject/form/fields');
Form::addRulePath(JPATH_PLATFORM . '/joomproject/form/rules');


// Define version
if (!defined('JPVERSION')) {
    $jpversion = new JPVersion();

    define('JPVERSION', $jpversion->getShortVersion());
}
// load assets
if(class_exists('JoomprojectHelperWebasset')){
    JoomprojectHelperWebasset::init();
}




