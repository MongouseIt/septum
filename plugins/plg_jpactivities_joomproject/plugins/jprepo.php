<?php
/**
 * @package      plg_jpactivities_joomproject
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();
use Joomla\CMS\Factory;

class plgJPactivitiesJPrepo extends plgJPactivities
{
    /**
     * Method to store user activity after a save event
     *
     * @param     string     $context    The item context
     * @param     object     $table      The item table object
     * @param     boolean    $is_new     New item indicator (True is new, False is update)
     * @param     boolean    $store      Indicates whether to store the data or not
     *
     * @return    boolean                True on success, False on error
     */
    public function onJPactivitiesAfterSave($context, $table, $is_new, $store = true)
    {
        parent::onJPactivitiesAfterSave($context, $table, $is_new, false);

        // Handle directory
        if ($context == 'com_jprepo.directory') {
            $item = $this->getProject($table->project_id);

            $item->p_alias = $item->alias;
            $item->p_title = $item->title;
            $item->path    = $table->path;
        }
        else {
            // Note and File
            list($ext, $type) = explode('.', $context, 2);
            if ($ext != 'com_jprepo' || $type == 'attachment') return true;

            $item = $this->getItem($type, $table->id);

            $this->item_data['metadata']->set('d_id', $item->parent_id);
            $this->item_data['metadata']->set('d_alias', $item->d_alias);
        }

        // Handle shared data
        $this->item_data['xref_id'] = $table->project_id;

        $this->item_data['metadata']->set('alias', $item->alias);
        $this->item_data['metadata']->set('p_alias', $item->p_alias);
        $this->item_data['metadata']->set('p_title', $item->p_title);
        $this->item_data['metadata']->set('path', $item->path);

        if ($store) return $this->save();

        return true;
    }


    /**
     * Method to store user activity after a delete event
     *
     * @param     string     $context    The item context
     * @param     object     $table      The item table object
     * @param     boolean    $store      Indicates whether to store the data or not
     *
     * @return    boolean                True on success, False on error
     */
    public function onJPactivitiesAfterDelete($context, $table, $store = true)
    {
        parent::onJPactivitiesAfterDelete($context, $table, false);

        $item = $this->getProject($table->project_id);

        $this->item_data['xref_id'] = $table->project_id;
        $this->item_data['metadata']->set('alias', $table->alias);
        $this->item_data['metadata']->set('p_alias', $item->alias);
        $this->item_data['metadata']->set('p_title', $item->title);

        list($ext, $type) = explode('.', $context, 2);

        if ($type == 'attachment') return true;

        if ($context != 'com_jprepo.directory') {
            $this->item_data['metadata']->set('d_id', $table->dir_id);
        }

        if ($store) return $this->save();

        return true;
    }


    /**
     * Method to get a partial item record
     *
     * @param     string     $type    The item type
     * @param     integer    $id      The task id
     *
     * @return    object              The task record data
     */
    private function getItem($type, $id)
    {
        static $cache = array('directory' => array(), 'note' => array(), 'file' => array());

        // Check the cache
        if (isset($cache[$type][$id])) return $cache[$type][$id];

        $tables = array(
            'directory' => '#__jp_repo_dirs',
            'note'      => '#__jp_repo_notes',
            'file'      => '#__jp_repo_files'
        );

        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select('a.asset_id, a.project_id, a.title, a.alias, a.access')
              ->select('p.title AS p_title, p.alias AS p_alias');

        if ($type != 'directory') {
            $query->select('a.dir_id AS parent_id')
                  ->select('d.alias AS d_alias, d.path')
                  ->join('left', '#__jp_repo_dirs AS d ON d.id = a.dir_id');
        }
        else {
            $query->select('a.path, a.parent_id');
        }

        $query->from($tables[$type] . ' AS a')
              ->join('left', '#__jp_projects AS p ON p.id = a.project_id')
              ->where('a.id = ' . $db->quote((int) $id));

        $db->setQuery($query);
        $cache[$type][$id] = $db->loadObject();

        return $cache[$type][$id];
    }


    /**
     * Method to get a project title and alias
     *
     * @param     integer    $id    The category id
     *
     * @return    object            The title and alias
     */
    private function getProject($id)
    {
        static $cache = array();

        // Check the cache
        if (isset($cache[$id])) return $cache[$id];

        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select('title, alias')
              ->from('#__jp_projects')
              ->where('id = ' . (int) $id);

        $db->setQuery($query);
        $cache[$id] = $db->loadObject();

        return $cache[$id];
    }
}
