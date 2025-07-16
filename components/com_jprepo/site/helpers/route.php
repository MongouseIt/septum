<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jprepo
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();
use Joomla\CMS\Factory;

jimport('joomla.application.component.helper');


/**
 * Joomproject Repository Component Route Helper
 *
 * @static
 */
abstract class JPrepoHelperRoute
{
    /**
     * Creates a valid repository directory path
     *
     * @param     string    $project    The project slug. Optional
     * @param     string    $path       The full directory path. Optional
     *
     * @return    string    $link       The link
     */
    public static function getRepositoryPath($project = '', $path = '')
    {
        static $paths = array();

        // Get all paths of the project
        if (!isset($paths[$project])) {
            $db    = Factory::getDbo();
            $query = $db->getQuery(true);

            $query->select('id, path')
                  ->from('#__jp_repo_dirs')
                  ->where('project_id = ' . $db->quote((int) $project));

            $db->setQuery($query);
            $list = (array) $db->loadObjectList();

            $project_paths = array();

            foreach($list AS $list_item)
            {
                $id = $list_item->id;
                $p  = $list_item->path;

                $project_paths[$p] = $id;
            }

            $paths[$project] = $project_paths;
        }

        if ($path) {
            $parts    = array_reverse(explode('/', $path));
            $new_path = array();
            $looped   = array();

            while(count($parts))
            {
                $part     = array_pop($parts);
                $looped[] = $part;

                $find = implode('/', $looped);

                if (isset($paths[$project][$find])) {
                    $new_path[] = $paths[$project][$find] . ':' . $part;
                }
            }

            $path = implode('/', $new_path);
        }

        return $path;
    }


    /**
     * Creates a link a repo directory
     *
     * @param     string    $project    The project slug. Optional
     * @param     string    $dir        The directory slug. Optional
     * @param     string    $path       The full directory path. Optional
     *
     * @return    string    $link       The link
     */
    public static function getRepositoryRoute($project = '', $dir = '', $path = '',$milestone = '')
    {
        $path  = self::getRepositoryPath($project, $path);
        $link  = 'index.php?option=com_jprepo&view=repository';

        if ($project) {
            $link .= '&filter_project=' . $project;
        }

        if ($dir) {
            $link .= '&filter_parent_id=' . $dir;
        }

        if ($path) {
            $link .= '&path=' . $path;
        }

        if($milestone){
            $link .= '&filter_milestone_id=' . $milestone;
        }

        $needles = array('filter_project'   => array((int) $project),
                         'filter_parent_id' => array((int) $dir),
                         'path' => array($path),
                        );

        if ($item = JPApplicationHelper::itemRoute($needles, 'com_jprepo.repository')) {
            $link .= '&Itemid=' . $item;
        }
        elseif ($item = JPApplicationHelper::itemRoute(null, 'com_jprepo.repository')) {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }


    /**
     * Creates a link a repo file
     *
     * @param     string    $file       The file slug
     * @param     string    $project    The project slug. Optional
     * @param     string    $dir        The directory slug. Optional
     * @param     string    $path       The full directory path. Optional
     * @param     string    $rev        The revision slug. Optional
     *
     *
     * @return    string    $link       The link
     */
    public static function getFileRoute($file, $project = '', $dir = '', $path = '', $rev = '')
    {
        $path  = self::getRepositoryPath($project, $path);
        $link  = 'index.php?option=com_jprepo&view=file';
        $link .= '&filter_project=' . $project;
        $link .= '&filter_parent_id=' . $dir;
        $link .= '&path=' . $path;
        $link .= '&id=' . $file;

        if ($rev) $link .= '&rev=' . $rev;

        $item = JPApplicationHelper::itemRoute(null, 'com_jprepo.repository');

        if ($item) {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }


    /**
     * Creates a link to a file revision list
     *
     * @param     string    $file       The file slug
     * @param     string    $project    The project slug. Optional
     * @param     string    $dir        The directory slug. Optional
     * @param     string    $path       The full directory path. Optional
     *
     *
     * @return    string    $link       The link
     */
    public static function getFileRevisionsRoute($file, $project = '', $dir = '', $path = '')
    {
        $path  = self::getRepositoryPath($project, $path);
        $link  = 'index.php?option=com_jprepo&view=filerevisions';
        $link .= '&filter_project=' . $project;
        $link .= '&filter_parent_id=' . $dir;
        $link .= '&path=' . $path;
        $link .= '&id=' . $file;

        $item = JPApplicationHelper::itemRoute(null, 'com_jprepo.repository');

        if ($item) $link .= '&Itemid=' . $item;

        return $link;
    }


    /**
     * Creates a link to a repo note
     *
     * @param     string    $note       The note slug
     * @param     string    $project    The project slug. Optional
     * @param     string    $dir        The directory slug. Optional
     * @param     string    $path       The full directory path. Optional
     * @param     string    $rev        The revision slug. Optional
     *
     *
     * @return    string    $link       The link
     */
    public static function getNoteRoute($note, $project = '', $dir = '', $path = '', $rev = '')
    {
        $path  = self::getRepositoryPath($project, $path);
        $link  = 'index.php?option=com_jprepo&view=note';
        $link .= '&filter_project=' . $project;
        $link .= '&filter_parent_id=' . $dir;
        $link .= '&path=' . $path;
        $link .= '&id=' . $note;

        if ($rev) $link .= '&rev=' . $rev;

        $item = JPApplicationHelper::itemRoute(null, 'com_jprepo.repository');

        if ($item) {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }


    /**
     * Creates a link to a note revision list
     *
     * @param     string    $note       The note slug
     * @param     string    $project    The project slug. Optional
     * @param     string    $dir        The directory slug. Optional
     * @param     string    $path       The full directory path. Optional
     *
     *
     * @return    string    $link       The link
     */
    public static function getNoteRevisionsRoute($note, $project = '', $dir = '', $path = '')
    {
        $path  = self::getRepositoryPath($project, $path);
        $link  = 'index.php?option=com_jprepo&view=noterevisions';
        $link .= '&filter_project=' . $project;
        $link .= '&filter_parent_id=' . $dir;
        $link .= '&path=' . $path;
        $link .= '&id=' . $note;

        $item = JPApplicationHelper::itemRoute(null, 'com_jprepo.repository');

        if ($item) {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }
}
