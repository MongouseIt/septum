<?php
/**
 * @package      Joomproject
 * @subpackage   Tasks
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
<div id="filter-bar" class="btn-toolbar d-flex justify-content-between mb-3">
    <div class="mb-3 mb-md-0">
        <div class="input-group">
            <input type="text" id="filter_search" name="filter_search" class="form-control"
                   data-bs-toggle="tooltip" data-placement="bottom" placeholder="<?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?>"/>
            <button type="submit" data-bs-toggle="tooltip" class="btn btn-primary hasTooltip"
                    title="<?php echo Text::_('COM_JOOMPROJECT_SEARCH_FILTER_TOOLTIP'); ?>">
                <i class="icon-search"></i>
            </button>
            <button class="btn btn-danger hasTooltip" data-bs-toggle="tooltip" type="button"
                    onclick="document.getElementById('filter_search').value='';this.form.submit();"
                    title="<?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    <div class="d-flex justify-content-start align-items-start">

        <?php echo HTMLHelper::_('jphtml.project.filter');?>


        <select name="directionTable" id="directionTable" class="form-select" onchange="Joomla.orderTable()">
            <option value="">
                <?php echo Text::_('JFIELD_ORDERING_DESC'); ?>
            </option>
            <option value="asc" <?php if ($list_dir == 'asc') echo 'selected="selected"'; ?>>
                <?php echo Text::_('JGLOBAL_ORDER_ASCENDING'); ?>
            </option>
            <option value="desc" <?php if ($list_dir == 'desc') echo 'selected="selected"'; ?>>
                <?php echo Text::_('JGLOBAL_ORDER_DESCENDING');  ?>
            </option>
        </select>
        <select name="sortTable" id="sortTable" class="form-select" onchange="Joomla.orderTable()">
            <option value=""><?php echo Text::_('JGLOBAL_SORT_BY');?></option>
            <?php echo HTMLHelper::_('select.options', $sort_fields, 'value', 'text', $list_order); ?>
        </select>
        <?php echo $this->pagination->getLimitBox(); ?>
    </div>
</div>