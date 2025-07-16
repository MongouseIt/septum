<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jprepo
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
 * Repository View Class
 *
 */
class JPrepoViewRepository extends HtmlView
{
    /**
     * List of items to display
     *
     * @var    array
     */
    protected $items;

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
     * JPagination instance object
     *
     * @var    object
     */
    protected $pagination;


    /**
     * Displays the view.
     *
     */
    public function display($tpl = null)
    {
        // Get data from model
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
        $this->state    = $this->get('State');
        $this->items    = $this->get('Items');
        $this->authors  = $this->get('Authors');
        $this->nulldate = Factory::getDbo()->getNullDate();

        // Check for errors
        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        // Set the pagination object
        if ($this->items['directory']->id == '1') {
            $this->pagination = $this->get('Pagination');
        }
        else {
            $this->pagination = null;
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
     */
    protected function addToolbar()
    {
        $user  = Factory::getApplication()->getIdentity();
        $state = $this->get('State');

        ToolbarHelper::title(Text::_('COM_JOOMPROJECT_REPO_TITLE'), 'folder');

        if ($state->get('filter.project') && $this->items['directory']->id > 1) {
            $access = JPrepoHelper::getActions('directory', $this->items['directory']->id);

            if ($access->get('core.create')) {
                ToolbarHelper::custom('directory.add', 'new.png', 'new_f2.png', 'JTOOLBAR_ADD_DIRECTORY', false);
                ToolbarHelper::custom('file.add', 'upload.png', 'upload_f2.png', 'JTOOLBAR_ADD_FILE', false);
                ToolbarHelper::custom('note.add', 'copy.png', 'html_f2.png', 'JTOOLBAR_ADD_NOTE', false);
            }

            if ($access->get('core.delete')) {
                ToolbarHelper::divider();
                ToolbarHelper::deleteList('', 'repository.delete','JTOOLBAR_DELETE');
            }
        }

        if ($user->authorise('core.admin')) {
            ToolbarHelper::preferences('com_jprepo');
        }
    }


    /**
     * Adds the page side bar for Joomla 3.0 and higher
     *
     * @return    void
     */
    protected function addSidebar()
    {
        Sidebar::setAction('index.php?option=com_jprepo&view=repository');

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
            'a.title'      => Text::_('JGLOBAL_TITLE'),
            'access_level' => Text::_('JGRID_HEADING_ACCESS'),
            'author_name'  => Text::_('JAUTHOR'),
            'a.created'    => Text::_('JDATE'),
            'a.id'         => Text::_('JGRID_HEADING_ID')
        );
    }
}
