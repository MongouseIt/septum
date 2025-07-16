<?php
/**
* @package      mod_jp_calendar
*
* @author       JoomBoost
* @copyright    Copyright (C) 2018 JoomBoost. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();
use Joomla\CMS\Factory;

modJPcalendarHelper::loadMedia();

$manipulateItems = <<<JS

let eventsList{$module->id} = $items;
let newEventsList{$module->id} = []; 
jQuery.each(eventsList{$module->id},function(index, value){    
    
    console.log(value.end)
    
    if(value.hasOwnProperty('start')){
        let dateStart = value.start.split('-'); 
        value.start = new Date(dateStart[0],parseInt(dateStart[1])-1,dateStart[2]);
    }
        
    if(value.hasOwnProperty('end'))  {
        
        let dateEnd = value.end.split('-');
        
        value.end = new Date(dateEnd[0],parseInt(dateEnd[1])-1,dateEnd[2]);

    } 
    
    
    newEventsList{$module->id}.push(value)
});

JS;


$js = array();
$js[] = 'jQuery(document).ready(function()';
$js[] = '{
'.$manipulateItems.'
';
$js[] = '    jQuery("#mod_jp_calendar_' . $module->id . '").fullCalendar(';
$js[] = '    {';
$js[] = '        handleWindowResize: true,';
$js[] = '        editable: false,';
$js[] = '        events: newEventsList'.$module->id.',';
$js[] = '        defaultView : \'basicWeek\',';
$js[] = '        firstDay: ' . (int) $params->get('week_start', 1) . ',';
$js[] = '        aspectRatio: ' . $params->get('aspect_ratio', '1.35') . ',';
$js[] = '        monthNames: ' . json_encode($months) . ',';
$js[] = '        dayNames: ' . json_encode($days) . ',';
$js[] = '        dayNamesShort: ' . json_encode($days_short) . ',';
$js[] = '        eventRender: function(event, element)';
$js[] = '        {';
$js[] = '            element.find(".fc-event-title").html(event.title);';
$js[] = '            element.attr("title", event.title_alt);';
$js[] = '            if (event.i_type == "ms") {';
$js[] = '                element.find(\'.fc-event-inner\').prepend(\'<i class="icon-flag"></i> \');';
$js[] = '            }';
$js[] = '            if (event.ev_type == "start") {';
$js[] = '                element.find(\'.fc-event-inner\').prepend(\'<i class="icon-play"></i> \');';
$js[] = '            }';
$js[] = '            if (event.ev_type == "end") {';
$js[] = '                element.find(\'.fc-event-inner\').prepend(\'<i class="icon-stop"></i> \');';
$js[] = '            }';
$js[] = '        }';
$js[] = '    })';
$js[] = '});';


Factory::getDocument()->addScriptDeclaration(implode("\n", $js));

?>
<div id="joomproject">
    <div id="mod_jp_calendar_<?php echo $module->id; ?>">
</div>


