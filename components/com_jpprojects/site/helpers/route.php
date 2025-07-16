<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jpprojects
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2006-2016 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

JLoader::register('JoomprojectHelperRoute',JPATH_SITE.'/components/com_joomproject/helpers/route.php');

/**
 * Projects Component Route Helper
 *
 * @static
 */
abstract class JPprojectsHelperRoute
{

    /**
     * Creates a link to the dashboard
     *
     * @param     string    $project_slug    The project slug. Optional
     *
     * @return    string    $link            The link
     */
    public static function getDashboardRoute($project_slug = '')
    {
        $link = 'index.php?option=com_joomproject&view=dashboard';

        // Get the id from the slug
        if (strrpos((string) $project_slug, ':') !== false) {
            $slug_parts = explode(':', $project_slug);
            $project_id = (int) $slug_parts[0];
        }
        else {
            $project_id = (int) $project_slug;
        }

        if ($project_id) {
            $link .= '&id=' . $project_slug;
        }

        $needles = array('id' => array($project_id));

        if ($item = JPApplicationHelper::itemRoute($needles, 'com_joomproject.dashboard')) {
            $link .= '&Itemid=' . $item;
        }
        elseif ($item = JPApplicationHelper::itemRoute(null, 'com_joomproject.dashboard')) {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }


    /**
     * Creates a link to the projects overview
     *
     * @param     string     $cat_slug    The category slug. Optional
     * @param     integer    $item        The desired menu item id to append. Optional
     *
     * @return    string     $link        The link
     */
    public static function getProjectsRoute($cat_slug = '', $item = null)
    {
        $link = 'index.php?option=com_jpprojects&view=projects';

        // Get the id from the slug
        if (strrpos((string) $cat_slug, ':') !== false) {
            $slug_parts = explode(':', $cat_slug);
            $cat_id = (int) $slug_parts[0];
        }
        else {
            $cat_id = (int) $cat_slug;
        }

        if ($cat_id) {
            $link .= '&filter_category=' . $cat_slug;
        }

        $needles = array('filter_category'  => array($cat_id));

        if (!is_null($item)) {
            $link .= '&Itemid=' . $item;
        }
        elseif ($item = JPApplicationHelper::itemRoute($needles, 'com_jpprojects.projects')) {
            $link .= '&Itemid=' . $item;
        }
        elseif ($item = JPApplicationHelper::itemRoute(null, 'com_jpprojects.projects')) {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }


    /**
     * Creates a link to the project form
     *
     * @param     string    $project_slug    The project slug. Optional
     *
     * @return    string    $link            The link
     */
    public static function getProjectEditRoute($project_slug = '')
    {
        $link = 'index.php?option=com_jpprojects&task=form.edit&id=' . $project_slug;

        // Get the form menu item
        $item = JPApplicationHelper::itemRoute(null, 'com_jpprojects.form');

        if (!$item) {
            $app = Factory::getApplication();

            // Stay on current menu item if we are viewing a project list
            if ($app->input->get('option') == 'com_jpprojects' && $app->input->get('view') == 'projects') {
                $item = JPApplicationHelper::getActiveMenuItemId();
            }
            else {
                // Find overview menu item
                $item = JPApplicationHelper::itemRoute(null, 'com_jpprojects.projects');
            }
        }

        if ($item) {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }
}
