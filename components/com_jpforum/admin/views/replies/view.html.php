<?php
/**
 * @package      Joomproject
 * @subpackage   Forum
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





class JPforumViewReplies extends HtmlView
{
    /**
     * A list of replies
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
     * Sql "null" date (0000-00-00 00:00:00)
     *
     * @var    string
     */
    protected $nulldate;

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
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
        $this->pagination = $this->get('Pagination');
        $this->state      = $this->get('State');
        $this->authors    = $this->get('Authors');
        $this->nulldate   = Factory::getDbo()->getNullDate();

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
        $access = JPforumHelper::getReplyActions(null, (int) $this->state->get('filter.topic'));

        ToolbarHelper::title(Text::_('COM_JOOMPROJECT_REPLIES_TITLE'), 'article.png');

        if ($access->get('core.create')) {
            ToolbarHelper::addNew('reply.add');
        }

        if ($access->get('core.edit')) {
            ToolbarHelper::editList('reply.edit');
        }

        if ($access->get('core.edit.state')) {
            ToolbarHelper::divider();
            ToolbarHelper::publish('replies.publish', 'JTOOLBAR_PUBLISH', true);
            ToolbarHelper::unpublish('replies.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            ToolbarHelper::divider();
            ToolbarHelper::archiveList('replies.archive');
            ToolbarHelper::checkin('replies.checkin');
        }

        if ($this->state->get('filter.published') == -2 && $access->get('core.delete')) {
            ToolbarHelper::deleteList('', 'replies.delete','JTOOLBAR_EMPTY_TRASH');
            ToolbarHelper::divider();
        }
        elseif ($access->get('core.edit.state')) {
            ToolbarHelper::trash('replies.trash');
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
        Sidebar::setAction('index.php?option=com_jpforum&view=topics');

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
            'a.state'       => Text::_('JSTATUS'),
            'author_name'   => Text::_('JAUTHOR'),
            'a.created'     => Text::_('JDATE'),
            'access_level'  => Text::_('JGRID_HEADING_ACCESS'),
            'a.id'          => Text::_('JGRID_HEADING_ID')
        );
    }
}
