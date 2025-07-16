<?php
/**
 * @package      Joomproject
 * @subpackage   Repository
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\Registry\Registry;
use Joomla\CMS\Factory;

/**
 * Dashboard Helper Class
 *
 */
abstract class JPrepoHelperDashboard
{
    /**
     * Returns a list of buttons for the frontend
     *
     * @return    array
     */
    public static function getSiteButtons()
    {
        $user = Factory::getApplication()->getIdentity();
        $app  = Factory::getApplication();
        $pid  = (int) $app->getUserState('com_joomproject.project.active.id');

        $buttons = array();

        if (!$pid || defined('JPDEMO')) return $buttons;

        // Get the project root dir
        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select('attribs')
              ->from('#__jp_projects')
              ->where('id = ' . $pid);

        $db->setQuery($query);
        $project_attribs = $db->loadResult();

        $project_params = new Registry;
        $project_params->loadString((string)$project_attribs);

        $repo_dir = (int) $project_params->get('repo_dir');
        if (!$repo_dir) return $buttons;

        // Get the access of the dir
        $query->clear()
              ->select('access')
              ->from('#__jp_repo_dirs')
              ->where('id = ' . $repo_dir);

        $db->setQuery($query);
        $access = (int) $db->loadResult();

        // Check viewing access
        if (!in_array($access, $user->getAuthorisedViewLevels()) && !$user->authorise('core.admin')) {
            return $buttons;
        }

        // Check permission
        if (!$user->authorise('core.create', 'com_jprepo.directory.' . $repo_dir)) {
            return $buttons;
        }

		$buttons[] = array(
            'title' => 'MOD_JP_DASH_BUTTONS_ADD_FILE',
            'link'  => JPrepoHelperRoute::getRepositoryRoute($pid, $repo_dir) . '&task=fileform.add',
            'icon'  => "<i class='far text-muted fa-file fa-3x'></i>",
            'iconClass' => 'far text-muted fa-file'
        );

        return $buttons;
    }


    /**
     * Returns a list of buttons for the backend
     *
     * @return    array
     */
    public static function getAdminButtons()
    {
        $user    = Factory::getApplication()->getIdentity();
        $buttons = array();

        if ($user->authorise('core.manage', 'com_jprepo')) {
            $buttons[] = array(
                'title' => 'COM_JOOMPROJECT_SUBMENU_REPO',
                'link'  => 'index.php?option=com_jprepo',
                'icon'  => '<i class="wt-icon-folder"></i>',
                'iconName' => 'copy', // for count card
                'iconClass' => 'bg-orange text-white', // for count card
                'iconColor' => 'text-orange',
                'countClass' => 'text-dark', // for count card
                'count' => JoomprojectHelperStats::getCount(null,'jp_repo_files')
            );
        }

        return $buttons;
    }
}