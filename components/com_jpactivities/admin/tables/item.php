<?php
/**
 * @package      pkg_jpactivities
 * @subpackage   com_jpactivities
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();
use Joomla\CMS\Table\Table;

/**
 * User Activity Item Table Class
 *
 */
class JPactivitiesTableItem extends Table
{
    /**
     * Constructor
     *
     * @param    object    $db    A database connector object
     */
    public function __construct($db)
    {
        parent::__construct('#__user_activity_items', 'asset_id', $db);

        // Disable asset tracking
        $this->_trackAssets = false;
    }
}
