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
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('bootstrap.dropdown');
HTMLHelper::_('bootstrap.collapse');
$project = (int) $this->state->get('filter.project');

if ($project)
{
	HTMLHelper::_('jphtml.script.jquerysortable');
}

HTMLHelper::_('jphtml.script.listform');

$filter_in  = ($this->state->get('filter.isset') ? 'in ' : '');
$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));

$album      = (int) $this->state->get('filter.album');
$state      = $this->state->get('filter.published');
$user       = Factory::getApplication()->getIdentity();
$uid        = $user->get('id');
$can_zip    = class_exists('ZipArchive');
$can_order  = $user->authorise('core.edit.state', 'com_jpdesigns');
$item_order = array();

list($prev_w, $prev_h) = explode('x', $this->params->get('img_preview_size', '300x200'), 2);
?>
<style type="text/css">
    .row-album .thumbnail {
        margin-bottom: 10px;
    }

    .row-fluid .thumbnails.thumbnails-designs > li[class*="span"]:first-child, .row-fluid .thumbnails.thumbnails-designs > li[class*="span"] {
        margin-left: 0.5em;
    }
</style>
<script>
    jQuery(document).ready(function () {
        jQuery('a.thumbnail').tooltip();
		<?php if ($can_order && $project > 0) : ?>
        JPlist.sortable('.list-designs', 'designs');
		<?php endif; ?>

        var url_a = window.location.hash.substring(1);

        if (url_a.length) {
            var design_id = url_a.replace('_', '');
            var design = jQuery('#design_' + design_id);

            if (design.length) {
                var design_pos = design.offset().top;
                jQuery('html, body').animate({scrollTop: design_pos}, 'fast');
            }
        }
    });
</script>
<div id="joomproject" class="category-list<?php echo $this->pageclass_sfx; ?> view-designs">

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

    <div class="clearfix"></div>
    <div class="grid">
        <form name="adminForm" id="adminForm" action="<?php echo Route::_(JPdesignsHelperRoute::getDesignsRoute()); ?>"
              method="post" enctype="multipart/form-data" autocomplete="off">
            <!-- Start Toolbar -->
            <div class="mb-4">
				<?php echo $this->toolbar; ?>
                <div class="filter-project btn-group">
					<?php echo HTMLHelper::_('jphtml.project.filter'); ?>
                </div>
            </div>
            <!-- End Toolbar -->
            <div class="clearfix"></div>
            <!-- Start Filters -->
            <div class="<?php echo $filter_in; ?>collapse" id="filters">
				<?php echo $this->loadTemplate('filters'); ?>
            </div>
            <!-- End Filters -->
            <div class="clearfix"></div>

            <!-- Start hidden upload field -->
            <div style="display: none !important;">
                <input type="hidden" name="id" id="jform_id" value="0"/>
                <input type="hidden" name="jform[parent_id]" id="jform_parent_id" value=""/>
                <input type="file" name="jform[file]" id="quick-upload" accept="image/jpeg,image/png,image/gif"
                       onchange="jQuery('#jform_task').val('revisionform.upload');this.form.submit();"/>
            </div>
            <!-- End hidden upload field -->
            <div class="row list-designs">

				<?php
				$k        = 0;
				foreach ($this->items as $i => $item) :
					$access = JPdesignsHelper::getActions($item->id);
					$link = JPdesignsHelperRoute::getDesignRoute($item->slug, $item->project_slug, $item->album_slug, '0:original');

					$item_order[] = $item->ordering;

					$can_create   = $access->get('core.create');
					$can_edit     = $access->get('core.edit');
					$can_checkin  = ($user->authorise('core.manage', 'com_checkin') || $item->checked_out == $uid || $item->checked_out == 0);
					$can_edit_own = ($access->get('core.edit.own') && $item->created_by == $uid);
					$can_change   = ($access->get('core.edit.state') && $can_checkin);
					$can_download = ($access->get('core.admin') || $access->get('core.download'));
					$upload       = (int) $item->params->get('designs_col_upload');

					// Get display params
					$cols = (int) $item->params->get('designs_col_limit', 2);
					$prev = (int) $item->params->get('designs_prev_img', 0);
					$span = (12 / $cols);

					// Override image path?
					if ($prev == 1 && !is_null($item->revision))
					{
						$item->preview_source = $item->revision->preview_source;
						$link                 = JPdesignsHelperRoute::getDesignRoute($item->slug, $item->project_slug, $item->album_slug, $item->revision->slug);
					}

					// Prepare the watch button
					$watch = '';

					if ($uid)
					{
						$options = array('div-class' => 'float-end', 'a-class' => 'btn-sm float-end');
						$watch   = HTMLHelper::_('jphtml.button.watch', 'designs', $i, $item->watching, $options);
					}

					// Prepare title
					$title         = '<h5 class="d-inline-block mb-0"><a href="' . Route::_($link) . '">' . $this->escape($item->title) . '</a></h5>';
					$project_title = '<a href="' . JPdesignsHelperRoute::getDesignsRoute($item->project_slug) . '">' . $this->escape($item->project_title) . '</a>';
					$album_title   = '<a href="' . JPdesignsHelperRoute::getDesignsRoute($item->project_slug, $item->album_slug) . '">' . $this->escape($item->album_title) . '</a>';

					/*if (!$this->state->get('filter.project') && !$this->state->get('filter.album')) {
						if (!$item->album_id) {
							$lang = 'COM_JOOMPROJECT_DESIGNS_DESIGN_TITLE_IN_PROJECT';
							$title .= ' <small>' . Text::sprintf($lang, $project_title) . '</small>';
						}
						else {
							$lang = 'COM_JOOMPROJECT_DESIGNS_DESIGN_TITLE_IN_PROJECT_ALBUM';
							$title .= ' <small>' . Text::sprintf($lang, $project_title, $album_title) . '</small>';
						}
					}
					elseif (!$this->state->get('filter.project')) {
						$lang = 'COM_JOOMPROJECT_DESIGNS_DESIGN_TITLE_IN_PROJECT';
						$title .= ' <small>' . Text::sprintf($lang, $project_title) . '</small>';
					}
					elseif (!$this->state->get('filter.album') && $item->album_id) {
						$lang = 'COM_JOOMPROJECT_DESIGNS_DESIGN_TITLE_IN_ALBUM';
						$title .= ' <small>' . Text::sprintf($lang, $album_title) . '</small>';
					}*/
					?>
                    <div class="col-md-<?php echo $span; ?> mb-4">
                        <div class="card">
                            <input type="hidden" name="order[]" value="<?php echo (int) $item->ordering; ?>"/>
                            <!-- Start design heading -->
                            <a id="design_<?php echo (int) $item->id; ?>"></a>
                            <div class=" design-title d-none d-lg-block card-header">
								<?php if ($can_change || $watch) : ?>
                                    <label for="cb<?php echo $i; ?>" class="checkbox float-start">
										<?php echo HTMLHelper::_('jp.html.id', $i, $item->id); ?>
                                    </label>
								<?php endif; ?>

								<?php
								$this->menu->start(array('class' => 'btn-sm btn-link'));
								$this->menu->itemEdit('designform', $item->id, ($can_edit || $can_edit_own));
								$this->menu->itemTrash('designs', $i, ($can_change && $state != '-2'));
								if ($can_download)
								{
									$this->menu->itemDivider();
									$this->menu->itemLink('fas fa-download', 'JACTION_DOWNLOAD', Route::_($link . '&tmpl=component&layout=download&format=raw'));

									if ($can_zip)
									{
										$this->menu->itemLink('fas fa-cube', 'JACTION_DOWNLOAD_ALL', Route::_($link . '&tmpl=component&layout=downloadAll&format=raw'));
									}
								}
								$this->menu->end();
								echo $this->menu->render(array('class' => 'btn-sm btn-mini'));
								?>

								<?php echo $watch; ?>
                                &nbsp;<?php echo $title; ?>
								<?php if ($item->approved_count) : ?>
                                    <span class="badge bg-success"><i
                                                class="fas fa-thumbs-up"></i> <?php echo (int) $item->approved_count; ?></span>
								<?php endif; ?>
								<?php if ($item->declined_count) : ?>
                                    <span class="badge bg-danger"><i
                                                class="fas fa-thumbs-down"></i> <?php echo (int) $item->declined_count; ?></span>
								<?php endif; ?>
                                <div class="clearfix"></div>
                            </div>
                            <!-- End design heading -->
                            <div class="card-body">
                                <!-- Start design image -->
                                <div id="album<?php echo $item->id; ?>">
                                    <div class="row row-album">

                                        <div class="col-md-12">
                                            <div class="text-center">
                                                <a class="d-inline" href="<?php echo Route::_($link); ?>">
                                                    <img class="w-100" src="<?php echo $item->preview_source; ?>"
                                                         alt="<?php echo $this->escape($item->title); ?>"/>
                                                </a>
                                            </div>
                                        </div>

										<?php if ($can_create && $upload) : ?>
                                            <div class="col-md-12 mt-3">
                                                <div class="text-center">
                                                    <a class="btn btn-sm btn-light border bg-light " style="cursor: pointer;"
                                                       onclick="jQuery('#jform_parent_id').val(<?php echo $item->id; ?>);jQuery('#quick-upload').click();"
                                                       rel="tooltip"
                                                       title="<?php echo addslashes(Text::_('COM_JOOMPROJECT_DESIGNS_UPLOAD_TT')); ?>"
                                                    >
                                                        <i class="fas fa-upload"></i> <?php echo addslashes(Text::_('COM_JOOMPROJECT_DESIGNS_UPLOAD_TT')); ?>
                                                    </a>
                                                </div>
                                            </div>
										<?php endif; ?>
                                    </div>
                                </div>
                                <!-- End design image -->
                                <?php echo \Joomla\CMS\Layout\LayoutHelper::render('project.link',['item' => $item,'class' => 'mt-3'],'',['client' => 'site','component' =>  'com_jpprojects']) ?>
                            </div>



                        </div>
                    </div>
				<?php
					//  $k = 1 - $k;
				endforeach;
				?>

            </div>

			<?php echo LayoutHelper::render('common.pagination', ['pagination' => $this->pagination, 'params' => $this->params]) ?>

            <input type="hidden" name="item-order-<?php echo $k; ?>" id="item_order_<?php echo $k; ?>"
                   value="<?php echo implode( '|',$item_order); ?>"/>
            <input type="hidden" id="boxchecked" name="boxchecked" value="0"/>
            <input type="hidden" name="task" id="jform_task" value=""/>
            <input type="hidden" name="return" value=""/>
			<?php echo HTMLHelper::_('form.token'); ?>
        </form>
    </div>
	<?php if ($can_order) : ?>
		<?php if (!$this->state->get('filter.project')) : ?>
            <div class="alert"><?php echo Text::_('COM_JOOMPROJECT_REORDER_DISABLED'); ?></div>
		<?php else: ?>
            <div class="alert alert-success"><?php echo Text::_('COM_JOOMPROJECT_REORDER_ENABLED'); ?></div>
		<?php endif; ?>
	<?php endif; ?>
</div>
