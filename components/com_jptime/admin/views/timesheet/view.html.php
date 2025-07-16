<?php
/**
 * @package      Joomproject
 * @subpackage   Timetracking
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */


defined('_JEXEC') or die();

use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\HTML\Helpers\Sidebar;
use Joomla\CMS\Toolbar\ToolbarHelper;




/**
 * Joomproject Time Tracking List View Class
 *
 */
class JPtimeViewTimesheet extends HtmlView
{
    /**
     * A list of topics
     *
     * @var    array
     */
    protected $items;

    /**
     * JPagination instance
     *
     * @var    object
     */
    protected $pagination;

    /**
     * State object
     *
     * @var    object
     */
    protected $state;

    /**
     * A list of authors
     *
     * @var    array
     */
    protected $authors;

    /**
     * A list of tasks
     *
     * @var    array
     */
    protected $tasks;

    /**
     *
     * @var    string
     */
    protected $nulldate;

    /**
     * Indicates whether the site is running Joomla 2.5 or not
     *
     * @var    boolean
     */



    /**
     * Display the view
     *
     * @param    string    $tpl    A template suffix
     *
     * @retun    void
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
        $this->tasks      = $this->get('Tasks');
        $this->nulldate   = Factory::getDbo()->getNullDate();

        // Check for errors
        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        if ($this->getLayout() !== 'modal') {
            $this->addToolbar();
            $this->sidebar = Sidebar::render();
        }

        parent::display($tpl);
    }


    /**
     * Add the page title and toolbar.
     *
     * @return    void
     */
    protected function addToolbar()
    {
        $user = Factory::getApplication()->getIdentity();

        ToolbarHelper::title(Text::_('COM_JOOMPROJECT_TIMESHEET_TITLE'), 'clock');

        if ($user->authorise('core.create', 'com_jptime')) {
            ToolbarHelper::addNew('time.add');
        }

        if ($user->authorise('core.edit', 'com_jptime') || $user->authorise('core.edit.own', 'com_jptime')) {
            ToolbarHelper::editList('time.edit');
        }

        if ($user->authorise('core.edit.state', 'com_jptime')) {
            ToolbarHelper::divider();
            ToolbarHelper::publish('timesheet.publish', 'JTOOLBAR_PUBLISH', true);
            ToolbarHelper::unpublish('timesheet.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            ToolbarHelper::divider();
            ToolbarHelper::archiveList('timesheet.archive');
            ToolbarHelper::checkin('timesheet.checkin');
        }

        if ($this->state->get('filter.published') == -2 && $user->authorise('core.delete', 'com_jptime')) {
            ToolbarHelper::deleteList('', 'timesheet.delete','JTOOLBAR_EMPTY_TRASH');
            ToolbarHelper::divider();
        }
        elseif ($user->authorise('core.edit.state', 'com_jptime')) {
            ToolbarHelper::trash('timesheet.trash');
            ToolbarHelper::divider();
        }

        if ($user->authorise('core.admin')) {
            ToolbarHelper::preferences('com_jptime');
        }
    }


    /**
     * Adds the page side bar for Joomla 3.0 and higher
     *
     * @return    void
     */
    protected function addSidebar()
    {
        Sidebar::setAction('index.php?option=com_jptime&view=timesheet');

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

        if ($this->state->get('filter.project')) {
            Sidebar::addFilter(
                Text::_('JOPTION_SELECT_AUTHOR'),
                'filter_author_id',
                HTMLHelper::_('select.options', $this->authors, 'value', 'text', $this->state->get('filter.author_id'))
            );

            Sidebar::addFilter(
                Text::_('COM_JOOMPROJECT_OPTION_SELECT_TASK'),
                'filter_task',
                HTMLHelper::_('select.options', $this->tasks, 'value', 'text', $this->state->get('filter.task'))
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
            'a.state'       => Text::_('JSTATUS'),
            'a.task_title'  => Text::_('COM_JOOMPROJECT_TASK_TITLE'),
            'project_title' => Text::_('JGRID_HEADING_PROJECT'),
            'a.log_date'    => Text::_('JDATE'),
            'a.log_time'    => Text::_('COM_JOOMPROJECT_TIME_SPENT_HEADING'),
            'access_level'  => Text::_('JGRID_HEADING_ACCESS'),
            'author_name'   => Text::_('JAUTHOR'),
            'a.id'          => Text::_('JGRID_HEADING_ID')
        );
    }
}
