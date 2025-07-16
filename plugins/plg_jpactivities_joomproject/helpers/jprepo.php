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
    require_once JPATH_SITE . '/components/com_jprepo/helpers/route.php';
}


class plgJPactivitiesJPrepoHelper extends plgJPactivitiesHelper
{
    protected function getTitleLink()
    {
        $meta = &$this->item->metadata;

        if ($this->item->name == 'directory') {
            if ($this->client_id) {
                $link = 'index.php?option=' . $this->item->extension
                      . '&filter_project=' . $this->item->xref_id
                      . '&filter_parent_id=' . (int) $this->item->item_id;
            }
            else {
                $item_slug = $this->item->item_id . ':' . $meta->get('alias');
                $p_slug    = $this->item->xref_id . ':' . $meta->get('p_alias');

                $link = JPrepoHelperRoute::getRepositoryRoute($p_slug, $item_slug, $meta->get('path'));
            }
        }

        if ($this->item->name == 'note') {
            if ($this->client_id) {
                $link  = 'index.php?option=' . $this->item->extension . '&task=note.edit'
                       . '&filter_project=' . $this->item->xref_id
                       . '&filter_parent_id=' . $meta->get('d_id')
                       . '&id=' . $this->item->item_id;
            }
            else {
                $item_slug = $this->item->item_id . ':' . $meta->get('alias');
                $p_slug    = $this->item->xref_id . ':' . $meta->get('p_alias');
                $d_slug    = $meta->get('d_id') . ':' . $meta->get('d_alias');

                $link = JPrepoHelperRoute::getNoteRoute($item_slug, $p_slug, $d_slug, $meta->get('path'));
            }
        }

        if ($this->item->name == 'file') {
            if ($this->client_id) {
                $link  = 'index.php?option=' . $this->item->extension . '&task=file.download'
                       . '&filter_project=' . $this->item->xref_id
                       . '&filter_parent_id=' . $meta->get('d_id')
                       . '&id=' . $this->item->item_id;
            }
            else {
                $item_slug = $this->item->item_id . ':' . $meta->get('alias');
                $p_slug    = $this->item->xref_id . ':' . $meta->get('p_alias');
                $d_slug    = $meta->get('d_id') . ':' . $meta->get('d_alias');

                $link = JPrepoHelperRoute::getFileRoute($item_slug, $p_slug, $d_slug, $meta->get('path'));
            }
        }

        return Route::_($link);
    }
}