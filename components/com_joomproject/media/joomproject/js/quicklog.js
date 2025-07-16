/**
 * @package      Joomproject
 *
 * @author       JooBoost
 * @copyright    Copyright (C) 2012-2020 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

jQuery(document).ready(function($){

    $("body").on('click','div[data-quicklog="true"] button',function (e) {

        e.preventDefault();
        var currentButton = $(this);
        var taskid = parseInt($(this).data('taskid')); // task id value
        var tasktitle = $(this).data('tasktitle'); // task title value
        var logtime = parseInt($(this).parent().prev().val()); // log time value from input

        $.ajax({
            type: 'POST',
            url: Joomla.getOptions('system.paths').root+
                '/index.php?option=com_jptime&view=recorder&task=recorder.quickLog&tmpl=component&format=json&'+Joomla.getOptions('csrf.token')+'=1',
            data: {
                'taskid': taskid,
                'logtime': logtime,
                'tasktitle': tasktitle,
            },
            success: function (data) {

                data = JSON.parse(data);

                // set log time to 0;
                currentButton.parent().prev().val(0);

                if(data.messages.length > 0){
                    $('.alertsContainer').append(data.messages)
                    $('.alert-item-'+taskid).delay(5000).fadeOut('slow');
                }
            }
        });
    });

});

