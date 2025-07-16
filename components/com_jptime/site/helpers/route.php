<?php
/**
 * @package      Joomproject
 * @subpackage   Timetracking
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2006-2012 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();
jimport('joomla.application.component.helper');


/**
 * Component Route Helper
 *
 * @static
 */
abstract class JPtimeHelperRoute
{
    /**
     * Creates a link to the timesheet overview
     *
     * @param     string    $project    The project slug. Optional
     *
     * @return    string    $link       The link
     */
    public static function getTimesheetRoute($project_slug = '')
    {

        if (!$project_slug || empty($project_slug)) {
            $project_slug = JPApplicationHelper::getActiveProjectId();
        }


        $link  = 'index.php?option=com_jptime&view=timesheet';
        $link .= '&filter_project=' . $project_slug;

        $needles = array('filter_project'   => array((int) $project_slug)
                        );

        if ($item = JPApplicationHelper::itemRoute($needles, 'com_jptime.timesheet')) {
            $link .= '&Itemid=' . $item;
        }
        elseif ($item = JPApplicationHelper::itemRoute(null, 'com_jptime.timesheet')) {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }



}
