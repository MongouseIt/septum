<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jpprojects
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */
defined('_JEXEC') or die();

use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\View\HtmlView;





/**
 * Project list view class.
 *
 */
class JPprojectsViewProjects extends HtmlView
{
    public $pageclass_sfx;
    public $items;
    public $pagination;
    public $params;
    public $state;
    public $access;
    public $toolbar;
    public $sort_options;
    public $order_options;


    /**
     * Display the view
     *
     * @return    void
     */
    public function display($tpl = null)
    {
        $app    = Factory::getApplication();
        $active = $app->getMenu()->getActive();
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state      = $this->get('State');
        $this->params     = $this->state->get('params');
        $this->access     = JPprojectsHelper::getActions();

        $this->toolbar       = $this->getToolbar();
        $this->sort_options  = $this->getSortOptions();
        $this->order_options = $this->getOrderOptions();
        $this->config	= ComponentHelper::getParams( 'com_joomproject' );



        // Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx',''));

        // Check for errors
        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        // Check for empty filter result
        if ((count($this->items) == 0) && $this->state->get('filter.isset')) {
            $app->enqueueMessage(Text::_('COM_JOOMPROJECT_EMPTY_SEARCH_RESULT'));
        }

        // Check for layout override
        if (isset($active->query['layout']) && (Factory::getApplication()->input->getCmd('layout') == '')) {
            $this->setLayout($active->query['layout']);
        }

        // Prepare the document
        $this->prepareDocument();

        // Display the view
        parent::display($tpl);
    }


    /**
     * Prepares the document
     *
     * @return    void
     */
    protected function prepareDocument()
    {
        $app     = Factory::getApplication();
        $menu    = $app->getMenu()->getActive();
        $pathway = $app->getPathway();
        $title   = null;

        // Because the application sets a default page title, we need to get it from the menu item itself
        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        }
        else {
            $this->params->def('page_heading', Text::_('COM_JOOMPROJECT_PROJECTS'));
        }

        // Set the page title
        $title = $this->params->get('page_title', '');

        if (empty($title)) {
            $title = $app->getCfg('sitename');
        }
        elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
            $title = Text::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
        }
        elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
            $title = Text::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
        }

        $this->document->setTitle($title);

        // Set crawler behavior info
        if ($this->params->get('robots')) {
            $this->document->setMetadata('robots', $this->params->get('robots'));
        }

        // Set page description
        if ($this->params->get('menu-meta_description')) {
            $this->document->setDescription($desc);
        }

        // Set page keywords
        if ($this->params->get('menu-meta_keywords')) {
            $this->document->setMetadata('keywords', $keywords);
        }

        // Add feed links
        if ($this->params->get('show_feed_link', 1)) {
            $link    = '&format=feed&limitstart=';
            $attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
            $this->document->addHeadLink(Route::_($link . '&type=rss'), 'alternate', 'rel', $attribs);
            $attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
            $this->document->addHeadLink(Route::_($link . '&type=atom'), 'alternate', 'rel', $attribs);
        }
    }


    /**
     * Generates the toolbar for the top of the view
     *
     * @return    string    Toolbar with buttons
     */
    protected function getToolbar()
    {
        $access  = JPprojectsHelper::getActions();
        $state   = $this->get('State');
        $options = array();

        JPToolbar::button(
            'COM_JOOMPROJECT_ACTION_NEW',
            'form.add',
            false,
	        ['access' => $access->get('core.create'),'class' => 'btn-success']
        );


        if ($access->get('core.edit.state')) {
            $options[] = array('text' => 'COM_JOOMPROJECT_ACTION_PUBLISH',   'task' => $this->getName() . '.publish');
            $options[] = array('text' => 'COM_JOOMPROJECT_ACTION_UNPUBLISH', 'task' => $this->getName() . '.unpublish');
            $options[] = array('text' => 'COM_JOOMPROJECT_ACTION_ARCHIVE',   'task' => $this->getName() . '.archive');
            $options[] = array('text' => 'COM_JOOMPROJECT_ACTION_CHECKIN',   'task' => $this->getName() . '.checkin');
        }

        if ($state->get('filter.published') == -2 && $access->get('core.delete')) {
            $options[] = array('text' => 'COM_JOOMPROJECT_ACTION_DELETE', 'task' => $this->getName() . '.delete');
        }
        elseif ($access->get('core.edit.state')) {
            $options[] = array('text' => 'COM_JOOMPROJECT_ACTION_TRASH', 'task' => $this->getName() . '.trash');
        }

        if (count($options)) {
            JPToolbar::listButton($options);
        }


        if ($this->params->get('show_filter', '1')) {
            JPToolbar::filterButton($this->state->get('filter.isset'));
        }

        return JPToolbar::render();
    }


    /**
     * Generates the table sort options
     *
     * @return    array    HTML list options
     */
    protected function getSortOptions()
    {
        $options = array();

        $options[] = HTMLHelper::_('select.option', '', Text::_('COM_JOOMPROJECT_ORDER_SELECT'));
        $options[] = HTMLHelper::_('select.option', 'category_title, a.title', Text::_('COM_JOOMPROJECT_ORDER_TITLE'));
        $options[] = HTMLHelper::_('select.option', 'a.end_date', Text::_('COM_JOOMPROJECT_ORDER_DEADLINE'));
        $options[] = HTMLHelper::_('select.option', 'author_name', Text::_('COM_JOOMPROJECT_ORDER_AUTHOR'));

        return $options;
    }


    /**
     * Generates the table order options
     *
     * @return    array    HTML list options
     */
    protected function getOrderOptions()
    {
        $options = array();

        $options[] = HTMLHelper::_('select.option', '', Text::_('COM_JOOMPROJECT_ORDER_SELECT_DIR'));
        $options[] = HTMLHelper::_('select.option', 'ASC', Text::_('COM_JOOMPROJECT_ORDER_ASC'));
        $options[] = HTMLHelper::_('select.option', 'DESC', Text::_('COM_JOOMPROJECT_ORDER_DESC'));

        return $options;
    }

    public function group_by($key, $data) {
        $result = array();

        foreach($data as $val) {
            if(array_key_exists($key, $val)){
                $result[$val[$key]][] = $val;
            }else{
                $result[""][] = $val;
            }
        }

        return $result;
    }

   public function array_group_by(array $array, $key)
    {
        if (!is_string($key) && !is_int($key) && !is_float($key) && !is_callable($key) ) {
            trigger_error('array_group_by(): The key should be a string, an integer, or a callback', E_USER_ERROR);
            return null;
        }
        $func = (!is_string($key) && is_callable($key) ? $key : null);
        $_key = $key;
        // Load the new array, splitting by the target key
        $grouped = [];
        foreach ($array as $value) {
            $key = null;
            if (is_callable($func)) {
                $key = call_user_func($func, $value);
            } elseif (is_object($value) && property_exists($value, $_key)) {
                $key = $value->{$_key};
            } elseif (isset($value[$_key])) {
                $key = $value[$_key];
            }
            if ($key === null) {
                continue;
            }
            $grouped[$key][] = $value;
        }
        // Recursively build a nested grouping if more parameters are supplied
        // Each grouped array value is grouped according to the next sequential key
        if (func_num_args() > 2) {
            $args = func_get_args();
            foreach ($grouped as $key => $value) {
                $params = array_merge([ $value ], array_slice($args, 2, func_num_args()));
                $grouped[$key] = call_user_func_array('array_group_by', $params);
            }
        }
        return $grouped;
    }
}

