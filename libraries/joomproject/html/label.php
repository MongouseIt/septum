<?php
/**
* @package      pkg_joomproject
* @subpackage   lib_joomproject
*
* @author       JoomBoost
* @copyright    Copyright (C) 2006-2013 JoomBoost. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/


defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;


abstract class JPhtmlLabel
{
    /**
     * Returns a list of label filters
     *
     * @param     string     $asset      The asset filter group
     * @param     integer    $project    The project filter
     *
     * @return    string                 The label html
     */
    public static function filter($asset, $project = 0, $selected = array(), $filter_style = '',$showSearchButton = true)
    {
        if (!$project) $project = JoomprojectHelper::getActiveProjectId();
        if (!$project) return '';

        if (!is_array($selected)) $selected = array();

        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        if ($asset == 'com_jprepo') {
            $asset = $db->quote('com_jprepo.directory')
                   . 'OR a.asset_group = ' . $db->quote('com_jprepo.file')
                   . 'OR a.asset_group = ' . $db->quote('com_jprepo.note');
        }
        else {
            $asset = $db->quote($db->escape($asset));
        }

        $query->select('a.id, a.title, a.style')
              ->from('#__jp_labels AS a')
              ->where('a.project_id = ' . $db->quote((int) $project))
              ->where('(a.asset_group = ' . $db->quote('com_jpprojects.project') . ' OR a.asset_group = ' . $asset . ')')
              ->order('a.style, a.title ASC');

        $db->setQuery($query);
        $items = (array) $db->loadObjectList();

        $html = array();

        if (!count($items)) {
            return  '';
        }

        $html[] = '<ul class="list-group list-group-horizontal ms-0">';

        foreach ($items AS $item)
        {
            $checked = (in_array($item->id, $selected) ? ' checked="checked"' : '');
            $class   = ($item->style != '' ? ' ' . $item->style : '');
            $cbid    = htmlspecialchars(str_replace('.', '_', $asset) . '_label_' . $item->id, ENT_COMPAT, 'UTF-8');

            $html[] = '<li class="list-group-item border-0">';
            $html[] = '<div class="form-check">';
            $html[] = '<input type="checkbox" id="' . $cbid . '" class="form-check-input" name="filter_label[]" value="' . (int) $item->id . '"' . $checked . '/>';
            $html[] = '<label class="form-check-label checkbox" for="' . $cbid . '" style="cursor:pointer">';
            $html[] = '<span class="badge' . $class . '">' . htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8') . '</span>';
            $html[] = '</label>';
            $html[] = '</div>';
            $html[] = '</li>';
        }

        $html[] = '</ul>';

        if($showSearchButton){
	        $html[] = '<div class="d-block mb-4 mt-4">';
	        $html[] = '<button class="btn btn-secondary btn-sm" onclick="this.form.submit()"><i class="fas fa-search"></i> ' . Text::_('JSEARCH_FILTER_SUBMIT') . '</button>';
	        $html[] = '</div>';
        }

        return implode('', $html);
    }


    /**
     * Returns the labels of an item as formatted html
     *
     * @param     array     $labels    The labels
     *
     * @return    string               The label html
     */
    public static function labels($labels = null)
    {
        if (!is_array($labels)) {
            return '';
        }

        $html = array();

        foreach ($labels AS $label)
        {
            $style  = ($label->style ? ' ' . $label->style : '');
            $title  = htmlspecialchars($label->title, ENT_COMPAT, 'UTF-8');
            $html[] = '<span class="badge' . $style. ' p-1"><i class="far fa-bookmark"></i> ' . $title . '</span>';
        }

        return implode(' ', $html);
    }


    /**
     * Returns a date as literal label
     *
     * @param     string    $date       The date
     * @param     string    $compact    If set to true, will only show the amount of days
     *
     * @return    string                The label html
     */
    public static function datetime($date, $compact = false, $options = array())
    {
        static $format = null;
        static $time_offset = null;

        HTMLHelper::_('bootstrap.tooltip','.hasTooltip');

        if (is_null($format)) {
            $params = ComponentHelper::getParams('com_joomproject');
            $format = $params->get('date_format');

            if (!$format) {
                $format = Text::_('DATE_FORMAT_LC1');
            }
        }

        if (is_null($time_offset)) {
            $config = Factory::getConfig();
		    $user   = Factory::getApplication()->getIdentity();

            $time_offset = $user->getParam('timezone', $config->get('offset'));
        }

        if (!isset($options['tz'])) {
            $options['tz'] = true;
        }


        $string = JPDate::relative($date, $options['tz']);

        if ($string == false) return '';

        if ($options['tz']) {
            // Get a date object based on UTC.
			$dateObj  = Factory::getDate($date, 'UTC');
            $now_date = Factory::getDate('now', 'UTC');

			// Set the correct time zone based on the user configuration.
			$dateObj->setTimeZone(new DateTimeZone($time_offset));
            $now_date->setTimeZone(new DateTimeZone($time_offset));

            $timestamp = strtotime($dateObj->calendar('Y-m-d H:i:s', true));
            $now       = strtotime($now_date->format('Y-m-d H:i:s', true, false));
        }
        else {
            $timestamp = strtotime($date);
            $now = time();
        }

        $remaining = $timestamp - $now;


        $is_past   = ($remaining <= 0) ? true : false;
        $tooltip   = HTMLHelper::_('date', $date, $format, ($options['tz'] ? false : true));


        if ($compact) {
            $days   = round($remaining / 86400);

            $isMonths = false;
            $durationType = 'D';

            if ($days == 0) {
                $string = '0';
            }
            else {

                if($days > 90){

                    $sub_struct_month = ($days / 30) ;
                    $days = floor($sub_struct_month);
                    $isMonths  = true;
                    $durationType = 'M';

                }

                $string = ($is_past ? '' : '+') . $days;
                $string = Text::sprintf('COM_JOOMPROJECT_DURATION_'.$durationType,$string);
            }
        }

        $past_class   = (isset($options['past-class'])   ? $options['past-class']   : 'text-danger');
        $past_icon    = (isset($options['past-icon'])    ? $options['past-icon']    : 'warning');
        $future_class = (isset($options['future-class']) ? $options['future-class'] : 'text-success');
        $future_icon  = (isset($options['future-icon'])  ? $options['future-icon']  : 'calendar');

        // enable relative dates
        if(!ComponentHelper::getParams('com_joomproject')->get('relative_date',1)){
            $string = HTMLHelper::_('date', $date, Text::_('DATE_FORMAT_LC5'));

        }

        $html = array();
        $html[] = '<span class="hasTooltip p-1 badge ' . ($is_past ? $past_class : $future_class);
        $html[] = '"  data-bs-toggle="tooltip" data-placement="top"  title="' . $tooltip . '" style="cursor: help">';
        $html[] = '<span aria-hidden="true" class="fas fa-' . ($is_past ? $past_icon : $future_icon) . '"></span> ';
        $html[] = $string;
        $html[] = '</span>';

        return implode('', $html);
    }


    /**
     * Returns the author of an item as label
     *
     * @param     string    $name      The user name
     * @param     string    $date      The date
     * @param     string    $format    The new date format for the tooltip
     *
     * @return    string               The label html
     */
    public static function author($name = null, $date = null, $format = null)
    {
        if (!$name || !$date) {
            return '';
        }

        $string = JPDate::relative($date);

        if ($string == false) {
            return '';
        }

        $tooltip = $string . '::' . HTMLHelper::_('date', $date, ($format ? $format : Text::_('DATE_FORMAT_LC1')));

        $html = array();
        $html[] = '<span class="badge text-secondary p-1" data-bs-toggle="tooltip" data-placement="top" title="' . $tooltip . '" style="cursor: help">';
        $html[] = '<i class="fas fa-user"></i> ';
        $html[] = htmlspecialchars($name, ENT_COMPAT, 'UTF-8');
        $html[] = '</span>';

        return implode('', $html);
    }


    /**
     * Returns the access level(s) of an item as label
     *
     * @param     integer    $id    The access level id
     *
     * @return    string            The label html
     */
    public static function access($id = null)
    {
        static $is_admin = null;
        static $cache    = array();

        if (is_null($is_admin)) {
            $is_admin = Factory::getApplication()->getIdentity()->authorise('core.admin');
        }

        if (!$is_admin || !$id) {
            return '';
        }

        if (!isset($cache[$id]) && $id) {
            $db    = Factory::getDbo();
            $query = $db->getQuery(true);

            $cache[$id] = array();

            $query->select('rules')
                  ->from('#__viewlevels')
                  ->where('id = ' . $db->quote((int) $id));

            $db->setQuery($query);
            $rules = $db->loadResult();

            if ($rules) {
                $ids = json_decode($rules);

                foreach ($ids AS $gid)
                {
                    $query->clear();
                    $query->select('title')
                          ->from('#__usergroups')
                          ->where('id = ' . $db->quote((int) $gid));

                    $db->setQuery($query);
                    $title = $db->loadResult();

                    if ($title) {
                        $cache[$id][] = htmlspecialchars($title, ENT_COMPAT, 'UTF-8');
                    }
                }
            }
        }

        $titles = $cache[$id];
        $count  = count($titles);
        $html   = array();

        if ($count == 1) {
            $html[] = '<span class="badge text-secondary access">';
            $html[] = '<i class="fas fa-eye"></i> ';
            $html[] = htmlspecialchars($titles[0], ENT_COMPAT, 'UTF-8');
            $html[] = '</span>';
        }
        else {
            $count = $count - 1;
            $name  = array_reverse($titles);
            $name  = array_pop($name);
            $name  = trim($name);

            $popovercontent = Text::_('JGRID_HEADING_ACCESS') . '::' . htmlspecialchars(implode('<br/>', $titles), ENT_COMPAT, 'UTF-8');

            $html[] = '<span class="badge text-secondary"  data-toggle="popover"  data-content="' . $popovercontent . '"';
            $html[] = '<i class="fas fa-eye"></i> ';
            $html[] = htmlspecialchars($name, ENT_COMPAT, 'UTF-8') . ' +' . $count;
            $html[] = '</span>';
        }

        return implode('', $html);
    }
}
