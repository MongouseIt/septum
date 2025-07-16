<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jptime
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Access\Rules;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
jimport('joomla.database.tableasset');


// Register compat class
JLoader::register('JPtableTimeCompat', dirname(__FILE__) . '/time_compat.php');


/**
 * Timesheet table
 *
 */
class JPtableTime extends JPtableTimeCompat
{
    /**
     * Constructor
     *
     * @param     database         $db    A database connector object
     * @return    jtableproject
     */
    public function __construct(&$db)
    {
        parent::__construct('#__jp_timesheet', 'id', $db);
	    $this->setColumnAlias('published','state');
    }


    /**
     * Method to compute the default name of the asset.
     *
     * @return    string
     */
    protected function _getAssetName()
    {
        $k = $this->_tbl_key;
        return 'com_jptime.time.' . (int) $this->$k;
    }


    /**
     * Method to get the parent asset id for the record
     *
     * @param     jtable     $table    A JTable object for the asset parent
     * @param     integer    $id
     *
     * @return    integer
     */
    protected function _getAssetParentIdCompat($table = null, $id = null)
    {
        // Initialise variables.
        $asset_id = null;
        $result   = null;

        $query = $this->_db->getQuery(true);

        // Get the asset id of the component project asset
        $query->select('id')
              ->from('#__assets')
              ->where('name' . ' = ' . $this->_db->quote("com_jptime.project." . (int) $this->project_id));

        $this->_db->setQuery($query);
        $result = $this->_db->loadResult();

        if ($result) $asset_id = (int) $result;

        if (!$result) {
            $query->clear();
            $query->select($this->_db->quoteName('id'))
                  ->from($this->_db->quoteName('#__assets'))
                  ->where($this->_db->quoteName('name') . ' = ' . $this->_db->quote("com_jptime"));

            $this->_db->setQuery($query);
            $result = $this->_db->loadResult();

            if ($result) $asset_id = (int) $result;
        }

        // Return the asset id.
        if ($asset_id) return $asset_id;

        return parent::_getAssetParentId($table, $id);
    }


    /**
     * Method to get the access level of the parent asset
     *
     * @return    integer
     */
    protected function _getParentAccess()
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $project = (int) $this->project_id;
        $access  = 0;

        if ($project > 0) {
            $query->select('access')
                  ->from('#__jp_projects')
                  ->where('id = ' . $db->quote($project));

            $db->setQuery($query);
            $access = (int) $db->loadResult();
        }

        if (!$access) $access = (int) Factory::getConfig()->get('access');

        return $access;
    }


    /**
     * Overloaded bind function
     *
     * @param     array    $array     Named array
     * @param     mixed    $ignore    An optional array or space separated list of properties to ignore while binding.
     *
     * @return    mixed               Null if operation was satisfactory, otherwise returns an error string
     */
    public function bind($array, $ignore = '')
    {
        if (isset($array['attribs']) && is_array($array['attribs'])) {
            $registry = new Registry;
            $registry->loadArray($array['attribs']);
            $array['attribs'] = (string) $registry;
        }

        // Bind the rules.
        if (isset($array['rules']) && is_array($array['rules'])) {
            $rules = new Rules($array['rules']);
            $this->setRules($rules);
        }

        return parent::bind($array, $ignore);
    }


    /**
     * Overloaded check function
     *
     * @return    boolean    True on success, false on failure
     */
    public function check()
    {
        // Check if a project is selected
        if ((int) $this->project_id <= 0) {
            $this->setError(Text::_('COM_JOOMPROJECT_WARNING_SELECT_PROJECT'));
            return false;
        }

        // Check if a task is selected
        if ((int) $this->task_id <= 0) {
            $this->setError(Text::_('COM_JOOMPROJECT_WARNING_SELECT_TASK'));
            return false;
        }

        // Check for selected access level
        if ($this->access <= 0) {
            $this->access = $this->_getParentAccess();
        }

        // Make sure we have a time
        if ($this->log_time <= 0) {
            $this->log_time = 1;
        }

        return true;
    }


    /**
     * Overrides Table::store to set modified data and user id.
     *
     * @param     boolean    True to update fields even if they are null.
     * @return    boolean    True on success.
     */
    public function store($updateNulls = false)
    {
        $date = Factory::getDate();
        $user = Factory::getApplication()->getIdentity();

        if ($this->id) {
            // Existing item
            $this->modified    = $date->toSql();
            $this->modified_by = $user->get('id');
        }
        else {
            // New item
            $this->created = $date->toSql();
            if (empty($this->created_by)) $this->created_by = $user->get('id');
        }

        if (empty($this->log_date)) {
            $this->log_date = $this->created;
        }

        // Store the main record
        $success = parent::store($updateNulls);

        return $success;
    }


    /**
     * Method to set the publishing state for a row or list of rows in the database
     * table.
     *
     * @param     mixed      $pks      An optional array of primary key values to update.
     * @param     integer    $state    The publishing state
     * @param     integer    $uid      The user id of the user performing the operation.
     *
     * @return    boolean              True on success.
     */
    public function publish($pks = null, $state = 1, $uid = 0)
    {
        return $this->setState($pks, $state, $uid);
    }


    /**
     * Method to set the state for a row or list of rows in the database
     * table.
     *
     * @param     mixed      $pks      An optional array of primary key values to update.
     * @param     integer    $state    The state.
     * @param     integer    $uid      The user id of the user performing the operation.
     *
     * @return    boolean              True on success.
     */
    public function setState($pks = null, $state = 1, $uid = 0)
    {
        // Sanitize input.
        \Joomla\Utilities\ArrayHelper::toInteger($pks);

        $k     = $this->_tbl_key;
        $uid   = (int) $uid;
        $state = (int) $state;

        // If there are no primary keys set check to see if the instance key is set.
        if (empty($pks)) {
            if ($this->$k) {
                $pks = array($this->$k);
            }
            else {
                // Nothing to set state on, return false.
                $this->setError(Text::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
                return false;
            }
        }

        // Build the WHERE clause for the primary keys.
        $where = $k . '=' . implode(' OR ' . $k . '=', $pks);

        // Determine if there is checkin support for the table.
        if (!property_exists($this, 'checked_out') || !property_exists($this, 'checked_out_time')) {
            $checkin = '';
        }
        else {
            $checkin = ' AND (checked_out = 0 OR checked_out = ' . (int) $uid . ')';
        }

        // Update the state for rows with the given primary keys.
        $this->_db->setQuery(
            'UPDATE ' . $this->_db->quoteName($this->_tbl).
            ' SET ' . $this->_db->quoteName('state').' = ' .(int) $state .
            ' WHERE (' . $where . ')' .
            $checkin
        );
        try
        {
            $this->_db->execute();
        }
        catch (RuntimeException $e)
        {
            $this->setError($e->getMessage());
            return false;
        }

        // If checkin is supported and all rows were adjusted, check them in.
        if ($checkin && (count($pks) == $this->_db->getAffectedRows())) {
            // Checkin the rows.
            foreach($pks as $pk)
            {
                $this->checkin($pk);
            }
        }

        // If the JTable instance value is in the list of primary keys that were set, set the instance.
        if (in_array($this->$k, $pks)) $this->state = $state;
        $this->setError('');

        return true;
    }
}
