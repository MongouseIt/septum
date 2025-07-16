<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jpmilestones
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


use Joomla\CMS\Table\Table;


if (version_compare(JVERSION, '3.2.0', '>=')) {
    class JPtableMilestoneCompat extends Table
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
    class JPtableMilestoneCompat extends Table
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