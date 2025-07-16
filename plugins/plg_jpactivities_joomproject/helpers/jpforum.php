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
    require_once JPATH_SITE . '/components/com_jpforum/helpers/route.php';
}


class plgJPactivitiesJPforumHelper extends plgJPactivitiesHelper
{
    protected function getTitleLink()
    {
        if ($this->client_id) {
            $link = 'index.php?option=' . $this->item->extension
                  . '&task=' . $this->item->name . '.edit'
                  . '&id=' . (int) $this->item->item_id;
        }
        else {
            $meta = &$this->item->metadata;

            if ($this->item->name == 'topic') {
                $item_slug = $this->item->item_id . ':' . $meta->get('alias');
                $p_slug    = $this->item->xref_id . ':' . $meta->get('p_alias');

                $link = JPforumHelperRoute::getTopicRoute($item_slug, $p_slug);
            }

            if ($this->item->name == 'reply') {
                $t_slug = $meta->get('t_id') . ':' . $meta->get('t_alias');
                $p_slug = $this->item->xref_id . ':' . $meta->get('p_alias');

                $link = JPforumHelperRoute::getTopicRoute($t_slug, $p_slug);
            }
        }

        return Route::_($link);
    }
}