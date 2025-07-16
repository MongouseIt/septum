<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jpforum
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Document\Feed\FeedItem;




/**
 * Replies Feed list view class.
 *
 */
class JPforumViewReplies extends HtmlView
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
        $params = $app->getParams();

        $doc->link  = htmlspecialchars(Uri::getInstance()->toString());
        $feed_email = (($app->getCfg('feed_email') == '') ? 'site' : $app->getCfg('feed_email'));
        $site_email = $app->getCfg('mailfrom');

        // Set the query limit to the feed setting
        Factory::getApplication()->input->set('limit', (int) $app->getCfg('feed_limit', 20));

        // Get model data
        $rows = $this->get('Items');

        foreach($rows as $row)
        {
            // Load individual item creator class
            $item = new FeedItem();

            $item->title       = html_entity_decode($this->escape($row->topic_title), ENT_COMPAT, 'UTF-8');
            $item->link        = '';
            $item->description = $row->description;
            $item->date        = ($row->created ? date('r', strtotime($row->created)) : '');
            $item->author      = $row->author_name;
            $item->authorEmail = ($feed_email == 'site') ? $site_email : $row->author_email;

            // Categorize the item
            $item->category = array();

            // Project
            if (!empty($row->project_title)) {
                $item->category[] = html_entity_decode(
                    $this->escape($row->project_title),
                    ENT_COMPAT,
                    'UTF-8'
                );
            }

            // Topic
            if (!empty($row->topic_title)) {
                $item->category[] = html_entity_decode(
                    $this->escape($row->topic_title),
                    ENT_COMPAT,
                    'UTF-8'
                );
            }

            // Loads item info into the RSS array
            $doc->addItem($item);
        }
    }
}
