<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jprepo
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */


defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Environment\Browser;

$dir = $this->items['directory'];
$browser = Browser::getInstance();
$txt_upload = ($browser->getBrowser() == 'msie') ? Text::_('COM_JOOMPROJECT_AJAX_UPLOAD_CLICK') : Text::_('COM_JOOMPROJECT_AJAX_UPLOAD_DD');
$allowed = JPrepoHelper::getAllowedFileExtensions();

$user = Factory::getApplication()->getIdentity();
$config = ComponentHelper::getParams('com_jprepo');
$filter_admin = $config->get('filter_ext_admin');
$is_admin = $user->authorise('core.admin');

// Restrict file extensions?
$exts = '';

if ($is_admin && !$filter_admin) $allowed = array();

if (count($allowed)) {
    $exts = ', allowedExtensions: ' . json_encode($allowed);

    $txt_upload .= '. ' . Text::_('COM_JOOMPROJECT_UPLOAD_ALLOWED_EXT') . ' ' . implode(', ', $allowed);
}

$area = array();
$area[] = '<div class="qq-uploader">';
$area[] = '<div class="qq-upload-drop-area qq-upload-button bg-light py-4 border-dashed border-width-3 border-color-gray text-center alert"><h3 class="me-0"><i class="fas fa-plus"></i> ' . $txt_upload . '</h3></div>';
$area[] = '</div>';
$area[] = '<div class="qq-upload-list my-4">';

$el = array();
$el[] = '<div class="card card-body bg-light">';
$el[] = '<div class="row">';
$el[] = '<div class="col-md-6">';
$el[] = '<span class="fas fa-flag"></span> ';
$el[] = '<span class="qq-upload-file"></span>';
$el[] = '<span class="qq-upload-spinner"></span>';
$el[] = '</div>';
$el[] = '<div class="col-md-2">';
$el[] = '<span class="qq-upload-failed-text">Failed</span>';
$el[] = '<a class="qq-upload-cancel btn btn-sm btn-sm" href="#"><i class="fas fa-times"></i> Cancel</a>';
$el[] = '</div>';
$el[] = '<div class="col-md-4">';
$el[] = '    <div class="progress active">';
$el[] = '        <div class="bar progress-bar progress-bar-striped" role="progressbar">';
$el[] = '            <span class="qq-upload-size float-start"></span>';
$el[] = '        </div>';
$el[] = '    </div>';
$el[] = '</div>';
$el[] = '</div>';
$el[] = '</div>';

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
            task: 'file.save',
            filter_parent_id: '" . $dir->id . "',
            format: 'json'
        },
        template: '" . implode('', $area) . "',
        fileTemplate: '" . implode('', $el) . "',
        listElement: document.getElementById('qq-upload-list'),
        debug: true
        " . $exts . "
    });
    }
});";

Factory::getDocument()->addScriptDeclaration($js);
?>
<div id="file-uploader" class="d-none d-sm-block"></div>

