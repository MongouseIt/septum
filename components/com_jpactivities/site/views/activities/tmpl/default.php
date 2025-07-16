<?php
/**
 * @package      pkg_jpactivities
 * @subpackage   com_jpactivities
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2013 JoomBoost.com. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;


HTMLHelper::_('bootstrap.tooltip');

$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir = $this->escape($this->state->get('list.direction'));
$date_format = $this->params->get('date_format', Text::_('DATE_FORMAT_LC1'));
$date_rel = (int)$this->params->get('date_relative', 1);
?>
<form action="<?php echo Route::_(JPactivitiesHelperRoute::getActivitiesRoute()); ?>" method="post"
      name="adminForm" id="adminForm" autocomplete="off">
    <!-- Start Filters -->
    <div class="row">
        <?php if ($this->params->get('show_filter_search')) : ?>
            <div class="col-md-4 mb-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control"
                           placeholder="<?php echo Text::_('COM_JPACTIVITIES_FILTER_SEARCH_DESC'); ?>"
                           name="filter_search" id="filter_search"
                           value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
                           title="<?php echo Text::_('COM_JPACTIVITIES_FILTER_SEARCH_DESC'); ?>"
                    />
                    <button class="btn btn-primary tip" data-toggle="tooltip" type="submit"
                            title="<?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?>">
                        <i class="fas fa-search"></i>
                    </button>
                    <button class="btn btn-danger  tip"
                            data-toggle="tooltip"
                            type="button"
                            onclick="document.getElementById('filter_search').value='';this.form.submit();"
                            title="<?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        <?php endif; ?>
        <?php if ($this->params->get('show_filter_extension')) : ?>
            <div class="col-md-4  mb-3">
                <select name="filter_extension" onchange="this.form.submit();" class="form-control">
                    <option value=""><?php echo Text::_('JOPTION_SELECT_EXTENSION'); ?></option>
                    <?php echo HTMLHelper::_('select.options', $this->extensions, 'value', 'text', $this->state->get('filter.extension')); ?>
                </select>
            </div>
        <?php endif; ?>
        <?php if ($this->params->get('show_filter_event')) : ?>
            <div class="col-md-4  mb-3">
                <select name="filter_event_id" onchange="this.form.submit();" class="form-control">
                    <option value=""><?php echo Text::_('JOPTION_SELECT_EVENT'); ?></option>
                    <?php echo HTMLHelper::_('select.options', $this->events, 'value', 'text', $this->state->get('filter.event_id')); ?>
                </select>
            </div>
        <?php endif; ?>
    </div>
    <!-- End Filters -->

    <!-- Start Items -->
    <div class="list-group list-group-flush list-group-striped">
        <?php
        foreach ($this->items as $i => $item) :
            $date = HTMLHelper::_('date', $item->created, $date_format);
            ?>
            <div class="list-group-item">
                <div class="row">
                    <div class="col-md-9">
                        <span class="row-title"><?php echo $item->text; ?></span>
                    </div>
                    <div class="col-md-3 text-right">
                        <?php
                        if ($date_rel) :
                            ?>
                            <span class="badge" data-toggle="tooltip" title="<?php echo $date; ?>" style="cursor: help;">
                                <i class="far fa-clock"></i>
                                <?php echo JPactivitiesHelper::relativeDateTime($item->created); ?>
                            </span>
                        <?php
                        else :
                            ?>
                            <span class="small">
                                <i class="far fa-clock"></i>
                                <?php echo $date; ?>
                            </span>
                        <?php
                        endif;
                        ?>
                    </div>
                </div>
            </div>

        <?php
        endforeach;
        ?>
    </div>
    <!-- End Items -->
    <div class="pagination">
        <?php echo $this->pagination->getPagesCounter(); ?>
        <?php echo $this->pagination->getPagesLinks(); ?>
    </div>
    <?php // echo $this->pagination->getListFooter(); ?>

    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="filter_order" value="<?php echo $list_order; ?>"/>
    <input type="hidden" name="filter_order_Dir" value="<?php echo $list_dir; ?>"/>
    <?php echo HTMLHelper::_('form.token'); ?>
</form>