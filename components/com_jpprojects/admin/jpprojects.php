<?php
/**
 * @package      Joomproject
 * @subpackage   Projects
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Component\ComponentHelper;

// Access check
if (!Factory::getApplication()->getIdentity()->authorise('core.manage', 'com_jpprojects')) {
	Factory::getApplication()->enqueueMessage( Text::_('JERROR_ALERTNOAUTHOR'),'warning');
    return false;
}

// load the new joomproject library
require_once JPATH_ADMINISTRATOR.'/components/com_joomproject/libraries/vendor/autoload.php';

jimport('joomproject.framework');
HTMLHelper::_('jquery.framework');

// Register classes to autoload
JLoader::register('JPprojectsHelper', JPATH_ADMINISTRATOR . '/components/com_jpprojects/helpers/jpprojects.php');
JLoader::register('JoomprojectHelperAccess',JPATH_SITE.'/components/com_joomproject/helpers/access.php');
JLoader::register('JoomProjectGalleryManager', JPATH_ADMINISTRATOR . '/components/com_jpprojects/helpers/gallerymanager.php');
JLoader::register('Control', JPATH_ADMINISTRATOR . '/components/com_jpprojects/helpers/control.php');
JLoader::register('JoomprojectSmartTag', JPATH_ADMINISTRATOR . '/components/com_jpprojects/helpers/SmartTags/SmartTag.php');
JLoader::register('JoomprojectSmartTags', JPATH_ADMINISTRATOR . '/components/com_jpprojects/helpers/SmartTags/SmartTags.php');
JLoader::register('JoomprojectFile', JPATH_ADMINISTRATOR . '/components/com_jpprojects/helpers/file.php');
JLoader::register('JoomprojectImage', JPATH_ADMINISTRATOR . '/components/com_jpprojects/helpers/image.php');
JLoader::register('JoomprojectMimes', JPATH_ADMINISTRATOR . '/components/com_jpprojects/helpers/mimes.php');
JLoader::register('JoomprojectFunctions', JPATH_ADMINISTRATOR . '/components/com_jpprojects/helpers/functions.php');
JLoader::register('JoomprojectCache', JPATH_ADMINISTRATOR . '/components/com_jpprojects/helpers/cache.php');
JLoader::register('JoomprojectCacheManager', JPATH_ADMINISTRATOR . '/components/com_jpprojects/helpers/cachemanager.php');
JLoader::register('JoomprojectWebClient', JPATH_ADMINISTRATOR . '/components/com_jpprojects/helpers/webclient.php');
JLoader::register('JoomprojectFactory', JPATH_ADMINISTRATOR . '/components/com_jpprojects/helpers/factory.php');
JLoader::register('JoomprojectVisitorToken', JPATH_ADMINISTRATOR . '/components/com_jpprojects/helpers/visitortoken.php');
JLoader::register('JoomprojectGallery', JPATH_ADMINISTRATOR . '/components/com_jpprojects/helpers/gallery.php');
JLoader::register('JoomprojectCountries', JPATH_ADMINISTRATOR . '/components/com_jpprojects/helpers/countries.php');

Text::script('COM_JOOMPROJECT_GALLERY_MANAGER_CONFIRM_REGENERATE_IMAGES');
Text::script('COM_JOOMPROJECT_GALLERY_MANAGER_CONFIRM_DELETE_ALL_SELECTED');
Text::script('COM_JOOMPROJECT_GALLERY_MANAGER_CONFIRM_DELETE_ALL');
Text::script('COM_JOOMPROJECT_GALLERY_MANAGER_CONFIRM_DELETE');
Text::script('COM_JOOMPROJECT_GALLERY_MANAGER_FILE_MISSING');
Text::script('COM_JOOMPROJECT_GALLERY_MANAGER_REACHED_FILES_LIMIT');
Text::script('COM_JOOMPROJECT_GALLERY_GENERATE_IMAGE_DESC_TO_ALL_IMAGES_CONFIRM');

LayoutHelper::$defaultBasePath = JPATH_ROOT.'/administrator/components/com_joomproject/layouts';
// force bootstrap load from CDN
$globalParams = ComponentHelper::getComponent('com_joomproject')->getParams();

$jinput = Factory::getApplication()->input;

HTMLHelper::_('script', 'com_joomproject/joomproject/iziModal.min.js', array('version' => 'auto', 'relative' => true));
HTMLHelper::_('stylesheet', 'com_joomproject/joomproject/iziModal.css');




$controller = BaseController::getInstance('JPprojects');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();
