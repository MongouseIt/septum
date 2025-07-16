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


class plgJPactivitiesJPtasks extends plgJPactivities
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

        // Handle task complete event
        if (\Joomla\CMS\Factory::getApplication()->input->get('task') == 'complete') {
            $event_name = ($table->complete ? 'complete' : 'incomplete');
            $event_id = $this->activity_model->getEventId($event_name);

            if (!$event_id) {
                $event_id = $this->activity_model->saveEvent($event_name);
            }

            if (!$event_id) return true;

            list($extension, $item_name) = explode('.', $context, 2);
            $this->setDataFromContext($extension, $item_name, $event_id);
        }

        $this->item_data['xref_id'] = $table->project_id;

        // Set meta data for a task
        if ($context == 'com_jptasks.task') {
            $item = $this->getTask($table->id);
            $this->item_data['metadata']->set('p_alias', $item->p_alias);
            $this->item_data['metadata']->set('p_title', $item->p_title);
            $this->item_data['metadata']->set('m_id',    $item->milestone_id);
            $this->item_data['metadata']->set('m_alias', $item->m_alias);
            $this->item_data['metadata']->set('m_title', $item->m_title);
            $this->item_data['metadata']->set('l_id',    $item->list_id);
            $this->item_data['metadata']->set('l_alias', $item->l_alias);
            $this->item_data['metadata']->set('l_title', $item->l_title);
        }

        // Set meta data for a list
        if ($context == 'com_jptasks.tasklist') {
            $item = $this->getTaskList($table->id);
            $this->item_data['metadata']->set('p_alias', $item->p_alias);
            $this->item_data['metadata']->set('p_title', $item->p_title);
            $this->item_data['metadata']->set('m_id',    $item->milestone_id);
            $this->item_data['metadata']->set('m_alias', $item->m_alias);
            $this->item_data['metadata']->set('m_title', $item->m_title);
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

        $this->item_data['xref_id'] = $table->project_id;

        // Set meta data for a task
        if ($context == 'com_jptasks.task') {
            $item = $this->getTask($table->id);
            $this->item_data['metadata']->set('p_alias', $item->p_alias);
            $this->item_data['metadata']->set('p_title', $item->p_title);
            $this->item_data['metadata']->set('m_id',    $item->milestone_id);
            $this->item_data['metadata']->set('m_alias', $item->m_alias);
            $this->item_data['metadata']->set('m_title', $item->m_title);
            $this->item_data['metadata']->set('l_id',    $item->list_id);
            $this->item_data['metadata']->set('l_alias', $item->l_alias);
            $this->item_data['metadata']->set('l_title', $item->l_title);
        }

        // Set meta data for a list
        if ($context == 'com_jptasks.tasklist') {
            $item = $this->getTaskList($table->id);
            $this->item_data['metadata']->set('p_alias', $item->p_alias);
            $this->item_data['metadata']->set('p_title', $item->p_title);
            $this->item_data['metadata']->set('m_id',    $item->milestone_id);
            $this->item_data['metadata']->set('m_alias', $item->m_alias);
            $this->item_data['metadata']->set('m_title', $item->m_title);
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

        if ($this->item_data['name'] == 'task') {
            $item = $this->getTask($id);
        }
        else {
            $item = $this->getTaskList($id);
        }

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
        $this->item_data['metadata']->set('m_id',    $item->milestone_id);
        $this->item_data['metadata']->set('m_alias', $item->m_alias);
        $this->item_data['metadata']->set('m_title', $item->m_title);

        if ($this->item_data['name'] == 'task') {
            $this->item_data['metadata']->set('l_id',    $item->list_id);
            $this->item_data['metadata']->set('l_alias', $item->l_alias);
            $this->item_data['metadata']->set('l_title', $item->l_title);
        }
    }


    /**
     * Method to get a partial task record
     *
     * @param     integer    $id    The task id
     *
     * @return    object            The task record data
     */
    private function getTask($id)
    {
        static $cache = array();

        // Check the cache
        if (isset($cache[$id])) return $cache[$id];

        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select('a.asset_id, a.project_id, a.milestone_id, a.list_id, a.title, a.alias, a.state, a.access')
              ->select('p.title AS p_title, p.alias AS p_alias')
              ->select('m.title AS m_title, m.alias AS m_alias')
              ->select('l.title AS l_title, l.alias AS l_alias')
              ->from('#__jp_tasks AS a')
              ->join('left', '#__jp_projects AS p ON p.id = a.project_id')
              ->join('left', '#__jp_milestones AS m ON m.id = a.milestone_id')
              ->join('left', '#__jp_task_lists AS l ON l.id = a.list_id')
              ->where('a.id = ' . $db->quote((int) $id));

        $db->setQuery($query);
        $cache[$id] = $db->loadObject();

        return $cache[$id];
    }


    /**
     * Method to get a partial task list record
     *
     * @param     integer    $id    The task list id
     *
     * @return    object            The task list record data
     */
    private function getTaskList($id)
    {
        static $cache = array();

        // Check the cache
        if (isset($cache[$id])) return $cache[$id];

        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select('a.asset_id, a.project_id, a.milestone_id, a.title, a.alias, a.state, a.access')
              ->select('p.title AS p_title, p.alias AS p_alias')
              ->select('m.title AS m_title, m.alias AS m_alias')
              ->from('#__jp_task_lists AS a')
              ->join('left', '#__jp_projects AS p ON p.id = a.project_id')
              ->join('left', '#__jp_milestones AS m ON m.id = a.milestone_id')
              ->where('a.id = ' . $db->quote((int) $id));

        $db->setQuery($query);
        $cache[$id] = $db->loadObject();

        return $cache[$id];
    }
}
