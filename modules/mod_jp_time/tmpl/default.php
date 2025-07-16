<?php
/**
* @package      Joomproject Timesheet Module
*
* @author       ANGEK DESIGN (Kon Angelopoulos)
* @copyright    Copyright (C) 2013 - 2015 ANGEK DESIGN. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

$doc   			= Factory::getDocument();

$script = '
function getData(l){
	var formURL = jQuery("#adminJPTEForm").attr("action");
	postData = "limitstart="+l+"&action=filter";
	jQuery.ajax(
	{
		url: formURL,
		type: "POST",
		data: postData,
		success: function(data, textStatus,jqXHR)
		{
			jQuery("#timedata").html(data);
			
		}
	});
}
jQuery(function() {
	jQuery(":button").click(function () {
		$(this).addClass("clicked");
	});
	var postData = jQuery("#adminJPTEForm").serialize();
	postData += "&action=filter";
	var formURL = jQuery("#adminJPTEForm").attr("action");

	jQuery.ajax(
	{
		url: formURL,
		type: "POST",
		data: postData,
		success: function(data, textStatus,jqXHR)
		{
			jQuery("#timedata").html(data);
		}
	});


	jQuery("#adminJPTEForm").submit(function(e)
	{
		var a = jQuery(".dfilter.clicked").val();
		jQuery(":button").removeClass("clicked");
		if (a == "export"){
			jQuery.fileDownload(jQuery(this).attr("action"), {
				successCallback: function(){
					document.cookie="fileDownload=false; expires=Thu, 01 Jan 1990 12:00:00 GMT; path=/";
				},
				httpMethod: "POST",
				data: jQuery(this).serialize() + "&action=export"
			});
			e.preventDefault();
		}
		else if (a == "filter"){
			var postData = jQuery(this).serialize();
			postData += "&action=filter";
			var formURL = jQuery(this).attr("action");
			jQuery.ajax(
			{
				url: formURL,
				type: "POST",
				data: postData,
				success: function(data, textStatus,jqXHR)
				{
					jQuery("#timedata").html(data);
				}
			});
			e.preventDefault();
		}
		else {
			e.preventDefault();
		}
	});
});
';
$style = '.task-title > a {'
			. 'margin-left:10px;'
			. 'margin-right:10px;'
			. '}'
			. '.margin-none {'
			. 'margin: 0;'
			. '}'
			. '.list-striped .dropdown-menu li {'
			. 'background-color:transparent;'
			. 'padding: 0;'
			. 'border-bottom-width: 0;'
			. '}'
			. '.list-striped .dropdown-menu li.divider {'
			. 'background-color: rgba(0, 0, 0, 0.1);'
			. 'margin: 2px 0;'
			. '}'
			. '.label {'
			. 'margin-left: 3px'
			. '}';

$doc->addScriptDeclaration( $script );
$doc->addStyleDeclaration( $style );
modJPtimeHelper::loadMedia();
?>
<div id="joomproject">
    <div id="JPTime<?php echo $module->id ?>" class="JPTime">
                <form
                        id="adminJPTEForm"
                        name="adminJPTEForm"
                        class="adminJPTEForm"
                        method="post"
                        action="<?php echo Uri::root().'index.php?option=com_ajax&module=jp_time&format=raw'; ?>"
                >
                                <div class="form-group">
                                    <label for="filter_start_date">
                                        <?php echo Text::_('MOD_JP_TIME_CONFIG_FILTER_DATE_FROM'); ?>
                                    </label>
                                        <?php
                                        $cal = HTMLHelper::calendar(date('y-m-d', strtotime('yesterday')), 'filter_start_date', 'filter_start_date', '%Y-%m-%d');
                                        echo str_replace('id="filter_start_date"', 'id="filter_start_date" class="form-control"', $cal);
                                        ?>

                                </div>
                                <div class="form-group">
                                    <label for="filter_end_date">
                                        <?php echo Text::_('MOD_JP_TIME_CONFIG_FILTER_DATE_TO'); ?>
                                    </label>
                                        <?php
                                        $cal = HTMLHelper::calendar(date('y-m-d', strtotime('now')), 'filter_end_date', 'filter_end_date', '%Y-%m-%d');
                                        echo str_replace('id="filter_end_date"', 'id="filter_end_date" class="form-control"', $cal);
                                        ?>
                                </div>

                    <div class="mt-2">
                        <button class="btn dfilter btn-primary btn-sm" value="filter"><?php echo Text::_('MOD_JP_TIME_CONFIG_DISPLAY_LABEL'); ?></button>
                    </div>
                    <?php echo HTMLHelper::_('form.token'); ?>
                    <input type="hidden" name="filter_project" id="filter.project" value="<?php echo JPApplicationHelper::getActiveProjectId(); ?>" />
                </form>
            <div id="timedata"></div>
    </div>
</div>