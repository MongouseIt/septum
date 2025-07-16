var JPcomments =
{
    cancel: function()
    {
        var editor = jQuery('#comment-editor').detach();
        var root   = jQuery('#jp-comments-container');

        root.after(editor);

        jQuery('#jform_parent_id').val(0);
    },

    save: function()
    {
        var f = jQuery('#commentForm');
        var c = jQuery('#jform_description', f);
        var t = jQuery('input[name|="task"]', f);


        if (jQuery.trim(c) == '') {
            alert('Please enter a description');
            return;
        }

        // Override the task value
        t.val('form.save');

        // Serialize the form
        var d = f.serializeArray();

        // empty the comment text
        c.val('');


        // Do the ajax request
        jQuery.ajax(
        {
            url: f.attr('action'),
            data: jQuery.param(d),
            type: 'POST',
            processData: true,
            cache: false,
            dataType: 'html',
            success: function(resp)
            {
                if (Joomproject.isJsonString(resp) == false) {
                    Joomproject.displayException(resp);
                }
                else {
                    resp = jQuery.parseJSON(resp);
                    Joomproject.displayMsg(resp);

                    // Increase comment count
                    var cc = jQuery('#comment_count');

                    if (cc.length) {
                        var cci = parseInt(cc.text());
                        cci++;
                        cc.text(cci);
                    }
                }
            },
            error: function(resp, e, msg)
            {
                Joomproject.displayMsg(resp, msg);
            },
            complete: function()
            {
                t.val('');

                // Move the editor back to its original position
                JPcomments.cancel();

                // Reload the comments
                JPcomments.reload();
            }
        });
    },

    add: function(event)
    {

        var i = event.data.i;
        var editor  = jQuery('#comment-editor').detach();
        var item    = jQuery('#comment-item-' + i);
        var content = jQuery('.comment-content', item);
        var cb      = jQuery('#cb' + i).val();


        content.append(editor);

        jQuery('#jform_parent_id').val(cb);
    },

    trash: function(event)
    {
        var i = event.data.i;
        var f = jQuery('#commentForm');
        var t = jQuery('input[name|="task"]', f);

        // Check the box
        jQuery('#cb' + i).attr('checked', true);

        // Override the task value
        t.val('comments.trash');

        // Serialize the form
        var d = f.serializeArray();

        // Do the ajax request
        jQuery.ajax(
        {
            url: f.attr('action'),
            data: jQuery.param(d),
            type: 'POST',
            processData: true,
            cache: false,
            dataType: 'html',
            success: function(resp)
            {
                if (Joomproject.isJsonString(resp) == false) {
                    Joomproject.displayException(resp);
                }
                else {
                    resp = jQuery.parseJSON(resp);
                    Joomproject.displayMsg(resp);

                    // Decrease comment count
                    var cc = jQuery('#comment_count');

                    if (cc.length) {
                        var cci = parseInt(cc.text());
                        cci--;
                        cc.text(cci);
                    }
                }
            },
            error: function(resp, e, msg)
            {
                Joomproject.displayMsg(resp);
            },
            complete: function()
            {
                t.val('');

                // Move the editor back to its original position
                JPcomments.cancel();

                // Reload the comments
                JPcomments.reload();
            }
        });
    },

    init: function(reload)
    {
        var editor = jQuery('#comment-editor');
        var root   = jQuery('#jp-comments');

        if (editor.length > 0) {
            if (typeof reload == 'undefined') {
                jQuery('#btn_comment_save', editor).click(this.save);
                jQuery('#btn_comment_cancel', editor).click(this.cancel);
            }
        }

        var btns_add   = jQuery('.btn-add-reply',   root);

        var btns_trash = jQuery('.btn-trash-reply', root);

        for(var it = 0; it < btns_add.length; it++)
        {
            var btn = jQuery(btns_add[it]);
            btn.bind('click', {i: btn.data('comment-id')}, function(event){JPcomments.add(event);});
        }

        for(var it = 0; it < btns_trash.length; it++)
        {
            var btn = jQuery(btns_trash[it]);
            btn.bind('click', {i:  btn.data('comment-id')}, function(event){JPcomments.trash(event);});
        }
    },

    reload: function()
    {


        // Do the ajax request
        jQuery.ajax(
        {
            url: Joomla.getOptions('system.paths').root+('/index.php?option=com_jpcomments&view=comments'),
            type: 'POST',
            data: {
                option: 'com_jpcomments',
                views: 'comments',
                filter_context: jQuery('#jform_context').val(),
                filter_item_id: jQuery('#jform_item_id').val(),
                tmpl: 'component',
                layout: 'default_items'
            },
            dataType: 'html',
            cache: false,
            success: function(resp)
            {
                jQuery('#jp-comments').empty();
                jQuery('#jp-comments').append(resp);

                jQuery('#comment-editor').appendTo('#jp-comments-container');
            },
            error: function(resp, e, msg)
            {
                Joomproject.displayMsg(resp);
            },
            complete: function()
            {
                JPcomments.init(true);
            }
        });
    }
}