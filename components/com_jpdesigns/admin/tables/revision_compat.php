<?php
/**
 * @package      Joomproject Pro
 * @subpackage   Designs
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2006-2014 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Table\Table;


if (version_compare(JVERSION, '3.2.0', '>=')) {
    class JPtableRevisionCompat extends Table
    {
        protected function _getAssetParentIdCompat($table = null, $id = null)
        {
            return 1;
        }

        protected function _getAssetParentId(Table $table = null, $id = null)
        {
            return $this->_getAssetParentIdCompat($table, $id);
        }
    }
}
else {
    class JPtableRevisionCompat extends Table
    {
        protected function _getAssetParentIdCompat($table = null, $id = null)
        {
            return 1;
        }

        protected function _getAssetParentId($table = null, $id = null)
        {
            return $this->_getAssetParentIdCompat($table, $id);
        }
    }
}