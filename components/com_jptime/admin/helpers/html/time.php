<?php
/**
* @package      Joomproject
* @subpackage   Timetracking
*
* @author       JoomBoost
* @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;


/**
 * Abstract class for Time sheet HTML elements
 *
 */
abstract class JHtmlTime
{
    /**
     * Formats the logged time into hours and seconds
     *
     * @param     integer    $secs     The seconds spent
     * @param     string     $style    (Optional) Format style
     *
     * @return    string               The formatted time string
     */
    public static function format($secs = 0, $style = 'literal')
    {
        static $nf_dec = null;
        static $nf_th  = null;

        if ($nf_dec == null) {
            $params = JPApplicationHelper::getProjectParams(0);

            $nf_dec = $params->get('decimal_delimiter', '.');
            $nf_th  = $params->get('thousands_delimiter', ',');
        }


        $secs   = intval($secs);
        $format = '';

        if (!$secs) return $format;

        $minutes = $secs / 60;
        $hours   = floor($minutes / 60);

        // Literal style
        switch(strtolower($style))
        {
            case 'decimal':
                if ($minutes > 0) {
                    $format = number_format($minutes / 60, 1, $nf_dec, $nf_th);
                }
                else {
                    $format = 0.00;
                }
                break;

            case 'literal':
            default:
                if ($hours > 0) {
                    $minutes = $minutes - ($hours * 60);
                }

                $minutes = floor($minutes);

                if ($hours) {
                    $format .= $hours . ' ' . ($hours > 1 ? Text::_('COM_JOOMPROJECT_TIME_HOURS') : Text::_('COM_JOOMPROJECT_TIME_HOUR'));
                }

                if ($minutes) {
                    if ($hours) $format .= ' ';
                    $format .= $minutes . ' ' . ($minutes > 1 ? Text::_('COM_JOOMPROJECT_TIME_MINUTES') : Text::_('COM_JOOMPROJECT_TIME_MINUTE'));
                }

                if (!$minutes && !$hours) {
                    $format .= '0 ' . Text::_('COM_JOOMPROJECT_TIME_MINUTES');
                }
                break;
        }


        return $format;
    }
}
