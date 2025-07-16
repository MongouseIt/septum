<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_joomproject
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2019 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */
# No Permission
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

extract($displayData);

$project    = (int) $current->state->get('filter.project');
$filter_in  = ($current->state->get('filter.isset') ? 'in ' : '');
$list_order = $current->escape($current->state->get('list.ordering'));
$list_dir   = $current->escape($current->state->get('list.direction'));

?>

<div class="<?php echo $filter_in; ?>collapse bg-light p-3 rounded mb-4" id="filters">

    <div class="row">
        <div class="col-md-3 mb-2 mb-xs-2">
            <input type="text" class="form-control" name="filter_search"
                   placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>" id="filter_search"
                   value="<?php echo $current->escape($current->state->get('filter.search')); ?>"/>
        </div>
        <div class="col-md-3 mb-2 mb-xs-2">
            <select name="filter_order" class="form-control" onchange="this.form.submit()">
				<?php echo HTMLHelper::_('select.options', $current->sort_options, 'value', 'text', $list_order, true); ?>
            </select>
        </div>
        <div class="col-md-3 mb-2 mb-xs-2">
            <select name="filter_order_Dir" class="form-control" onchange="this.form.submit()">
				<?php echo HTMLHelper::_('select.options', $current->order_options, 'value', 'text', $list_dir, true); ?>
            </select>
        </div>
        <div class="col-md-3 mb-2 mb-xs-2">

        </div>
    </div>
	<?php if ($project) : ?>
        <div class="row mt-2">
            <div class="col-12">
                <div class="filter-labels">
					<?php echo HTMLHelper::_('jphtml.label.filter', 'com_jprepo', $current->state->get('filter.project'), $current->state->get('filter.labels'),'',false); ?>
                </div>
            </div>
        </div>
	<?php endif; ?>


    <div class="row mt-3">
        <div class="col-12">
            <button type="submit" class="btn btn-success" data-bs-toggle="tooltip"
                    data-placement="top" title="<?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?>">
                <i class="fas fa-search"></i> <?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?>
            </button>
            <button type="button" class="btn btn-danger" data-bs-toggle="tooltip"
                    data-placement="top" title="<?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>"
                    onclick="document.getElementById('filter_search').value='';this.form.submit();">
                <i class="fas fa-times"></i> <?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>
            </button>
        </div>
    </div>

</div>
