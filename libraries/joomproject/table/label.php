<?php
/**
 * @package      Joomproject.Library
 * @subpackage   Table
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 20012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;


jimport('joomla.database.tableasset');


/**
 * Project Label table
 *
 */
class JPTableLabel extends Table
{
    /**
     * Constructor
     *
     * @param    database    &    $db    A database connector object
     */
    public function __construct(&$db)
    {
        parent::__construct('#__jp_labels', 'id', $db);
    }


    /**
	 * Method to delete a row from the database table by primary key value.
	 *
	 * @param   mixed  $pk  An optional primary key value to delete.  If not set the instance property value is used.
	 *
	 * @return  boolean  True on success.
	 */
	public function delete($pk = null)
	{
		// Initialise variables.
		$k  = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		// If no primary key is given, return false.
		if ($pk === null) {
			$e = new Exception(Text::_('JLIB_DATABASE_ERROR_NULL_PRIMARY_KEY'));
			$this->setError($e);
			return false;
		}

		// Delete the row by primary key.
		$query = $this->_db->getQuery(true);

		$query->delete()
		      ->from($this->_tbl)
		      ->where($this->_tbl_key . ' = ' . $this->_db->quote($pk));

		$this->_db->setQuery($query);

		// Check for a database error.
        try
        {
            $this->_db->execute();
        }
        catch (RuntimeException $e)
        {
            $this->setError(Text::sprintf('JLIB_DATABASE_ERROR_DELETE_FAILED', get_class($this), $e->getMessage()));
            return false;
        }

        // Delete the references
        $query->clear();
        $query->delete('#__jp_ref_labels')
              ->where('label_id = ' . $this->_db->quote($pk));

        $this->_db->setQuery($query);

        // Check for a database error.
        try
        {
            $this->_db->execute();
        }
        catch (RuntimeException $e)
        {
            $this->setError(Text::sprintf('JLIB_DATABASE_ERROR_DELETE_FAILED', get_class($this), $e->getMessage()));
            return false;
        }


		return true;
	}
}
