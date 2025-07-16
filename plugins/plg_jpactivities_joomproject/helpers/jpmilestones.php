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


require_once JPATH_PLUGINS . '/content/jpactivities/helpers/jpactivities.php';

if (Factory::getApplication()->isClient('site')) {
    // Include the route helper if we're in the frontend
    require_once JPATH_SITE . '/components/com_jpmilestones/helpers/route.php';
}


class plgJPactivitiesJPmilestonesHelper extends plgJPactivitiesHelper
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
            $p_slug    = $this->item->xref_id . ':' . $this->item->metadata->get('p_alias');

            $link = JPmilestonesHelperRoute::getMilestoneRoute($item_slug, $p_slug);
        }

        return Route::_($link);
    }
}