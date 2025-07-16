<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jptasks
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\HTML\Helpers\Sidebar;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

defined('_JEXEC') or die();



HTMLHelper::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jptasks/helpers/html');


/**
 * Joomproject Tasks List View Class
 *
 */
class JPtasksViewTasks extends HtmlView
{
    /**
     * List of items to display
     *
     * @var    array
     */
    protected $items;

    /**
     * JPagination instance object
     *
     * @var    object
     */
    protected $pagination;

    /**
     * Model state object
     *
     * @var    object
     */
    protected $state;

    /**
     * List of item authors
     *
     * @var    array
     */
    protected $authors;

    /**
     * List of assigned users
     *
     * @var    array
     */
    protected $assigned;

    /**
     * List of task lists
     *
     * @var    array
     */
    protected $tasklists;

    /**
     * List of milestones
     *
     * @var    array
     */
    protected $milestones;

    /**
     * Sql "null" date (0000-00-00 00:00:00)
     *
     * @var    string
     */
    protected $nulldate;


    /**
     * Display the view
     *
     */
    public function display($tpl = null)
    {

        // Get data from model
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
        $this->state      = $this->get('State');
        $this->authors    = $this->get('Authors');
        $this->assigned   = $this->get('AssignedUsers');
        $this->tasklists  = $this->get('Tasklists');
        $this->milestones = $this->get('Milestones');
        $this->nulldate   = Factory::getDbo()->getNullDate();

        // Check for errors
        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        if ($this->getLayout() !== 'modal') {
            $this->addToolbar();

          //  $this->addSidebar();
            $this->sidebar = Sidebar::render();

        }

        parent::display($tpl);
    }


    /**
     * Add the page title and toolbar.
     *
     * @return  void
     */
    protected function addToolbar()
    {
        $user = Factory::getApplication()->getIdentity();
	    // Get the toolbar object instance
	    $bar = Toolbar::getInstance('toolbar');

// Get the toolbar object instance
        ToolbarHelper::title(Text::_('COM_JOOMPROJECT_TASKS_TITLE'), 'tasks');

        if ($user->authorise('core.create', 'com_jptasks')) {
            $bar->addNew('task.add');
        }

        if ($user->authorise('core.edit', 'com_jptasks') || $user->authorise('core.edit.own', 'com_jptasks')) {
            ToolbarHelper::editList('task.edit');
        }

        if ($user->authorise('core.edit.state', 'com_jptasks')) {
            ToolbarHelper::divider();
            ToolbarHelper::publish('tasks.publish', 'JTOOLBAR_PUBLISH', true);
            ToolbarHelper::unpublish('tasks.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            ToolbarHelper::divider();
            ToolbarHelper::archiveList('tasks.archive');
            ToolbarHelper::checkin('tasks.checkin');
        }

        if ($this->state->get('filter.published') == -2 && $user->authorise('core.delete', 'com_jptasks')) {
            ToolbarHelper::deleteList('', 'tasks.delete','JTOOLBAR_EMPTY_TRASH');
            ToolbarHelper::divider();
        }
        elseif ($user->authorise('core.edit.state', 'com_jptasks')) {
            ToolbarHelper::trash('tasks.trash');
            ToolbarHelper::divider();
        }


        if (
            $user->authorise('core.create', 'com_jptasks') &&
            $user->authorise('core.edit', 'com_jptasks')
        ) {

            $toolbar = Toolbar::getInstance();

            // display batch modal on joomla 5 and more because popuptype not supported on J4
            if(\JoomProject\Version\Joomla::isJoomla5()){
                $toolbar->popupButton('batch', 'JTOOLBAR_BATCH')
                    ->popupType('inline')
                    ->textHeader(Text::_('COM_JPPROJECTS_BATCH_OPTIONS'))
                    ->url('#joomla-dialog-batch')
                    ->modalWidth('800px')
                    ->modalHeight('fit-content')
                    ->listCheck(true);
            }

        }


        if ($user->authorise('core.admin')) {
            ToolbarHelper::preferences('com_jptasks');
        }


    }


    /**
     * Adds the page side bar for Joomla 3.0 and higher
     *
     * @return    void
     */
    protected function addSidebar()
    {
        Sidebar::setAction('index.php?option=com_jptasks&view=tasks');

        Sidebar::addFilter(
            Text::_('JOPTION_SELECT_PUBLISHED'),
            'filter_published',
            HTMLHelper::_('select.options', HTMLHelper::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true)
        );

        Sidebar::addFilter(
            Text::_('JOPTION_SELECT_ACCESS'),
            'filter_access',
            HTMLHelper::_('select.options', HTMLHelper::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'))
        );

        Sidebar::addFilter(
            Text::_('JOPTION_SELECT_PRIORITY'),
            'filter_priority',
            HTMLHelper::_('select.options', HTMLHelper::_('jptasks.priorityOptions'), 'value', 'text', $this->state->get('filter.priority'))
        );

        Sidebar::addFilter(
            Text::_('JOPTION_SELECT_COMPLETITION'),
            'filter_complete',
            HTMLHelper::_('select.options', HTMLHelper::_('jptasks.completeOptions'), 'value', 'text', $this->state->get('filter.complete'))
        );

        Sidebar::addFilter(
            Text::_('JOPTION_SELECT_AUTHOR'),
            'filter_author_id',
            HTMLHelper::_('select.options', $this->authors, 'value', 'text', $this->state->get('filter.author_id'))
        );

        Sidebar::addFilter(
            Text::_('JOPTION_SELECT_ASSIGNED_USER'),
            'filter_assigned_id',
            HTMLHelper::_('select.options', $this->assigned, 'value', 'text', $this->state->get('filter.assigned_id'))
        );

        if ($this->state->get('filter.project')) {
            Sidebar::addFilter(
                Text::_('JOPTION_SELECT_MILESTONE'),
                'filter_milestone',
                HTMLHelper::_('select.options', $this->milestones, 'value', 'text', $this->state->get('filter.milestone'))
            );

            Sidebar::addFilter(
                Text::_('JOPTION_SELECT_TASKLIST'),
                'filter_tasklist',
                HTMLHelper::_('select.options', $this->tasklists, 'value', 'text', $this->state->get('filter.tasklist'))
            );
        }
    }


    /**
     * Returns an array of fields the table can be sorted by.
     * Requires Joomla 3.0 or higher
     *
     * @return    array    Array containing the field name to sort by as the key and display text as value
     */
    protected function getSortFields()
    {
        return array(
            'a.ordering'    => Text::_('JGRID_HEADING_ORDERING'),
            'a.state'       => Text::_('JSTATUS'),
            'a.title'       => Text::_('JGLOBAL_TITLE'),
            'project_title' => Text::_('JGRID_HEADING_PROJECT'),
            'a.end_date'    => Text::_('JGRID_HEADING_DEADLINE'),
            'access_level'  => Text::_('JGRID_HEADING_ACCESS'),
            'author_name'   => Text::_('JAUTHOR'),
            'a.created'     => Text::_('JDATE'),
            'a.id'          => Text::_('JGRID_HEADING_ID')
        );
    }
}
