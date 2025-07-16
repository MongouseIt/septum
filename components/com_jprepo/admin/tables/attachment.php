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


jimport('joomla.database.tableasset');


/**
 * Attachment table
 *
 */
class JPtableAttachment extends Table
{
    /**
     * Constructor
     *
     * @param    database    &    $db    A database connector object
     */
    public function __construct(&$db)
    {
        parent::__construct('#__jp_ref_attachments', 'id', $db);
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
        // Verify that the attachment connection is unique
        $table = Table::getInstance('Attachment', 'JPtable');
        $data  = array('item_type' => $this->item_type, 'item_id' => $this->item_id, 'attachment' => $this->attachment);

        if ($table->load($data) && ($table->id != $this->id || $this->id == 0)) {
            return true;
        }

        return parent::store($updateNulls);
    }
}
