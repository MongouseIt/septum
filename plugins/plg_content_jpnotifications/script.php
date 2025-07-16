<?php
/**
 * @package      Joomproject Notifications
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();
use Joomla\CMS\Filesystem\File;

class plgContentJpnotificationsInstallerScript
{

    /**
     * Called after any type of action
     *
     * @param     string              $route      Which action is happening (install|uninstall|discover_install)
     * @param     jadapterinstance    $adapter    The object responsible for running this script
     *
     * @return    boolean                         True on success
     */
    public function postflight($route, $parent)
    {
        $action = strtolower($route);


        // Move CLI scripts to cli folder
        if ($action == 'install' || $action == 'update') {
        	
            $cli_j3_source = JPATH_SITE . '/plugins/content/jpnotifications/jpnotifications.php';
            $cli_j3_dest   = JPATH_SITE . '/cli/jpnotifications.php';


            if (file_exists($cli_j3_source)) {
                if (file_exists($cli_j3_dest)) {
                    if (File::delete($cli_j3_dest)) {
                        File::copy($cli_j3_source, $cli_j3_dest);
                    }
                }
                else {
                    File::copy($cli_j3_source, $cli_j3_dest);
                }
            }
        }

        // Remove CLI scripts
        if ($action == 'uninstall') {
            
            $cli_j3_dest = JPATH_SITE . '/cli/jpnotifications.php';

          

            if (file_exists($cli_j3_dest)) {
                File::delete($cli_j3_dest);
            }
        }

        return true;
    }
}
