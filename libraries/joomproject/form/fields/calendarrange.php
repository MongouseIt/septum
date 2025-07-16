<?php
/**
 * @package      Joomproject.Library
 * @subpackage   Form
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 20012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\FormField;
use Joomla\Utilities\ArrayHelper;

class JFormFieldCalendarRange extends FormField
{
    public $type = 'CalendarRange';

    protected $parents;


    protected function getInput()
    {
        // Get possible parent field values
        // Note that the order of the array elements matter!
        $parents = array();
        $parents['project']   = (int) $this->form->getValue('project_id');
        $parents['milestone'] = (int) $this->form->getValue('milestone_id');
        $parents['task']      = (int) $this->form->getValue('task_id');

        $this->parents = $parents;

        // Initialize some field attributes.
        $format = $this->element['format'] ? (string) $this->element['format'] : '%Y-%m-%d';

        // Build the attributes array.
        $attributes = array();
        if ($this->element['size'])                        $attributes['size'] = (int) $this->element['size'];
        if ($this->element['maxlength'])                   $attributes['maxlength'] = (int) $this->element['maxlength'];
        if ($this->element['class'])                       $attributes['class'] = (string) $this->element['class'];
        if ((string) $this->element['readonly'] == 'true') $attributes['readonly'] = 'readonly';
        if ((string) $this->element['disabled'] == 'true') $attributes['disabled'] = 'disabled';
        if ($this->element['onchange'])                    $attributes['onchange'] = (string) $this->element['onchange'];

        $range = $this->getRange();

        $attributes['range_start'] = $range[0];
        $attributes['range_end']   = $range[1];

        // Handle the special case for "now".
        if (strtoupper($this->value) == 'NOW') {
            $this->value = strftime($format);
        }

        // Get some system objects.
        $config = Factory::getConfig();
        $user   = Factory::getApplication()->getIdentity();

        $clip     = (string) $this->element['clip'];
        $nulldate = Factory::getDbo()->getNullDate();

        // Clip the current date if its not in range
        if ($clip && !empty($this->value) && $this->value != $nulldate) {
            $time  = strtotime($this->value);
            $start = 0;
            $end   = 0;

            if ($attributes['range_start'] && $attributes['range_start'] != $nulldate) {
                $start = strtotime($attributes['range_start']);
            }

            if ($attributes['range_end'] && $attributes['range_end'] != $nulldate) {
                $end = strtotime($attributes['range_end']);
            }

            if ($start && ($start > $time || $time < $time) && $clip == 'start') {
                $this->value = $attributes['range_start'];
            }

            if ($end && ($end < $time) && $clip == 'end') {
                $this->value = $attributes['range_end'];
            }
        }

        // If a known filter is given use it.
        switch (strtoupper((string) $this->element['filter']))
        {
            case 'SERVER_UTC':
                // Convert a date to UTC based on the server timezone.
                if (intval($this->value)) {
                    // Get a date object based on the correct timezone.
                    $date = Factory::getDate($this->value, 'UTC');
                    $date->setTimezone(new DateTimeZone($config->get('offset')));

                    // Transform the date string.
                    $this->value = $date->format('Y-m-d H:i:s', true, false);
                }
                break;

            case 'USER_UTC':
                // Convert a date to UTC based on the user timezone.
                if (intval($this->value)) {
                    // Get a date object based on the correct timezone.
                    $date = Factory::getDate($this->value, 'UTC');
                    $date->setTimezone(new DateTimeZone($user->getParam('timezone', $config->get('offset'))));

                    // Transform the date string.
                    $this->value = $date->format('Y-m-d H:i:s', true, false);
                }
                break;
        }

        return $this->getCalendar($this->value, $this->name, $this->id, $format, $attributes);
    }


    protected function getRange()
    {
        $tbl = null;
        $id  = 0;

        if ($this->parents['project']) {
            $tbl = '#__jp_projects';
            $id  = $this->parents['project'];
        }

        if ($this->parents['milestone']) {
            $tbl = '#__jp_milestones';
            $id  = $this->parents['milestone'];
        }

        if ($this->parents['task']) {
            $tbl = '#__jp_tasks';
            $id  = $this->parents['task'];
        }

        if (!$tbl) {
            return array(null, null);
        }

        $db       = Factory::getDbo();
        $query    = $db->getQuery(true);
        $nulldate = $db->getNullDate();

        $query->select('start_date, end_date')
              ->from($tbl)
              ->where('id = ' . $db->quote($id));

        $db->setQuery($query);
        $result = $db->loadObject();

        if (!$result) {
            return array(null, null);
        }

        $start = null;
        $end   = null;

        if ($result->start_date != $nulldate) {
            $time  = strtotime($result->start_date);
            $start = date('Y-m-d', $time);
        }

        if ($result->end_date != $nulldate) {
            $time = strtotime($result->end_date);
            $end  = date('Y-m-d', $time);
        }

        return array($start, $end);
    }


    protected function getCalendar($value, $name, $id, $format = '%Y-%m-%d', $attribs = null)
    {
        static $done;

        if ($done === null) {
            $done = array();
        }

        $readonly = isset($attribs['readonly']) && $attribs['readonly'] == 'readonly';
        $disabled = isset($attribs['disabled']) && $attribs['disabled'] == 'disabled';

        $range_start = (isset($attribs['range_start']) ? $attribs['range_start'] : null);
        $range_end   = (isset($attribs['range_end'])   ? $attribs['range_end']   : null);

        if (is_array($attribs)) {
            unset($attribs['range_start']);
            unset($attribs['range_end']);
            $attribs = \Joomla\Utilities\ArrayHelper::toString($attribs);
        }

        $task   = Factory::getApplication()->input->get('task');
        $reload = strpos($task, 'reload');

        if (!$readonly && !$disabled) {
            // Load the calendar behavior
            HTMLHelper::_('behavior.calendar');
            HTMLHelper::_('bootstrap.tooltip');

            // Only display the triggers once for each control.
            if (!in_array($id, $done)) {
                $script = array();
                if (!$reload) {
                    $script[] = 'window.addEvent(\'domready\', function() {';
                }
                $script[] = 'Calendar.setup({';
                $script[] = '    inputField: "' . $id . '",';
                $script[] = '    ifFormat: "' . $format . '",';
                $script[] = '    button: "' . $id . '_img",';
                $script[] = '    align: "Tl",';
                $script[] = '    singleClick: true,';
                $script[] = '    firstDay: ' . Factory::getLanguage()->getFirstDay() . ',';
                $script[] = '    disableFunc: function(d)';
                $script[] = '    {';
                $script[] = '        var i_date  = parseInt(d.getTime());';
                if ($range_start) {
                    $script[] = '    var d_start = new Date("' . $range_start . 'T00:00:00");';
                    $script[] = '    var i_start = parseInt(d_start.getTime());';
                    $script[] = '    if (i_start > i_date) {return true;}';
                }
                if ($range_end) {
                    $script[] = '    var d_end = new Date("' . $range_end . 'T23:59:59");';
                    $script[] = '    var i_end = parseInt(d_end.getTime());';
                    $script[] = '    if (i_end < i_date) {return true;}';
                }
                $script[] = '    return false;';
                $script[] = '    }';
                $script[] = '});';
                if (!$reload) {
                    $script[] = '});';
                }
                $done[] = $id;
            }

            if (version_compare(JVERSION, '3.0.0', 'ge')) {
                return '<div class="input-append"><input type="text" title="' . (0 !== (int) $value ? HTMLHelper::_('date', $value) : '') . '" name="' . $name . '" id="' . $id
                . '" value="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '" ' . $attribs . ' /><button class="btn" id="' . $id . '_img"><i class="icon-calendar"></i></button></div>'
                . '<script type="text/javascript">' . implode("\n", $script) . '</script>';
            }
            else {
                return '<input type="text" title="' . (0 !== (int) $value ? HTMLHelper::_('date', $value, null, null) : '') . '" name="' . $name . '" id="' . $id
                . '" value="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '" ' . $attribs . ' />'
                . HTMLHelper::_('image', 'system/calendar.png', Text::_('JLIB_HTML_CALENDAR'), array('class' => 'calendar', 'id' => $id . '_img'), true)
                . '<script type="text/javascript">' . implode("\n", $script) . '</script>';
            }

        }
        else {
             return '<input type="text" title="' . (0 !== (int) $value ? HTMLHelper::_('date', $value, null, null) : '')
             . '" value="' . (0 !== (int) $value ? HTMLHelper::_('date', $value, 'Y-m-d H:i:s', null) : '') . '" ' . $attribs
             . ' /><input type="hidden" name="' . $name . '" id="' . $id . '" value="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '" />';
        }
    }
}
