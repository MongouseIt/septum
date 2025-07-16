<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jptasks
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Table\Table;


jimport('joomla.database.tableasset');


/**
 * Task Dependency table
 *
 */
class JPTableTaskRef extends Table
{
    /**
     * Constructor
     *
     * @param    database    &    $db    A database connector object
     */
    public function __construct(&$db)
    {
        parent::__construct('#__jp_ref_tasks', 'id', $db);
    }


    /**
     * Overrides Table::store to set modified data and user id.
     *
     * @param     boolean    True to update fields even if they are null.
     *
     * @return    boolean    True on success.
     */
    public function store($updateNulls = false)
    {
        return parent::store($updateNulls);
    }
}
