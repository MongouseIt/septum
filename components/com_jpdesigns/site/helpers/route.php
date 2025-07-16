<?php
/**
 * @package      Joomproject Pro
 * @subpackage   Designs
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

/**
 * Designs Component Route Helper
 *
 * @static
 */
abstract class JPdesignsHelperRoute
{
    protected static $lookup;


    /**
     * Creates a link to a design
     *
     * @param     string    $id         The id slug
     * @param     string    $project    The project slug. Optional
     * @param     string    $album      The album slug. Optional
     * @param     string    $rev        The revision slug. Optional
     * @return    string    $link       The link
     */
    public static function getDesignRoute($id, $project = '', $album = '', $rev = '')
    {
        $link  = 'index.php?option=com_jpdesigns&view=design';
        $link .= '&filter_project=' . $project;
        $link .= '&filter_album=' . $album;
        $link .= '&id=' . $id;
        $link .= '&revision=' . $rev;

        $needles = array('id'  => array((int) $id));

        if ($item = JPApplicationHelper::itemRoute($needles, 'com_jpdesigns.design')) {
            $link .= '&Itemid=' . $item;
        }
        elseif ($item = JPApplicationHelper::itemRoute(null, 'com_jpdesigns.designs')) {
            $link .= '&Itemid=' . $item;
        }
        elseif ($item = JPApplicationHelper::itemRoute(null, 'com_jpdesigns.albums')) {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }


    /**
     * Creates a link to the designs overview
     *
     * @return    string    $link    The link
     */
    public static function getDesignsRoute($project = '', $album = '')
    {
        $link = 'index.php?option=com_jpdesigns&view=designs';

        if ($project) {
            $link .= '&filter_project=' . $project;
        }

        if ($album) {
            $link .= '&filter_album=' . $album;
        }

        if ($item = JPApplicationHelper::itemRoute(null, 'com_jpdesigns.designs')) {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }

    /**
     * Creates a link to the albums overview
     *
     * @return    string    $link    The link
     */
    public static function getAlbumsRoute($project = '')
    {
        $link = 'index.php?option=com_jpdesigns&view=albums';

        if ($project) {
            $link .= '&filter_project=' . $project;
        }

        if ($item = JPApplicationHelper::itemRoute(null, 'com_jpdesigns.albums')) {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }
}
