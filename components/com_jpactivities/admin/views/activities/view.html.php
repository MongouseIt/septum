<?php
/**
 * @package      pkg_jpactivities
 * @subpackage   com_jpactivities
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
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\HTML\Helpers\Sidebar;
use Joomla\CMS\Toolbar\ToolbarHelper;



class JPactivitiesViewActivities extends HtmlView
{
    protected $items;

    protected $pagination;

    protected $state;

    protected $authors;

    protected $extensions;

    protected $ext_items;

    protected $events;

    protected $locations;

    protected $user;

    protected $params;


    /**
     * Displays the view.
     *
     */
    public function display($tpl = null)
    {
        // Get data from model
        $this->items      = $this->get('Items');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
        $this->pagination = $this->get('Pagination');
        $this->state      = $this->get('State');
        $this->authors    = $this->get('Authors');
        $this->extensions = $this->get('Extensions');
        $this->ext_items  = $this->get('ExtensionItems');
        $this->events     = $this->get('Events');
        $this->locations  = $this->get('Locations');
        $this->params     = ComponentHelper::getParams('com_jpactivities', true);

        // Check for errors
        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        $this->user   = Factory::getApplication()->getIdentity();

        // Add the tool- and sidebar
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

        ToolbarHelper::title(Text::_('COM_JPACTIVITIES_ACTIVITIES_TITLE'), ' fas fa-clipboard-list');

        if ($user->authorise('core.edit.state', 'com_jpactivities')) {
            ToolbarHelper::divider();
            ToolbarHelper::publish('activities.publish', 'JTOOLBAR_PUBLISH', true);
            ToolbarHelper::unpublish('activities.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            ToolbarHelper::divider();
            ToolbarHelper::archiveList('activities.archive');
        }

        if ($this->state->get('filter.published') == -2 && $user->authorise('core.delete', 'com_jpactivities')) {
            ToolbarHelper::deleteList('', 'activities.delete','JTOOLBAR_EMPTY_TRASH');
            ToolbarHelper::divider();
        }
        elseif ($user->authorise('core.edit.state', 'com_jpactivities')) {
            ToolbarHelper::trash('activities.trash');
            ToolbarHelper::divider();
        }

        if ($user->authorise('core.admin', 'com_jpactivities')) {
            ToolbarHelper::preferences('com_jpactivities');
        }
    }


    /**
     * Adds the page side bar for Joomla 3.0 and higher
     *
     * @return    void
     */
    protected function addSidebar()
    {
        Sidebar::setAction('index.php?option=com_jpactivities&view=activities');

        Sidebar::addFilter(
            '',
            'filter_client_id',
            HTMLHelper::_('select.options', $this->locations, 'value', 'text', $this->state->get('filter.client_id')),
            false
        );

        Sidebar::addFilter(
            '',
            'filter_published',
            HTMLHelper::_('select.options', HTMLHelper::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true),
            false
        );

        Sidebar::addFilter(
            Text::_('JOPTION_SELECT_EVENT'),
            'filter_event_id',
            HTMLHelper::_('select.options', $this->events, 'value', 'text', $this->state->get('filter.event_id'))
        );

        Sidebar::addFilter(
            Text::_('JOPTION_SELECT_EXTENSION'),
            'filter_extension',
            HTMLHelper::_('select.options', $this->extensions, 'value', 'text', $this->state->get('filter.extension'))
        );

        Sidebar::addFilter(
            Text::_('JOPTION_SELECT_ACCESS'),
            'filter_access',
            HTMLHelper::_('select.options', HTMLHelper::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'))
        );
    }


    /**
     * Returns an array of fields the table can be sorted by.
     *
     * @return    array    Array containing the field name to sort by as the key and display text as value
     */
    protected function getSortFields()
    {
        return array('a.created' => Text::_('JDATE'));
    }
}
