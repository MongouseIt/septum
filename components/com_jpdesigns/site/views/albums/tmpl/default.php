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

$state      = $this->state->get('filter.published');
$user       = Factory::getApplication()->getIdentity();
$uid        = $user->get('id');
$can_order  = $user->authorise('core.edit.state', 'com_jpdesigns');
$item_order = array();

list($prev_w, $prev_h) = explode('x', $this->params->get('img_preview_size', '300x200'), 2);
?>
<style type="text/css">
    .row-album .thumbnail {
        margin-bottom: 10px;
    }
</style>
<script type="text/javascript">
    jQuery(document).ready(function () {
        jQuery('a.thumbnail').tooltip();
		<?php if ($can_order && $project > 0) : ?>
        JPlist.sortable('.list-albums', 'albums');
		<?php endif; ?>

        var url_a = window.location.hash.substring(1);

        if (url_a.length) {
            var album_id = url_a.replace('_', '');
            var album = jQuery('#album_' + album_id);

            if (album.length) {
                var album_pos = album.offset().top;
                jQuery('html, body').animate({scrollTop: album_pos}, 'fast');
            }
        }
    });
</script>
<div id="joomproject" class="category-list<?php echo $this->pageclass_sfx; ?> view-albums">

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
        <form name="adminForm" id="adminForm" action="<?php echo Route::_(JPdesignsHelperRoute::getAlbumsRoute()); ?>"
              method="post" enctype="multipart/form-data" autocomplete="off">
            <!-- Start Toolbar -->
            <div class="btn-toolbar btn-toolbar-top d-block mb-3" >
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
                <input type="hidden" name="id" value="0"/>
                <input type="hidden" name="jform[album_id]" id="jform_album_id" value=""/>
                <input type="file" name="jform[file]" id="quick-upload" accept="image/jpeg,image/png,image/gif"
                       onchange="jQuery('#jform_task').val('designform.upload');this.form.submit();"/>
            </div>
            <!-- End hidden upload field -->

            <ul class="list-albums unstyled">
				<?php
				$k = 0;
				foreach ($this->items as $i => $item) :
				$access = JPdesignsHelper::getAlbumActions($item->id);
				$link   = JPdesignsHelperRoute::getDesignsRoute($item->project_slug, $item->slug);
				$desc   = $this->escape(($item->description ? $item->description : Text::_('COM_JOOMPROJECT_DESIGNS_NO_DESCRIPTION')));

				$can_create   = $access->get('core.create');
				$can_edit     = $access->get('core.edit');
				$can_checkin  = ($user->authorise('core.manage', 'com_checkin') || $item->checked_out == $uid || $item->checked_out == 0);
				$can_edit_own = ($access->get('core.edit.own') && $item->created_by == $uid);
				$can_change   = ($access->get('core.edit.state') && $can_checkin);

				// Get display params
				$rows  = (int) $item->params->get('album_row_limit', 1);
				$limit = (int) $item->params->get('album_row_items', 8);
				$sdesc = (int) $item->params->get('show_album_desc', 1);
				$span  = (12 / $limit);

				// Get the first design to show as cover
				$item->designs = array_reverse($item->designs);
				$first         = array_pop($item->designs);
				$item->designs = array_reverse($item->designs);
				reset($item->designs);

				if (isset($first->placeholder))
				{
					$first_link = $link;
					$first_tt   = $item->title;
				}
				else
				{
					$first_link = JPdesignsHelperRoute::getDesignRoute($first->slug, $item->project_slug, $item->slug, '0:original');
					$first_tt   = $first->title;
				}

				// Prepare title
				$title         = '<a href="' . Route::_($link) . '">' . $this->escape($item->title) . '</a>';
				$project_title = '<a href="' . JPdesignsHelperRoute::getAlbumsRoute($item->project_slug) . '">' . $this->escape($item->project_title) . '</a>';

				if (!$this->state->get('filter.project'))
				{
					$lang  = 'COM_JOOMPROJECT_DESIGNS_ALBUM_TITLE_IN_PROJECT';
					$title .= ' <small>' . Text::sprintf($lang, $project_title) . '</small>';
				}
				?>
                <li>
                    <input type="hidden" name="order[]" value="<?php echo (int) $item->ordering; ?>"/>
                    <!-- Start album heading -->
                    <a id="design_<?php echo (int) $item->id; ?>"></a>
                    <h3>
						<?php if ($can_change) : ?>
                            <label for="cb<?php echo $i; ?>" class="checkbox float-start">
								<?php echo HTMLHelper::_('jp.html.id', $i, $item->id); ?>
                            </label>
						<?php endif; ?>
                        <div class="btn-group float-start">
							<?php
							$this->menu->start(array('class' => 'btn-mini'));
							$this->menu->itemEdit('albumform', $item->id, ($can_edit || $can_edit_own));
							$this->menu->itemTrash('albums', $i, ($can_change && $state != '-2'));
							$this->menu->end();
							echo $this->menu->render(array('class' => 'btn-mini'));
							?>
                        </div>
                        &nbsp;<?php echo $title; ?>
                        <div class="clearfix"></div>
                    </h3>
                    <!-- End album heading -->
                    <!-- Start designs -->
                    <a id="album_<?php echo (int) $item->id; ?>"></a>
                    <div class="row">
                        <div class="col-md-<?php echo($sdesc ? 8 : 12); ?>">
                            <a href="<?php echo $first_link; ?>" data-bs-toggle="tooltip"
                               data-placement="top" title="<?php echo addslashes($this->escape($first_tt)); ?>">
                                <img  class="img-thumbnail  w-100" src="<?php echo $first->cover_source; ?>"/>
                            </a>
                        </div>
						<?php if ($sdesc) : ?>
                            <div class="col-md-4">
                                <h3><?php echo Text::sprintf('COM_JOOMPROJECT_DESIGNS_ALBUM_DESIGN_COUNT', $item->design_count); ?></h3>
                                <p>
									<?php echo HTMLHelper::_('jphtml.label.author', $item->author_name, $item->created); ?>
									<?php echo HTMLHelper::_('jphtml.label.access', $item->access); ?>
                                </p>
                                <p>
									<?php echo $desc; ?>
                                </p>
								<?php if ($item->design_count) : ?>
                                    <p>
                                        <a class="btn btn-sm" href="<?php echo $link; ?>">
                                            <i class="fas fa-image"></i> <?php echo Text::_('COM_JOOMPROJECT_DESIGNS_SHOW_ALL'); ?>
                                        </a>
                                    </p>
								<?php endif; ?>
                            </div>
						<?php endif; ?>
                    </div>
                    <div class="clearfix">&nbsp;</div>
                    <div class="row">
						<?php
						$l = 0;
						foreach ($item->designs as $design)
						{
						$design_link = JPdesignsHelperRoute::getDesignRoute($design->slug, $item->project_slug, $item->slug, '0:original');

						if ($l == $limit)
						{
						$l = 0;
						?>

    </div>
    <div class="row mb-3">

			<?php
			}
			?>
            <div class="col-md-<?php echo $span; ?> mb-3">
				<?php if (isset($design->placeholder)) : ?>
					<?php if ($can_create) : ?>
                        <a class="w-100" style="cursor: pointer;"
                           onclick="jQuery('#jform_album_id').val(<?php echo $item->id; ?>);jQuery('#quick-upload').click();"
                           data-bs-toggle="tooltip" data-placement="top"
                           title="<?php echo addslashes(Text::_('COM_JOOMPROJECT_DESIGNS_UPLOAD_TT')); ?>"
                        >
                            <img class="w-100 img-thumbnail preview-placeholder" src="<?php echo $design->preview_source; ?>"/>
                        </a>
					<?php else : ?>
                        <a class="w-100" href="<?php echo Route::_($link); ?>">
                            <img class="w-100 img-thumbnail preview-placeholder" src="<?php echo $design->preview_source; ?>"/>
                        </a>
					<?php endif; ?>
				<?php else : ?>
                    <a class="w-100" href="<?php echo Route::_($design_link); ?>"
                       data-bs-toggle="tooltip" data-placement="top"
                       title="<?php echo addslashes($this->escape($design->title)); ?>"
                    >
                        <img class="w-100 img-thumbnail " src="<?php echo $design->preview_source; ?>"
                             alt="<?php echo $this->escape($design->title); ?>"/>
                    </a>
				<?php endif; ?>
            </div>
			<?php
			$l++;
			}
			?>
        </div>
        <hr/>
        </li>
		<?php
		$k = 1 - $k;
		endforeach;
		?>
        </ul>


		<?php echo LayoutHelper::render('common.pagination', ['pagination' => $this->pagination, 'params' => $this->params]) ?>


        <input type="hidden" id="boxchecked" name="boxchecked" value="0"/>
        <input type="hidden" name="task" id="jform_task" value=""/>
        <input type="hidden" name="return"
               value="<?php echo base64_encode(Route::_(JPdesignsHelperRoute::getAlbumsRoute(), false)); ?>"/>
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
