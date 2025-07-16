<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jprepo
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */


defined('_JEXEC') or die();

use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\View\HtmlView;





/**
 * Repository note revisions view class.
 *
 */
class JPrepoViewNoteRevisions extends HtmlView
{
    /**
     * CSS page class suffix
     *
     * @var    string
     */
    protected $pageclass_sfx;

    /**
     * Note head revision record
     *
     * @var    object
     */
    protected $item;

    /**
     * List of items to display
     *
     * @var    array
     */
    protected $items;

    /**
     * Model parameters
     *
     * @var    object
     */
    protected $params;

    /**
     * Model state object
     *
     * @var    object
     */
    protected $state;

    /**
     * Toolbar html code
     *
     * @var    string
     */
    protected $toolbar;

    /**
     * Object holding user permissions
     *
     * @var    object
     */
    protected $access;


    /**
     * Display the view
     *
     * @return    void
     */
    public function display($tpl = null)
    {

        if(!\JoomProject\Permission\GlobalAccess::check('com_jprepo'))
            return;

        $app    = Factory::getApplication();
        $user   = Factory::getApplication()->getIdentity();
        $active = $app->getMenu()->getActive();

        $this->item    = $this->get('Item');
        $this->items   = $this->get('Items');
        $this->state   = $this->get('State');
        $this->params  = $this->state->params;
        $this->access  = JPrepoHelper::getActions();
        $this->toolbar = $this->getToolbar();

        // Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx',''));

        if ($this->state->get('list.direction') == 'desc') {
            array_unshift($this->items, $this->item);
        }
        else {
            $this->items[] = $this->item;
        }

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        if (!$this->item || empty($this->item->id)) {
            \Joomla\CMS\Factory::getApplication()->enqueueMessage( Text::_('COM_JOOMPROJECT_ERROR_NOTE_NOT_FOUND'),'error');
            return false;
        }

        // Check access
        $user = Factory::getApplication()->getIdentity();

        if (!$user->authorise('core.admin', 'com_jprepo')) {
            $levels = $user->getAuthorisedViewLevels();

            if (!in_array($this->item->access, $levels)) {
                \Joomla\CMS\Factory::getApplication()->enqueueMessage( Text::_('JERROR_ALERTNOAUTHOR'),'warning');
                return false;
            }
        }

        // Check for layout override
        if (isset($active->query['layout']) && (\Joomla\CMS\Factory::getApplication()->input->getCmd('layout') == '')) {
            $this->setLayout($active->query['layout']);
        }

        // Prepare the document
        $this->prepareDocument();

        // Display the view
        parent::display($tpl);
    }


    /**
     * Prepares the document
     *
     */
    protected function prepareDocument()
    {
        $app     = Factory::getApplication();
        $menus   = $app->getMenu();
        $pathway = $app->getPathway();
        $title   = null;

        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menu = $menus->getActive();

        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        }
        else {
            $t = Text::_('COM_JOOMPROJECT_PAGE_VIEW_NOTE_REVISIONS') . ': ' . $this->escape($this->item->title);
            $this->params->def('page_heading', $t);
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
        $access    = JPrepoHelper::getActions('note', $this->item->id);
        $link      = JPrepoHelperRoute::getRepositoryRoute($this->item->project_id, $this->item->dir_id, $this->item->path);
        $back_opts = array('access' => true, 'href' => $link);
        $edit_opts = array('access' => $access->get('core.edit'));

        JPToolbar::button('COM_JOOMPROJECT_ACTION_BACK', '', false, $back_opts);
        JPToolbar::button('COM_JOOMPROJECT_ACTION_EDIT', 'noteform.edit', false, $edit_opts);

        JPToolbar::filterButton($this->state->get('filter.isset'));

        return JPToolbar::render();
    }
}
