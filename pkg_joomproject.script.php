<?php
/**
 * @package      Joomproject
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2013-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;

defined('_JEXEC') or die();


class  pkg_joomprojectInstallerScript
{
    /**
     * Called before any type of action
     *
     * @param     string              $route      Which action is happening (install|uninstall|discover_install)
     * @param     jadapterinstance    $adapter    The object responsible for running this script
     *
     * @return    boolean                         True on success
     */
    public function preflight($route, $parent)
    {
        // Do not run on uninstall.
        if ($route === 'uninstall')
        {
            return true;
        }

        // Prevent users from installing this on Joomla 3
        if (version_compare(JVERSION, '3.999.999', 'le'))
        {
            $msg = "<p>This version of Joomproject cannot run on Joomla 3. Please download and install JoomProject compatible with Joomla 3 instead. Kindly note that our site's Downloads page clearly indicates which version of our software is compatible with Joomla 3 and which version is compatible with Joomla 4.</p>";

            Log::add($msg, Log::WARNING, 'jerror');

            return false;
        }

        return true;
    }


    public function postflight($type, $parent)
    {
        $app = Factory::getApplication();

        // don't enable plugins if action type is update
        if ($type == 'update' || $type == 'uninstall') return;


        $db = Factory::getDBO();

        $manifest = $parent->getManifest();

        // Enable Plugins and set Default plugin
        $plugins = array();

        foreach ($manifest->files->folder as $file) {
            $attributes = $file->attributes();

            if ($attributes['enable'] && $attributes['type'] == 'plugin' && $attributes['enable'] == '1') {
                $plugins[] = $db->quote($attributes['id']);
            }
        }

        $query = 'UPDATE #__extensions'
            . ' SET enabled = 1'
            . ' WHERE element IN (' . implode(', ', $plugins) . ') AND type =' . $db->q("plugin");
        $db->setQuery($query);
        if (!$db->execute()) {
            $application = Factory::getApplication();
            $application->enqueueMessage('Failed to Enable plugins ', 'error');
        }

    }

}
