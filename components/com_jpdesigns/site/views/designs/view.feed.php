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
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Document\Feed\FeedItem;


/**
 * Feed list view class.
 *
 */
class JPdesignsViewDesigns extends HtmlView
{
    /**
     * Generates a list of RSS feed items.
     *
     * @param null $tpl
     * @return    void
     */
    function display($tpl = null)
    {
        $app    = Factory::getApplication();
        $doc    = Factory::getDocument();

        $doc->link  = htmlspecialchars(Uri::getInstance()->toString());
        $feed_email = (($app->getCfg('feed_email') == '') ? 'site' : $app->getCfg('feed_email'));
        $site_email = $app->getCfg('mailfrom');

        // Set the query limit to the feed setting
        \Joomla\CMS\Factory::getApplication()->input->set('limit', (int) $app->getCfg('feed_limit', 20));

        // Get model data
        $rows = $this->get('Items');

        foreach($rows as $row)
        {
            // URL link to item
            $link = Route::_(JPdesignsHelperRoute::getDesignRoute($row->slug, $row->project_slug, $row->album_slug, '0:original'));

            // Strip html from feed item title
            $title = $this->escape($row->title);
            $title = html_entity_decode($title, ENT_COMPAT, 'UTF-8');

            $author = $row->author_name;
            $desc   = $row->description;
            $date   = ($row->created ? date('r', strtotime($row->created)) : '');

            // Load individual item creator class
            $item = new FeedItem();

            $item->title       = $title;
            $item->link        = $link;
            $item->description = $desc;
            $item->date        = $date;
            $item->author      = $author;
            $item->authorEmail = ($feed_email == 'site') ? $site_email : $row->author_email;

            // Categorize the item
            if ($row->album_id > 0) {
                // Strip html from feed item title
                $album = $this->escape($row->album_title);
                $album = html_entity_decode($album, ENT_COMPAT, 'UTF-8');

                $item->category = array($album);
            }

            // Loads item info into the RSS array
            $doc->addItem($item);
        }
    }
}
