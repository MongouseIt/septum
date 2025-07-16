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

use Joomla\CMS\Access\Rules;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;


jimport('joomla.database.tableasset');

// Register compat class
JLoader::register('JPtableNoteCompat', dirname(__FILE__) . '/note_compat.php');


/**
 * Repository Note Table Class
 *
 */
class JPtableNote extends JPtableNoteCompat
{
    /**
     * Constructor
     *
     * @param    database    $db    A database connector object
     */
    public function __construct(&$db)
    {
        parent::__construct('#__jp_repo_notes', 'id', $db);
    }


    /**
     * Method to compute the default name of the asset.
     * The default name is in the form table_name.id
     * where id is the value of the primary key of the table.
     *
     * @return    string
     */
    protected function _getAssetName()
    {
        $k = $this->_tbl_key;
        return 'com_jprepo.note.' . (int) $this->$k;
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

        $query = $this->_db->getQuery(true);

        if ($this->dir_id) {
            // Build the query to get the asset id for the parent directory.
            $query->select('asset_id')
                  ->from('#__jp_repo_dirs')
                  ->where('id = ' . (int) $this->dir_id);

            // Get the asset id from the database.
            $this->_db->setQuery((string) $query);
            $result = $this->_db->loadResult();

            if ($result) $asset_id = (int) $result;
        }

        if (!$asset_id) {
            // Build the query to get the asset id for the parent component.
            $query->clear();
            $query->select($this->_db->quoteName('id'))
                  ->from($this->_db->quoteName('#__assets'))
                  ->where($this->_db->quoteName('name') . ' = ' . $this->_db->quote("com_jprepo"));

            // Get the asset id from the database.
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
        $query = $this->_db->getQuery(true);

        $dir     = (int) $this->dir_id;
        $project = (int) $this->project_id;

        if ($dir > 1) {
            $query->select('access')
                  ->from('#__jp_repo_dirs')
                  ->where('id = ' . $dir);
        }
        elseif ($project > 0) {
            $query->select('access')
                  ->from('#__jp_projects')
                  ->where('id = ' . $project);
        }

        $this->_db->setQuery($query);
        $access = (int) $this->_db->loadResult();

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
        if (trim(str_replace('&nbsp;', '', $this->description)) == '') {
            $this->setError(Text::_('COM_JOOMPROJECT_WARNING_PROVIDE_VALID_DESC'));
            return false;
        }

        // Check if a project is selected
        if ((int) $this->project_id <= 0) {
            $this->setError(Text::_('COM_JOOMPROJECT_WARNING_SELECT_PROJECT'));
            return false;
        }

        // Check if a directory is selected
        if ((int) $this->dir_id <= 1) {
            $this->setError(Text::_('COM_JOOMPROJECT_WARNING_SELECT_DIRECTORY'));
            return false;
        }

        // Check for selected access level
        if ($this->access <= 0) {
            $this->access = $this->_getParentAccess();
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
        $date = Factory::getDate()->toSql();
        $user = Factory::getApplication()->getIdentity()->get('id');

        if ($this->id) {
            // Existing item
            $this->modified    = $date;
            $this->modified_by = $user;
        }
        else {
            // New item.
            $this->created = $date;
            if (empty($this->created_by)) $this->created_by = $user;
        }

        // Store the main record
        $success = parent::store($updateNulls);

        return $success;
    }


    /**
     * Method to delete a row from the database table by primary key value.
     *
     * @param     mixed      $pk    An optional primary key value to delete.
     *
     * @return    boolean           True on success.
     */
    public function delete($pk = null)
    {
        $k  = $this->_tbl_key;
        $pk = (is_null($pk)) ? $this->$k : $pk;

         // Call parent method
         if (!parent::delete($pk)) {
             return false;
         }

         // Delete references
         $this->deleteReferences($pk);

         return true;
    }


    /**
     * Method to delete referenced data of an item.
     *
     * @param     mixed      $pk    An primary key value to delete.
     *
     * @return    boolean
     */
    public function deleteReferences($pk = null)
    {
        if (empty($pk)) return true;

        // Delete revisions
        $query = $this->_db->getQuery(true);

        $query->clear()
              ->delete('#__jp_repo_note_revs')
              ->where('parent_id = ' . (int) $pk);

        $this->_db->setQuery($query);
        $this->_db->execute();
    }
}
