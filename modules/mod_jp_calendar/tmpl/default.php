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

// Set desired truncate length here
$truncateLength = $params->get('event_title_truncate', 25);
// Convert month names to JSON
$monthNames = json_encode($months);
// Convert day names to JSON
$dayNames = json_encode($days);
// Convert short day names to JSON
$dayNamesShort = json_encode($days_short);

$js = <<<JS
jQuery(document).ready(function() {
    // Initialize events list
    let eventsList{$module->id} = $items;
    let newEventsList{$module->id} = [];

    // Iterating and manipulating each event in the list
    jQuery.each(eventsList{$module->id}, function(index, value) {
        if(value.hasOwnProperty('start')) {
            let dateStart = value.start.split('-');
            value.start = new Date(dateStart[0],parseInt(dateStart[1])-1,dateStart[2]);
        }    
    
        if(value.hasOwnProperty('end')) {
            let dateEnd = value.end.split('-');
            value.end = new Date(dateEnd[0],parseInt(dateEnd[1])-1,dateEnd[2]);
        }
        
        newEventsList{$module->id}.push(value);
    });

    // Initializing the FullCalendar
    jQuery("#mod_jp_calendar_{$module->id}").fullCalendar({
        handleWindowResize: true,
        editable: false,
        events: newEventsList{$module->id},
        firstDay: {$params->get('week_start', 1)},
        aspectRatio: {$params->get('aspect_ratio', '1.35')},
        monthNames: {$monthNames},
        dayNames: {$dayNames},
        dayNamesShort: {$dayNamesShort},
        eventRender: function(event, element) {
            
            let truncatedTitle = event.title;
            if (truncatedTitle.length > {$truncateLength}) {
                truncatedTitle = truncatedTitle.substring(0, {$truncateLength}) + '...';
            }
            
            // Setting event HTML
            element.find(".fc-event-title").html(truncatedTitle);
            element.attr("data-bs-original-title", event.title_alt);            
           
            // Initialize tooltip following Bootstrap 5 syntax but using jQuery to find DOM elements
                new bootstrap.Tooltip(element[0],{
                    title: event.title, 
                    placement: 'top', 
                    trigger: 'hover' 
                });
            
            if(event.i_type == "ms") {
                element.find('.fc-event-inner').prepend('<i class="fas fa-flag"></i> ');
            }
            if(event.ev_type == "start") {
                element.find('.fc-event-inner').prepend('<i class="fas fa-play"></i> ');
            }      
            if(event.ev_type == "end") {
                element.find('.fc-event-inner').prepend('<i class="fas fa-stop"></i> ');
            }
        }
    });
});
JS;

// Add JavaScript block to document
Factory::getDocument()->addScriptDeclaration($js);

?>
<div id="joomproject">
    <div id="mod_jp_calendar_<?php echo $module->id; ?>"></div>
</div>

