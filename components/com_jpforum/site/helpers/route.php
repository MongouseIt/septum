<?php
/**
 * @package      Joomproject
 * @subpackage   Forum
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
abstract class JPforumHelperRoute
{
    /**
     * Creates a link to the topics overview
     *
     * @param     string    $project    The project slug. Optional
     *
     * @return    string    $link       The link
     */
    public static function getTopicsRoute($project = '')
    {
        $link  = 'index.php?option=com_jpforum&view=topics';
        $link .= '&filter_project=' . $project;

        $needles = array('filter_project'   => array((int) $project)
                        );

        if ($item = JPApplicationHelper::itemRoute($needles, 'com_jpforum.topics')) {
            $link .= '&Itemid=' . $item;
        }
        elseif ($item = JPApplicationHelper::itemRoute(null, 'com_jpforum.topics')) {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }


    /**
     * Creates a link to a topic item view
     *
     * @param     string    $id         The topic slug
     * @param     string    $project    The project slug. Optional
     *
     * @return    string    $link       The link
     */
    public static function getTopicRoute($id, $project = '')
    {
        return JPforumHelperRoute::getRepliesRoute($id, $project);
    }


    /**
     * Creates a link to a topic item view
     *
     * @param     string    $id         The topic slug
     * @param     string    $project    The project slug. Optional
     *
     * @return    string    $link       The link
     */
    public static function getRepliesRoute($id, $project = '')
    {
        $link  = 'index.php?option=com_jpforum&view=replies';
        $link .= '&filter_project=' . $project;
        $link .= '&filter_topic=' . $id;

        $needles = array('id' => array((int) $id));

        if ($item = JPApplicationHelper::itemRoute($needles, 'com_jpforum.topics')) {
            $link .= '&Itemid=' . $item;
        }
        elseif ($item = JPApplicationHelper::itemRoute(null, 'com_jpforum.topics')) {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }
}
