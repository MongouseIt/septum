<?php
/**
 * JoomCRM
 * @author     JoomBoost <support@joomboost.com>
 * @copyright  Copyright (C) 2018 Joomboost.com All Rights Reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Website: https://www.joomboost.com
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

extract($displayData);



?>
<?php $jsoncode = time();?>

<div class="nav nav-tabs mb-3"  id="nav-tab" role="tablist">
	<a class="nav-link nav-link active"><?php echo Text::_('COM_JOOMPROJECT_IMPORTUPLOAD')?>
	</a>
	<a class="nav-link nav-link "><?php echo Text::_('COM_JOOMPROJECT_IMPORTCONFIG')?></a>
	<a class="nav-link nav-link"><?php echo Text::_('COM_JOOMPROJECT_IMPORTFINISH')?></a>
</div>

<div class="progress hide"  id="progress">
	<div class="progress-bar progress-bar-striped" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
</div>
<div class="progress hide"  id="progress2">
	<div class="progress-bar progress-bar-striped"  role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0"aria-valuemax="100"></div>
</div>

<hr>

<?php echo Text::_('COM_JOOMPROJECT_IMPORT_DESCRIPTION');?>

<script src="<?php echo Uri::root(true)?>/media/com_joomproject/js/uploader/vendor/jquery.ui.widget.js"></script>
<script src="<?php echo Uri::root(true)?>/media/com_joomproject/js/uploader/jquery.iframe-transport.js"></script>
<script src="<?php echo Uri::root(true)?>/media/com_joomproject/js/uploader/jquery.fileupload.js"></script>


<form action="<?php echo Uri::getInstance()->toString();?>" method="post" name="importForm" class="form-horizontal">

	<input type="hidden" name="type" value="<?php echo $current->type;  ?>"/>

    <div class="form-group mb-3">
        <label for="fileupload"> <?php echo Text::_('COM_JOOMPROJECT_IMPORTUPLOAD')?> </label>
        <div class="border rounded p-2">
            <input type="file"  name="files[]" class="h-auto" id="fileupload" aria-describedby="fileupload">
        </div>
    </div>

	<div class="rounded p-2 border d-flex justify-content-end bg-light w-100">
		<button type="submit" class="btn btn-primary" id="next-step" disabled="disabled">
            <?php echo Text::_('COM_JOOMPROJECT_NEXT')?> <i class="fas fa-arrow-right"></i> (<?php echo Text::_('COM_JOOMPROJECT_IMPORTCONFIG') ?>)
        </button>
	</div>
	<input type="hidden" name="key" value="<?php echo $jsoncode?>">
	<input type="hidden" name="step" value="2">
</form>
<script>
    $(document).ready(function(){
        $('#fileupload').fileupload({
            url: Joomla.getOptions('system.paths').baseFull+'index.php?option=com_jptasks&task=import.upload',
            dataType: 'json',
            maxChunkSize: 2000000,
            multipart: false,
            maxNumberOfFiles: 1,
            singleFileUploads: true,
            type: 'POST',
            change: function() {
                $('#progress2').hide();
                $('#progress2 .progress-bar').text('').css('width', '0').removeClass('bg-warning').removeClass('bg-success');
                $('#progress .progress-bar').text('').css('width', '0').removeClass('bg-warning').removeClass('bg-success');
            },
            done: function (e, data) {
                $('#progress').removeClass('active');
                $('#progress .progress-bar')
                    .text('<?php echo Text::_('COM_JOOMPROJECT_IMPORTUPLOADFINISH')?>')
                    .css('width', '100%')
                    .addClass('bg-success').removeClass('bg-warning');

                $.each(data.result.files, function (index, file) {
                    if(file.error)
                    {
                        $('#progress .progress-bar')
                            .text(file.error)
                            .css('width', '100%')
                            .addClass('bg-warning')
                            .removeClass('bg-success');
                        return;
                    }
                    $.ajax({
                        url: Joomla.getOptions('system.paths').baseFull+'<?php echo 'index.php?option=com_jptasks&task=import.analize&tmpl=component&json='.$jsoncode; ?>&file='+file.name,
                        dataType: 'json',
                        type: 'POST',
                        beforeSend: function() {
                            $('#progress2').show().addClass('active');
                        }
                    }).done(function(data){
                        setTimeout(function(){updatebar('<?php echo $jsoncode?>');}, 200);

                    }).fail(function(jqXhr, textStatus, errorMessage) {
                       console.log(jqXhr);
                        console.log(textStatus);
                        console.log(errorMessage);
                    });

                    return false;
                });
            },
            progressall: function (e, data) {
                var progress = parseInt(data.loaded / data.total * 100, 10);
                $('#progress').show().addClass('active');
                $('#progress .progress-bar')
                    .css('width', progress + '%')
                    .html('<?php echo Text::_('COM_JOOMPROJECT_IMPORTUPLOAD')?> <b>'+progress+'%</b>')
                    .removeClass('bg-success').removeClass('bg-warning');
            }
        });
    });
    function updatebar(name)
    {
        $.ajax({
            url: Joomla.getOptions('system.paths').baseFull+'index.php?option=com_jptasks&task=import.getJsonStatus&name='+name,
            dataType: 'json',
            type: 'POST'
        }).done(function(data){
            if(data != null){
                if(data.error != null)
                {
                    $('#progress').removeClass('active');
                    $('#progress2 .progress-bar')
                        .text(data.error)
                        .css('width', '100%')
                        .addClass('bg-warning')
                        .removeClass('bg-success');
                }
                else if(data.status < 100)
                {
                    $('#progress2 .progress-bar')
                        .css('width', data.status + '%')
                        .html(data.msg + ' <b>'+data.status+'%</b>');
                    setTimeout(function(){updatebar(name);}, 200);
                }
                else if(data.status >= 100)
                {
                    $('#progress2').removeClass('active');
                    $('#progress2 .progress-bar')
                        .text('<?php echo Text::_('COM_JOOMPROJECT_IMPORTANYLIZEFINISH')?>')
                        .css('width', '100%').removeClass('bg-warning')
                        .addClass('bg-success');

                    $('#next-step').removeAttr('disabled');
                }
            }
        }).fail(function(){
            setTimeout(function(){updatebar(name);}, 200);
        })

    }
</script>