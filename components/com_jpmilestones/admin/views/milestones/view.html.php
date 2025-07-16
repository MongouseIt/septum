<?php
/**
 * @package      Joomproject
 * @subpackage   Milestones
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
 * Joomproject Milestones List View Class
 *
 */
class JPmilestonesViewMilestones extends HtmlView
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
     * Sql "null" date (0000-00-00 00:00:00)
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
     * Displays the view.
     *
     */
    public function display($tpl = null)
    {
        // Get data from model
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
        $this->authors    = $this->get('Authors');
        $this->nulldate = Factory::getDbo()->getNullDate();

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
     * Adds the page title and toolbar.
     *
     * @return    void
     */
    protected function addToolbar()
    {
        $user = Factory::getApplication()->getIdentity();

        ToolbarHelper::title(Text::_('COM_JOOMPROJECT_MILESTONES_TITLE'), 'flag');

        if ($user->authorise('core.create', 'com_jpmilestones')) {
            ToolbarHelper::addNew('milestone.add');
        }

        if ($user->authorise('core.edit', 'com_jpmilestones') || $user->authorise('core.edit.own', 'com_jpmilestones')) {
            ToolbarHelper::editList('milestone.edit');
        }

        if ($user->authorise('core.edit.state', 'com_jpmilestones')) {
            ToolbarHelper::divider();
            ToolbarHelper::publish('milestones.publish', 'JTOOLBAR_PUBLISH', true);
            ToolbarHelper::unpublish('milestones.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            ToolbarHelper::divider();
            ToolbarHelper::archiveList('milestones.archive');
            ToolbarHelper::checkin('milestones.checkin');
        }

        if ($this->state->get('filter.published') == -2 && $user->authorise('core.delete', 'com_jpmilestones')) {
            ToolbarHelper::deleteList('', 'milestones.delete', 'JTOOLBAR_EMPTY_TRASH');
            ToolbarHelper::divider();
        } elseif ($user->authorise('core.edit.state', 'com_jpmilestones')) {
            ToolbarHelper::trash('milestones.trash');
            ToolbarHelper::divider();
        }

        if ($user->authorise('core.admin')) {
            ToolbarHelper::preferences('com_jpmilestones');
        }

    }


    /**
     * Adds the page side bar for Joomla 3.0 and higher
     *
     * @return    void
     */
    protected function addSidebar()
    {
        Sidebar::setAction('index.php?option=com_jpmilestones&view=milestones');

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
            'a.state' => Text::_('JSTATUS'),
            'a.title' => Text::_('JGLOBAL_TITLE'),
            'project_title' => Text::_('JGRID_HEADING_PROJECT'),
            'a.end_date' => Text::_('JGRID_HEADING_DEADLINE'),
            'access_level' => Text::_('JGRID_HEADING_ACCESS'),
            'author_name' => Text::_('JAUTHOR'),
            'a.created' => Text::_('JDATE'),
            'a.id' => Text::_('JGRID_HEADING_ID')
        );
    }
}
