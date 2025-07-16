<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jprepo
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView;

defined('_JEXEC') or die();





/**
 * File Download View class for the Joomproject component
 *
 */
class JPrepoViewFile extends HtmlView
{
    protected $item;
    protected $state;


    function display($tpl = null)
    {
        if(!\JoomProject\Permission\GlobalAccess::check('com_jprepo'))
            return;

        $user = Factory::getApplication()->getIdentity();
        $params = \Joomla\CMS\Component\ComponentHelper::getParams('com_jprepo');

        $this->item  = $this->get('Item');
        $this->state = $this->get('State');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        // Check access
		if ($this->item->params->get('access-view') != true) {
		    Factory::getApplication()->enqueueMessage( Text::_('JERROR_ALERTNOAUTHOR'),'warning');
			return false;
		}

        $rev = Factory::getApplication()->input->getUInt('rev');

        if ($rev) {
            $rev_model = BaseDatabaseModel::getInstance('FileRevision', 'JPrepoModel', $c = array('ignore_request' => true));
            $file_rev  = $rev_model->getItem($rev);

            if (!$file_rev || empty($file_rev->id)) {
                Factory::getApplication()->enqueueMessage( Text::_('COM_JOOMPROJECT_ERROR_FILE_NOT_FOUND'),'error');
                return false;
            }

            // Check access
            if ($file_rev->parent_id != $this->item->id) {
                Factory::getApplication()->enqueueMessage( Text::_('JERROR_ALERTNOAUTHOR'),'warning');
                return false;
            }

            $filepath = JPrepoHelper::getBasePath($this->item->project_id) . '/_revs/file_' . $this->item->id;
            $filename = $file_rev->file_name;
        }
        else {
            $filepath = $this->item->physical_path;
            $filename = $this->item->file_name;
        }

        // Check if the file exists
        if (empty($filepath) || !File::exists($filepath . '/' . $filename)) {
            Factory::getApplication()->enqueueMessage( Text::_('COM_JOOMPROJECT_ERROR_FILE_NOT_FOUND'),'error');
            return false;
        }

        if (headers_sent($file, $line)) {
            Factory::getApplication()->enqueueMessage( $line,'error');
            return false;
        }

        while (ob_get_level())
        {
            ob_end_clean();
        }



        // allowed files extensions to be downloaded
        $forceDownload = (bool) $params->get('force_files_download',0);
        $allowedExtensions = $params->get('force_files_download_extensions','pdf,docx,ppt,doc,txt');

        $allowedExtensions = explode(',',$allowedExtensions);


        // if file is pdf and not forced to be downloaded
        if($forceDownload && in_array($this->item->file_extension,$allowedExtensions) ){
            header('Content-type: '.mime_content_type($filepath . '/' . $filename));
            header("Content-Length: " . filesize($filepath . '/' . $filename));
            header("Content-Disposition: attachment; filename=\"" . $filename . "\";");
        }
        else{
            header("Content-Type: ".mime_content_type($filepath . '/' . $filename));
            header("Content-Length: " . filesize($filepath . '/' . $filename));
            header("Content-Disposition: inline; filename=\"" . $filename . "\";");
            header("Content-Transfer-Encoding: Binary");
            header('Accept-Ranges: bytes');
        }

        if (function_exists('readfile')) {

            readfile($filepath . '/' . $filename);
        }
        else {

            echo file_get_contents($filepath . '/' . $filename);
        }

        jexit();
    }
}
