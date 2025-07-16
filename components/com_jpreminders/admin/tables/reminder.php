<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jpreminders
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;

class TableReminder extends Table
{

    /**
     * Constructor
     *
     * @param   JDatabaseDriver  $db  Database connector object
     *
     * @since   1.6
     */
    public function __construct(&$db)
    {
        parent::__construct('#__jp_reminders', 'id', $db);
    }

    public function store($updateNulls = false)
    {


        $date   = Factory::getDate()->toSql();
        $userId = Factory::getApplication()->getIdentity()->id;

        $this->ordering = is_null($this->ordering) ? 0 : $this->ordering;

        $this->modified = $date;

        if ($this->id)
        {
            // Existing item
            $this->modified_by = $userId;
        }
        else
        {
            // New contact. A contact created and created_by field can be set by the user,
            // so we don't touch either of these if they are set.
            if (!(int) $this->created)
            {
                $this->created = $date;
            }

            if (empty($this->created_by))
            {
                $this->created_by = $userId;
            }
        }

        // Set publish_up to null date if not set
        if (!$this->state)
        {
            $this->state = 1;
        }


        return parent::store($updateNulls);
    }
}
