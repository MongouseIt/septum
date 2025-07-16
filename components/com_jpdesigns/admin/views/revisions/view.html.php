<?php
/**
 * @package      Joomproject Pro
 * @subpackage   Designs
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



class JPdesignsViewRevisions extends HtmlView
{
    protected $items;
    protected $pagination;
    protected $state;
    protected $authors;
    protected $nulldate;
    protected $parent_id;


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
        $this->nulldate   = Factory::getDbo()->getNullDate();
        $this->parent_id  = (int) $this->state->get('filter.parent_id');
        // Check for errors
        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        if ($this->getLayout() !== 'modal') {

            if (version_compare(JVERSION, '3', 'ge')) {
                JPdesignsHelper::addSubmenu('revisions');

                $this->sidebar = Sidebar::render();
            }
        }

        parent::display($tpl);
    }


    /**
     * Adds the page title and toolbar.
     *
     */
    protected function addToolbar()
    {
        $user   = Factory::getApplication()->getIdentity();
        $asset  = 'com_jpdesigns.design.' . $this->parent_id;
        $dtitle = $this->escape($this->get('DesignTitle'));

        ToolbarHelper::title(Text::_('COM_JOOMPROJECT_DESIGN_REVISIONS_TITLE') . ': ' . $dtitle, 'article.png');

        if ($user->authorise('core.create', $asset)) {
            ToolbarHelper::addNew('revision.add');
        }

        if ($user->authorise('core.edit', $asset) || $user->authorise('core.edit.own', $asset)) {
            ToolbarHelper::editList('revision.edit');
        }

        if ($user->authorise('core.edit.state', $asset)) {
            ToolbarHelper::divider();
            ToolbarHelper::publish('revisions.publish', 'JTOOLBAR_PUBLISH', true);
            ToolbarHelper::unpublish('revisions.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            ToolbarHelper::divider();
            ToolbarHelper::archiveList('revisions.archive');
            ToolbarHelper::checkin('revisions.checkin');
        }

        if ($this->state->get('filter.published') == -2 && $user->authorise('core.delete', $asset)) {
            ToolbarHelper::deleteList('', 'revisions.delete','JTOOLBAR_EMPTY_TRASH');
        }
        elseif ($user->authorise('core.edit.state', $asset)) {
            ToolbarHelper::trash('revisions.trash');
        }


        if (version_compare(JVERSION, '3', 'ge')) {
            Sidebar::setAction('index.php?option=com_jpdesigns&view=revisions');

    		Sidebar::addFilter(
    			Text::_('JOPTION_SELECT_PUBLISHED'),
    			'filter_published',
    			HTMLHelper::_('select.options', HTMLHelper::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true)
    		);

            Sidebar::addFilter(
    			Text::_('JOPTION_SELECT_AUTHOR'),
    			'filter_author_id',
    			HTMLHelper::_('select.options', $this->authors, 'value', 'text', $this->state->get('filter.author_id'))
    		);
        }
    }


    /**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   3.0
	 */
	protected function getSortFields()
	{
		return array(
			'a.ordering'     => Text::_('JGRID_HEADING_REVISION'),
			'a.state'        => Text::_('JSTATUS'),
			'a.title'        => Text::_('JGLOBAL_TITLE'),
			'a.created_by'   => Text::_('JAUTHOR'),
			'a.created'      => Text::_('JDATE'),
			'a.id'           => Text::_('JGRID_HEADING_ID')
		);
	}
}
