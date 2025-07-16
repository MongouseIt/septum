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

HTMLHelper::_('bootstrap.dropdown');
HTMLHelper::_('bootstrap.collapse');

// Load the zoom jquery extension
HTMLHelper::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jpdesigns/helpers/html');
HTMLHelper::_('designscripts.jQueryZoom');

// Create shortcuts to some parameters.
$item = &$this->item;
$revision = &$this->revision;


$params = &$this->params;
$text = (!empty($revision) ? $revision->text : $item->text);
$text = (trim($text) == '' ? Text::_('COM_JOOMPROJECT_DESIGNS_NO_DESCRIPTION') : $text);

$approved_by = ($revision ? $revision->approved : $item->approved);
$declined_by = ($revision ? $revision->declined : $item->declined);
$return_page = JPdesignsHelperRoute::getDesignRoute($item->slug, $item->project_slug, $item->album_slug, ($revision ? $revision->slug : '0:original'));

$approved_count = count($approved_by);
$declined_count = count($declined_by);
$revision_count = count($this->revisions);

$isPDF = (bool)strpos($item->file_name, '.pdf');


?>
<script>
    jQuery(document).ready(function () {
        jQuery('#thumbnail_full').zoom({on: 'click'});
    });

    function confirmApprove(el) {
        jQuery('#' + el).parent().parent().hide();
        jQuery('#approve-confirm').show();
    }

    function confirmDecline(el) {
        jQuery('#' + el).parent().parent().hide();
        jQuery('#decline-confirm').show();
    }

    Joomla.submitbutton = function (task) {


        let fname = 'item-form';

        if (task == 'revisionform.add') {
            jQuery('#jform_id').val(0);
        }

        if (task == 'revisions.publish' || task == 'revisions.unpublish' || task == 'revisions.checkin' ||
            task == 'revisions.trash' || task == 'revisions.delete' || task == 'revisions.archive') {
            // Override target form name
            fname = 'adminForm';
        }

        Joomla.submitform(task, document.getElementById(fname));
    }
</script>
<style type="text/css">
    .thumbnails-designs .text-large {
        font-size: 16px;
        margin: 5px 0;
        display: block;
    }

    .row-comments img {
        float: left;
        margin-right: 10px;
    }

    .row-comments .btn-toolbar {
        margin: 0 0 0 26px;
    }

    .row-comments blockquote {
        margin-left: 26px;
        padding-left: 5px;
        font-size: 12px;
    }

    .row-versions {
        white-space: nowrap;
        width: 100%;
        overflow-x: auto;
        overflow-y: hidden;
        padding-bottom: 10px;
    }
</style>
<div id="joomproject" class="item-page view-design">

    <?php

    // load internal navigation
    echo JPhtmlNav::loadMain();
    ?>

    <?php
    // load header
    echo JPhtmlNav::loadHeader($this->params);
    ?>

    <?php
    // load project internal navigation
    echo JPhtmlNav::loadProject();
    ?>


    <form action="<?php echo htmlspecialchars(Route::_($return_page)); ?>" method="post" name="itemForm" id="item-form"
          enctype="multipart/form-data">
        <div class="mb-4">
            <?php echo $this->toolbar; ?>

            <!-- Start hidden approve confirmation buttons -->
            <span id="approve-confirm" style="display: none;">
                <div class="dropdownContainer d-inline">
                    <button class="btn dropdown-toggle btn-sm bg-success text-white" type="button"
                            data-bs-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo Text::_('COM_JOOMPROJECT_DESIGNS_WARNING_APPROVE_CONFIRM'); ?>
                    <!--<span class="caret"></span>-->
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                        <a class="dropdown-item" href="#"
                           onclick="Joomla.submitbutton('<?php echo($revision ? 'revisionform.approve' : 'designform.approve'); ?>')">
                            <i class="fas fa-thumbs-up"></i> <?php echo Text::_('COM_JPDESIGNS_ACTION_APPROVE'); ?>
                        </a>
                        </li>
                        <li>
                        <a class="dropdown-item" href="#"
                           onclick="jQuery('#approve-confirm').hide();jQuery('#approve-design').parent().parent().show();">
                            <i class="fas fa-times"></i> <?php echo Text::_('JCANCEL'); ?>
                        </a>
                        </li>
                    </ul>
                </div>
            </span>
            <!-- End hidden approve confirmation buttons -->
            <!-- Start hidden decline confirmation buttons -->
            <span id="decline-confirm" style="display: none;">
                <div class="dropdownContainer d-inline">
                    <button class="btn dropdown-toggle bg-warning btn-sm" type="button" data-bs-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo Text::_('COM_JOOMPROJECT_DESIGNS_WARNING_APPROVE_CONFIRM'); ?>
                        <!--<span class="caret"></span>-->
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                              <a class="dropdown-item" href="#"
                                 onclick="Joomla.submitbutton('<?php echo($revision ? 'revisionform.decline' : 'designform.decline'); ?>')">
                            <i class="fas fa-thumbs-down"></i> <?php echo Text::_('COM_JPDESIGNS_ACTION_DECLINE'); ?>
                        </a>
                        </li>
                      <li>
                           <a class="dropdown-item" href="#"
                              onclick="jQuery('#decline-confirm').hide();jQuery('#decline-design').parent().parent().show();">
                            <i class="fas fa-times"></i> <?php echo Text::_('JCANCEL'); ?>
                        </a>
                      </li>


                    </ul>
                </div>
            </span>
            <!-- End hidden decline confirmation buttons -->

        </div>
        <div class="page-header">
            <h2><?php echo $this->escape($item->title); ?></h2>
        </div>
        <div class="jp-tags clearfix mb-4">
            <div class="float-start">
                <?php if ($revision) : ?>
                    #<?php echo $revision->ordering . ' ' . $this->escape($revision->title); ?>
                    <?php echo HTMLHelper::_('jphtml.label.author', $revision->author_name, $revision->created); ?>
                    <?php echo HTMLHelper::_('jphtml.label.access', $revision->access); ?>
                <?php else: ?>

                    <?php echo Text::_('COM_JOOMPROJECT_HEADING_DESIGN_ORIGINAL'); ?>
                    <?php echo HTMLHelper::_('jphtml.label.author', $item->author_name, $item->created); ?>
                    <?php echo HTMLHelper::_('jphtml.label.access', $item->access); ?>
                <?php endif; ?>
            </div>
            <div class="btn-group float-end">
                <a data-bs-toggle="collapse" data-bs-target="#design-details" class="btn btn-secondary">
                    <?php echo Text::_('COM_JOOMPROJECT_DETAILS_LABEL'); ?> <span class="fas fa-caret-down"></span>
                </a>
            </div>

        </div>

        <?php echo($revision ? $revision->event->beforeDisplayContent : $item->event->beforeDisplayContent); ?>

        <div class="collapse mb-4" id="design-details">
            <div class="card card-body">
                <div class="item-description">
                    <?php echo $text; ?>
                </div>
                <!-- Start Approved/Declined user list -->
                <?php if ($approved_count || $declined_count) : ?>
                    <hr/>
                    <div>
                        <?php foreach ($approved_by as $approved) : ?>
                            <span class="badge bg-success"><i
                                        class="fas fa-thumbs-up"></i> <?php echo $this->escape($approved['author']); ?></span>
                        <?php endforeach; ?>
                        <?php foreach ($declined_by as $declined) : ?>
                            <span class="badge bg-danger"><i
                                        class="fas fa-thumbs-down"></i> <?php echo $this->escape($declined['author']); ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <!-- End Approved/Declined user list -->
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">

                <?php if ($isPDF): ?>

                    <iframe src="<?php echo JPdesignsHelper::getBaseUrl($item->project_id) . '/' . $item->file_name; ?>"
                            frameborder="0" class="w-100" style="min-height: 100vh"></iframe>

                <?php else: ?>

                    <div class="thumbnail" id="thumbnail_full" style="cursor: pointer;">
                        <img src="<?php echo($revision ? $revision->full_source : $item->full_source); ?>"
                             alt="<?php echo $this->escape(addslashes(($revision ? $revision->title : $item->title))); ?>"/>
                    </div>

                <?php endif; ?>

            </div>
        </div>
        <?php if (count($item->recent) > 0 && $this->params->get('show_recent', '1') == '1') : ?>
            <!-- Start recent revisions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="m-0">
                        <?php echo Text::_('COM_JOOMPROJECT_DESIGNS_RECENT'); ?>
                    </h3>
                </div>
                <div class="card-body">
                    <div class="d-flex">
                        <?php foreach ($item->recent as $i => $rev) :
                            $rev_approved = (int)$rev->approved_count;
                            $rev_declined = (int)$rev->declined_count;
                            ?>
                            <div class="rounded shadow-sm me-3">

                                <?php if ($isPDF): ?>
                                    <iframe src="<?php echo JPdesignsHelper::getBaseUrl($rev->project_id) . '/' . $rev->file_name; ?>"
                                            frameborder="0" width="100%" height="100vh"></iframe>
                                <?php else: ?>
                                    <a href="<?php echo Route::_(JPdesignsHelperRoute::getDesignRoute($item->slug, $item->project_slug, $item->album_slug, $rev->slug)); ?>">
                                        <img style="max-height: 150px" src="<?php echo $rev->preview_source; ?>"
                                             alt="<?php echo $this->escape(addslashes($item->title)); ?>"/>
                                    </a>
                                <?php endif; ?>
                                <!-- Need to add the approved/declined count and checkbox here somewhere -->
                            </div>

                        <?php endforeach; ?>
                        <?php if ($revision_count <= (int)$this->params->get('show_recent', '1')) : ?>
                            <a href="<?php echo Route::_(JPdesignsHelperRoute::getDesignRoute($item->slug, $item->project_slug, $item->album_slug, '0:original')); ?>">
                                <img class="img-polaroid" src="<?php echo $item->preview_source; ?>"
                                     alt="<?php echo $this->escape(addslashes($item->title)); ?>"/>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <!-- End recent revisions -->
        <?php endif; ?>


        <!-- Start hidden upload field -->
        <div style="display: none !important;">
            <input type="file" name="jform[file]" id="quick-upload" accept="image/jpeg,image/png,image/gif"
                   onchange="jQuery('#jform_task').val('revisionform.upload');this.form.submit();"/>
        </div>
        <!-- End hidden upload field -->

        <input type="hidden" name="view"
               value="<?php echo htmlspecialchars($this->get('Name'), ENT_COMPAT, 'UTF-8'); ?>"/>
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="return" value="<?php echo base64_encode(Route::_($return_page, false)); ?>"/>

        <input type="hidden" name="jform[parent_id]" id="jform_parent_id" value="<?php echo (int)$item->id; ?>"/>
        <input type="hidden" name="filter_parent_id" value="<?php echo (int)$item->id; ?>"/>
        <input type="hidden" name="id" id="jform_id"
               value="<?php echo intval(($revision ? $revision->id : $item->id)); ?>"/>
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>

    <!-- Start show all revisions -->
    <?php
    if ($this->params->get('show_all', '1') == '1') :
        echo $this->loadTemplate('all');
    endif;
    ?>
    <!-- End show all revisions -->

    <?php echo($revision ? $revision->event->afterDisplayContent : $item->event->afterDisplayContent); ?>
</div>