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


class plgJPactivitiesJPmilestones extends plgJPactivities
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

        $this->item_data['xref_id'] = $table->project_id;

        // Set meta data
        $p = $this->getProject($table->project_id);
        $this->item_data['metadata']->set('p_alias', $p->alias);
        $this->item_data['metadata']->set('p_title', $p->title);

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

        $this->item_data['xref_id'] = $table->project_id;

        // Set meta data
        $p = $this->getProject($table->project_id);
        $this->item_data['metadata']->set('p_alias', $p->alias);
        $this->item_data['metadata']->set('p_title', $p->title);

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

        // Get the item
        $item = $this->getItem($id);
        if (!$item) return false;

        // Set the data
        $this->item_data['title']      = $item->title;
        $this->item_data['asset_id']   = $item->asset_id;
        $this->item_data['xref_id']    = $item->project_id;
        $this->item_data['access']     = $item->access;
        $this->item_data['state']      = $item->state;
        $this->activity_data['access'] = $item->access;

        // Set meta data
        $this->item_data['metadata']->set('alias', $item->alias);
        $this->item_data['metadata']->set('p_alias', $item->p_alias);
        $this->item_data['metadata']->set('p_title', $item->p_title);
    }


    /**
     * Method to get a partial item record
     *
     * @param     integer    $id    The item id
     *
     * @return    object            The item record data
     */
    private function getItem($id)
    {
        static $cache = array();

        // Check the cache
        if (isset($cache[$id])) return $cache[$id];

        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select('a.asset_id, a.project_id, a.title, a.alias, a.state, a.access')
              ->select('p.title AS p_title, p.alias AS p_alias')
              ->from('#__jp_milestones AS a')
              ->join('left', '#__jp_projects AS p ON p.id = a.project_id')
              ->where('a.id = ' . (int) $id);

        $db->setQuery($query);
        $cache[$id] = $db->loadObject();

        return $cache[$id];
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
