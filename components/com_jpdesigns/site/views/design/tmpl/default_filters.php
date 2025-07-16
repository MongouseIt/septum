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

$access = JPdesignsHelper::getActions($this->item->id);
?>
    <div class="input-group">
        <input type="text" class="form-control" name="filter_search" placeholder="<?php echo Text::_('JSEARCH_FILTER_SEARCH'); ?>" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>"/>
            <button type="submit" class="btn btn-secondary" title="<?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?>">
                <i class="fas fa-search"></i>
            </button>
            <button type="button" class="btn btn-danger" title="<?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value=''; this.form.submit();">
                <i class="fas fa-times"></i>
            </button>
        <?php if ($access->get('core.edit.state') || $access->get('core.edit')) : ?>
            <select name="filter_published" class="form-control" onchange="this.form.submit()">
                <option value=""><?php echo Text::_('JOPTION_SELECT_PUBLISHED');?></option>
                <?php echo HTMLHelper::_('select.options', HTMLHelper::_('jgrid.publishedOptions'), 'value', 'text', $this->model_revisions->getState('filter.published'), true);?>
            </select>

        <?php endif; ?>
    </div>
