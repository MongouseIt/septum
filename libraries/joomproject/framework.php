<?php
/**
 * @package      pkg_joomproject
 * @subpackage   lib_joomproject
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2006-2013 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Form\Form;



// Make sure the cms libraries are loaded
if (!defined('JPATH_PLATFORM')) {
    require_once dirname(__FILE__) . '/../cms.php';
}

if (!defined('JP_FRAMEWORK')) {
    define('JP_FRAMEWORK', 1);
}
else {
    // Make sure we run the code below only once
    return;
}

define('JPPATH_CACHE',JPATH_ROOT.'/cache');

jimport('joomla.filesystem.folder');


// Include the library
require_once dirname(__FILE__) . '/library.php';


// Get the list of Joomproject components, as well as the currently active
$components = JPApplicationHelper::getComponents();
$current    = Factory::getApplication()->input->get('option');
$lang       = Factory::getLanguage();
$is_site    = Factory::getApplication()->isClient('site');

// Go through each component
foreach ($components AS $component)
{
    $site_path  = JPATH_SITE . '/components/' . $component->element;
    $admin_path = JPATH_ADMINISTRATOR . '/components/' . $component->element;

    $com_name = str_replace('com_', '', $component->element);

    if (substr($com_name, 0, 2) == 'jp') {
        $com_name = 'JP' . substr($com_name, 2);
    }
    else {
        $com_name = ucfirst($com_name);
    }

    // Begin loading language files
    if ($component->element != $current) {
        $lang->load($component->element);
    }

    if ($is_site) {
        // Also load the backend language when in frontend
        $lang->load($component->element, JPATH_ADMINISTRATOR);

        // Load the language from the component frontend directory if it exists
        if (Folder::exists($site_path . '/language')) {
            $lang->load($component->element, $site_path);
        }
    }

    // Load the language from the component backend directory if it exists
    if (Folder::exists($admin_path . '/language')) {
        $lang->load($component->element, $admin_path);
    }

    // Register backend helper class
    if (File::exists($admin_path . '/helpers/' . strtolower($com_name) . '.php')) {
        JLoader::register($com_name . 'Helper', $admin_path . '/helpers/' . strtolower($com_name) . '.php');
    }

    // Register the routing helper class
    if ($is_site) {
        if (File::exists($site_path . '/helpers/route.php')) {
            JLoader::register($com_name . 'HelperRoute', $site_path . '/helpers/route.php');
        }
    }

    if ($component->element != $current || $is_site) {
        // Register backend table classes
        if (Folder::exists($admin_path . '/tables')) {
            Table::addIncludePath($admin_path . '/tables');
        }

        // Register backend model classes
        if (Folder::exists($admin_path . '/models')) {
            if ($is_site && Folder::exists($site_path . '/models')) {
                // Give frontend models a priority over admin models
                BaseDatabaseModel::addIncludePath($admin_path . '/models', $com_name . 'Model');
                BaseDatabaseModel::addIncludePath($site_path . '/models', $com_name . 'Model');
            }
            else {
                BaseDatabaseModel::addIncludePath($admin_path . '/models', $com_name . 'Model');
            }
        }

        // Register backend html classes
        if (Folder::exists($admin_path . '/helpers/html')) {
            HTMLHelper::addIncludePath($admin_path . '/helpers/html');
        }

        // Register backend forms
        if (Folder::exists($admin_path . '/models/forms')) {
            Form::addFormPath($admin_path . '/models/forms');
        }

        // Register backend form fields
        if (Folder::exists($admin_path . '/models/fields')) {
            Form::addFieldPath($admin_path . '/models/fields');
        }

        // Register backend form rules
        if (Folder::exists($admin_path . '/models/rules')) {
            Form::addRulePath($admin_path . '/models/rules');
        }
    }

    if ($component->element != $current && $is_site) {
        // Register frontend model classes
        if (Folder::exists($site_path . '/models')) {
            BaseDatabaseModel::addIncludePath($site_path . '/models', $com_name . 'Model');
        }

        // Register frontend html classes
        if (Folder::exists($site_path . '/helpers/html')) {
            HTMLHelper::addIncludePath($site_path . '/helpers/html');
        }
    }
}

