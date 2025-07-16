/**
 * @package      Joomproject
 *
 * @author       JooBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */


/**
 * A collection of time recording related functions
 *
 */
var JPtimerec =
{
    fn: null,
    fe: null,
    te: null,
    pe: null,

    setForm: function(n)
    {
        JPtimerec.fn = n;
        JPtimerec.fe = jQuery('#' + n);
    },


    setTicker: function(tn, tpn)
    {
        JPtimerec.te = jQuery('#' + tn, JPtimerec.fe);
        JPtimerec.pe = jQuery('#' + tpn).children('div.progress-bar');
    },


    save: function(i)
    {
        var cid = 'cb' + i;
        var btn = jQuery('#btn-rec-save-' + i);

        if (btn.hasClass('disabled')) return true;

        btn.addClass('disabled');
        var rq = JPlist.listItemTask(cid, 'recorder.save', JPtimerec.fn, true);

        rq.done(function(resp)
        {
            if (!Joomproject.isJsonString(resp)) return false;
            btn.removeClass('disabled');
            btn.addClass('btn-success');

            setTimeout(function()
            {
                jQuery('#rec-edit-' + i).collapse('hide');
            }, 2000);
        });

        rq.fail(function(resp, e, msg)
        {
            btn.removeClass('disabled');
            btn.removeClass('btn-success');
            btn.addClass('btn-danger');

            setTimeout(function()
            {
                btn.removeClass('btn-danger');
                btn.addClass('btn-success');
            }, 2000);
        });
    },


    closeEdit: function(i)
    {
        jQuery('#rec-edit-' + i).collapse('hide');
    },


    remove: function(i, c)
    {
        jQuery('input[name|="complete"]', JPtimerec.fe).val(c);

        var cid = 'cb' + i;
        var rq  = JPlist.listItemTask(cid, 'recorder.delete', JPtimerec.fn, true);

        rq.done(function(resp)
        {
            if (!Joomproject.isJsonString(resp)) return false;

            jQuery('#rec-' + i).hide('fast', function() {this.remove();});
        });

        return rq;
    },


    togglePause: function(i)
    {
        var cid  = 'cb' + i;
        var btn  = jQuery('#btn-rec-state-' + i);
        var c    = jQuery('#rec-state-' + i);

        if (btn.hasClass('disabled')) return true;

        btn.addClass('disabled');
        var rq = JPlist.listItemTask(cid, 'recorder.pause', JPtimerec.fn, true);

        rq.done(function(resp)
        {
            if (!Joomproject.isJsonString(resp)) {
                btn.removeClass('disabled');
                btn.removeClass('btn-success');
                btn.addClass('btn-danger');
                return false;
            }

            btn.removeClass('disabled');
            btn.removeClass('btn-danger');

            if (parseInt(c.val()) == 0) {
                c.val(1);
                btn.removeClass('btn-success');
                btn.addClass('btn-light');
                btn.removeClass('active');
                btn.children('i').removeClass('fa-pause').addClass('fa-play');
            }
            else {
                c.val(0);
                btn.removeClass('btn-light');
                btn.addClass('btn-success');
                btn.addClass('active');
                btn.children('i').removeClass('fa-play').addClass('fa-pause');
            }
        });
        rq.fail(function(resp, e, msg){
            btn.removeClass('btn-success');
            btn.removeClass('disabled');
            btn.addClass('btn-danger');
        });

        return rq;
    },


    pauseAll: function()
    {
        JPtimerec.setAll(0);
    },


    startAll: function()
    {
        JPtimerec.setAll(1);
    },


    setAll: function(s, qr, ir)
    {
        if (typeof s == 'undefined') s = 2;

        if (typeof qr == 'undefined') {
            var recs = jQuery('.recording', JPtimerec.fe);
            var i  = 0;
            var v  = 0;
            var q  = [];
            var f = false;

            if (recs.length == 0) return true;

            for(i = 0; i < recs.length; i++)
            {
                f = false;
                if (s == 2) {
                    f = true;
                }
                else {
                    v = parseInt(jQuery('#rec-state-' + i).val());

                    if (s == 1 && v > 0)  f = true;
                    if (s == 0 && v == 0) f = true;
                }

                if (f) q.push(i);
            }

            JPtimerec.setAll(s, q, 0);
        }
        else {
            JPtimerec.togglePause(qr[ir]).done(function(){
                ir++;
                if (qr.length > ir) {
                    JPtimerec.setAll(s, qr, ir);
                }
            });
        }
    },


    removeAll: function(c, qr, ir)
    {
        if (typeof qr == 'undefined') {
            var recs = jQuery('.recording', JPtimerec.fe);
            var i  = 0;
            var q  = [];

            if (recs.length == 0) return true;

            for(i = 0; i < recs.length; i++)
            {
                q.push(i);
            }

            JPtimerec.removeAll(c, q, 0);
        }
        else {
            JPtimerec.remove(qr[ir], c).done(function(){
                ir++;
                if (qr.length > ir) {
                    JPtimerec.removeAll(c, qr, ir);
                }
            });
        }
    },


    punch: function()
    {
        var btns = JPtimerec.fe.find('.btn-rec-state');

        if (btns.length) {
            btns.each(function(idx)
            {
                var btn = jQuery(this);

                if (btn.hasClass('active')) {
                    btn.addClass('disabled');
                    console.log(btn.children('i'))
                    btn.children('i').removeClass('fa-play').addClass('fa-pause');
                }
            });

            var rq = JPlist.submitform('recorder.punch', JPtimerec.fn, true);

            rq.done(function(resp)
            {
                btns.each(function(idx)
                {
                    var btn = jQuery(this);

                    if (btn.hasClass('active')) {
                        btn.removeClass('disabled');
                        btn.children('i').removeClass('fa-pause').addClass('fa-play');
                    }
                });

                if (!Joomproject.isJsonString(resp)) return false;

                resp = jQuery.parseJSON(resp);

                if (resp.success == "true") {
                    if (typeof resp.data != 'undefined') {
                        jQuery.each(resp.data, function(i, v)
                        {
                            jQuery("#rec-time-" + i).empty().append(v);
                        });
                    }
                }
            });

            rq.fail(function(resp, e, msg)
            {
                btns.each(function(idx)
                {
                    var btn = jQuery(this);

                    if (btn.hasClass('active')) {
                        btn.removeClass('disabled');
                        btn.children('i').removeClass('fa-pause').addClass('fa-play');
                    }
                });
            });
        }
    },

    tick: function()
    {
        var bs = JPtimerec.fe.find('.btn-rec-state');
        var v  = parseInt(JPtimerec.te.val());
        var a  = 0;

        if (bs.length) {
            bs.each(function(idx)
            {
                if (jQuery(this).hasClass('active')) a += 1;
            });
        }

        if (a) {
            v += 1;
        }
        else {
            v = 0;
        }

        if (v >= 60) {
            v = 0;
            JPtimerec.punch();
        }

        // Update the progress bar
        JPtimerec.pe.css('width', (v * 1.66) + '%');

        JPtimerec.te.val(v);
    }
}