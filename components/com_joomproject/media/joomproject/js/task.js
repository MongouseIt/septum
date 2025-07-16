/**
 * @package      Joomproject
 *
 * @author       JooBoost
 * @copyright    Copyright (C) 2006-2012 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */


/**
 * A collection of task related functions
 *
 */

var JPtask =
    {
        track_url: null,
        track_win_opts: null,

        setTimeTracker: function (turl, topts) {
            JPtask.track_url = turl;
            JPtask.track_win_opts = topts;
        },

        trackItem: function (tid) {
            console.log(JPtask.track_url + '&cid[]=' + tid)
            window.open(JPtask.track_url + '&cid[]=' + tid, 'winJPtimerec', JPtask.track_win_opts);
        },

        /**
         * Function to mark a task as complete/incomplete
         *
         * @param  integer   i     The item number
         * @param  string    fi    The form id (optional)
         */
        complete: function (i, fi) {
            var cid = 'cb' + i;
            var btn = jQuery('#complete-btn-' + i);
            var c = jQuery('#complete' + i);
            var item = jQuery('#list-item-' + i);

            btn.addClass('disabled');
            var rq = JPlist.listItemTask(cid, 'tasks.complete', fi, true);

            rq.done(function (resp) {
                btn.removeClass('disabled');

                if (resp != false) {
                    btn.removeClass('btn-secondary');

                    var v = c.val();

                    if (v == '0') {
                        c.val('1');
                        btn.addClass('btn-success');
                        btn.addClass('active');
                        item.addClass('complete');
                    } else {
                        c.val('0');
                        btn.removeClass('btn-success');
                        btn.addClass('btn-secondary');
                        btn.removeClass('active');
                        item.removeClass('complete');
                    }
                } else {
                    btn.addClass('btn-secondary');
                }
            });
        },

        priority: function (i, v, t, fi) {
            var cid = 'cb' + i;
            var p = jQuery('#priority' + i);

            p.val(v);

            var rq = JPlist.listItemTask(cid, 'tasks.priority', fi, true);

            rq.done(function (resp) {
                if (resp != false) {
                    var l = jQuery('#list-item-' + i);

                    if (l.length) {

                        l.removeClass('priority-1');
                        l.removeClass('priority-2');
                        l.removeClass('priority-3');
                        l.removeClass('priority-4');
                        l.removeClass('priority-5');

                        if (v == 1) {
                            l.addClass('priority-1');
                        }
                        if (v == 2) {
                            l.addClass('priority-2');
                        }
                        if (v == 3) {
                            l.addClass('priority-3');
                        }
                        if (v == 4) {
                            l.addClass('priority-4');
                        }
                        if (v == 5) {
                            l.addClass('priority-5');
                        }

                    }
                }
            });
        }
    }


function jSelectUser_JPtaskAssignUser(id, title) {
    parent.document.querySelector('.iziModal-button-close').click();

    var ti = jQuery('#target-item');

    if (ti.length) {
        var i = ti.val();
        var f = jQuery('#assigned' + i);
        var cid = 'cb' + i;

        if (f.length) {
            f.val(id);

            var rq = JPlist.listItemTask(cid, 'tasks.addUsers', 'adminForm', true);

            rq.done(function (resp) {
                f.val(0);

                if (Joomproject.isJsonString(resp)) {
                    var lbl = jQuery('#assigned_' + i + '_label');

                    if (lbl.length) {
                        var c = lbl.html();

                        if (c == '') {
                            lbl.addClass('label');
                            lbl.html('<i class="fas fa-user text-white"></i> ' + title);
                        } else {
                            var pts = c.split('+');

                            if (pts.length == 1) {
                                pts[1] = 0;
                            }

                            var count = parseInt(pts[1]) + 1;

                            lbl.html(pts[0] + ' +' + count);
                        }
                    }
                }
            });
        }
    }
}
