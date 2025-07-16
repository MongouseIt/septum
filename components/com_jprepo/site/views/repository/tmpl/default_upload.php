<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jprepo
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */


defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Environment\Browser;

$dir        = $this->items['directory'];
$browser    = Browser::getInstance();
$txt_upload = ($browser->getBrowser() == 'msie') ? Text::_('COM_JOOMPROJECT_AJAX_UPLOAD_CLICK') : Text::_('COM_JOOMPROJECT_AJAX_UPLOAD_DD');
$allowed    = JPrepoHelper::getAllowedFileExtensions();

$user         = Factory::getApplication()->getIdentity();
$config       = ComponentHelper::getParams('com_jprepo');
$filter_admin = $config->get('filter_ext_admin');
$is_admin     = $user->authorise('core.admin');

// Restrict file extensions?
$exts = '';

if ($is_admin && !$filter_admin) $allowed = array();

$alloweduploads = "";

if (count($allowed)) {
    $alloweduploads = '<small class="text-muted">' . Text::_('COM_JOOMPROJECT_UPLOAD_ALLOWED_EXT') . ' ' . implode(', ', $allowed).'</small>';
}

$area = array();
$area[] = '<div class="qq-uploader text-muted bg-light py-4 border-dashed border-width-2 border-color-gray text-center alert">';
$area[] = '<div class="qq-upload-drop-area qq-upload-button "><h3 class="me-0 font-weight-light text-dark"><i class="fas fa-cloud-upload-alt"></i> ' . $txt_upload . '</h3>';
$area[] = $alloweduploads;
$area[] = '</div>';

$area[] = '<div class="qq-upload-list my-4">';

$el = array();
$el[] = '<div class="card card-body bg-white border-0 text-left mb-1">';
$el[] = '<div class="row">';
$el[] = '<div class="col-md-6">';
$el[] = '<span class="fas fa-flag"></span> ';
$el[] = '<span class="qq-upload-file"></span>';
$el[] = '<span class="qq-upload-spinner"></span>';
$el[] = '</div>';
$el[] = '<div class="col-md-4">';
$el[] = '    <div class="progress active">';
$el[] = '        <div class="bar progress-bar progress-bar-striped" role="progressbar">';
$el[] = '            <span class="qq-upload-size float-start"></span>';
$el[] = '        </div>';
$el[] = '    </div>';
$el[] = '</div>';
$el[] = '<div class="col-md-2">';
$el[] = '<span class="qq-upload-failed-text text-danger">Failed</span>';
$el[] = '<a class="qq-upload-cancel btn btn-sm btn-danger" href="#"><i class="fas fa-times"></i> Cancel</a>';
$el[] = '</div>';
$el[] = '</div>';
$el[] = '</div>';
$area[] = '</div>';

// Init Ajax upload
HTMLHelper::_('jphtml.script.upload');
HTMLHelper::_('stylesheet', 'com_joomproject/joomproject/ajax-upload.css');

$js = "
$(document).ready(function(){
 function createUploader()
{
    new qq.FileUploader({
        element: document.getElementById('file-uploader'),
        action: 'index.php',
        params: {
            option: 'com_jprepo',
            task: 'fileform.save',
            filter_parent_id: '" . $dir->id  . "',
            format: 'json'
        },
        template: '" . implode('', $area) . "',
        fileTemplate: '" . implode('', $el) . "',
        listElement: document.getElementById('qq-upload-list'),
        debug: true
        " . $exts . "
    });
}
})
;";

Factory::getDocument()->addScriptDeclaration($js);
?>
<div id="file-uploader" class="d-none d-sm-block"></div>

