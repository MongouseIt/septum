<?php
/**
 * @package      Joomproject
 * @subpackage   Projects
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;


jimport('joomla.application.component.controllerform');


class JPprojectsControllerProject extends FormController
{

	protected $widget_options = [
		// The input name
		'name' => '',

		// Context of the field
		// module, default
		'context' => 'default',

		// The field ID associated to this Gallery Manager, used to retrieve the field settings on AJAX actions
		'field_id' => null,

		// The item ID associated to this Gallery Manager, used to retrieve the field settings on AJAX actions
		'item_id' => null,

		/**
		 * Max file size in MB.
		 *
		 * Defults to 0 (no limit).
		 */
		'max_file_size' => 0,

		/**
		 * How many files we can upload.
		 *
		 * Defaults to 0 (no limit).
		 */
		'limit_files' => 0,

		// Allowed upload file types
		'allowed_file_types' => '.jpg, .jpeg, .png, .gif, .webp, image/webp',

		/**
		 * Original Image
		 */
		// Original image resize width
		'original_image_resize_width' => null,

		// Original image resize height
		'original_image_resize_height' => null,

		/**
		 * Thumbnails
		 */
		// Thumbnails width
		'thumb_width' => null,

		// Thumbnails height
		'thumb_height' => null,

		// Thumbnails resize method (crop, stretch, fit)
		'thumb_resize_method' => 'crop',

		// The list of tags already available for this gallery
		'tags' => [],

		// Open AI API Key
		'openai_api_key' => '',

		// The widget name
		'widget' => 'GalleryManager'
	];

	public function __construct($options = [])
	{
		parent::__construct($options);

	//	$this->prepare();
	}

	public function prepare()
	{

		// return json empty
		header('Content-Type: application/json');
		echo json_encode([]);

		$params = ComponentHelper::getParams('com_jpprojects');
		// Set gallery items
		$this->options['gallery_items'] = is_array($this->options['value']) ? $this->options['value'] : [];

		// Set css class for readonly state
		if ($this->options['readonly'])
		{
			$this->options['css_class'] .= ' readonly';
		}

		// Adds a css class when the gallery contains at least one item
		if (count($this->options['gallery_items']))
		{
			$this->options['css_class'] .= ' dz-has-items';
		}

		// Get the Open AI API key
		$this->options['openai_api_key'] =$params->get('openai_api_key');

		// Load translation strings
		Text::script('COM_JOOMPROJECT_GALLERY_MANAGER_CONFIRM_REGENERATE_IMAGES');
		Text::script('COM_JOOMPROJECT_GALLERY_MANAGER_CONFIRM_DELETE_ALL_SELECTED');
		Text::script('COM_JOOMPROJECT_GALLERY_MANAGER_CONFIRM_DELETE_ALL');
		Text::script('COM_JOOMPROJECT_GALLERY_MANAGER_CONFIRM_DELETE');
		Text::script('COM_JOOMPROJECT_GALLERY_MANAGER_FILE_MISSING');
		Text::script('COM_JOOMPROJECT_GALLERY_MANAGER_REACHED_FILES_LIMIT');
		Text::script('COM_JOOMPROJECT_GALLERY_GENERATE_IMAGE_DESC_TO_ALL_IMAGES_CONFIRM');

		$this->prepareTags();


	}

	public function prepareTags()
	{
		if (!is_array($this->options['gallery_items']))
		{
			return;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select([$db->quoteName('id'), $db->quoteName('title')])
			->from($db->quoteName('#__tags'))
			->where($db->quoteName('published') . ' = 1')
			->where($db->quoteName('level') . ' > 0');

		$db->setQuery($query);
		$tags = $db->loadAssocList('id', 'title');

		$this->options['tags'] = $tags;
	}

	public function getSettings($context)
	{
		// Make sure we have a valid context
		if (!$context)
		{
			return false;
		}

		$field_data = [];

		$input = Factory::getApplication()->input;

		if ($context === 'default')
		{
			// Make sure we have a valid field id
			if (!$field_id = $input->getInt('field_id',0))
			{
				$this->exitWithMessage('COM_JOOMPROJECT_GALLERY_MANAGER_FIELD_ID_ERROR');
			}

			if (!$field_data = JoomprojectGalleryManager::getData($field_id))
			{
				$this->exitWithMessage('COM_JOOMPROJECT_GALLERY_MANAGER_INVALID_FIELD_DATA');
			}
		}
		else if ($context === 'module')
		{
			// Make sure we have a valid item id
			if (!$item_id = $input->getInt('item_id',0))
			{
				$this->exitWithMessage('COM_JOOMPROJECT_GALLERY_MANAGER_ITEM_ID_ERROR');
			}

			if (!$field_data = JoomprojectGalleryManager::getModuleData($item_id))
			{
				$this->exitWithMessage('COM_JOOMPROJECT_GALLERY_MANAGER_INVALID_FIELD_DATA');
			}

			$field_data->set('style', $field_data->get('provider', 'grid'));
		}

		return $field_data;
	}

	/**
	 * The upload task called by the AJAX hanler
	 *
	 * @return  void
	 */
	public function ajaxGalleryUpload()
	{
		$input = Factory::getApplication()->getInput();

		// Make sure we have a valid context
		if (!$context = $input->get('context'))
		{
			$this->exitWithMessage('COM_JOOMPROJECT_GALLERY_MANAGER_CONTEXT_ERROR');
		}

		// Make sure we have a valid file passed
		if (!$file = $input->files->get('file'))
		{
			$this->exitWithMessage('COM_JOOMPROJECT_GALLERY_MANAGER_ERROR_INVALID_FILE');
		}

		if (!$field_data = ComponentHelper::getParams('com_jpprojects'))
		{
			$this->exitWithMessage('COM_JOOMPROJECT_GALLERY_MANAGER_INVALID_FIELD_DATA');
		}

		// get the media uploader file data, values are passed when we upload a file using the Media Uploader
		$media_uploader_file_data = [
			'is_media_uploader_file' => $input->get('media_uploader', false) == '1',
			'media_uploader_filename' => $input->getString('media_uploader_filename', '')
		];

		// In case we allow multiple uploads the file parameter is a 2 levels array.
		$first_property = array_pop($file);
		if (is_array($first_property))
		{
			$file = $first_property;
		}

		$style = $field_data->get('style', 'grid');

		$uploadSettings = [
			'allow_unsafe' => false,
			'allowed_types' => $field_data->get('allowed_file_types', $this->widget_options['allowed_file_types']),
			'style' => $style
		];

		// Add watermark
		if ($field_data->get('watermark.type', 'disabled') !== 'disabled')
		{
			$uploadSettings['watermark'] = (array) $field_data->get('watermark', []);
			$uploadSettings['watermark']['image'] = !empty($uploadSettings['watermark']['image']) ? explode('#', JPATH_SITE . DIRECTORY_SEPARATOR . $uploadSettings['watermark']['image'])[0] : null;
			$uploadSettings['watermark']['apply_on_thumbnails'] = $field_data->get('watermark.apply_on_thumbnails', false) === '1';
		}

		$field_data_array = $field_data->toArray();

		$resize_method = $field_data->get('resize_method', 'crop');
		$thumb_height = $field_data->get('thumb_height', null);

		switch ($style)
		{
			case 'slideshow':
				if (isset($field_data_array['slideshow_thumb_height']))
				{
					$thumb_height = $field_data_array['slideshow_thumb_height'];
				}

				if ($slideshow_resize_method = $field_data->get('slideshow_resize_method'))
				{
					$resize_method = $slideshow_resize_method;
				}
				break;
			case 'masonry':
				$thumb_height = null;
				break;
			case 'zjustified':
			case 'justified':
				$thumb_height = $field_data->get('justified_item_height', 200);
				break;
		}

		// resize image settings
		$resizeSettings = [
			'thumb_height' => $thumb_height,
			'thumb_resize_method' => $resize_method,

			// TODO: Remove this line when ACF is also updated, so we don't rely on this to resize the original image
			'original_image_resize' => false,

			'original_image_resize_width' => $field_data->get('original_image_resize_width'),
			'original_image_resize_height' => $field_data->get('original_image_resize_height')
		];

		/**
		 * For backwards compatibility.
		 *
		 * TODO: Update this code block to not rely on "original_image_resize" to resize original image when removed from ACF.
		 */
		$resize_original_image_setting_value = $field_data->get('original_image_resize', null);
		if ($style === 'slideshow' && ($resizeSettings['original_image_resize_width'] || $resizeSettings['original_image_resize_height']))
		{
			$resize_original_image_setting_value = true;
		}

		if ($resize_original_image_setting_value)
		{
			$resizeSettings['original_image_resize_height'] = $style === 'slideshow' ? $resizeSettings['original_image_resize_height'] : null;
			$resizeSettings['original_image_resize'] = $style === 'slideshow' ? true : $resize_original_image_setting_value;
		}
		else if (is_null($resize_original_image_setting_value) && ($resizeSettings['original_image_resize_width'] || $resizeSettings['original_image_resize_height']))
		{
			$resizeSettings['original_image_resize'] = true;
		}
		if (!$resizeSettings['original_image_resize'])
		{
			$resizeSettings['original_image_resize_width'] = null;
			$resizeSettings['original_image_resize_height'] = null;
		}

		if (in_array($style, ['grid', 'masonry', 'slideshow']))
		{
			$resizeSettings['thumb_width'] = $field_data->get('thumb_width');

			$slideshow_thumb_width = $field_data->get('slideshow_thumb_width');
			if (!is_null($slideshow_thumb_width) && $style === 'slideshow')
			{
				$resizeSettings['thumb_width'] = $slideshow_thumb_width;
			}
		}

		// Upload the file and resize the images as required
		if (!$uploaded_filenames = JoomProjectGalleryManager::upload($file, $uploadSettings, $media_uploader_file_data, $resizeSettings))
		{
			$this->exitWithMessage('COM_JOOMPROJECT_GALLERY_MANAGER_ERROR_CANNOT_UPLOAD_FILE');
		}

		header('Content-Type: application/json');
		echo json_encode([
			'source' => $uploaded_filenames['source'],
			'original' => $uploaded_filenames['original'],
			'thumbnail' => $uploaded_filenames['thumbnail'],
			'is_media_uploader_file' => $media_uploader_file_data['is_media_uploader_file']
		]);
		die;
	}

	/**
	 * The delete task called by the AJAX hanlder
	 *
	 * @return void
	 */
	public function ajaxGalleryDelete()
	{
		$input = Factory::getApplication()->input;

		// Get source image path.
		$source = $input->getString('source');

		// Make sure we have a valid file passed
		if (!$original = $input->getString('original'))
		{
			$this->exitWithMessage('COM_JOOMPROJECT_GALLERY_MANAGER_ERROR_INVALID_FILE');
		}

		// Make sure we have a valid file passed
		if (!$thumbnail = $input->getString('thumbnail'))
		{
			$this->exitWithMessage('COM_JOOMPROJECT_GALLERY_MANAGER_ERROR_INVALID_FILE');
		}

		if (!$context = $input->get('context'))
		{
			$this->exitWithMessage('COM_JOOMPROJECT_GALLERY_MANAGER_CONTEXT_ERROR');
		}

		if (!$field_data = componentHelper::getParams('jpprojects'))
		{
			$this->exitWithMessage('COM_JOOMPROJECT_GALLERY_MANAGER_INVALID_FIELD_DATA');
		}

		// Delete the source, original, and thumbnail file
		$deleted = JoomProjectGalleryManager::deleteFile($source, $original, $thumbnail);

		echo json_encode(['success' => $deleted]);
	}

	/**
	 * This task allows us to regenerate the images.
	 *
	 * @return void
	 */
	public function ajax_regenerate_images()
	{

		$input = Factory::getApplication()->input;

		// Make sure we have a valid context
		if (!$context = $input->get('context'))
		{
			echo json_encode(['success' => false, 'message' => Text::_('COM_JOOMPROJECT_GALLERY_MANAGER_CONTEXT_ERROR')]);
			die();
		}

		if (!$field_data = ComponentHelper::getParams('jpprojects'))
		{
			echo json_encode(['success' => false, 'message' => Text::_('COM_JOOMPROJECT_GALLERY_MANAGER_INVALID_FIELD_DATA')]);
			die();
		}

		$field_id = $input->getInt('field_id',0);
		$item_id = $input->getInt('item_id',0);

		$field_data_array = $field_data->toArray();

		$style = $field_data->get('style', 'grid');

		$resize_method = $field_data->get('resize_method', 'crop');
		$thumb_height = $field_data->get('thumb_height', null);

		switch ($style)
		{
			case 'slideshow':
				if (isset($field_data_array['slideshow_thumb_height']))
				{
					$thumb_height = $field_data_array['slideshow_thumb_height'];
				}

				if ($slideshow_resize_method = $field_data->get('slideshow_resize_method'))
				{
					$resize_method = $slideshow_resize_method;
				}
				break;
			case 'masonry':
				$thumb_height = null;
				break;
			case 'zjustified':
			case 'justified':
				$thumb_height = $field_data->get('justified_item_height', 200);
				break;
		}

		$resizeSettings = [
			'thumb_height' => $thumb_height,
			'thumb_resize_method' => $resize_method
		];

		if (in_array($style, ['grid', 'masonry', 'slideshow']))
		{
			$resizeSettings['thumb_width'] = $field_data->get('thumb_width');

			$slideshow_thumb_width = $field_data->get('slideshow_thumb_width');
			if (!is_null($slideshow_thumb_width) && $style === 'slideshow')
			{
				$resizeSettings['thumb_width'] = $slideshow_thumb_width;
			}
		}

		// TODO: Remove this line when ACF is also updated, so we don't rely on this to resize the original image
		$original_image_resize = false;

		$original_image_resize_width = $field_data->get('original_image_resize_width');
		$original_image_resize_height = $field_data->get('original_image_resize_height');

		/**
		 * For backwards compatibility.
		 *
		 * TODO: Update this code block to not rely on "original_image_resize" to resize original image when removed from ACF.
		 */
		$resize_original_image_setting_value = $field_data->get('original_image_resize', null);
		if ($style === 'slideshow' && ($original_image_resize_width || $original_image_resize_height))
		{
			$resize_original_image_setting_value = true;
		}

		if ($resize_original_image_setting_value)
		{
			$original_image_resize_height = $style === 'slideshow' ? $original_image_resize_height : null;
			$original_image_resize = $style === 'slideshow' ? true : $resize_original_image_setting_value;
		}
		else if (is_null($resize_original_image_setting_value) && ($original_image_resize_width || $original_image_resize_height))
		{
			$original_image_resize = true;
		}
		if (!$original_image_resize)
		{
			$original_image_resize_width = null;
			$original_image_resize_height = null;
		}

		$watermarkSettings = [];
		// Add watermark
		if ($field_data->get('watermark.type', 'disabled') !== 'disabled')
		{
			$watermarkSettings = (array) $field_data->get('watermark', []);
			$watermarkSettings['image'] = !empty($watermarkSettings['image']) ? explode('#', JPATH_SITE . DIRECTORY_SEPARATOR . $watermarkSettings['image'])[0] : null;
			$watermarkSettings['apply_on_thumbnails'] = $field_data->get('watermark.apply_on_thumbnails', false) === '1';
		}
		$watermarkEnabled = isset($watermarkSettings['type']) && $watermarkSettings['type'] !== 'disabled';
		$thumbnailWatermarkEnabled = isset($watermarkSettings['type']) && $watermarkSettings['type'] !== 'disabled' && $watermarkSettings['apply_on_thumbnails'];

		$items = $input->get('items', null, 'ARRAY');
		$items = json_decode($items[0], true);

		$ds = DIRECTORY_SEPARATOR;

		// Parse all images
		if (is_array($items) && count($items))
		{
			foreach ($items as &$item)
			{
				$sourceImage = isset($item['source']) ? $item['source'] : '';
				$originalImage = isset($item['original']) ? $item['original'] : '';
				$thumbnailImage = isset($item['thumbnail']) ? $item['thumbnail'] : '';
				$thumbnailImagePath = implode($ds, [JPATH_ROOT, $thumbnailImage]);

				$sourceImagePath = $sourceImage ? implode($ds, [JPATH_ROOT, $sourceImage]) : false;
				$sourceImageExists = $sourceImagePath && file_exists($sourceImagePath);
				$originalImagePath = implode($ds, [JPATH_ROOT, $originalImage]);
				$originalImageExists = $originalImagePath && file_exists($originalImagePath);

				// If source image does not exist, watermark is enabled, create it by clothing the original image
				if (!$sourceImageExists && $watermarkEnabled && $originalImage && file_exists($originalImagePath))
				{
					// Create source from original image
					$sourceImagePath = JoomprojectFile::copy($originalImagePath, $originalImagePath, false, true);
					$sourceImageExists = true;

					// Modify the database entry and add "source" image to item
					// We just need the relative path to file
					$_sourceImagePath = str_replace(JPATH_ROOT . DIRECTORY_SEPARATOR, '', $sourceImagePath);
					$item['source'] = $_sourceImagePath;
					$_originalImagePath = str_replace(JPATH_ROOT . DIRECTORY_SEPARATOR, '', $originalImagePath);
					JoomProjectGalleryManager::setItemFieldSource($item_id, $field_id, $_sourceImagePath, $_originalImagePath);
				}

				if (!$originalImageExists)
				{
					continue;
				}

				if (!$sourceImageExists)
				{
					$sourceImagePath = $originalImagePath;
				}

				/**
				 * Handle original image.
				 */
				// Generate original image by using the source image
				if ($original_image_resize_width && $original_image_resize_height)
				{
					$originalImagePath = JoomprojectImage::resize($sourceImagePath, $original_image_resize_width, $original_image_resize_height, 70, 'crop', $originalImagePath);
				}
				else if ($original_image_resize_width)
				{
					$originalImagePath = JoomprojectImage::resizeAndKeepAspectRatio($sourceImagePath, $original_image_resize_width, 70, $originalImagePath);
				}
				else if ($original_image_resize_height)
				{
					$originalImagePath = JoomprojectImage::resizeByHeight($sourceImagePath, $original_image_resize_height, $originalImagePath, 70);
				}

				$originalImageSourcePath = $originalImagePath;

				if ($watermarkEnabled)
				{
					$payload = array_merge($watermarkSettings, ['source' => $sourceImagePath, 'destination' => $originalImagePath]);
					JoomprojectImage::applyWatermark($payload);
				}

				/**
				 * Handle thumbnail image.
				 */
				// Generate thumbnail image by using the source image
				JoomProjectGalleryManager::generateThumbnail($sourceImagePath, $thumbnailImagePath, $resizeSettings, null, false);

				// Apply watermark to thumbnail image
				if ($watermarkEnabled && $thumbnailWatermarkEnabled)
				{
					$payload = array_merge($watermarkSettings, ['source' => $thumbnailImagePath]);
					JoomprojectImage::applyWatermark($payload);
				}
			}
		}


		echo json_encode(['success' => true, 'message' => Text::_('COM_JOOMPROJECT_GALLERY_MANAGER_IMAGES_REGENERATED'), 'items' => $items]);
		exit;
	}

	/**
	 * Exits the page with given message.
	 *
	 * @param   string  $translation_string
	 *
	 * @return  void
	 */
	public function exitWithMessage($translation_string)
	{
		http_response_code('500');
		die(Text::_($translation_string));
	}

	public function ajax_generate_caption()
	{
		set_time_limit(300); // 5 Minutes
		ini_set('memory_limit', '-1');

		$fullURL = Uri::root() . Factory::getApplication()->input->getString('image');

		$imageToText = new \NRFramework\AI\TextGeneration\ImageToText();
		$generated = $imageToText->generate($fullURL);

		echo json_encode($generated);
	}

	/**
     * The prefix to use with controller messages.
     *
     * @var    string
     */
    protected $text_prefix = "COM_JOOMPROJECT_PROJECT";





    /**
     * Method to get a model object, loading it if required.
     *
     * @param     string    $name      The model name. Optional.
     * @param     string    $prefix    The class prefix. Optional.
     * @param     array     $config    Configuration array for model. Optional.
     *
     * @return    object               The model.
     */
    public function getModel($name = 'Project', $prefix = 'JPprojectsModel', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }


    /**
     * Method to run batch operations.
     *
     * @param   object  $model  The model.
     *
     * @return  boolean   True if successful, false otherwise and internal error is set.
     *
     * @since   1.6
     */
    public function batch($model = null)
    {
        $this->checkToken();

        // Set the model
        $model = $this->getModel('Project', 'JPprojectsModel', []);

        // Preset the redirect
        $this->setRedirect(Route::_('index.php?option=com_jpprojects&view=projects' . $this->getRedirectToListAppend(), false));

        return parent::batch($model);
    }


    /**
     * Method to save a record.
     *
     * @param     string     $key       The name of the primary key of the URL variable.
     * @param     string     $urlVar    The name of the URL variable if different from the primary key.
     *
     * @return    boolean               True if successful, false otherwise.
     */
    public function save($key = null, $urlVar = null)
    {
        $data = \Joomla\CMS\Factory::getApplication()->input->post->get('jform',[],'array');


        $task = $this->getTask();

        // Separate the different component rules before passing on the data
        if (isset($data['rules'])) {
            $rules = $data['rules'];

            if (isset($data['rules']['com_jpprojects'])) {
                $data['rules'] = $data['rules']['com_jpprojects'];

                unset($rules['com_jpprojects']);
            }

            $data['component_rules'] = $rules;
        }

        if ($task == 'save2copy') {

            $recordId = \Joomla\CMS\Factory::getApplication()->input->getUInt('id');

            JPprojectsHelper::adjustDataForSaveToCopy($data,$recordId);
        }

        $this->input->post->set('jform', $data);

        return parent::save($key, $urlVar);
    }


	public function postSaveHook(\Joomla\CMS\MVC\Model\BaseDatabaseModel $model, $validData = [])
	{

		$id     = $model->getState('project.id', 0);
		$input = Factory::getApplication()->getInput();
		$data = [];
		$data["id"] = $id;
		$jform = $input->get('jform',[],'array');

		$gallery_items = $jform['gallery'];

		if(!$id){
			return;
		}

		// save gallery
		$project = new stdClass();
		$project->id = $id;
		$project->gallery_items =  $this->processFiles($gallery_items, $id);

		$result = Factory::getDbo()->updateObject('#__jp_projects', $project, 'id');


	}


	/**
	 * Processes the files.
	 *
	 * Either duplicates the files or uploads them to final directory.
	 *
	 * @param   array   $fields
	 * @param   array   $fieldsData
	 * @param   object  $item
	 *
	 * @return  void
	 */
	private function processFiles($fieldsData, $id)
	{

		$fieldparams = \Joomla\CMS\Component\ComponentHelper::getParams('com_jpprojects');


		if (!$fieldsData || !$id)
		{
			return;
		}

		// Whether we should clean up the temp folder at the end of this process
		$should_clean = false;

		$value = $fieldsData;

		// Check if value can be json_decoded
		if (is_string($value))
		{
			if ($decoded = json_decode($value, true))
			{
				$value = $decoded;
			}
		}

		// $value is a string when batching an item
		if (!is_array($value))
		{
			$value = [];
		}



		if (JoomprojectGalleryManager::isCopying())
		{
			// Duplicate files
			JoomprojectGalleryHelper::duplicateFiles($value);
		}
		else
		{
			// We should run our cleanup routine at the end
			$should_clean = true;

			// Move to final folder
			$items = JoomprojectGalleryManager::moveTempItemsToDestination($value, $fieldparams, $this->getDestinationFolder($fieldparams, $id));

		}

		if ($should_clean)
		{
			// Clean old files from temp folder
			JoomprojectGalleryManager::clean();
		}

		$gallery = [];
		$gallery['items']  = $items;

		return json_encode($gallery);
	}

	/**
	 * Returns the destination folder.
	 *
	 * @param   object  $field
	 * @param   array   $item
	 *
	 * @return  string
	 */
	private function getDestinationFolder($fieldparams, $id)
	{
		$ds = DIRECTORY_SEPARATOR;
		$destination_folder = null;

		// Make field params use Registry
		if (!$fieldparams instanceof Registry)
		{
			$fieldparams = new Registry($fieldparams);
		}

		switch ($fieldparams->get('upload_folder_type', 'auto'))
		{
			case 'auto':
			default:
				// Get context and remove `com_` part
				$context = preg_replace('/^com_/', '', Factory::getApplication()->input->get('option'));
				$destination_folder = ['media', 'com_joomproject','gallery', $context, $id];
				break;
			case 'custom':

				$upload_folder = trim(ltrim($fieldparams->get('upload_folder'), $ds), $ds);

				// Smart Tags Instance
				$st = new JoomprojectSmartTags();

				$custom_tags = [
					'item_id' => $id,
				];

				$st->add($custom_tags, 'field.');

				// Replace Smart Tags
				$upload_folder = $st->replace($upload_folder);

				$destination_folder = [$upload_folder];
				break;
		}

		return implode($ds, array_merge([JPATH_ROOT], $destination_folder)) . $ds;
	}

	/**
	 * Returns a category alias by its ID.
	 *
	 * @param   int     $cat_id
	 *
	 * @return  string
	 */
	private function getCategoryAlias($cat_id = null)
	{
		if (!$cat_id)
		{
			return;
		}

		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select($db->quoteName('alias'))
			->from($db->quoteName('#__categories'))
			->where($db->quoteName('id') . ' = ' . (int) $cat_id);
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Transforms the field into a DOM XML element and appends it as a child on the given parent.
	 *
	 * @param   stdClass    $field   The field.
	 * @param   DOMElement  $parent  The field node parent.
	 * @param   Form        $form    The form.
	 *
	 * @return  DOMElement
	 *
	 * @since   3.7.0
	 */
	/*public function onCustomFieldsPrepareDom($field, DOMElement $parent, Joomla\CMS\Form\Form $form)
	{
		if (!$fieldNode = parent::onCustomFieldsPrepareDom($field, $parent, $form))
		{
			return $fieldNode;
		}

		$fieldNode->setAttribute('field_id', $field->id);

		return $fieldNode;
	}*/

	/**
	 * The form event. Load additional parameters when available into the field form.
	 * Only when the type of the form is of interest.
	 *
	 * @param   JForm     $form  The form
	 * @param   stdClass  $data  The data
	 *
	 * @return  void
	 */
	/*public function onContentPrepareForm(Joomla\CMS\Form\Form $form, $data)
	{
		// Make sure we are manipulating the right field.
		if (isset($data->type) && $data->type != $this->_name)
		{
			return;
		}

		$result = parent::onContentPrepareForm($form, $data);

		// Display the server's maximum upload size in the field's description
		$max_upload_size_str = HTMLHelper::_('number.bytes', Utility::getMaxUploadSize());
		$field_desc = $form->getFieldAttribute('max_file_size', 'description', null, 'fieldparams');
		$form->setFieldAttribute('max_file_size', 'description', Text::sprintf($field_desc, $max_upload_size_str), 'fieldparams');

		// Set the Field ID in Upload Folder Type description (if field is saved), otherwise, show FIELD_ID placeholder.
		// ITEM_ID is not replaceable in the field settings.
		$field_id = isset($data->id) ? $data->id : 'FIELD_ID';
		$upload_folder_type_desc = $form->getFieldAttribute('upload_folder_type', 'description', null, 'fieldparams');
		$form->setFieldAttribute('upload_folder_type', 'description', Text::sprintf($upload_folder_type_desc, $field_id), 'fieldparams');

		return $result;
	}*/


}
