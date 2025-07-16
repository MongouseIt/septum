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
    require_once JPATH_SITE . '/components/com_jpprojects/helpers/route.php';
}


class plgJPactivitiesJPprojectsHelper extends plgJPactivitiesHelper
{
    protected function getTitleLink()
    {
        if ($this->client_id) {
            $link = 'index.php?option=' . $this->item->extension
                  . '&task=' . $this->item->name . '.edit'
                  . '&id=' . (int) $this->item->item_id;
        }
        else {
            $item_slug = $this->item->item_id . ':' . $this->item->metadata->get('alias');

            $link = JPprojectsHelperRoute::getDashboardRoute($item_slug);
        }

        return Route::_($link);
    }
}