<?php
/**
 * @package      Joomproject
 * @subpackage   Users
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2006-2012 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */
defined('_JEXEC') or die();

use Joomla\CMS\MVC\View\GenericDataException;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\View\HtmlView;





class JPusersViewUsers extends HtmlView
{
    protected $items;
    protected $pagination;
    protected $params;
    protected $state;
    protected $toolbar;
    protected $pageclass_sfx;
    protected $user;
    protected $sort_options;
    protected $order_options;


    /**
     * Display the view
     *
     */
    public function display($tpl = null)
    {
       $app = Factory::getApplication();

        // Check for layout override
        $active = $app->getMenu()->getActive();
        if (isset($active->query['layout']) && (Factory::getApplication()->input->getCmd('layout') == '')) {
            $this->setLayout($active->query['layout']);
        }

        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state      = $this->get('State');
        $this->params     = $this->state->params;
        $this->user       = Factory::getApplication()->getIdentity();

        $this->toolbar       = $this->getToolbar();
        $this->sort_options  = $this->getSortOptions();
        $this->order_options = $this->getOrderOptions();

        // Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx',''));

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        // Prepare the document
        $this->prepareDocument();

        parent::display($tpl);
    }


    /**
     * Prepares the document
     *
     */
    protected function prepareDocument()
    {
        $app     = Factory::getApplication();
        $menu    = $app->getMenu()->getActive();
        $pathway = $app->getPathway();
        $title   = null;

        // Because the application sets a default page title, we need to get it from the menu item itself
        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        }
        else {
            $this->params->def('page_heading', Text::_('COM_JOOMPROJECT_USERS'));
        }

        // Set the page title
        $title = $this->params->get('page_title', '');

        if (empty($title)) {
            $title = $app->getCfg('sitename');
        }
        elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
            $title = Text::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
        }
        elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
            $title = Text::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
        }

        $this->document->setTitle($title);

        // Set crawler behavior info
        if ($this->params->get('robots')) {
            $this->document->setMetadata('robots', $this->params->get('robots'));
        }

        // Set page description
        if ($this->params->get('menu-meta_description')) {
            $this->document->setDescription($desc);
        }

        // Set page keywords
        if ($this->params->get('menu-meta_keywords')) {
            $this->document->setMetadata('keywords', $keywords);
        }

        // Add feed links
        if ($this->params->get('show_feed_link', 1)) {
            $link = '&format=feed&limitstart=';
            $attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
            $this->document->addHeadLink(Route::_($link . '&type=rss'), 'alternate', 'rel', $attribs);
            $attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
            $this->document->addHeadLink(Route::_($link . '&type=atom'), 'alternate', 'rel', $attribs);
        }
    }


    /**
     * Generates the toolbar for the top of the view
     *
     * @return    string    Toolbar with buttons
     */
    protected function getToolbar()
    {
        JPToolbar::filterButton($this->state->get('filter.isset'));

        return JPToolbar::render();
    }


    /**
     * Generates the table sort options
     *
     * @return    array    HTML list options
     */
    protected function getSortOptions()
    {
        $options = array();

        $options[] = HTMLHelper::_('select.option', '', Text::_('COM_JOOMPROJECT_ORDER_SELECT'));
        $options[] = HTMLHelper::_('select.option', 'a.name', Text::_('COM_JOOMPROJECT_ORDER_NAME'));
        $options[] = HTMLHelper::_('select.option', 'a.username', Text::_('COM_JOOMPROJECT_ORDER_USERNAME'));
        $options[] = HTMLHelper::_('select.option', 'a.lastvisitDate', Text::_('COM_JOOMPROJECT_ORDER_LOGIN'));

        return $options;
    }


    /**
     * Generates the table order options
     *
     * @return    array    HTML list options
     */
    protected function getOrderOptions()
    {
        $options = array();

        $options[] = HTMLHelper::_('select.option', '', Text::_('COM_JOOMPROJECT_ORDER_SELECT_DIR'));
        $options[] = HTMLHelper::_('select.option', 'asc', Text::_('COM_JOOMPROJECT_ORDER_ASC'));
        $options[] = HTMLHelper::_('select.option', 'desc', Text::_('COM_JOOMPROJECT_ORDER_DESC'));

        return $options;
    }
}
