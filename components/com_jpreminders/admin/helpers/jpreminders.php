<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jpreminders
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

use Joomla\CMS\Date\Date;

defined('_JEXEC') or die();

jimport('joomla.filesystem.path');
jimport('joomproject.framework');

abstract class JPremindersHelper
{
    /**
     * The component name
     *
     * @var    string
     */
    public static $extension = 'com_jpreminders';

    /**
     * Indicates whether this component uses a project asset or not
     *
     * @var    boolean
     */
    public static $project_asset = true;


    /**
     * Configure the Linkbar.
     *
     * @param     string    $view    The name of the active view.
     *
     * @return    void
     */
    public static function addSubmenu($view)
    {

	    JoomprojectHelper::addSubmenu($view);

    }

    public static function reminderDates($start_date,$amount,$type_date,$repeats){
        $date = new Date($start_date);

	    $next_date  = array();
        $start_date = $date->format('Y-m-d H:i');

        $next_date[] = $start_date;

        switch ($type_date) {
            case 1:
                $type  = ' hour';
                break;
            case 2:
                $type  = ' day';
                break;
            case 3:
                $type  = ' week';
                break;
            case 4:
                $type  = ' month';
                break;
            default:
                $type  = ' hour';
        }

        for ($i = 0; $i < $repeats; $i++){
            $next_date[] = date('Y-m-d H:i',strtotime('+'.(($i+1)*$amount).$type,strtotime($start_date)));
        }

        return $next_date;
    }

    public static function reminder_Diff($start_date){
        $start_date = new Date($start_date);
        $curent_date = new Date('now');
        return  $start_date->diff($curent_date)->format('%Y-%m-%d %H:%I');

    }


}
