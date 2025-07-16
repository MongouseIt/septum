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


class plgJPactivitiesJPdesigns extends plgJPactivities
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

        $project = $this->getProject($table->project_id);

        $this->item_data['xref_id'] = $table->project_id;
        $this->item_data['metadata']->set('alias', $table->alias);
        $this->item_data['metadata']->set('p_alias', $project->alias);
        $this->item_data['metadata']->set('p_title', $project->title);

        if ($context == 'com_jpdesigns.design') {
            if ($table->album_id) {
                $album = $this->getItem('album', $table->album_id);

                $this->item_data['metadata']->set('a_id',    $table->album_id);
                $this->item_data['metadata']->set('a_alias', $album->alias);
                $this->item_data['metadata']->set('a_title', $album->title);
            }
            else {
                $this->item_data['metadata']->set('a_id',    0);
                $this->item_data['metadata']->set('a_alias', '');
                $this->item_data['metadata']->set('a_title', '');
            }
        }

        if ($context == 'com_jpdesigns.revision') {
            $design = $this->getItem('design', $table->parent_id);

            $this->item_data['metadata']->set('d_id',    $table->parent_id);
            $this->item_data['metadata']->set('d_alias', $design->alias);
            $this->item_data['metadata']->set('d_title', $design->title);
            $this->item_data['metadata']->set('a_id',    $design->album_id);
            $this->item_data['metadata']->set('a_alias', $design->a_alias);
            $this->item_data['metadata']->set('a_title', $design->a_title);
        }

        // Override event
        if (in_array(\Joomla\CMS\Factory::getApplication()->input->get('task'), array('approve', 'decline')) && !$is_new) {
            $event_name = ($table->approved ? 'approve_design' : 'decline_design');
            $event_id = $this->activity_model->getEventId($event_name);

            if (!$event_id) {
                $event_id = $this->activity_model->saveEvent($event_name);
            }

            if (!$event_id) return true;

            list($extension, $item_name) = explode('.', $context, 2);
            $this->setDataFromContext($extension, $item_name, $event_id);
        }

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

        $project = $this->getProject($table->project_id);

        $this->item_data['xref_id'] = $table->project_id;
        $this->item_data['metadata']->set('p_alias', $project->alias);
        $this->item_data['metadata']->set('p_title', $project->title);

        if ($context == 'com_jpdesigns.design') {
            if ($table->album_id) {
                $album = $this->getItem('album', $table->album_id);

                if ($album) {
                    $this->item_data['metadata']->set('a_id',    $table->album_id);
                    $this->item_data['metadata']->set('a_alias', $album->alias);
                    $this->item_data['metadata']->set('a_title', $album->title);
                }
                else {
                    $this->item_data['metadata']->set('a_id',    0);
                    $this->item_data['metadata']->set('a_alias', '');
                    $this->item_data['metadata']->set('a_title', '');
                }
            }
            else {
                $this->item_data['metadata']->set('a_id',    0);
                $this->item_data['metadata']->set('a_alias', '');
                $this->item_data['metadata']->set('a_title', '');
            }
        }

        if ($context == 'com_jpdesigns.revision') {
            $design = $this->getItem('design', $table->parent_id);

            $this->item_data['metadata']->set('d_id', $table->parent_id);

            if ($design) {
                $this->item_data['metadata']->set('d_alias', $design->alias);
                $this->item_data['metadata']->set('d_title', $design->title);
                $this->item_data['metadata']->set('a_id',    $design->album_id);
                $this->item_data['metadata']->set('a_alias', $design->a_alias);
                $this->item_data['metadata']->set('a_title', $design->a_title);
            }
            else {
                $this->item_data['metadata']->set('d_alias', '');
                $this->item_data['metadata']->set('d_title', '');
                $this->item_data['metadata']->set('a_id',    0);
                $this->item_data['metadata']->set('a_alias', '');
                $this->item_data['metadata']->set('a_title', '');
            }
        }

        if ($store) return $this->save();

        return true;
    }


    /**
     * Method to set some of the activity data from the item id and state
     *
     * @param     integer    $id       The item id
     * @param     integer    $state    The item state
     *
     * @return    void
     */
    protected function setDataFromItemState($id, $state)
    {
        parent::setDataFromItemState($id, $state);

        $item = $this->getItem($this->item_data['name'], $id);

        if (!$item) return false;

        // Set the data
        $this->item_data['title']      = $item->title;
        $this->item_data['asset_id']   = $item->asset_id;
        $this->item_data['xref_id']    = $item->project_id;
        $this->item_data['access']     = $item->access;
        $this->activity_data['access'] = $item->access;

        // Set meta data
        $this->item_data['metadata']->set('alias', $item->alias);
        $this->item_data['metadata']->set('p_alias', $item->p_alias);
        $this->item_data['metadata']->set('p_title', $item->p_title);

        if ($this->item_data['name'] == 'design') {
            if ($table->album_id) {
                $album = $this->getItem('album', $table->album_id);

                $this->item_data['metadata']->set('a_id',    $table->album_id);
                $this->item_data['metadata']->set('a_alias', $album->alias);
                $this->item_data['metadata']->set('a_title', $album->title);
            }
            else {
                $this->item_data['metadata']->set('a_id',    0);
                $this->item_data['metadata']->set('a_alias', '');
                $this->item_data['metadata']->set('a_title', '');
            }
        }

        if ($this->item_data['name'] == 'revision') {
            $design = $this->getItem('design', $item->parent_id);

            $this->item_data['metadata']->set('d_id',    $item->parent_id);
            $this->item_data['metadata']->set('d_alias', $design->alias);
            $this->item_data['metadata']->set('d_title', $design->title);
            $this->item_data['metadata']->set('a_id',    $design->album_id);
            $this->item_data['metadata']->set('a_alias', $design->a_alias);
            $this->item_data['metadata']->set('a_title', $design->a_title);
        }
    }


    /**
     * Method to get a partial item record
     *
     * @param string $type The item type
     * @param     integer    $id    The task id
     *
     * @return    object            The task record data
     */
    private function getItem($type, $id)
    {
        static $cache = array('album' => array(), 'design' => array(), 'revision' => array());

        // Check the cache
        if (isset($cache[$type][$id])) return $cache[$type][$id];

        $tables = array(
            'album'    => '#__jp_design_albums',
            'design'   => '#__jp_designs',
            'revision' => '#__jp_design_revisions'
        );

        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select('a.asset_id, a.project_id, a.title, a.alias, a.state, a.access')
              ->select('p.title AS p_title, p.alias AS p_alias');

        if ($type == 'design') {
            $query->select('a.album_id, b.alias AS a_alias, b.title AS a_title')
                  ->join('left', '#__jp_design_albums AS b ON b.id = a.album_id');
        }

        if ($type == 'revision') {
            $query->select('a.parent_id');
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
