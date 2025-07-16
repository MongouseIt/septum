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

class JPrepoViewFileRevisions extends HtmlView
{
    /**
     * File head revision record
     *
     * @var    object
     */
    protected $item;

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
     * Indicates whether the site is running Joomla 2.5 or not
     *
     * @var    boolean
     */


    /**
     * Display the view
     *
     * @return    void
     */
    public function display($tpl = null)
    {
        $this->item    = $this->get('Item');
        $this->items   = $this->get('Items');
        $this->state   = $this->get('State');
        $this->authors = $this->get('Authors');

        if ($this->state->get('list.direction') == 'desc') {
            array_unshift($this->items, $this->item);
        }
        else {
            $this->items[] = $this->item;
        }

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        if (!$this->item || empty($this->item->id)) {
            \Joomla\CMS\Factory::getApplication()->enqueueMessage( Text::_('COM_JOOMPROJECT_ERROR_FILE_NOT_FOUND'),'error');
            return false;
        }

        // Check access
        $user = Factory::getApplication()->getIdentity();

        if (!$user->authorise('core.admin', 'com_jprepo')) {
            $levels = $user->getAuthorisedViewLevels();

            if (!in_array($this->item->access, $levels)) {
                \Joomla\CMS\Factory::getApplication()->enqueueMessage( Text::_('JERROR_ALERTNOAUTHOR'),'warning');
                return false;
            }
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
        $id     = $this->item->id;
        $author = $this->item->created_by;
        $asset  = 'com_jprepo.file.' . $id;
        $user   = Factory::getApplication()->getIdentity();
        $uid    = $user->get('id');

        $title = Text::_('COM_JOOMPROJECT_PAGE_VIEW_FILE_REVISIONS')
               . ': ' . $this->escape($this->item->title);

        $link = 'index.php?option=com_jprepo&view=repository'
              . '&filter_project=' . (int) $this->item->project_id
              . '&filter_parent_id=' . (int) $this->item->dir_id;

        ToolbarHelper::title($title, 'article-add.png');

        ToolbarHelper::back('JTOOLBAR_BACK', $link);

        if ($user->authorise('core.edit', $asset) || ($user->authorise('core.edit.own', $asset) && $author == $uid)) {
            ToolbarHelper::custom('file.edit', 'edit', 'edit', 'JTOOLBAR_EDIT', false);
        }
    }


    /**
     * Adds the page side bar for Joomla 3.0 and higher
     *
     * @return    void
     */
    protected function addSidebar()
    {
        $link = 'index.php?option=com_jprepo&view=filerevisions'
              . '&filter_project=' . (int) $this->item->project_id
              . '&filter_parent_id=' . (int) $this->item->dir_id
              . '&id=' . (int) $this->item->id;

        Sidebar::setAction($link);

        Sidebar::addFilter(
            Text::_('JOPTION_SELECT_AUTHOR'),
            'filter_author_id',
            HTMLHelper::_('select.options', $this->authors, 'value', 'text', $this->state->get('filter.author_id'))
        );
    }
}
