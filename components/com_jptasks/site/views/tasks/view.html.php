<?php
/**
 * @package      Joomproject
 * @subpackage   Tasks
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





/**
 * Task list view class.
 *
 */
class JPtasksViewTasks extends HtmlView
{
	public $pageclass_sfx;
	public $items;
	public $nulldate;
	public $pagination;
	public $params;
	public $state;
	public $milestones;
	public $lists;
	public $assigned;
	public $actions;
	public $toolbar;
	public $authors;
    public $access;
	public $menu;
	public $sort_options;
	public $order_options;

    /**
     * Display the view
     *
     * @return    void
     */
    public function display($tpl = null)
    {
        if(!\JoomProject\Permission\GlobalAccess::check('com_jptasks'))
            return;

        $app     = Factory::getApplication();
        $state   = $this->get('State');
        $layout  = $this->getLayout();
        $project = (int) $state->get('filter.project');
        $active  = $app->getMenu()->getActive();

        // Check for layout override
        if (isset($active->query['layout']) && (\Joomla\CMS\Factory::getApplication()->input->getCmd('layout') == '')) {
            $this->setLayout($active->query['layout']);
        }

        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state      = $this->get('State');
        $this->milestones = $this->get('Milestones');
        $this->lists      = $this->get('TaskLists');
        $this->authors    = $this->get('Authors');
        $this->assigned   = $this->get('AssignedUsers');
        $this->params     = $this->state->params;
        $this->toolbar    = $this->getToolbar();
        $this->access     = JPtasksHelper::getActions();
        $this->nulldate   = Factory::getDbo()->getNullDate();
        $this->menu       = new JPMenuContext();

        $this->sort_options  = $this->getSortOptions();
        $this->order_options = $this->getOrderOptions();

        // Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx',''));

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        // Check for empty search result
        if ((count($this->items) == 0) && $this->state->get('filter.isset')) {
            $app->enqueueMessage(Text::_('COM_JOOMPROJECT_EMPTY_SEARCH_RESULT'));
        }

        // Prepare the document
        $this->prepareDocument();

        // Display the view
        parent::display($tpl);
    }


    /**
     * Prepares the document
     *
     * @return    void
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
            $this->params->def('page_heading', Text::_('COM_JOOMPROJECT_TASKS'));
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
            $link    = '&format=feed&limitstart=';
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
        $access = JPtasksHelper::getActions();

        $state  = $this->get('State');
        $create = $access->get('core.create');

        if ($create) {
            $items = array();
            $items[] = array('text' => 'COM_JOOMPROJECT_ACTION_NEW_TASK',
                             'task' => 'taskform.add');

            $items[] = array('text' => 'COM_JOOMPROJECT_ACTION_NEW_TASKLIST',
                             'task' => 'tasklistform.add');

            JPToolbar::dropdownButton($items);
        }


        // In the getToolbar() method, add after line 211:
        if ($access->get('core.create')
            && $access->get('core.edit')
            && $access->get('core.edit.state')) {
            JPToolbar::batchButton( [
                'class' => 'btn-info',
                'icon' => 'fas fa-layer-group',
                'modal-id' => 'collapseModal'
            ]);
        }

        $items = array();

        if ($access->get('core.edit.state')) {
            $items[] = array('text' => 'COM_JOOMPROJECT_ACTION_PUBLISH',   'task' => $this->getName() . '.publish');
            $items[] = array('text' => 'COM_JOOMPROJECT_ACTION_UNPUBLISH', 'task' => $this->getName() . '.unpublish');
            $items[] = array('text' => 'COM_JOOMPROJECT_ACTION_ARCHIVE',   'task' => $this->getName() . '.archive');
            $items[] = array('text' => 'COM_JOOMPROJECT_ACTION_CHECKIN',   'task' => $this->getName() . '.checkin');
        }

        if ($state->get('filter.published') == -2 && $access->get('core.delete')) {
            $items[] = array('text' => 'COM_JOOMPROJECT_ACTION_DELETE', 'task' => $this->getName() . '.delete');
        }
        elseif ($access->get('core.edit.state')) {
            $items[] = array('text' => 'COM_JOOMPROJECT_ACTION_TRASH', 'task' => $this->getName() . '.trash');
        }

        if (count($items)) {
            JPToolbar::listButton($items);
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
        $options[] = HTMLHelper::_('select.option', 'a.ordering', Text::_('JGRID_HEADING_ORDERING'));
        $options[] = HTMLHelper::_('select.option', 'a.state', Text::_('JSTATUS'));
        $options[] = HTMLHelper::_('select.option', 'a.title', Text::_('JGLOBAL_TITLE'));
        $options[] = HTMLHelper::_('select.option', 'project_title', Text::_('JGRID_HEADING_PROJECT'));
        $options[] = HTMLHelper::_('select.option', 'a.end_date', Text::_('JGRID_HEADING_DEADLINE'));
        $options[] = HTMLHelper::_('select.option', 'access_level', Text::_('JGRID_HEADING_ACCESS'));
        $options[] = HTMLHelper::_('select.option', 'author_name', Text::_('JAUTHOR'));
        $options[] = HTMLHelper::_('select.option', 'a.created', Text::_('JDATE'));
        $options[] = HTMLHelper::_('select.option', 'a.id', Text::_('JGRID_HEADING_ID'));

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
