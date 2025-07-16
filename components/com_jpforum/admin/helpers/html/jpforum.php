<?php
/**
 * @package      Joomproject
 * @subpackage   Forum
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;


abstract class JHtmlPfforum
{
    static function repliesLabel($replies = 0, $activity = null)
    {
        static $format = null;

        if (is_null($format)) {
            $params = ComponentHelper::getParams('com_joomproject');
            $format = $params->get('date_format');

            if (!$format) {
                $format = Text::_('DATE_FORMAT_LC1');
            }
        }

        $html = array();
        $text = Text::plural('COM_JOOMPROJECT_N_REPLIES', (int) $replies);

        if ($replies == 0) {
            $html[] = '<span class="label">' . $text . '</span>';
        }
        else {
            $title = '';
            $class = '';
            $style = '';

            if ($activity && $activity != Factory::getDbo()->getNullDate()) {
                $title = ' title="' . JPDate::relative($activity) . '::' . HTMLHelper::_('date', $activity, $format) . '"';
                $style = ' style="cursor: help"';
                $class = ' ';
            }

            $html[] = '<span class="label label-success' . $class . '"' . $title . $style . '>' . $text . '</span>';
        }

        return implode('', $html);
    }
}
