<?php
/**
 * @package      pkg_jpactivities
 * @subpackage   plg_jpactivities_content
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2013 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;

if (Factory::getApplication()->isClient('site')) {
    // Include the content route helper if we're in the frontend
    require_once JPATH_SITE . '/components/com_content/helpers/route.php';
}


/**
 * Content Category Activity Translation Helper Class
 *
 */
class plgJPactivitiesContentCategoryHelper extends plgJPactivitiesHelper
{
    /**
     * Method to get the item title link
     *
     * @return    string              The title link
     */
    protected function getTitleLink()
    {
        if ($this->client_id) {
            $link = 'index.php?option=com_categories'
                  . '&task=' . $this->item->name . '.edit'
                  . '&id=' . (int) $this->item->item_id;
        }
        else {
            $item_slug = $this->item->item_id . ':' . $this->item->metadata->get('alias');

            $link = RouteHelper::getCategoryRoute($item_slug);
        }

        return Route::_($link);
    }
}