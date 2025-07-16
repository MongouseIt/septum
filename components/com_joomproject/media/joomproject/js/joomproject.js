jQuery(document).ready(function ($) {

    // Called once on domready, again when a subform row is added
    function initButtonGroup(event, container) {
        var $container = $(container || document);

        // Turn radios into btn-group
        $container.find('.btn-group.btn-group-toggle label').addClass('btn');

        $container.find(".btn-group input:checked").each(function () {
            var $input = $(this);
            var $label = $('label[for=' + $input.attr('id') + ']');
            var btnClass = 'primary';

            if ($input.val() != '') {
                var reversed = $input.parent().hasClass('btn-group-reversed');
                btnClass = ($input.val() == 0 ? !reversed : reversed) ? 'danger ' : 'success';
            }

            $label.addClass('active btn-' + btnClass);
        });
    }

    initButtonGroup()


    // Turn radios into btn-group
    $(document)
        .on('click', ".btn-group label:not(.active)", function () {
            var $label = $(this);
            var $input = $('#' + $label.attr('for'));

            if ($input.prop('checked')) {
                return;
            }

            $label.closest('.btn-group').find("label").removeClass('active btn-success btn-danger btn-primary');

            var btnClass = 'primary';


            if ($input.val() != '') {
                var reversed = $label.closest('.btn-group').hasClass('btn-group-reversed');
                btnClass = ($input.val() == 0 ? !reversed : reversed) ? 'danger' : 'success';
            }

            $label.addClass('active btn-' + btnClass);
            $input.prop('checked', true).trigger('change');
        })

    // end radio


    $(".deleteReminder").click(function (event) {
        event.preventDefault();
        let number = $(this).attr('id');
        let id = number.match(/[\d\.]+/g);
        let elm = $(this).parent().parent().parent();
        let isSite = $(this).data('site');
        let task = $(this).data('site') == 'site' ? 'form' : 'reminder';

        $.ajax({
            type: 'POST',
            url: 'index.php?option=com_jpreminders&view=form&task=' + task + '.deleteReminder',
            // dataType   : "json",
            data: {'id': id},
            success: function (data) {
                elm.fadeOut().remove();
            }
        });
    });



    $(document).on("click", ".btn-izimodal", function (event) {

        event.preventDefault();

        let link = $(this).data('original-link');

        // to prevent displaying already selected items from repository, we pass items as json in link param.
        if ($('#jform_attachment_list').find('input').length > 0 && $(this).hasClass('modal_jform_attachment')) {

            var attachements = [];

            $('#jform_attachment_list').find('input').each(function (index) {
                attachements.push($(this).attr('value'));
            });


            if (Array.isArray(attachements) && attachements.length) {
                $(this).attr('href', link + '&selectedItems=' + JSON.stringify(attachements));
            }

        } else {
            $(this).attr('href', link);
        }

        let title = $(this).attr("title");
        let prefix = $(this).attr("data-op-modal");
        let icon = $(this).attr("data-op-icon");
        var $myDiv = $("#modal-iframe-" + prefix);
        if ( $myDiv.length == 0) {
            $('body').append("<div id='modal-iframe-"+prefix+"'></div>");
        }

        let mymodal = $("#modal-iframe-" + prefix).iziModal({
            title: title,
            fullscreen: true,
            iframe: true,
            icon: icon,
            zindex: 99999

        });
        $(mymodal).iziModal('open', event);

    });

    // for user modal
    $(document).on("click", ".btn-user-izimodal", function (event) {
        event.preventDefault();
        let link = $(this).attr("data-ua-open");
        let title = $(this).attr("data-title-user");


        let mymodal = $("#" + link).iziModal({
            title: title,
            padding: 10,
            fullscreen: true,
            icon: 'fas fa-users',
            zindex: 99999

        });
        $(mymodal).iziModal('open', event);

    });


});


var Joomproject =
    {
        /**
         * Function to watch an item
         *
         * @param    integer   i      The item number
         * @param    string    v      The name of the view
         * @param    string    fi     The form id (optional)
         * @param    string    nomsg  If set to true, will suppress success messages
         */
        watchItem: function (i, v, fi, nomsg) {
            var cid = 'cb' + i;
            var c = jQuery('#watch-' + v + '-' + i);
            var btn = jQuery('#watch-btn-' + v + '-' + i);

            if (btn.length) {
                if (btn.hasClass('disabled') == true) {
                    return;
                }
            }

            btn.addClass('disabled');

            if (c.val() == '1') {
                var act = v + '.unwatch';
                var rq = JPlist.listItemTask(cid, act, fi, true);
            } else {
                var act = v + '.watch';
                var rq = JPlist.listItemTask(cid, act, fi, true);
            }

            rq.done(function (resp) {
                if (Joomproject.isJsonString(resp)) {
                    resp = jQuery.parseJSON(resp);

                    if (btn.length && resp.success == "true") {
                        if (c.val() == '0') {
                            c.val('1');
                        } else {
                            c.val('0');
                        }

                        if (btn.hasClass('btn-success')) {
                            btn.removeClass('btn-success').addClass('btn-info');
                            btn.removeClass('active');
                        } else {
                            btn.removeClass('btn-info').addClass('btn-success');
                            btn.addClass('active');
                        }
                    }
                } else {
                    btn.addClass('btn-danger');
                }

                btn.removeClass('disabled');
            });
        },

        isJsonString: function (str) {
            if (typeof str == 'undefined') return false;

            var l = str.length;
            var e = l - 1;

            if (l == 0) return false;
            if (str[0] != '{' && str[0] != '[') return false;
            if (str[e] != '}' && str[e] != ']') return false;

            return true;
        },


        /**
         * Method to display the ajax response messages
         *
         * @param    object    resp    The ajax response object
         * @param    string    err     The error message
         */
        displayMsg: function (resp, err) {
            var mc = jQuery('#system-message-container');

            if (typeof mc == 'undefined') return false;

            if (resp.length != 0 && typeof resp.success != 'undefined') {
                if (typeof resp.messages != 'undefined') {
                    var c = (resp.success == "true") ? 'success' : 'error';
                    var l = resp.messages.length;
                    var x = 0;

                    if (l > 0) {
                        for (x = 0; x < l; x++) {
                            mc.append('<div class="alert alert-' + c + '"><a class="close" data-dismiss="alert" href="#">×</a>' + resp.messages[x] + '</div>');
                        }
                    }
                }
            } else {
                var m = (typeof err != 'undefined' && err.length > 0) ? err : 'Request failed!';

                mc.append('<div class="alert alert-error"><a class="close" data-dismiss="alert" href="#">×</a>' + m + '</div>');
            }
        },


        displayException: function (msg) {
            var mc = jQuery('#system-message-container');

            (mc.length == 0) ? alert(msg) : mc.append(msg);
        }
    }
