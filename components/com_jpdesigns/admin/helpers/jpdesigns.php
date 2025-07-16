<?php
/**
 * @package      Joomproject Pro
 * @subpackage   Designs
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Path;

jimport('joomproject.framework');


class JPdesignsHelper
{
    /**
     * The component name
     *
     * @var    string
     */
    public static $extension = 'com_jpdesigns';

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
     * @param     int        The item id
     *
     * @return    jobject
     */
    public static function getActions($id = 0, $album = 0)
    {
        $user   = Factory::getApplication()->getIdentity();
        $result = new CMSObject;

        if (!empty($id)) {
            $asset = 'com_jpdesigns.design.' . (int) $id;
        }
        elseif (!empty($album)) {
            $asset = 'com_jpdesigns.album.' . (int) $album;
        }
        else {
            if (version_compare(JPVERSION, '4.2', 'ge')) {
                $pid   = JPApplicationHelper::getActiveProjectId();
                $asset = (empty($pid) ? self::$extension : 'com_jpdesigns.project.' . $pid);
            }
            else {
                $asset = self::$extension;
            }
        }

        $actions = array(
            'core.admin', 'core.manage',
            'core.create', 'core.edit',
            'core.edit.own', 'core.edit.state',
            'core.delete', 'core.download', 'core.approve'
        );

        $custom = array('core.download', 'core.approve');

        foreach ($actions as $action)
        {
            if (in_array($action, $custom)) {
                $result->set($action, ($user->authorise($action, $asset) || $result->get('core.admin')));
            }
            else {
                $result->set($action, $user->authorise($action, $asset));
            }
        }

        return $result;
    }


    /**
     * Gets a list of actions that can be performed on an album.
     *
     * @param     int        The item id
     *
     * @return    jobject
     */
    public static function getAlbumActions($id = 0)
    {
        $user   = Factory::getApplication()->getIdentity();
        $result = new CMSObject;

        if (empty($id)) {
            if (version_compare(JPVERSION, '4.2', 'ge')) {
                $pid   = JPApplicationHelper::getActiveProjectId();
                $asset = (empty($pid) ? self::$extension : 'com_jpdesigns.project.' . $pid);
            }
            else {
                $asset = self::$extension;
            }
        }
        else {
            $asset = 'com_jpdesigns.album.' . (int) $id;
        }

        $actions = array(
            'core.admin', 'core.manage',
            'core.create', 'core.edit',
            'core.edit.own', 'core.edit.state',
            'core.delete', 'core.download', 'core.approve'
        );

        $custom = array('core.download', 'core.approve');

        foreach ($actions as $action)
        {
            if (in_array($action, $custom)) {
                $result->set($action, ($user->authorise($action, $asset) || $result->get('core.admin')));
            }
            else {
                $result->set($action, $user->authorise($action, $asset));
            }
        }

        return $result;
    }


    /**
     * Gets a list of actions that can be performed on a revision.
     *
     * @param     integer    $id         The item id
     * @param     integer    $parent      The parent id
     *
     * @return    jobject
     */
    public static function getRevisionActions($id = 0, $parent = 0)
    {
        $user   = Factory::getApplication()->getIdentity();
        $result = new CMSObject;

        if (!empty($id)) {
            $asset = 'com_jpdesigns.revision.' . (int) $id;
        }
        elseif (!empty($parent)) {
            $asset = 'com_jpdesigns.design.' . (int) $parent;
        }
        else {
            if (version_compare(JPVERSION, '4.2', 'ge')) {
                $pid   = JPApplicationHelper::getActiveProjectId();
                $asset = (empty($pid) ? self::$extension : 'com_jpdesigns.project.' . $pid);
            }
            else {
                $asset = self::$extension;
            }
        }

        $actions = array(
            'core.admin', 'core.manage',
            'core.create', 'core.edit',
            'core.edit.own', 'core.edit.state',
            'core.delete', 'core.download', 'core.approve'
        );

        $custom = array('core.download', 'core.approve');

        foreach ($actions as $action)
        {
            if (in_array($action, $custom)) {
                $result->set($action, ($user->authorise($action, $asset) || $result->get('core.admin')));
            }
            else {
                $result->set($action, $user->authorise($action, $asset));
            }
        }

        return $result;
    }


    public static function getBaseUrl($project = NULL){
        jimport('joomla.filesystem.path');

        $params = ComponentHelper::GetParams('com_jpdesigns');

        $base = Uri::root(true) . '/';
        $dest = $params->get('design_basepath', '/images/com_joomproject/designs/');

        $fchar = substr($dest, 0, 1);
        $lchar = substr($dest, -1, 1);

        if ($fchar == '/' || $fchar == '\\') {
            $dest = substr($dest, 1);
        }

        if ($lchar == '/' || $lchar == '\\') {
            $dest = substr($dest, 0, -1);
        }

        if (is_numeric($project)) {
            $dest .= '/project_' . (int) $project;
        }

        $basepath = Path::clean($base . $dest);



        $basepath = str_replace('\\','/',$basepath);

        return $basepath;
    }


    /**
     * Method to get the base upload path for a design
     *
     * @param     int       $project     Optional project id
     *
     * @return    string    $basepath    The upload directory
     */
    public static function getBasePath($project = NULL)
    {
        jimport('joomla.filesystem.path');

        $params = ComponentHelper::GetParams('com_jpdesigns');

        $base = JPATH_SITE . '/';
        $dest = $params->get('design_basepath', '/images/com_joomproject/designs/');

        $fchar = substr($dest, 0, 1);
        $lchar = substr($dest, -1, 1);

        if ($fchar == '/' || $fchar == '\\') {
            $dest = substr($dest, 1);
        }

        if ($lchar == '/' || $lchar == '\\') {
            $dest = substr($dest, 0, -1);
        }

        if (is_numeric($project)) {
            $dest .= '/project_' . (int) $project;
        }

        $basepath = Path::clean($base . $dest);

        return $basepath;
    }


    /**
     * Method for translating an upload error code into human readable format
     *
     * @param     integer    $num     The error code
     * @param     string     $name    The name of the file
     *
     * @return    string     $msg     The error message
     */
    public static function getFileErrorMsg($num, $name)
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
     * Method to get the thumbnail links of an item
     *
     * @param     object    $item    The design or revision object
     * @param     string    $type    The item type (design or revision)
     *
     * @return    void
     */
    public static function getThumbnails(&$item, $type = 'design')
    {
        static $preview_img = null;
        static $full_img    = null;
        static $cover_img   = null;

        $global_params = ComponentHelper::getParams('com_jpdesigns', true);

        // Set preview image class instance
        if (is_null($preview_img)) {
            $options = array();
            $options['crop']    = true;
            $options['quality'] = 60;
            $options['size']    = $global_params->get('img_preview_size', '300x200');

            $preview_img = BaseDatabaseModel::getInstance('Image', 'JPdesignsModel', $options);
        }

        // Set full image class instance
        if (is_null($full_img)) {
            $options = array();
            $options['crop']    = true;
            $options['quality'] = 80;
            $options['size']    = $global_params->get('img_full_size', '1280x720');

            $full_img = BaseDatabaseModel::getInstance('Image', 'JPdesignsModel', $options);
        }

        // Set cover image class instance
        if (is_null($cover_img)) {
            $options = array();
            $options['crop']    = true;
            $options['quality'] = 75;
            $options['size']    = $global_params->get('img_cover_size', '770x300');

            $cover_img = BaseDatabaseModel::getInstance('Image', 'JPdesignsModel', $options);
        }

        // Try to find the media file
        $uploadpath = JPdesignsHelper::getBasePath($item->project_id);

        $item->file_exists = File::exists($uploadpath . '/' . $item->file_name);

        if ($item->file_exists) {
            // Find out whether the image is cached or not
            $preview_img->setCacheId($type, $item->project_id, $item->id);
            $item->preview_cached = $preview_img->isCached();

            $full_img->setCacheId($type, $item->project_id, $item->id);
            $item->full_cached = $full_img->isCached();

            $cover_img->setCacheId($type, $item->project_id, $item->id);
            $item->cover_cached = $cover_img->isCached();

            if ($type == 'design') {
                $link = JPdesignsHelperRoute::getDesignRoute($item->slug, $item->project_slug, $item->album_slug, '0:original');

                $item->preview_source = ($item->preview_cached ? $preview_img->getCachedURL() : Route::_($link . '&tmpl=component&layout=preview&format=raw'));
                $item->full_source    = ($item->full_cached    ? $full_img->getCachedURL()    : Route::_($link . '&tmpl=component&layout=full&format=raw'));
                $item->cover_source   = ($item->cover_cached   ? $cover_img->getCachedURL()   : Route::_($link . '&tmpl=component&layout=cover&format=raw'));
            }
            else {
                $link = 'index.php?option=com_jpdesigns&view=revision&id=' . $item->slug;

                $item->preview_source = ($item->preview_cached ? $preview_img->getCachedURL() : Route::_($link . '&tmpl=component&layout=preview&format=raw'));
                $item->full_source    = ($item->full_cached    ? $full_img->getCachedURL()    : Route::_($link . '&tmpl=component&layout=full&format=raw'));
            }
        }
        else {
            list($p_w, $p_h) = explode('x', $global_params->get('img_preview_size', '400x300'), 2);
            list($c_w, $c_h) = explode('x', $global_params->get('img_cover_size', '770x300'), 2);

            $item->preview_source = HTMLHelper::_('image', 'com_jpdesigns/preview-missing-' . intval($p_w) . 'x' . intval($p_h) . '.jpg', 'placeholder', null, true, true);
            $item->full_source    = HTMLHelper::_('image', 'com_jpdesigns/full-missing.jpg', 'placeholder', null, true, true);
            $item->cover_source   = HTMLHelper::_('image', 'com_jpdesigns/cover-missing-' . intval($p_w) . 'x' . intval($p_h) . '.jpg', 'placeholder', null, true, true);
        }
    }
}
