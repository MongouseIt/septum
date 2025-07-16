<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jprepo
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;


jimport('joomla.filesystem.path');
jimport('joomproject.framework');

abstract class JPrepoHelper
{
    /**
     * The component name
     *
     * @var    string
     */
    public static $extension = 'com_jprepo';

    /**
     * Indicates whether this component uses a project asset or not
     *
     * @var    boolean
     */
    public static $project_asset = true;


    /**
     * Configure the Linkbar.
     *
     * @param     string    $view    The name of the active view.
     *
     * @return    void
     */
    public static function addSubmenu($view)
    {
        JoomprojectHelper::addSubmenu($view);
    }


    /**
     * Gets a list of actions that can be performed.
     *
     * @param     string     $name    The asset name
     * @param     int        $id      The item id
     *
     * @return    jobject
     */
    public static function getActions($name = 'directory', $id = 0)
    {
        $user   = Factory::getApplication()->getIdentity();
        $result = new CMSObject;

        if (empty($id)) {
            $pid   = JPApplicationHelper::getActiveProjectId();
            $asset = (empty($pid) ? self::$extension : 'com_jprepo.project.' . $pid);
        }
        else {
            $asset = self::$extension . '.' . $name . '.' . (int) $id;
        }

        $actions = array(
            'core.admin', 'core.manage',
            'core.create', 'core.edit',
            'core.edit.own', 'core.edit.state',
            'core.delete'
        );

        foreach ($actions as $action)
        {
            $result->set($action, $user->authorise($action, $asset));
        }

        return $result;
    }


    /**
     * Method to get the base upload path for a project
     *
     * @param     int       $project     Optional project id
     *
     * @return    string    $basepath    The upload directory
     */
    public static function getBasePath($project = null)
    {
        static $cache = array();

        $project = (int) $project;

        // Check the cache
        if (isset($cache[$project])) return $cache[$project];

        $params = ComponentHelper::GetParams('com_jprepo');
        $dest   = $params->get('repo_basepath', '/media/com_joomproject/repo/');
        $base   = JPATH_SITE . '/';

        $fchar = substr($dest, 0, 1);
        $lchar = substr($dest, -1, 1);

        if ($fchar == '/' || $fchar == '\\') $dest = substr($dest, 1);
        if ($lchar == '/' || $lchar == '\\') $dest = substr($dest, 0, -1);

        if ($project) {
            $db    = Factory::getDbo();
            $query = $db->getQuery(true);

            $query->select('path')
                  ->from('#__jp_repo_dirs')
                  ->where('project_id = ' . $project)
                  ->where('parent_id = 1');

            $db->setQuery($query);
            $path = $db->loadResult();

            if (empty($path)) {
                $query->clear()
                      ->select('alias')
                      ->from('#__jp_projects')
                      ->where('id = ' . $project);

                $db->setQuery($query);
                $path = $db->loadResult();
            }

            if ($path) {
                $dest .= '/' . $path;
            }
        }

        $cache[$project] = Path::clean($base . $dest);

        return $cache[$project];
    }


    /**
     * Method to get the pyhsical path location of a file
     *
     * @param     string     $name    The file name
     * @param     integer    $dir     The directory id in which the file is stored
     *
     * @return    string              The path
     */
    public static function getFilePath($name, $dir)
    {
        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select('project_id, path')
              ->from('#__jp_repo_dirs')
              ->where('id = ' . (int) $dir);

        $db->setQuery($query);
        $dir = $db->loadObject();

        if (empty($dir)) return '';

        $base = JPrepoHelper::getBasePath();
        $file = $base . '/' . $dir->path . '/' . $name;

        // Look in the directory
        if (\Joomla\CMS\Filesystem\File::exists($file)) {
            return $base . '/' . $dir->path;
        }

        // Look in the base dir (4.0 backwards compat)
        $file = $base . '/' . $dir->project_id . '/' . $name;

        if (\Joomla\CMS\Filesystem\File::exists($file)) {
            return $base . '/' . $dir->project_id;
        }

        // Look in the base dir (3.0 backwards compat)
        $file = $base . '/project_' . $dir->project_id . '/' . $name;

        if (\Joomla\CMS\Filesystem\File::exists($file)) {
            return $base . '/project_' . $dir->project_id;
        }

        return '';
    }


    /**
     * Method for translating an upload error code into human readable format
     *
     * @param     integer    $num     The error code
     * @param     string     $name    The name of the file
     *
     * @return    string     $msg     The error message
     */
    public static function getFileErrorMsg($num, $name = '')
    {
        $size_limit = ini_get('upload_max_filesize');
        $name = '"' . htmlspecialchars($name, ENT_COMPAT, 'UTF-8') . '"';

        switch ($num)
        {
            case 1:
                $msg = Text::sprintf('COM_JOOMPROJECT_WARNING_FILE_UPLOAD_ERROR_' . $num, $name, $size_limit);
                break;

            case 2:
                $msg = Text::sprintf('COM_JOOMPROJECT_WARNING_FILE_UPLOAD_ERROR_' . $num, $name);
                break;

            case 3:
            case 7:
            case 8:
                $msg = Text::sprintf('COM_JOOMPROJECT_WARNING_FILE_UPLOAD_ERROR_' . $num, $name);
                break;

            case 4:
            case 6:
                $msg = Text::_('COM_JOOMPROJECT_WARNING_FILE_UPLOAD_ERROR_' . $num);
                break;

            default:
                $msg = Text::sprintf('COM_JOOMPROJECT_WARNING_FILE_UPLOAD_ERROR_UNKNOWN' . $num, $name, $num);
                break;
        }

        return $msg;
    }


    /**
     * Method to get the max upload size from the php config
     *
     * @return    integer    $size    Max size in bytes
     */
    public static function getMaxUploadSize()
    {
        $val  = strtolower(trim(ini_get('upload_max_filesize')));
        $char = substr($val, -1);
        $size = (int) substr($val, 0, -1);

        switch ($char)
        {
            case 'g': $size *= 1024;
            case 'm': $size *= 1024;
            case 'k': $size *= 1024;
        }

        return $size;
    }


    /**
     * Method to get the max post size from the php config
     *
     * @return    integer    $size    Max size in bytes
     */
    public static function getMaxPostSize()
    {
        $val  = strtolower(trim(ini_get('post_max_size')));
        $char = substr($val, -1);
        $size = (int) substr($val, 0, -1);

        switch ($char)
        {
            case 'g': $size *= 1024;
            case 'm': $size *= 1024;
            case 'k': $size *= 1024;
        }

        return $size;
    }


    /**
     * Method to get the project id of a directory
     *
     * @param     integer    $id    The directory id
     *
     * @return    integer           The project id
     */
    public static function getProjectFromDir($id)
    {
        static $cache = array();

        // Check cache
        if (isset($cache[$id])) return $cache[$id];

        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select('project_id')
              ->from('#__jp_repo_dirs')
              ->where('id = ' . (int) $id);

        $db->setQuery($query);
        $cache[$id] = (int) $db->loadResult();

        return $cache[$id];
    }


    /**
     * Method to get the allowed file extensions for upload
     *
     * @return    array    $allowed    The allowed file extensions
     */
    public static function getAllowedFileExtensions()
    {
        $config      = ComponentHelper::getParams('com_jprepo');
        $allowed_str = trim($config->get('filter_ext',''));
        $allowed     = array();

        if (empty($allowed_str)) return $allowed;

        $extensions = explode(',', $allowed_str);

        foreach ($extensions AS $ext)
        {
            $clean_ext = strtolower(trim($ext));

            if (strpos($clean_ext, '.') === 0) {
                $clean_ext = substr($clean_ext, 1);
            }

            $allowed[] = $clean_ext;
        }

        sort($allowed);

        return $allowed;
    }

    /**
     * Returns a valid section for articles. If it is not valid then null
     * is returned.
     *
     * @param   string  $section  The section to get the mapping for
     *
     * @return  string|null  The new section
     *
     * @since   3.7.0
     */
    public static function validateSection($section)
    {
        if (Factory::getApplication()->isClient('site'))
        {

            if($section == 'fileform'){
                $section = 'file';
            }elseif($section == 'directoryform'){
                $section = 'directory';
            }


        }
        return $section;
    }

    /**
     * Returns valid contexts
     *
     * @return  array
     *
     * @since   3.7.0
     */
    public static function getContexts()
    {
        Factory::getLanguage()->load('com_jprepo', JPATH_ADMINISTRATOR);

        $contexts = array(
            'com_jprepo.file'    => Text::_('COM_JPREPO_FILE')
        );

        return $contexts;
    }
}
