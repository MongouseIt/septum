<?php
/**
 * @package      Joomproject
 * @subpackage   Comments
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





class JPcommentsViewComments extends HtmlView
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
     * A list of context options
     *
     * @var    array
     */
    protected $contexts;

    /**
     * A list of context item options
     *
     * @var    array
     */
    protected $cntxt_items;

    /**
     * Sql "null" date (0000-00-00 00:00:00)
     *
     * @var    string
     */
    protected $nulldate;

    /**
     * Display the view
     *
     * @param    string    $tpl    A template suffix
     * @retun    void
     */
    public function display($tpl = null)
    {
        // Get data from model
        $this->items       = $this->get('Items');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
        $this->pagination  = $this->get('Pagination');
        $this->state       = $this->get('State');
        $this->authors     = $this->get('Authors');
        $this->contexts    = $this->get('Contexts');
        $this->cntxt_items = $this->get('ContextItems');
        $this->nulldate    = Factory::getDbo()->getNullDate();

        // Check for errors
        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
            return false;
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

        ToolbarHelper::title(Text::_('COM_JOOMPROJECT_COMMENTS_TITLE'), 'comments');

        if ($user->authorise('core.create', 'com_jpcomments') && is_numeric($this->state->get('filter.item_id')) && $this->state->get('filter.context')) {
            ToolbarHelper::addNew('comment.add');
        }

        if ($user->authorise('core.edit', 'com_jpcomments') || $user->authorise('core.edit.own', 'com_jpcomments')) {
            ToolbarHelper::editList('comment.edit');
        }

        if ($user->authorise('core.edit.state', 'com_jpcomments')) {
            ToolbarHelper::divider();
            ToolbarHelper::publish('comments.publish', 'JTOOLBAR_PUBLISH', true);
            ToolbarHelper::unpublish('comments.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            ToolbarHelper::divider();
            ToolbarHelper::archiveList('comments.archive');
            ToolbarHelper::checkin('comments.checkin');
        }

        if ($this->state->get('filter.published') == -2 && $user->authorise('core.delete', 'com_jpcomments')) {
            ToolbarHelper::deleteList('', 'comments.delete','JTOOLBAR_EMPTY_TRASH');
            ToolbarHelper::divider();
        }
        elseif ($user->authorise('core.edit.state', 'com_jpcomments')) {
            ToolbarHelper::trash('comments.trash');
            ToolbarHelper::divider();
        }

        if ($user->authorise('core.admin')) {
            ToolbarHelper::preferences('com_jpcomments');
        }
    }


    /**
     * Adds the page side bar for Joomla 3.0 and higher
     *
     * @return    void
     */
    protected function addSidebar()
    {
        Sidebar::setAction('index.php?option=com_jpcomments&view=comments');

        Sidebar::addFilter(
            Text::_('JOPTION_SELECT_PUBLISHED'),
            'filter_published',
            HTMLHelper::_('select.options', HTMLHelper::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true)
        );

        Sidebar::addFilter(
            Text::_('JOPTION_SELECT_CONTEXT'),
            'filter_context',
            HTMLHelper::_('select.options', $this->contexts, 'value', 'text', $this->state->get('filter.context'), true)
        );

        if ((int) $this->state->get('filter.project') > 0) {
            if ($this->state->get('filter.context') != '') {
                Sidebar::addFilter(
                    Text::_('JOPTION_SELECT_CONTEXT_ITEM'),
                    'filter_item_id',
                    HTMLHelper::_('select.options', $this->cntxt_items, 'value', 'text', $this->state->get('filter.item_id'), true)
                );
            }

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
            'a.state'       => Text::_('JSTATUS'),
            'a.title'       => Text::_('JGLOBAL_TITLE'),
            'a.context'     => Text::_('JGRID_HEADING_CONTEXT'),
            'author_name'   => Text::_('JAUTHOR'),
            'a.created'     => Text::_('JDATE'),
            'a.id'          => Text::_('JGRID_HEADING_ID')
        );
    }
}
