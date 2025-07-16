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

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Path;
/**
 * Methods supporting a list of importable files.
 *
 */
class JPdesignsModelImport extends ListModel
{
    /**
     * Constructor
     *
     * @param    array          An optional associative array of configuration settings.
     * @see      jcontroller
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
    }


    public function getItems()
    {
        $project     = (int) $this->getState('filter.project');
        $base_path   = JPdesignsHelper::getBasePath($project);
        $import_path = Path::clean($base_path . '/_import');

        if ($project <= 0 || Folder::exists($import_path) == false) {
            return array();
        }

        $i     = 0;
        $items = array();
        $files = (array) Folder::files($import_path);

        // First pass: Get uncategorised new designs
        foreach ($files AS $file)
        {
            $ext = strtolower(File::getExt($file));

            if (!in_array($ext, array('jpeg', 'jpg', 'png', 'gif'))) {
                continue;
            }

            $info = getimagesize($import_path . '/' . $file);

            if (!$info) {
                continue;
            }

            if (!in_array($info[2], array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG))) {
                continue;
            }

            $item = new stdClass();

            $item->file_name   = $file;
            $item->file_size   = round(filesize($import_path . '/' . $file) / 1024);
            $item->file_ext    = $ext;
            $item->file_source = '_import';

            $item->title = $file;
            $item->size  = $info[0] . 'x' . $info[1];
            $item->project_id = $project;
            $item->parent_id  = 0;
            $item->album_id   = 0;

            $item->album_title  = '';
            $item->design_title = '';

            $items[$i] = $item;

            $i++;
        }

        $sub_folders = (array) Folder::folders($import_path);

        // Second pass: Find categorised new designs and design revisions
        foreach ($sub_folders AS $folder)
        {
            $folder_length    = strlen($folder);
            $is_album_folder  = false;
            $is_design_folder = false;
            $album_id         = 0;
            $design_id        = 0;
            $album_title      = '';
            $design_title     = '';

            if ($folder_length < 6) {
                continue;
            }

            if (substr($folder, 0, 7) == 'design_') {
                $is_design_folder = true;
            }
            elseif ($folder_length > 6) {
                if (substr($folder, 0, 6) == 'album_') {
                    $is_album_folder = true;
                }
            }

            if (!$is_design_folder && !$is_album_folder) {
                // Folder is neither a design or album dir. skip it
                continue;
            }

            list($type, $id) = explode('_', $folder, 2);
            $id = (int) $id;

            // Find the album title
            if ($is_album_folder) {
                $album_title = $this->getAlbumTitle($id);
                $album_id    = $id;

                if (!$album_title) {
                    continue;
                }
            }

            // Find the design title
            if ($is_design_folder) {
                $design_title = $this->getDesignTitle($id, $project);
                $design_id    = $id;

                if (!$design_title) {
                    continue;
                }
            }

            // Get the actual files in the folder
            $files = (array) Folder::files($import_path . '/' . $folder);

            foreach ($files AS $file)
            {
                $ext = strtolower(File::getExt($file));

                if (!in_array($ext, array('jpeg', 'jpg', 'png', 'gif'))) {
                    continue;
                }

                $info = getimagesize($import_path . '/' . $folder . '/' . $file);

                if (!$info) {
                    continue;
                }

                if (!in_array($info[2], array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG))) {
                    continue;
                }

                $item = new stdClass();

                $item->file_name   = $file;
                $item->file_size   = round(filesize($import_path . '/' . $folder . '/' . $file) / 1024);
                $item->file_ext    = $ext;
                $item->file_source = '_import/' . $folder;

                $item->title = $file;
                $item->size  = $info[0] . 'x' . $info[1];
                $item->project_id = $project;
                $item->parent_id  = $design_id;
                $item->album_id   = $album_id;

                $item->album_title  = $album_title;
                $item->design_title = $design_title;

                $items[$i] = $item;

                $i++;
            }
        }

        return $items;
    }


    public function getDesigns()
    {
        $project = (int) $this->getState('filter.project');

        if (!$project) {
            return array();
        }

        $db    = $this->getDbo();
        $query = $db->getQuery(true);
        $user  = Factory::getApplication()->getIdentity();

        $query->select('a.id, a.title')
              ->from('#__jp_designs AS a');

        // Get the category title
        $query->select('c.title AS album_title')
              ->join('LEFT', '#__jp_design_albums AS c ON c.id = a.album_id');

        // Implement View Level Access
        if (!$user->authorise('core.admin')) {
            $levels = implode(',', $user->getAuthorisedViewLevels());
            $query->where('a.access IN (' . $levels . ')');
        }

        $query->group('a.id');
        $query->order('album_title, a.title ASC');

        $db->setQuery($query);

        $designs = (array) $db->loadObjectList();
        $items   = array();

        foreach ($designs AS $design)
        {
            $title = $design->title;

            if ($design->album_title) {
                $title = $design->album_title . '/' . $title;
            }

            $items[] = HTMLHelper::_('select.option', (int) $design->id, $title);
        }

        return $items;
    }


    public function getAlbums()
    {
        $project = (int) $this->getState('filter.project');

        if (!$project) {
            return array();
        }

        $db    = $this->getDbo();
        $query = $db->getQuery(true);
        $user  = Factory::getApplication()->getIdentity();

        $query->select('a.id, a.title')
              ->from('#__jp_design_albums AS a');

        // Implement View Level Access
        if (!$user->authorise('core.admin')) {
            $levels = implode(',', $user->getAuthorisedViewLevels());
            $query->where('a.access IN (' . $levels . ')');
        }

        $query->group('a.id');
        $query->order('a.title ASC');

        $db->setQuery($query);

        $albums = (array) $db->loadObjectList();
        $items   = array();

        foreach ($albums AS $album)
        {
            $items[] = HTMLHelper::_('select.option', (int) $album->id, $album->title);
        }

        return $items;
    }


    public function import($cid, $data)
    {
        $design   = $this->getInstance('Design', 'JPdesignsModel', array('ignore_request' => true));
        $revision = $this->getInstance('Revision', 'JPdesignsModel', array('ignore_request' => true));
        $success  = true;

        foreach ($cid AS $i)
        {
            if ($data[$i]['parent_id']) {
                if (!$revision->import($data[$i])) {
                    $success = false;
                    $this->setError($revision->getError());
                }
            }
            else {
                if (!$design->import($data[$i])) {
                    $success = false;
                    $this->setError($design->getError());
                }
            }
        }

        return $success;
    }


    /**
     * Method to get an album title
     *
     * @param     int       $id    The album id
     *
     * @return    string           The album title
     */
    protected function getAlbumTitle($id = null)
    {
        static $cache = array();

        $id = (int) $id;

        if (isset($cache[$id])) {
            return $cache[$id];
        }

        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select('title')
              ->from('#__jp_design_albums')
              ->where('id = ' . $db->quote($id));

        $db->setQuery($query);
        $cache[$id] = $db->loadResult();

        return $cache[$id];
    }


    /**
     * Method to get a design title
     *
     * @param     int       $id         The design id
     * @param     int       $project    The project id of the design
     *
     * @return    string                The design title
     */
    protected function getDesignTitle($id = null, $project = 0)
    {
        static $cache = array();

        $id = (int) $id;

        if (isset($cache[$id])) {
            return $cache[$id];
        }

        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select('title')
              ->from('#__jp_designs')
              ->where('id = ' . $db->quote($id))
              ->and('project_id = ' . $db->quote($project));

        $db->setQuery($query);
        $cache[$id] = $db->loadResult();

        return $cache[$id];
    }


    /**
     * Method to auto-populate the model state.
     * Note: Calling getState in this method will result in recursion.
     *
     * @return    void
     */
    protected function populateState($ordering = 'a.ordering', $direction = 'asc')
    {
        // Initialise variables.
        $app = Factory::getApplication();

        // Adjust the context to support modal layouts.
        if ($layout = Factory::getApplication()->input->get('layout')) $this->context .= '.' . $layout;

        $project = JPApplicationHelper::getActiveProjectId('filter_project');
        $this->setState('filter.project', $project);

        // List state information.
        parent::populateState($ordering, $direction);
    }


    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param     string    $id    A prefix for the store id.
     * @return    string           A store id.
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.project');

        return parent::getStoreId($id);
    }


    /**
     * Build an SQL query to load the list data.
     *
     * @return    jdatabasequery
     */
    protected function getListQuery()
    {
        return null;
    }
}
