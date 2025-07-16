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
    require_once JPATH_SITE . '/components/com_jptasks/helpers/route.php';
}


class plgJPactivitiesJPtasksHelper extends plgJPactivitiesHelper
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

            if ($this->item->name == 'task') {
                $item_slug = $this->item->item_id . ':' . $meta->get('alias');
                $p_slug    = $this->item->xref_id . ':' . $meta->get('p_alias');
                $m_slug    = ($meta->get('m_id') ? $meta->get('m_id') . ':' . $meta->get('m_alias') : '');
                $l_slug    = ($meta->get('l_id') ? $meta->get('l_id') . ':' . $meta->get('l_alias') : '');

                $link = JPtasksHelperRoute::getTaskRoute($item_slug, $p_slug, $m_slug, $l_slug);
            }

            if ($this->item->name == 'tasklist') {
                $item_slug = $this->item->item_id . ':' . $meta->get('alias');
                $p_slug    = $this->item->xref_id . ':' . $meta->get('p_alias');
                $m_slug    = ($meta->get('m_id') ? $meta->get('m_id') . ':' . $meta->get('m_alias') : '');

                $link = JPtasksHelperRoute::getTasksRoute($p_slug, $m_slug, $item_slug);
            }
        }

        return Route::_($link);
    }

}