<?php
/**
 * @package      pkg_projectknife
 * @subpackage   com_jpmilestones
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2006-2016 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die;
use Joomla\CMS\Factory;

/**
 * Component Route Helper
 *
 * @static    
 */
abstract class JPmilestonesHelperRoute
{
    /**
     * Creates a link to the milestones overview
     *
     * @param     string     $project_slug    The project slug. Optional
     * @param     integer    $item            The desired menu item id to append. Optional
     *
     * @return    string     $link            The link
     */
    public static function getMilestonesRoute($project_slug = '', $item = null)
    {
        if (!$project_slug) {
            $project_slug = JPApplicationHelper::getActiveProjectId();
        }

        $link = 'index.php?option=com_jpmilestones&view=milestones&filter_project=' . $project_slug;

        // Get the id from the slug
        if (strrpos($project_slug, ':') !== false) {
            $slug_parts = explode(':', $project_slug);
            $project_id = (int) $slug_parts[0];
        }
        else {
            $project_id = (int) $project_slug;
        }

        $needles = array('filter_project'  => array($project_id));

        if (!is_null($item)) {
            $link .= '&Itemid=' . $item;
        }
        elseif ($item = JPApplicationHelper::itemRoute($needles, 'com_jpmilestones.milestones')) {
            $link .= '&Itemid=' . $item;
        }
        elseif ($item = JPApplicationHelper::itemRoute(null, 'com_jpmilestones.milestones')) {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }


    /**
     * Creates a link to a milestone item view
     *
     * @param     string    $milestone_slug    The milestone slug
     * @param     string    $project_slug      The project slug. Optional
     *
     * @return    string    $link              The link
     */
    public static function getMilestoneRoute($milestone_slug, $project_slug = '')
    {
        if (!$project_slug) {
            $project_slug = JPApplicationHelper::getActiveProjectId();
        }

        $link = 'index.php?option=com_jpmilestones&view=milestone&filter_project=' . $project_slug . '&id=' . $milestone_slug;

        // Get the id from the slug
        if (strrpos($milestone_slug, ':') !== false) {
            $slug_parts   = explode(':', $milestone_slug);
            $milestone_id = (int) $slug_parts[0];
        }
        else {
            $milestone_id = (int) $milestone_slug;
        }

        $needles = array('id' => array($milestone_slug));
        $item    = JPApplicationHelper::itemRoute($needles, 'com_jpmilestones.milestone');

        if (!$item) {
            $app = Factory::getApplication();

            // Stay on current menu item if we are viewing a milestone list
            if ($app->input->get('option') == 'com_jpmilestones' && $app->input->get('view') == 'milestones') {
                $item = JPApplicationHelper::getActiveMenuItemId();
            }
            else {
                // Find overview menu item
                $item = JPApplicationHelper::itemRoute(null, 'com_jpmilestones.milestones');
            }
        }

        if ($item) {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }
}
