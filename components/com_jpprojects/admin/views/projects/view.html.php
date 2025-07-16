<?php
/**
 * @package      Joomproject
 * @subpackage   Projects
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */



defined('_JEXEC') or die();

use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\Helpers\Sidebar;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\MVC\View\HtmlView;


/**
 * Joomproject Project List View Class
 *
 */
class JPprojectsViewProjects extends HtmlView
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
     * Form object for search filters
     *
     * @var  \Joomla\CMS\Form\Form
     */
    public $filterForm;

    /**
     * The active search filters
     *
     * @var  array
     */
    public $activeFilters;
    /**
     * Displays the view.
     *
     * @return    void
     */
    public function display($tpl = null)
    {
        // Get data from model
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state         = $this->get('State');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
        $this->authors    = $this->get('Authors');
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
     * Adds the page title and toolbar.
     *
     * @return    void
     */
    protected function addToolbar()
    {
        $user = Factory::getApplication()->getIdentity();

        $toolbar = Toolbar::getInstance();

        ToolBarHelper::title(Text::_('COM_JOOMPROJECT_PROJECTS_TITLE'), 'briefcase');

        if ($user->authorise('core.create', 'com_jpprojects')) {
            ToolBarHelper::addNew('project.add');
        }

        if ($user->authorise('core.edit', 'com_jpprojects') || $user->authorise('core.edit.own', 'com_jpprojects')) {
            ToolBarHelper::editList('project.edit');
        }

        if ($user->authorise('core.edit.state', 'com_jpprojects')) {
            ToolBarHelper::divider();
            ToolBarHelper::publish('projects.publish', 'JTOOLBAR_PUBLISH', true);
            ToolBarHelper::unpublish('projects.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            ToolBarHelper::divider();
            ToolBarHelper::archiveList('projects.archive');
            ToolBarHelper::checkin('projects.checkin');
        }


        // Add a batch button
        if (
            $user->authorise('core.create', 'com_jpprojects')
            && $user->authorise('core.edit', 'com_jpprojects')
        ) {

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

        if ($this->state->get('filter.published') == -2 && $user->authorise('core.delete', 'com_jpprojects')) {
            ToolBarHelper::deleteList('', 'projects.delete','JTOOLBAR_EMPTY_TRASH');
            ToolBarHelper::divider();
        }
        elseif ($user->authorise('core.edit.state', 'com_jpprojects')) {
            ToolBarHelper::trash('projects.trash');
            ToolBarHelper::divider();
        }

        if ($user->authorise('core.admin')) {
            ToolBarHelper::preferences('com_jpprojects');
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
            'a.state'        => Text::_('JSTATUS'),
            'a.title'        => Text::_('JGLOBAL_TITLE'),
            'category_title' => Text::_('JCATEGORY'),
            'a.end_date'     => Text::_('JGRID_HEADING_DEADLINE'),
            'access_level'   => Text::_('JGRID_HEADING_ACCESS'),
            'author_name'    => Text::_('JAUTHOR'),
            'a.created'      => Text::_('JDATE'),
            'a.id'           => Text::_('JGRID_HEADING_ID')
        );
    }
}
