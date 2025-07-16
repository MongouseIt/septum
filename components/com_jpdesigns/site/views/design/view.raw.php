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
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Filesystem\File;

/**
 * Design image class.
 *
 */
class JPdesignsViewDesign extends HtmlView
{
    /**
     * Display the view
     *
     * @return    void
     */
    public function display($tpl = null)
    {
        $item   = $this->get('Item');
        $params = ComponentHelper::getParams('com_jpdesigns', true);
        $layout = Factory::getApplication()->input->getCmd('layout', 'preview');



        // Permission check.
        if ($item->params->get('access-view') !== true) {
            Factory::getApplication()->enqueueMessage( Text::_('JERROR_ALERTNOAUTHOR'),'error');
            return false;
        }

        if ($layout == 'download' || $layout == 'downloadAll') {
            if (headers_sent($file, $line)) {
                Factory::getApplication()->enqueueMessage( $line,'error');
                return false;
            }

            // Download permission check.
            $access = JPdesignsHelper::getActions($item->id);

            if (($access->get('core.admin') || $access->get('core.download')) !== true) {
                Factory::getApplication()->enqueueMessage( Text::_('JERROR_ALERTNOAUTHOR'),'error');
                return false;
            }

            // Download a file
            if ($layout == 'download') {
                $base_path = JPdesignsHelper::getBasePath($item->project_id);

                if ($item->revision) {
                    $file_path = $base_path . '/' . $item->revision->file_name;

                    $name      = $item->revision->alias . '.' . $item->revision->file_extension;
                }
                else {
                    $file_path = $base_path . '/' . $item->file_name;
                    $name      = $item->alias . '.' . $item->file_extension;
                }

                if (!File::exists($file_path)) {
                    Factory::getApplication()->enqueueMessage( Text::_('COM_JOOMPROJECT_ERROR_FILE_NOT_FOUND'),'error');
                    return false;
                }

                ob_end_clean();
                header("Content-Type: APPLICATION/OCTET-STREAM");
                header("Content-Length: " . filesize($file_path));
                header("Content-Disposition: attachment; filename=\"" . $name . "\";");
                header("Content-Transfer-Encoding: Binary");

                if (function_exists('readfile')) {
                    readfile($file_path);
                }
                else {
                    echo file_get_contents($file_path);
                }
            }
            else {
                // Download including revisions
                if (!class_exists('ZipArchive')) {
                    Factory::getApplication()->enqueueMessage( Text::_('COM_JOOMPROJECT_DESIGNS_ERROR_ZIP_EXTENSION'),'error');
                    return false;
                }

                // Get all revisions
                $revs_model = BaseDatabaseModel::getInstance('Revisions', 'JPdesignsModel');
                $revisions = (array) $revs_model->getItems();

                // Collect files
                $base_path = JPdesignsHelper::getBasePath($item->project_id);
                $files     = array();

                // Add the design itself to the list
                $file_path = $base_path . '/' . $item->file_name;

                if (File::exists($file_path)) {
                    $files[$file_path] = '0-' . $item->alias . '.' . $item->file_extension;
                }

                foreach ($revisions AS $rev)
                {
                    // Download permission check.
                    $access = JPdesignsHelper::getRevisionActions($rev->id);

                    if (($access->get('core.admin') || $access->get('core.download')) !== true) {
                        continue;
                    }

                    $file_path = $base_path . '/' . $rev->file_name;

                    if (File::exists($file_path)) {
                        $files[$file_path] = $rev->ordering . '-' . $rev->alias . '.' . $rev->file_extension;
                    }
                }

                // Make sure we have files
                if (!count($files)) {
                    Factory::getApplication()->enqueueMessage( Text::_('COM_JOOMPROJECT_ERROR_FILE_NOT_FOUND'),'error');
                    return false;
                }

                // Delete old archive if exists
                $archive = $base_path . '/' . $item->alias . '.zip';

                if (File::exists($archive)) {
                    if (!File::delete($archive)) {
                        Factory::getApplication()->enqueueMessage( Text::_('COM_JOOMPROJECT_DESIGNS_ERROR_ZIP_DELETE_FAILED'),'error');
                        return false;
                    }
                }

                // Create new archive
                $zip = new ZipArchive();
                $zip_class = true;

                if(!$zip->open($archive, ZIPARCHIVE::CREATE)) {
                    Factory::getApplication()->enqueueMessage( Text::_('COM_JOOMPROJECT_DESIGNS_ERROR_ZIP_CREATE_FAILED'),'error');
                    return false;
                }

                // Add files to archive
                foreach($files as $path => $name)
                {
                    $zip->addFile($path, $name);
                }

                // Close archive
                $zip->close();

                if (File::exists($archive)) {
                    ob_end_clean();
                    header("Content-Type: APPLICATION/OCTET-STREAM");
                    header("Content-Length: " . filesize($archive));
                    header("Content-Disposition: attachment; filename=\"" . $item->alias . '.zip' . "\";");
                    header("Content-Transfer-Encoding: Binary");

                    if (function_exists('readfile')) {
                        readfile($archive);
                    }
                    else {
                        echo file_get_contents($archive);
                    }
                }
                else {
                    Factory::getApplication()->enqueueMessage( Text::_('COM_JOOMPROJECT_DESIGNS_ERROR_ZIP_STORE_FAILED'),'error');
                    return false;
                }
            }
        }
        else {
            // Generate thumbnail
            $options = array();

            $customThumbEnabled = ComponentHelper::getParams('com_jpdesigns')->get('enable_custom_thumbnail',0);
            $subPath = '';
            $thumbName = $item->file_name;

            switch ($layout)
            {
                case 'full':
                    $options['crop']    = false;
                    $options['quality'] = (int) $params->get('quality_img_full', 95);
                    $options['size']    = $params->get('img_full_size', '1280x720');
                    break;

                case 'cover':
                    $options['crop']    = $params->get('crop_img_cover',1) ? true : false;
                    $options['quality'] = (int) $params->get('quality_img_cover',85);
                    $options['size']    = $params->get('img_cover_size', '1280x720');
                    break;

                case 'preview': // support of custom thumbnail feature
                default:
                    if(!empty($item->thumbnail) && $customThumbEnabled){
                        $subPath = 'thumbnails/';
                        $thumbName = $item->thumbnail;
                    }
                    $options['crop']    = $params->get('crop_img_preview',1) ? true : false;
                    $options['quality'] = (int) $params->get('quality_img_preview',85);
                    $options['size']    = $params->get('img_preview_size', '300x200');
                    break;
            }


            $source = JPdesignsHelper::getBasePath($item->project_id) . '/' .$subPath . $thumbName;



            // for images

            $image  = BaseDatabaseModel::getInstance('Image', 'JPdesignsModel', $options);

            $image->setSource($source);
            $image->setCacheId('design', $item->project_id, $item->id);
            $image->setAuthor($item->author_name);
            $image->save();

            if ($image->isCached()) {
                Factory::getApplication()->redirect($image->getCachedURL());
            }
            else {
                $buffer = $image->getBuffer();


                if ($buffer) {
                    ob_end_clean();
                    header("Content-Type: image/jpeg");
            		header("Accept-Ranges: bytes");
            		header("Content-Length: " . filesize($image->getCachedFilePath()));

                    echo $buffer;

                    die('');
                }
            }
        }
        die();
    }

}
