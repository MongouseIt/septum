<?php
/**
 * JoomProject By JoomBoost
 * a component for Joomla 5 (http://www.joomla.org)
 * Author Website: https://www.joomboost.com/
 * @copyright Copyright (C) 2012 JoomBoost (http://www.joomboost.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;


class JoomprojectTableImport extends Table
{

    public function __construct(&$_db)
    {
        parent::__construct('#__jp_import', 'id', $_db);
    }
    public function check()
    {
        if ($this->ctime == '' || $this->ctime == '0000-00-00 00:00:00' || is_null($this->ctime))
        {
            $this->ctime = Factory::getDate()->toSql();
        }
        return true;
    }
}
