<?php

/**
 * @package      Joomproject
 * @subpackage   Projects
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

extract($displayData);
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;


$app =  Factory::getApplication();
/*if (!$item_id && $context === 'module')
{
	echo Text::_('COM_JOOMPROJECT_GALLERY_MANAGER_PLEASE_SAVE_ITEM_FIRST');
	return;
}*/
$component_params = ComponentHelper::getParams('com_jpprojects');

$gallery_items = $value ? $value : [];

$context ="default";
$widget = "GalleryManager";
$item_id = $app->getInput()->getInt('id', 0);
$openai_api_key = $component_params->get('openai_api_key', 0);
//$name = $component_params->get('name', 'com_jpprojects[gallery][items][]');
$max_file_size = $component_params->get('max_file_size', 0);
$limit_files = $component_params->get('limit_files', 0);
$allowed_file_types = $component_params->get('allowed_file_types', '.jpg, .jpeg, .png, .gif, .webp, image/webp');
HTMLHelper::_('bootstrap.dropdown');


if (!$disabled)
{
	HTMLHelper::_('bootstrap.modal');

	if (strpos($css_class, 'ordering-default') !== false)
	{
		HTMLHelper::script('com_joomproject/sortable.min.js', ['relative' => true, 'version' => 'auto']);
	}

		HTMLHelper::_('bootstrap.dropdown', '.dropdown-toggle');
		
		$doc = Factory::getApplication()->getDocument();
		$doc->addScriptOptions('media-picker', [
			'images' => array_map(
				'trim',
				explode(
					',',
					ComponentHelper::getParams('com_media')->get(
						'image_extensions',
						'bmp,gif,jpg,jpeg,png'
					)
				)
			)
		]);

		$wam = $doc->getWebAssetManager();
		$wam->useScript('webcomponent.media-select');
		
		Text::script('JFIELD_MEDIA_LAZY_LABEL');
		Text::script('JFIELD_MEDIA_ALT_LABEL');
		Text::script('JFIELD_MEDIA_ALT_CHECK_LABEL');
		Text::script('JFIELD_MEDIA_ALT_CHECK_DESC_LABEL');
		Text::script('JFIELD_MEDIA_CLASS_LABEL');
		Text::script('JFIELD_MEDIA_FIGURE_CLASS_LABEL');
		Text::script('JFIELD_MEDIA_FIGURE_CAPTION_LABEL');
		Text::script('JFIELD_MEDIA_LAZY_LABEL');
		Text::script('JFIELD_MEDIA_SUMMARY_LABEL');

}

// Use admin gallery manager path if browsing via backend
$gallery_manager_path = Factory::getApplication()->isClient('administrator') ? 'administrator/' : '';

// Javascript files should always load as they are used to populate the Gallery Manager via Dropzone
HTMLHelper::script('com_joomproject/dropzone.min.js', ['relative' => true, 'version' => 'auto']);
HTMLHelper::script(Uri::root().'media/com_joomproject/js/manager_init.js', ['relative' => true, 'version' => 'auto']);
HTMLHelper::script(Uri::root().'media/com_joomproject/js//manager.js', ['relative' => true, 'version' => 'auto']);
$load_stylesheet = 1;
if ($load_stylesheet)
{


	HTMLHelper::stylesheet(Uri::root().'media/com_joomproject/css/gallerymanager.css', ['relative' => true, 'version' => 'auto']);
}

$tags = isset($tags) ? $tags : [];

$ai_icon = '<svg width="24" height="22" viewBox="0 0 24 22" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M23.7471 8.7502C20.687 8.11795 18.3098 5.71543 17.6775 2.65536C17.6523 2.50362 17.5258 2.40247 17.3488 2.40247C17.197 2.40247 17.0706 2.50362 17.02 2.65536C16.3878 5.71543 13.9852 8.11795 10.9505 8.7502C10.7987 8.77549 10.6976 8.90194 10.6976 9.07897C10.6976 9.2307 10.7987 9.35715 10.9505 9.40773C14.0105 10.04 16.3878 12.4425 17.02 15.4773C17.0453 15.629 17.1718 15.7302 17.3488 15.7302C17.5005 15.7302 17.627 15.629 17.6775 15.4773C18.3098 12.4172 20.7123 10.04 23.7471 9.40773C23.8988 9.38244 24 9.25599 24 9.07897C24 8.90194 23.8988 8.77549 23.7471 8.7502Z" fill="currentColor"/><path d="M13.5554 15.882C11.1022 15.3762 9.18022 13.4542 8.67443 10.9758C8.64914 10.8493 8.54798 10.7734 8.42153 10.7734C8.29508 10.7734 8.19392 10.8493 8.16863 10.9758C7.66284 13.4542 5.74081 15.3762 3.28771 15.882C3.16126 15.9073 3.08539 16.0084 3.08539 16.1349C3.08539 16.2613 3.16126 16.3625 3.28771 16.3878C5.74081 16.8936 7.66284 18.8156 8.16863 21.2687C8.19392 21.3951 8.29508 21.471 8.42153 21.471C8.54798 21.471 8.64914 21.3951 8.67443 21.2687C9.18022 18.8156 11.1022 16.8936 13.5554 16.3878C13.6818 16.3625 13.7577 16.2613 13.7577 16.1349C13.7577 16.0084 13.6818 15.9073 13.5554 15.882Z" fill="currentColor"/><path d="M4.83035 10.0906C4.88093 10.2424 5.00737 10.3435 5.15911 10.3435C5.31085 10.3435 5.46259 10.2424 5.48788 10.0906C6.01897 7.83983 7.83983 6.04426 10.0653 5.51317C10.2171 5.46259 10.3182 5.33614 10.3182 5.1844C10.3182 5.03266 10.2171 4.88093 10.0653 4.85564C7.78925 4.29926 6.01897 2.52898 5.48788 0.252898C5.46259 0.101159 5.31085 0 5.15911 0C5.00737 0 4.85564 0.101159 4.83035 0.252898C4.27397 2.52898 2.52898 4.29926 0.252898 4.85564C0.101159 4.90622 0 5.03266 0 5.1844C0 5.33614 0.101159 5.48788 0.252898 5.51317C2.4784 6.04426 4.27397 7.83983 4.83035 10.0906Z" fill="currentColor"/></svg>';
?>
<!-- Gallery Manager -->
<div
	class="nrf-widget tf-gallery-manager<?php echo $css_class; ?>"
	data-context="<?php echo $context; ?>"
	data-field-id="<?php echo $field_id; ?>"
	data-item-id="<?php echo $item_id; ?>"
	data-widget="<?php echo $widget; ?>"
>
	<?php if ($required) { ?>
		<!-- Make Joomla client-side form validator happy by adding a fake hidden input field when the Gallery is required. -->
		<input type="hidden" required class="required" id="<?php echo $id; ?>"/>
	<?php } ?>

	<!-- Actions -->
	<div class="tf-gallery-actions">
		<div class="btn-group tf-gallery-actions-dropdown " title="<?php echo Text::_('COM_JOOMPROJECT_GALLERY_MANAGER_SELECT_UNSELECT_IMAGES'); ?>">
			<button class="btn btn-secondary add tf-gallery-actions-dropdown-current tf-gallery-actions-dropdown-action select" onclick="return false;"><i class="me-2 icon-checkbox-unchecked"></i></button>
			<button class="btn btn-secondary add dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" title="<?php echo Text::_('COM_JOOMPROJECT_GALLERY_MANAGER_ADD_DROPDOWN'); ?>">
				<span class="caret"></span>
			</button>
			<ul class="dropdown-menu">
				<li><a href="#" class="dropdown-item tf-gallery-actions-dropdown-action select"><?php echo Text::_('COM_JOOMPROJECT_GALLERY_MANAGER_SELECT_ALL_ITEMS'); ?></a></li>
				<li><a href="#" class="dropdown-item tf-gallery-actions-dropdown-action unselect is-hidden"><?php echo Text::_('COM_JOOMPROJECT_GALLERY_MANAGER_UNSELECT_ALL_ITEMS'); ?></a></li>
			</ul>
		</div>
		<a class="tf-gallery-regenerate-images-button icon-button" title="<?php echo Text::_('COM_JOOMPROJECT_GALLERY_MANAGER_REGENERATE_IMAGES_TITLE'); ?>">
			<i class="icon-refresh"></i>
			<div class="message"></div>
		</a>
		<a class="tf-gallery-remove-selected-items-button icon-button" title="<?php echo Text::_('COM_JOOMPROJECT_GALLERY_MANAGER_REMOVE_SELECTED_IMAGES'); ?>">
			<i class="icon-trash"></i>
		</a>


		<div class="btn-group add-button dropdown">
			<button class="btn btn-success add tf-gallery-add-item-button" onclick="return false;" title="<?php echo Text::_('COM_JOOMPROJECT_GALLERY_MANAGER_ADD_IMAGES'); ?>"><i class="me-2 icon-pictures"></i><?php echo Text::_('COM_JOOMPROJECT_GALLERY_MANAGER_ADD_IMAGES'); ?></button>
			<button class="btn btn-success add dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" title="<?php echo Text::_('COM_JOOMPROJECT_GALLERY_MANAGER_ADD_DROPDOWN'); ?>">
				<span class="caret"></span>
			</button>
			<ul class="dropdown-menu">
				<li>
					<a href="#" data-bs-toggle="modal" data-bs-target="#tf-GalleryMediaManager-<?php echo $id ?>" class="dropdown-item tf-gallery-browse-item-button popup" title="<?php echo Text::_('COM_JOOMPROJECT_GALLERY_MANAGER_BROWSE_MEDIA_LIBRARY'); ?>"><i class="me-2 icon-folder-open"></i><?php echo Text::_('COM_JOOMPROJECT_GALLERY_MANAGER_BROWSE_MEDIA_LIBRARY'); ?></a>
				</li>
			</ul>
		</div>
		<span class="tf-gallery-ai-status"><?php echo Text::_('COM_JOOMPROJECT_GALLERY_GENERATED_IMAGE_DESCRIPTIONS'); ?></span>
		<input type="hidden" class="media_uploader_file" id="<?php echo $id; ?>_uploaded_file" />
	</div>
	<!-- /Actions -->

	<!-- Dropzone -->
	<div
		data-inputname="<?php echo $name; ?>"
		data-maxfilesize="<?php echo $max_file_size; ?>"
		data-maxfiles="<?php echo $limit_files; ?>"
		data-acceptedfiles="<?php echo $allowed_file_types; ?>"
		data-value='<?php echo $gallery_items ? json_encode($gallery_items, JSON_HEX_APOS) : ''; ?>'
		data-baseurl="<?php echo Uri::base(); ?>"
		data-rooturl="<?php echo Uri::root(); ?>"
		class="tf-gallery-dz">
		<!-- DZ Message Wrapper -->
		<div class="dz-message">
			<!-- Message -->
			<div class="dz-message-center">
				<span class="text"><?php echo Text::_('COM_JOOMPROJECT_GALLERY_MANAGER_DRAG_AND_DROP_TEXT'); ?></span>
				<span class="browse"><?php echo Text::_('COM_JOOMPROJECT_GALLERY_MANAGER_BROWSE'); ?></span>
			</div>
			<!-- /Message -->
		</div>
		<!-- /DZ Message Wrapper -->
	</div>
	<!-- /Dropzone -->

	<!-- Dropzone Preview Template -->
	<template class="previewTemplate">
		<div class="tf-gallery-preview-item template" data-item-id="">
			<div class="checkmark-edited-icon"><?php echo Text::_('COM_JOOMPROJECT_GALLERY_UPDATED'); ?></div>
			<div class="select-item-checkbox" title="<?php echo Text::_('COM_JOOMPROJECT_GALLERY_MANAGER_CHECK_TO_DELETE_ITEMS'); ?>">
				<input type="checkbox" id="<?php echo $name; ?>[select-item]" />
				<label for="<?php echo $name; ?>[select-item]">
					<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><mask id="mask0_279_439" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="20" height="20"><rect width="20" height="20" fill="#D9D9D9"/></mask><g mask="url(#mask0_279_439)"><path d="M8.83342 13.8333L14.7084 7.95829L13.5417 6.79163L8.83342 11.5L6.45841 9.12496L5.29175 10.2916L8.83342 13.8333ZM10.0001 18.3333C8.8473 18.3333 7.76397 18.1145 6.75008 17.677C5.73619 17.2395 4.85425 16.6458 4.10425 15.8958C3.35425 15.1458 2.7605 14.2638 2.323 13.25C1.8855 12.2361 1.66675 11.1527 1.66675 9.99996C1.66675 8.84718 1.8855 7.76385 2.323 6.74996C2.7605 5.73607 3.35425 4.85413 4.10425 4.10413C4.85425 3.35413 5.73619 2.76038 6.75008 2.32288C7.76397 1.88538 8.8473 1.66663 10.0001 1.66663C11.1529 1.66663 12.2362 1.88538 13.2501 2.32288C14.264 2.76038 15.1459 3.35413 15.8959 4.10413C16.6459 4.85413 17.2397 5.73607 17.6772 6.74996C18.1147 7.76385 18.3334 8.84718 18.3334 9.99996C18.3334 11.1527 18.1147 12.2361 17.6772 13.25C17.2397 14.2638 16.6459 15.1458 15.8959 15.8958C15.1459 16.6458 14.264 17.2395 13.2501 17.677C12.2362 18.1145 11.1529 18.3333 10.0001 18.3333ZM10.0001 16.6666C11.8612 16.6666 13.4376 16.0208 14.7292 14.7291C16.0209 13.4375 16.6667 11.8611 16.6667 9.99996C16.6667 8.13885 16.0209 6.56246 14.7292 5.27079C13.4376 3.97913 11.8612 3.33329 10.0001 3.33329C8.13897 3.33329 6.56258 3.97913 5.27091 5.27079C3.97925 6.56246 3.33341 8.13885 3.33341 9.99996C3.33341 11.8611 3.97925 13.4375 5.27091 14.7291C6.56258 16.0208 8.13897 16.6666 10.0001 16.6666Z" fill="currentColor"/></g></svg>
				</label>
			</div>
			<div class="tf-gallery-preview-item--actions">
				<a href="#" class="tf-gallery-preview-edit-item" title="<?php echo Text::_('COM_JOOMPROJECT_GALLERY_MANAGER_CLICK_TO_EDIT_ITEM'); ?>"  data-bs-toggle="modal" data-bs-target="#tf-GalleryEditItem-<?php echo $id; ?>"><svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><mask id="mask0_279_182" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="20" height="20"><rect width="20" height="20" fill="#D9D9D9"/></mask><g mask="url(#mask0_279_182)"><path d="M4.16667 15.8333H5.35417L13.5 7.6875L12.3125 6.5L4.16667 14.6458V15.8333ZM2.5 17.5V13.9583L13.5 2.97917C13.6667 2.82639 13.8507 2.70833 14.0521 2.625C14.2535 2.54167 14.4653 2.5 14.6875 2.5C14.9097 2.5 15.125 2.54167 15.3333 2.625C15.5417 2.70833 15.7222 2.83333 15.875 3L17.0208 4.16667C17.1875 4.31944 17.309 4.5 17.3854 4.70833C17.4618 4.91667 17.5 5.125 17.5 5.33333C17.5 5.55556 17.4618 5.76736 17.3854 5.96875C17.309 6.17014 17.1875 6.35417 17.0208 6.52083L6.04167 17.5H2.5ZM12.8958 7.10417L12.3125 6.5L13.5 7.6875L12.8958 7.10417Z" fill="currentColor"/></g></svg></a>
				<a href="#" class="tf-gallery-preview-remove-item" title="<?php echo Text::_('COM_JOOMPROJECT_GALLERY_MANAGER_CLICK_TO_DELETE_ITEM'); ?>" data-dz-remove><svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><mask id="mask0_279_185" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="20" height="20"><rect width="20" height="20" fill="#D9D9D9"/></mask><g mask="url(#mask0_279_185)"><path d="M5.83301 17.5C5.37467 17.5 4.98231 17.3368 4.65592 17.0104C4.32954 16.684 4.16634 16.2917 4.16634 15.8333V5H3.33301V3.33333H7.49967V2.5H12.4997V3.33333H16.6663V5H15.833V15.8333C15.833 16.2917 15.6698 16.684 15.3434 17.0104C15.017 17.3368 14.6247 17.5 14.1663 17.5H5.83301ZM14.1663 5H5.83301V15.8333H14.1663V5ZM7.49967 14.1667H9.16634V6.66667H7.49967V14.1667ZM10.833 14.1667H12.4997V6.66667H10.833V14.1667Z" fill="currentColor"/></g></svg></a>
			</div>
			<div class="dz-status"></div>
			<div class="dz-thumb">
				<div class="tf-gallery-preview-item--temp-label" title="<?php echo Text::_('COM_JOOMPROJECT_GALLERY_TEMPORARY_IMAGE_TITLE'); ?>"><?php echo Text::_('COM_JOOMPROJECT_GALLERY_TEMPORARY'); ?></div>
				<div class="dz-progress"><span class="text"><?php echo Text::_('COM_JOOMPROJECT_GALLERY_MANAGER_UPLOADING'); ?></span><span class="dz-upload" data-dz-uploadprogress></span></div>
				<div class="tf-gallery-preview-in-queue"><?php echo Text::_('COM_JOOMPROJECT_GALLERY_MANAGER_IN_QUEUE'); ?></div>
				<img data-dz-thumbnail />
			</div>
			<div class="tf-gallery-preview-item--alt">
				<?php if ($pro): ?>
				<div class="tf-gallery-preview-item--alt--existing"></div>
				<?php endif; ?>
				<div class="tf-gallery-preview-item--alt--fields">
					<textarea name="<?php echo $name; ?>[alt]" class="item-alt" placeholder="<?php echo Text::_('COM_JOOMPROJECT_GALLERY_MANAGER_IMAGE_DESCRIPTION_HINT'); ?>" title="<?php echo Text::_('COM_JOOMPROJECT_GALLERY_MANAGER_ALT_HINT'); ?>" rows="2"></textarea>


				</div>
			</div>
			<div class="tf-gallery-preview-error"><div data-dz-errormessage></div></div>
			<input type="hidden" value="" class="item-source" name="<?php echo $name; ?>[source]" />
			<input type="hidden" value="" class="item-original" name="<?php echo $name; ?>[image]" />
			<input type="hidden" value="" class="item-thumbnail" name="<?php echo $name; ?>[thumbnail]" />
			<input type="hidden" value="" class="item-caption" name="<?php echo $name; ?>[caption]" />
			<input type="hidden" value="" class="item-tags" name="<?php echo $name; ?>[tags]" />
		</div>
	</template>
	<!-- /Dropzone Preview Template -->

	<?php
	if (!$disabled)
	{




			$opts = [
				'title'       => Text::_('COM_JOOMPROJECT_GALLERY_MANAGER_SELECT_ITEM'),
				'url' 		  => Route::_(Uri::root() . $gallery_manager_path . '?option=com_media&view=media&tmpl=component'),
				'height'      => '400px',
				'width'       => '800px',
				'bodyHeight'  => 80,
				'modalWidth'  => 80,
				'backdrop' 	  => 'static',
				'footer'      => '<button type="button" class="btn btn-primary tf-gallery-button-save-selected" data-bs-dismiss="modal">' . Text::_('JSELECT') . '</button>' . '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">' . Text::_('JCANCEL') . '</button>',
				'isJoomla' 	  => true
			];
			HTMLHelper::_('bootstrap.modal', '#tf-GalleryMediaManager-' . $id, $opts);

			$layoutData = [
				'selector' => 'tf-GalleryMediaManager-' . $id,
				'params'   => $opts,
				'body'     => ''
			];
	
			echo LayoutHelper::render('libraries.html.bootstrap.modal.main', $layoutData);

		if (!$readonly)
		{
			// Print Edit Modal
			$opts = [
				'title'       => Text::_('COM_JOOMPROJECT_GALLERY_MANAGER_EDIT_ITEM'),
                'height'      => '100%',
                'width'       => '100%',
				'backdrop' 	  => 'static',
				'footer'      => '<button type="button" class="btn btn-primary tf-gallery-button-save-edited-item" data-bs-dismiss="modal" data-dismiss="modal">' . Text::_('COM_JOOMPROJECT_GALLERY_SAVE') . '</button>' .
								 '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">' . Text::_('JCANCEL') . '</button>'
			];
	
			$content = LayoutHelper::render('edit', [
				'tags' => $tags
			], __DIR__);
	
			echo HTMLHelper::_('bootstrap.renderModal', 'tf-GalleryEditItem-' . $id, $opts, $content);
		}
	}
	?>
</div>
<!-- /Gallery Manager -->