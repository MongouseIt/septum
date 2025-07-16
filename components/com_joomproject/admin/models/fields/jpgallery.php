<?php
/**
 * @package      Joomproject
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Filesystem\Path;
use Joomla\Registry\Registry;

/**
 * Form Field class for selecting a project.
 *
 */
class JFormFieldJpgallery extends  FormField
{

    protected $type = 'jpgallery';
    /**
     * Generates the Gallery Field
     *
     * @return  string  The field input markup.
     */
    protected function getInput()
    {

        require_once JPATH_ADMINISTRATOR . '/components/com_joomproject/helpers/galleryhelper.php';

        $style = (string)$this->element['style'];
        $style = JoomprojectGalleryHelper::getStyle($style);

        $data = [
            'value' => $this->prepareValue(),
            'required' => (string)$this->element['required'] == 'true' ? true : false,
            'name' => (int)$this->element['limit_files'] == 1 ? $this->name . '[items][0]' : $this->name . '[items][ITEM_ID]',
            'limit_files' => (string)$this->element['limit_files'],
            'max_file_size' => (string)$this->element['max_file_size'],
            'style' => $style,
            'original_image_resize' => (string)$this->element['original_image_resize'] === '1',
            'original_image_resize_width' => (string)$this->element['original_image_resize_width'],
            'thumb_width' => (string)$this->element['thumb_width'],
            'thumb_height' => (string)$this->element['thumb_height'],
            'thumb_resize_method' => (string)$this->element['resize_method'],
            'css_class' => ' ordering-' . (string)$this->element['ordering'],
            'disabled' => $this->disabled,
            'field_id' => (int)$this->element['field_id'],
            'item_id' => $this->getItemID(),
            'id' => $this->id,
            'pro' => true,
            'readonly' => $this->readonly
        ];

        HTMLHelper::script('com_joomproject/joomprojectgallery.js', ['relative' => true, 'version' => 'auto']);


        return  \Joomla\CMS\Layout\LayoutHelper::render('gallerymanager.default', $data,'',['client' => 'admin', 'component' => 'com_joomproject']);



    }

    private function getItemID()
    {
        $item_id = (int)Factory::getApplication()->input->get('id',0);

        switch (Factory::getApplication()->input->get('option')) {
            case 'com_users':
                $item_id = Factory::getUser()->id;
                break;
        }

        return $item_id;
    }

    /**
     * The list of uploaded Gallery Items.
     *
     * @return  mixed
     */
    private function prepareValue()
    {
	    BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jpprojects/models', 'JPprojectsModel');
	    $model = BaseDatabaseModel::getInstance('Project', 'JPprojectsModel',array('ignore_request' => true));
		$id = Factory::getApplication()->input->get('id',0);
		$item = $model->getItem($id);
	    $this->value = $item->gallery_items;


	    if (empty($this->value)) {
            return;
        }

        $this->value = is_string($this->value) ? json_decode($this->value, true) : (array)$this->value;

        if (!isset($this->value['items'])) {
            return;
        }

        $value = [];

        foreach ($this->value['items'] as $key => $file) {
            $file = new Registry($file);


	        $original = $file->get('original') ? str_replace('\\','/',$file->get('original')) : str_replace('\\','/',$file->get('image'));

            $value[] = [
                'source' => $file->get('source'),
                'original' =>  $original,
                'exists' => is_file(Path::clean(implode(DIRECTORY_SEPARATOR, [JPATH_ROOT, $file->get('thumbnail')]))),
                'caption' => $file->get('caption', ''),
                'thumbnail' => str_replace('\\','/',$file->get('thumbnail', '')),
                'is_media_uploader_file' => ($file->get('media_upload_source', 'false') == 'true'),
                'alt' => $file->get('alt', ''),
                'tags' => json_encode($file->get('tags', []))
            ];
        }


        return $value;
    }


}