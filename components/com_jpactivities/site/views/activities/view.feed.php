<?php
/**
 * @package      pkg_jpactivities
 * @subpackage   com_jpactivities
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2013 JoomBoost.com. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Document\Feed\FeedItem;




/**
 * User Activity Feed list view class.
 *
 */
class JPactivitiesViewActivities extends HtmlView
{
    /**
     * Generates a list of RSS feed items.
     *
     * @return    void
     */
    function display()
    {
        $app    = Factory::getApplication();
        $doc    = Factory::getDocument();
        $params = $app->getParams();

        $std_link   = Route::_(JPactivitiesHelperRoute::getActivitiesRoute());
        $doc->link  = htmlspecialchars(Factory::getURI()->toString());
        $feed_email = (($app->getCfg('feed_email') == '') ? 'site' : $app->getCfg('feed_email'));
        $site_email = $app->getCfg('mailfrom');

        // Set the query limit to the feed setting
	    $app->getInput()->set('limit', (int) $app->getCfg('feed_limit', 20));

        // Get model data
        $rows = $this->get('Items');

        foreach($rows as $row)
        {
            // Load individual item creator class
            $item = new FeedItem();

            $item->title       = html_entity_decode(strip_tags($row->text), ENT_COMPAT, 'UTF-8');
            $item->link        = ($row->feed_link ? $row->feed_link : $std_link);
            $item->date        = ($row->created ? date('r', strtotime($row->created)) : '');
            $item->author      = $row->author_name;
            $item->authorEmail = ($feed_email == 'site') ? $site_email : $row->author_email;

            // Loads item info into the RSS array
            $doc->addItem($item);
        }
    }
}
