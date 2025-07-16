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

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

$list_order  = $this->escape($this->state->get('list.ordering'));
$list_dir    = $this->escape($this->state->get('list.direction'));
$sort_fields = $this->getSortFields();
?>
<div id="filter-bar" class="btn-toolbar d-flex justify-content-between mb-4">
    <div class="input-group">
        <input type="text" class="form-control" name="filter_search" placeholder="<?php echo Text::_('COM_JPACTIVITIES_FILTER_SEARCH_DESC'); ?>" id="filter_search"
               value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
               title="<?php echo Text::_('COM_JPACTIVITIES_FILTER_SEARCH_DESC'); ?>"
        />
        <button class="btn tip  btn-success" type="submit" title="<?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
        <button class="btn btn-danger tip hasTooltip" type="button" onclick="document.getElementById('filter_search').value='';this.form.submit();" title="<?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i>
        </button>
    </div>
    <div class="d-flex justify-content-start">
        <select name="directionTable" id="directionTable" class="form-select" onchange="Joomla.orderTable()">
            <option value=""><?php echo Text::_('JFIELD_ORDERING_DESC'); ?></option>
            <option value="asc" <?php if ($list_dir == 'asc') echo 'selected="selected"'; ?>><?php echo Text::_('JGLOBAL_ORDER_ASCENDING'); ?></option>
            <option value="desc" <?php if ($list_dir == 'desc') echo 'selected="selected"'; ?>><?php echo Text::_('JGLOBAL_ORDER_DESCENDING');  ?></option>
        </select>
        <select name="sortTable" id="sortTable" class="form-select" onchange="Joomla.orderTable()">
            <option value=""><?php echo Text::_('JGLOBAL_SORT_BY');?></option>
            <?php echo HTMLHelper::_('select.options', $sort_fields, 'value', 'text', $list_order); ?>
        </select>
        <?php echo $this->pagination->getLimitBox(); ?>
    </div>
</div>

