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

$list_order = $current->escape($current->state->get('list.ordering'));
$list_dir   = $current->escape($current->state->get('list.direction'));

?>

<?php if ($current->params->get('show_filter', '1')) : ?>
    <div class="collapse bg-light p-3 rounded mb-4" id="filters">

        <div class="row">
            <div class="col-md-3 mb-2 mb-xs-2">
                <input type="text" class="form-control" name="filter_search"
                       placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>" id="filter_search"
                       value="<?php echo $current->escape($current->state->get('filter.search')); ?>"
                />
            </div>
			<?php if ($current->state->get('filter.project')) : ?>
                <div class="col-md-3 mb-2 mb-xs-2">
                    <select id="filter_author" name="filter_author" class="form-control"
                            onchange="this.form.submit()">
                        <option value=""><?php echo Text::_('JOPTION_SELECT_AUTHOR'); ?></option>
						<?php echo HTMLHelper::_('select.options', $current->authors, 'value', 'text', $current->state->get('filter.author'), true); ?>
                    </select>
                </div>
			<?php endif; ?>

			<?php if ($current->access->get('core.edit.state') || $current->access->get('core.edit')) : ?>
                <div class="col-md-3 mb-2 mb-xs-2">
                    <select onchange="this.form.submit()" class="form-control" name="filter_published"
                            id="filter_published">
                        <option value=""><?php echo Text::_('JOPTION_SELECT_PUBLISHED'); ?></option>
						<?php echo HTMLHelper::_('select.options', HTMLHelper::_('jgrid.publishedOptions'), 'value', 'text', $current->state->get('filter.published'), true); ?>
                    </select>
                </div>

			<?php endif; ?>

            <div class="col-md-3 mb-2 mb-xs-2">
                <select name="filter_category" class="form-control"
                        onchange="this.form.submit()">
                    <option value=""><?php echo Text::_('JOPTION_SELECT_CATEGORY'); ?></option>
                    <?php echo HTMLHelper::_('select.options', HTMLHelper::_('category.options', 'com_jpmilestones'), 'value', 'text', $current->state->get('filter.category')); ?>
                </select>
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


        </div>

		<?php if ($current->state->get('filter.project')) : ?>
            <div class="row mt-2">
                <div class="col-12">
                    <div class="filter-labels">
						<?php echo HTMLHelper::_('jphtml.label.filter', 'com_jpmilestones.milestone', $current->state->get('filter.project'), $current->state->get('filter.labels'), '', false); ?>
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
<?php endif; ?>

