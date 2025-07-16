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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;


// don't continue if uploading pdf is not enabled
if(!ComponentHelper::getParams('com_jpdesigns')->get('support_pdf',0))
    return;

// load pdf js library
HTMLHelper::_('script', 'com_joomproject/pdf.js', array('version' => 'auto', 'relative' => true));

?>

<script>
    jQuery(document).ready(function ($) {

        // move elements inside form
        $('#jform_file-lbl').parent().parent().append($('#pdfcanvas'));
        $('#item-form').append($('#pdfpreview'));


        function showPDF(pdf_url) {

            // Asynchronous download of PDF
            var loadingTask = pdfjsLib.getDocument(pdf_url);
            loadingTask.promise.then(function(pdf) {

                // Fetch the first page
                var pageNumber = 1;
                pdf.getPage(pageNumber).then(function(page) {

                    var scale = 1.5;
                    var viewport = page.getViewport({scale: scale});

                    // Prepare canvas using PDF page dimensions
                    var canvas = document.getElementById('pdfcanvas');
                    var context = canvas.getContext('2d');
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;

                    // Render PDF page into canvas context
                    var renderContext = {
                        canvasContext: context,
                        viewport: viewport
                    };
                    var renderTask = page.render(renderContext);
                    renderTask.promise.then(function () {


                        $("#pdfcanvas").show();

                        // convert pdf first page canvas to image and store it in hidden input pdfpreview
                        $('#pdfpreview').attr('value',$('#pdfcanvas').get(0).toDataURL());

                        $('#pdfcanvas').css('zoom',0.5)
                    });
                });
            }, function (reason) {
                // PDF loading error
                console.error(reason);
            });

        }

        // When user chooses a PDF file
        jQuery("#jform_file").on('change', function() {

            // reset pdf preview
            $('#pdfpreview').attr('value','');
            $('#pdfcanvas').empty();
            $('#pdfcanvas').css('zoom',1)

            // Validate whether PDF
            if(['application/pdf'].indexOf(jQuery("#jform_file").get(0).files[0].type) == 0) {

                // Send the object url of the pdf
                showPDF(URL.createObjectURL($("#jform_file").get(0).files[0]));


            }

        });


    });
</script>
<style>
    #pdfcanvas{
        display:none;
    }
</style>
<canvas id="pdfcanvas" width="800" height="1200" class="mt-3 border rounded shadow-sm"></canvas>
<input type="hidden" id="pdfpreview" name="pdfpreview" value="">
