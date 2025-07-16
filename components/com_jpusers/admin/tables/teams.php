<?php
/**
 * @package      Joomproject
 * @subpackage   Dashboard
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\Table\Table;


class JPTableTeams extends Table
{
    /**
     * Primary Key
     *
     * @var int
     */
    var $id = null;


    /**
     * Constructor
     *
     * @param object Database connector object
     */
    function __construct(& $db) {
        parent::__construct('#__jp_users_teams', 'id', $db);
        $this->setColumnAlias('published', 'state');
    }
}