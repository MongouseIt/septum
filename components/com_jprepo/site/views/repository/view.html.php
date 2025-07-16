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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\View\HtmlView;
use JoomProject\Permission\GlobalAccess;


/**
 * Repository view class.
 *
 */
class JPrepoViewRepository extends HtmlView
{
    /**
     * CSS page class suffix
     *
     * @var    string
     */
    protected $pageclass_sfx;

    /**
     * List of items to display
     *
     * @var    array
     */
    protected $items;

    /**
     * Sql "null" date (0000-00-00 00:00:00)
     *
     * @var    string
     */
    protected $nulldate;

    /**
     * Model parameters
     *
     * @var    object
     */
    public $params;

    /**
     * Model state object
     *
     * @var    object
     */
    public $state;

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
     * Context menu instance
     *
     * @var    object
     */
    protected $menu;

    /**
     * JPagination instance object
     *
     * @var    object
     */
    protected $pagination;

    /**
     * Select list sorting options
     *
     * @var    array
     */
    public $sort_options;

    /**
     * Select list ordering options
     *
     * @var    array
     */
    public $order_options;


    /**
     * Display the view
     *
     * @return    void
     */
    public function display($tpl = null)
    {

        if(!GlobalAccess::check('com_jprepo'))
            return;

        $user   = Factory::getApplication()->getIdentity();
        $app    = Factory::getApplication();
        $active = $app->getMenu()->getActive();

        $this->items    = $this->get('Items');
        $this->state    = $this->get('State');
        $this->params   = $this->state->params;
        $this->access   = JPrepoHelper::getActions();
        $this->nulldate = Factory::getDbo()->getNullDate();
        $this->menu     = new JPMenuContext();

        $this->toolbar       = $this->getToolbar();
        $this->sort_options  = $this->getSortOptions();
        $this->order_options = $this->getOrderOptions();

        // Check the view access to the current directory
		if ($this->items['directory']->params->get('access-view') != true) {
		    \Joomla\CMS\Factory::getApplication()->enqueueMessage( Text::_('JERROR_ALERTNOAUTHOR'),'warning');
			return;
		}

        // Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx',''));

        // Set the pagination object       
        $this->pagination = $this->get('Pagination');        

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        // Check for empty search result
        if ((count($this->items) == 0) && $this->state->get('filter.isset')) {
            $app->enqueueMessage(Text::_('COM_JOOMPROJECT_EMPTY_SEARCH_RESULT'));
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
            $this->params->def('page_heading', Text::_('COM_JOOMPROJECT_REPO_TITLE'));
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
        $dir    = $this->items['directory'];
        $access = JPrepoHelper::getActions('directory', $dir->id);

        if ($dir->id > 1) {
            $items = array();
            $items[] = array('text'    => 'COM_JOOMPROJECT_ACTION_NEW_FILE',
                             'task'    => 'fileform.add',
                             'options' => array('access' => ($access->get('core.create') && !defined('JPDEMO'))));

            $items[] = array('text'    => 'COM_JOOMPROJECT_ACTION_NEW_DIRECTORY',
                             'task'    => 'directoryform.add',
                             'options' => array('access' => $access->get('core.create')));

            $items[] = array('text'    => 'COM_JOOMPROJECT_ACTION_NEW_NOTE',
                             'task'    => 'noteform.add',
                             'options' => array('access' => $access->get('core.create')));

            JPToolbar::dropdownButton($items);

            $items = array();
            $items[] = array(
                'text' => 'COM_JOOMPROJECT_ACTION_DELETE',
                'task' => $this->getName() . '.delete',
                'options' => array('access' => $access->get('core.delete')));

            $items[] = array(
                'text' => 'COM_JOOMPROJECT_ACTION_CHECKIN',
                'task' => $this->getName() . '.checkin'
            );

            if (count($items)) {
                JPToolbar::listButton($items);
            }
        }

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
        $options[] = HTMLHelper::_('select.option', 'a.title', Text::_('COM_JOOMPROJECT_ORDER_TITLE'));
        $options[] = HTMLHelper::_('select.option', 'a.created', Text::_('COM_JOOMPROJECT_ORDER_CREATE_DATE'));
        $options[] = HTMLHelper::_('select.option', 'a.modified', Text::_('COM_JOOMPROJECT_ORDER_EDIT_DATE'));
        $options[] = HTMLHelper::_('select.option', 'a.created_by', Text::_('COM_JOOMPROJECT_ORDER_AUTHOR'));

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
        $options[] = HTMLHelper::_('select.option', 'ASC', Text::_('COM_JOOMPROJECT_ORDER_ASC'));
        $options[] = HTMLHelper::_('select.option', 'DESC', Text::_('COM_JOOMPROJECT_ORDER_DESC'));

        return $options;
    }
}
