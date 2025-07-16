<?php
/**
 * @package      Joomproject.Library
 * @subpackage   Object
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 20012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;

abstract class JPObjectHelper
{
    /**
     * Method to get the property changes between two item objects
     *
     * @param     object    $old        The old object
     * @param     object    $new        The new/updated object
     * @param     array     $props      The property/comparison method pairs
     *
     * @return    array     $changes    The changed property values
     */
    public static function getDiff($old, $new, $props)
    {
        $changes = array();

        if ($old instanceof Table) {
            $old_props = $old->getProperties(true);
        }
        else {
            $old_props = get_object_vars($old);
        }

        if ($new instanceof Table) {
            $new_props = $new->getProperties(true);
        }
        else {
            $new_props = get_object_vars($new);
        }

        foreach($props AS $prop)
        {
            if (!is_array($prop)) {
                $prop = array($prop, 'NE');
            }

            if (count($prop) != 2) continue;

            list($name, $cmp) = $prop;

            if (!array_key_exists($name, $new_props) || !array_key_exists($name, $old_props)) {
                continue;
            }

            switch (strtoupper($cmp))
            {
                case 'NE-SQLDATE':
                    // Not equal, not sql null date
                    if ($new->$name != $old->$name && $new->$name != Factory::getDbo()->getNullDate()) {
                        $changes[$name] = $new->$name;
                    }
                    break;

                case 'NE':
                default:
                    // Default, not equal
                    if ($new->$name != $old->$name) {
                        $changes[$name] = $new->$name;
                    }
                    break;
            }
        }

        return $changes;
    }


    public static function toArray($obj, $props)
    {
        $data = array();

        $obj_props = array();

        if ($obj instanceof Table) {
            $obj_props = $obj->getProperties(true);
        }
        else {
            $obj_props = get_object_vars($obj);
        }

        foreach($props AS $prop)
        {
            if (!is_array($prop)) {
                $prop = array($prop, 'NE');
            }

            if (count($prop) != 2) continue;

            list($name, $cmp) = $prop;

            switch (strtoupper($cmp))
            {
                case 'NE-SQLDATE':
                    // Not equal, not sql null date
                    if ($obj->$name != Factory::getDbo()->getNullDate()) {
                        $data[$name] = $obj->$name;
                    }
                    break;

                case 'NE':
                default:
                    // Default, not equal
                    $data[$name] = $obj->$name;
                    break;
            }
        }

        return $data;
    }


    public static function toContentItem(&$item)
    {
        static $content;

        if (is_object($item)) {
            if (!$content) {
                $content_table = Table::getInstance('Content');
                $content = $content_table->getProperties(true);
            }

            $item_props    = get_object_vars($item);
            $content_props = array_keys($content);

            foreach ($content_props AS $prop)
            {
                if (!array_key_exists($prop, $item_props)) {
                    $item->$prop = $content[$prop];
                }
            }
        }
    }
}