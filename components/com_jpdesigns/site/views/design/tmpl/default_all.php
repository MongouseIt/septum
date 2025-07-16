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
use Joomla\CMS\Router\Route;

HTMLHelper::_('jphtml.script.listform');

$visible     = (int) $this->params->get('show_all_visible', 0);
$item        = &$this->item;
$revision    = &$this->revision;
$filter_in   = ($this->model_revisions->getState('filter.isset') ? 'in ' : '');
$show_all    = (($filter_in || $visible) ? 'in ' : '');
$return_page = JPdesignsHelperRoute::getDesignRoute($item->slug, $item->project_slug, $item->album_slug, ($revision ? $revision->slug : '0:original'));

$x       = 0;
$per_row = (int) $this->params->get('show_all_row_limit', 4);
$span    = (12 / $per_row);
?>
<form action="<?php echo htmlspecialchars(Route::_($return_page)); ?>" method="post" name="adminForm" id="adminForm"  autocomplete="off">
    <div class="">
        <a data-bs-toggle="collapse" data-bs-target="#all-revisions" class="btn btn-light btn-sm border"><i class="fas fa-list"></i> <?php echo Text::_('COM_JOOMPROJECT_DESIGNS_SHOW_ALL');?></a>
        <?php echo $this->toolbar_rev; ?>
    </div>
    <!-- Start Filters -->
    <div class="<?php echo $filter_in; ?>collapse mt-3" id="filters">
        <?php echo $this->loadTemplate('filters'); ?>
    </div>
    <!-- End Filters -->
    <div id="all-revisions" class="<?php echo $show_all; ?>collapse mt-3">
        <div class="row">
            <?php
            foreach ($this->revisions AS $i => $rev) :
                $rev_approved = (int) $rev->approved_count;
                $rev_declined = (int) $rev->declined_count;

                if ($x == $per_row) {
                    $x = 0;
                    ?>
                    </div><div class="row">
                    <?php
                }
            ?>
            <div class="col-md-<?php echo $span; ?>">
                <div class="thumbnail">
                    <a href="<?php echo Route::_(JPdesignsHelperRoute::getDesignRoute($item->slug, $item->project_slug, $item->album_slug, $rev->slug)); ?>">
                        <img src="<?php echo $rev->preview_source; ?>" alt="<?php echo $this->escape(addslashes($rev->title)); ?>"/>
                    </a>
                    <div class="clearfix"></div>
                    <div>
                        <label for="cb<?php echo $i; ?>" class="checkbox float-start">
                            <?php echo HTMLHelper::_('jp.html.id', $i, $rev->id); ?>
                        </label>
                        <?php if ($rev_approved) : ?>
                            <span class="badge bg-success"><i class="fas fa-thumbs-up"></i> <?php echo $rev_approved; ?></span>
                        <?php endif; ?>
                        <?php if ($rev_declined) : ?>
                            <span class="badge bg-success"><i class="fas fa-thumbs-down"></i> <?php echo (int) $rev_declined; ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
            <?php
                $x++;
                endforeach;
            ?>
            <!-- End of revisions loop -->

            <?php if ($x == $per_row) : ?>
                </div><div class="row">
            <?php endif; ?>

            <!-- Show the original design here too -->
            <div class="col-md-<?php echo $span; ?>">
                <div class="thumbnail">
                    <a href="<?php echo Route::_(JPdesignsHelperRoute::getDesignRoute($item->slug, $item->project_slug, $item->album_slug, '0:original')); ?>">
                        <img src="<?php echo $item->preview_source; ?>" alt="<?php echo $this->escape(addslashes($item->title)); ?>"/>
                    </a>
                    <?php if (count($item->approved)) : ?>
                        <span class="badge bg-success"><i class="fas fa-thumbs-up"></i> <?php echo count($item->approved); ?></span>
                    <?php endif; ?>
                    <?php if (count($item->declined)) : ?>
                        <span class="badge bg-danger"><i class="fas fa-thumbs-down"></i> <?php echo count($item->declined); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <!-- End of original design -->

        </div>
    </div>

    <input type="hidden" name="filter_project" value="<?php echo (int) $item->project_id; ?>" />
    <input type="hidden" name="filter_album" value="<?php echo (int) $item->album_id; ?>" />
    <input type="hidden" name="id" value="<?php echo (int) $item->id; ?>" />
    <input type="hidden" name="revision" value="" />
    <input type="hidden" name="return" value="" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" id="boxchecked" name="boxchecked" value="0" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
<hr />
