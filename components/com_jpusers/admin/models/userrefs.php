<?php
/**
 * @package      Joomproject
 * @subpackage   Users
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\MVC\Model\ListModel;


jimport('joomla.application.component.modellist');


/**
 * Methods supporting a list of user references.
 *
 */
class JPusersModelUserRefs extends ListModel
{
    /**
     * Method to get a list of user references.
     *
     * @return    mixed    An array of data items on success, false on failure.
     */
    public function getItems($item_type = null, $item_id = 0)
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);
        $items = array();

        $query->select('a.id, a.user_id, u.username, u.name')
              ->from('#__jp_ref_users AS a')
              ->join('INNER', '#__users AS u ON u.id = a.user_id')
              ->where('a.item_type = ' . $db->quote($item_type))
              ->where('a.item_id = ' . (int) $item_id);

        $db->setQuery((string) $query);
        $items = (array) $db->loadObjectList();

        return $items;
    }


    public function store($users, $item_type = null, $item_id = 0)
    {
        $db = $this->getDbo();

        if (is_null($item_type) || $item_type == '') {
            $item_type = $this->getState('item.type');
        }

        if ((int) $item_id == 0) {
            $item_type = (int) $this->getState('item.id');
        }

        if ($item_id == 0) {
            $this->setError('COM_JOOMPROJECT_ERROR_USER_REFERENCE_ID');
            return false;
        }

        if (!is_array($users) || !count($users)) {
            $this->setError('COM_JOOMPROJECT_ERROR_EMPTY_USER_REFERENCE');
            return false;
        }

        $list   = $this->getItems($item_type, $item_id);
        $stored = array();

        foreach($list AS $ref)
        {
            $stored[] = (int) $ref->user_id;
        }

        foreach($users AS $user)
        {
            $uid   = (int) $user;
            $query = $db->getQuery(true);

            if (!$uid || in_array($uid, $stored)) continue;


            $query->insert('#__jp_ref_users');
            $query->values('NULL, ' . $db->quote($item_type) . ', ' . $db->quote($item_id) . ', ' . $db->quote($uid));

            $db->setQuery((string) $query);


            try
            {
                $db->execute();
            }
            catch (RuntimeException $e)
            {
                $this->setError($e->getMessage());
                return false;
            }


            $stored[] = $uid;
        }

        return true;
    }


    /**
     * Method to auto-populate the model state.
     * Note. Calling getState in this method will result in recursion.
     *
     * @return    void
     */
    protected function populateState($ordering = 'title', $direction = 'ASC')
    {
        // Item type
        $value = str_replace('form', '', \Joomla\CMS\Factory::getApplication()->input->getCmd('view', 'taskform'));
        $this->setState('item.type', $value);

        // Item id
        $value = \Joomla\CMS\Factory::getApplication()->input->getUint('id');
        $this->setState('item.id');
    }
}
