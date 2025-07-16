<?php
/**
 * @package      Joomproject
 * @subpackage   Tasks
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
 * Joomproject Task list List View Class
 *
 */
class JPtasksViewTasklists extends HtmlView
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
     * List of milestones
     *
     * @var    array
     */
    protected $milestones;



    /**
     * Display the view.
     *
     */
    public function display($tpl = null)
    {
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
        $this->state      = $this->get('State');
        $this->authors    = $this->get('Authors');
        $this->milestones = $this->get('Milestones');

        // Check for errors.
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
     */
    protected function addToolbar()
    {
        $user = Factory::getApplication()->getIdentity();

        ToolbarHelper::title(Text::_('COM_JOOMPROJECT_TASKLISTS_TITLE'), 'article.png');

        if ($user->authorise('core.create', 'com_jptasks')) {
            ToolbarHelper::addNew('tasklist.add');
        }

        if ($user->authorise('core.edit', 'com_jptasks') || $user->authorise('core.edit.own', 'com_jptasks')) {
            ToolbarHelper::editList('tasklist.edit');
        }

        if ($user->authorise('core.edit.state', 'com_jptasks')) {
            ToolbarHelper::divider();
            ToolbarHelper::publish('tasklists.publish', 'JTOOLBAR_PUBLISH', true);
            ToolbarHelper::unpublish('tasklists.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            ToolbarHelper::divider();
            ToolbarHelper::archiveList('tasklists.archive');
            ToolbarHelper::checkin('tasklists.checkin');
        }

        if ($this->state->get('filter.published') == -2 && $user->authorise('core.delete', 'com_jptasks')) {
            ToolbarHelper::deleteList('', 'tasklists.delete','JTOOLBAR_EMPTY_TRASH');
            ToolbarHelper::divider();
        }
        elseif ($user->authorise('core.edit.state', 'com_jptasks')) {
            ToolbarHelper::trash('tasklists.trash');
            ToolbarHelper::divider();
        }
    }


    /**
     * Adds the page side bar for Joomla 3.0 and higher
     *
     * @return    void
     */
    protected function addSidebar()
    {
        Sidebar::setAction('index.php?option=com_jptasks&view=tasklists');

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
                Text::_('JOPTION_SELECT_MILESTONE'),
                'filter_milestone',
                HTMLHelper::_('select.options', $this->milestones, 'value', 'text', $this->state->get('filter.milestone'))
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
            'a.state'         => Text::_('JSTATUS'),
            'a.title'         => Text::_('JGLOBAL_TITLE'),
            'project_title'   => Text::_('JGRID_HEADING_PROJECT'),
            'milestone_title' => Text::_('JGRID_HEADING_MILESTONE'),
            'task_count'      => Text::_('COM_JOOMPROJECT_TASKS_TITLE'),
            'access_level'    => Text::_('JGRID_HEADING_ACCESS'),
            'author_name'     => Text::_('JAUTHOR'),
            'a.created'       => Text::_('JDATE'),
            'a.id'            => Text::_('JGRID_HEADING_ID')
        );
    }
}
