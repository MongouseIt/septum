<?php
/**
 * @package      Joomproject Pro
 * @subpackage   Designs
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

?>
<div class="input-group">
    <input type="text" class="form-control" name="filter_search" placeholder="<?php echo Text::_('JSEARCH_FILTER_SEARCH'); ?>" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>"/>
        <button type="submit" class="btn btn-secondary" title="<?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?>">
            <i class="fas fa-search"></i>
        </button>
        <button type="button" class="btn btn-danger" title="<?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();">
            <i class="fas fa-times"></i>
        </button>
</div>
<div class="row my-4">
    <?php if ($this->state->get('filter.project')) : ?>
            <div class="col-md-3">
                <select name="filter_album" class="form-control" onchange="this.form.submit()">
                    <option value=""><?php echo Text::_('JOPTION_SELECT_DESIGN_ALBUM');?></option>
                    <?php echo HTMLHelper::_('select.options', $this->albums, 'value', 'text', $this->state->get('filter.album'));?>
                </select>
            </div>
            <div class="col-md-3">
                <select id="filter_author" name="filter_author" class="form-control" onchange="this.form.submit()">
                    <option value=""><?php echo Text::_('JOPTION_SELECT_AUTHOR');?></option>
                    <?php echo HTMLHelper::_('select.options', $this->authors, 'value', 'text', $this->state->get('filter.author'));?>
                </select>
            </div>
    <?php endif; ?>
    <?php if ($this->access->get('core.edit.state') || $this->access->get('core.edit')) : ?>
            <div class="col-md-3">
                <select name="filter_published" class="form-control" onchange="this.form.submit()">
                    <option value=""><?php echo Text::_('JOPTION_SELECT_PUBLISHED');?></option>
                    <?php echo HTMLHelper::_('select.options', HTMLHelper::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
                </select>
            </div>
    <?php endif; ?>
</div>
<?php if ($this->state->get('filter.project')) : ?>
    <div class="filter-labels">
        <?php echo HTMLHelper::_('jphtml.label.filter', 'com_jpdesigns.design', $this->state->get('filter.project'), $this->state->get('filter.labels')); ?>
    </div>
<?php endif; ?>
<div class="filters btn-toolbar mb-4">
    <div class="input-group">
        <select name="filter_order" class="form-control" onchange="this.form.submit()">
			<?php echo HTMLHelper::_('select.options', $this->sort_options, 'value', 'text', $list_order, true);?>
        </select>
        <select name="filter_order_Dir" class="form-control" onchange="this.form.submit()">
			<?php echo HTMLHelper::_('select.options', $this->order_options, 'value', 'text', $list_dir, true);?>
        </select>
    </div>
</div>







