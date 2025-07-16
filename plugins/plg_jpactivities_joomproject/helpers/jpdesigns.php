<?php
/**
 * @package      plg_jpactivities_joomproject
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;


if (Factory::getApplication()->isClient('site')) {
    // Include the route helper if we're in the frontend
    require_once JPATH_SITE . '/components/com_jpdesigns/helpers/route.php';
}


class plgJPactivitiesJPdesignsHelper extends plgJPactivitiesHelper
{
    /**
     * Method to translate an activity item title, adding a link to it if possible
     *
     * @return    string                The translated and formatted title
     */
    protected function getTitle()
    {
        // Check the cache
        $key = $this->item->name . '.' . $this->item->item_id;

        if (isset($this->cache_title[$key])) {
            return $this->cache_title[$key];
        }

        $access = (($this->item->asset_exists > 0) ? $this->getTitleAccess() : false);

        if ($this->item->name == 'revision') {
            $title = $this->item->metadata->get('d_title') . ' - ' . $title = $this->item->title;
        }
        else {
            $title = $this->item->title;
        }

        if ($access) {
            $this->cache_title_link[$key] = $this->getTitleLink();

            $this->cache_title[$key] = '<a href="' . $this->cache_title_link[$key] . '">' . htmlspecialchars($title, ENT_COMPAT, 'UTF-8') . '</a>';
        }
        else {
            $this->cache_title_link[$key] = null;

            $this->cache_title[$key] = htmlspecialchars($title, ENT_COMPAT, 'UTF-8');
        }

        return $this->cache_title[$key];
    }


    protected function getTitleLink()
    {
        if ($this->client_id) {
            $link = 'index.php?option=' . $this->item->extension
                  . '&task=' . $this->item->name . '.edit'
                  . '&id=' . (int) $this->item->item_id;
        }
        else {
            $meta = &$this->item->metadata;

            $item_slug = $this->item->item_id . ':' . $meta->get('alias');
            $p_slug    = $this->item->xref_id . ':' . $meta->get('p_alias');

            if ($this->item->name == 'album') {
                $link = JPdesignsHelperRoute::getDesignsRoute($p_slug, $item_slug);
            }

            if ($this->item->name == 'design') {
                $a_slug = ($meta->get('a_id') ? $meta->get('a_id') . ':' . $meta->get('a_alias') : 0);
                $link = JPdesignsHelperRoute::getDesignRoute($item_slug, $p_slug, $a_slug);
            }

            if ($this->item->name == 'revision') {
                $d_slug = intval($meta->get('d_id')) . ':' . $meta->get('d_alias');
                $a_slug = ($meta->get('a_id') ? intval($meta->get('a_id')) . ':' . $meta->get('a_alias') : 0);
                $link   = JPdesignsHelperRoute::getDesignRoute($d_slug, $p_slug, $a_slug, $item_slug);
            }
        }

        return Route::_($link);
    }
}