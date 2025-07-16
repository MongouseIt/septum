<?php
/**
 * @package      plg_jpactivities_joomproject
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;


require_once JPATH_PLUGINS . '/content/jpactivities/helpers/jpactivities.php';

if (Factory::getApplication()->isClient('site')) {
    // Include the route helper if we're in the frontend
    require_once JPATH_SITE . '/components/com_jptime/helpers/route.php';
}

class plgJPactivitiesJPtimeHelper extends plgJPactivitiesHelper
{
    /**
     * Method to translate an single activity record
     *
     * @param     object    $item    The record to translate
     *
     * @return    object    $item    The translated item
     */
    public function translateItem($item)
    {
        $this->item = $item;

        // Load meta data into JRegistry
        $metadata = $this->item->metadata;
        $this->item->metadata = new Registry();
        $this->item->metadata->loadString((string)$metadata);

        $key = strtoupper($item->extension) . '_UA_' . strtoupper($item->name) . '_' . strtoupper($item->event_name);

        // Check cache
        if (!isset($this->cache_token[$key])) {
            $this->cache_token[$key] = Text::_($key);
        }

        // Translate
        if ($this->item->event_name == 'save_new') {
            $seconds = (int) $this->item->metadata->get('log_time');
            $minutes = ($seconds > 0 ? round($seconds / 60): 1);

            $item->text = sprintf(
                $this->cache_token[$key],
                $this->getUserName(),
                $minutes,
                $this->getTitle()
            );
        }
        else {
            $item->text = sprintf(
                $this->cache_token[$key],
                $this->getUserName(),
                $this->getTitle()
            );
        }


        // Get the feed link
        if ($this->format == 'feed') {
            $key = $this->item->name . '.' . $this->item->item_id;
            $item->feed_link = (isset($this->cache_title_link[$key]) ? $this->cache_title_link[$key] : '');
        }

        return $item;
    }


    protected function getTitleLink()
    {
        $meta = &$this->item->metadata;

        if ($this->client_id) {
            $link = 'index.php?option=' . $this->item->extension
                  . '&filter_project=' . $this->item->xref_id
                  . '&filter_task=' . (int) $meta->get('t_id');
        }
        else {
            $p_slug = $this->item->xref_id . ':' . $this->item->metadata->get('p_alias');

            $link = JPtimeHelperRoute::getTimesheetRoute($p_slug)
                  . '&filter_task=' . (int) $meta->get('t_id');
        }

        return Route::_($link);
    }
}