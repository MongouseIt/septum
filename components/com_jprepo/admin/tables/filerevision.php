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

use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;


jimport('joomla.database.tableasset');


/**
 * File Revision Table Class
 *
 */
class JPtableFileRevision extends Table
{
    /**
     * Constructor
     *
     * @param    database    $db    A database connector object
     */
    public function __construct(&$db)
    {
        parent::__construct('#__jp_repo_file_revs', 'id', $db);
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

        return parent::bind($array, $ignore);
    }


    /**
     * Overloaded check function
     *
     * @return    boolean    True on success, false on failure
     */
    public function check()
    {
        if (trim(str_replace('&nbsp;', '', $this->title)) == '') {
            if ($this->file_name == '') {
                $this->setError(Text::_('COM_JOOMPROJECT_WARNING_PROVIDE_VALID_TITLE'));
                return false;
            }
            else {
                $this->title = $this->file_name;
            }
        }

        // Check if a project is selected
        if ((int) $this->project_id <= 0) {
            $this->setError(Text::_('COM_JOOMPROJECT_WARNING_SELECT_PROJECT'));
            return false;
        }

        return true;
    }
}
