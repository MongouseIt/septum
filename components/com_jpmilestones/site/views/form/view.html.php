<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jpmilestones
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */


defined('_JEXEC') or die();

use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;





/**
 * Milestone Form View Class for Joomproject component
 *
 */
class JPmilestonesViewForm extends HtmlView
{
    protected $form;
    protected $item;
    protected $return_page;
    protected $state;
    protected $toolbar;
    protected $pageclass_sfx;
    protected $params;


    public function display($tpl = null)
    {

        // check global access
        if(!\JoomProject\Permission\GlobalAccess::check('com_jpmilestones'))
            return;

        $this->state       = $this->get('State');
        $this->item        = $this->get('Item');
        $this->form        = $this->get('Form');
        $this->return_page = $this->get('ReturnPage');
        $this->params      = $this->state->params;
        $this->toolbar     = $this->getToolbar();

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
            return false;
        }

        // Permission check.
        if ($this->item->id <= 0) {
            $access = JPmilestonesHelper::getActions();
            $authorised = $access->get('core.create');
        }
        else {
            $authorised = $this->item->params->get('access-edit');
        }

        if ($authorised !== true) {
            \Joomla\CMS\Factory::getApplication()->enqueueMessage( Text::_('JERROR_ALERTNOAUTHOR'),'error');
            return false;
        }

        //Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($this->state->params->get('pageclass_sfx',''));

        // Prepare the document
        $this->_prepareDocument();

        // Display the view
        parent::display($tpl);
    }


    /**
     * Prepares the document
     *
     */
    protected function _prepareDocument()
    {
        $app     = Factory::getApplication();
        $menu    = $app->getMenu()->getActive();
        $pathway = $app->getPathway();
        $title   = null;

        $def_title = Text::_('COM_JOOMPROJECT_PAGE_' . ($this->item->id > 0 ? 'EDIT' : 'ADD') . '_MILESTONE');

        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        if ($menu) {
            if (strpos($menu->link, 'view=milestones') !== false) {
                $this->params->def('page_heading', $def_title);
            }
            else {
                $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
            }
        }
        else {
            $this->params->def('page_heading', $def_title);
        }

        $title = $this->params->def('page_title', $def_title);

        if ($app->getCfg('sitename_pagetitles', 0) == 1) {
            $title = Text::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
        }
        elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
            $title = Text::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
        }

        $this->document->setTitle($title);

        $pathway = $app->getPathWay();
        $pathway->addItem($title, '');

        if ($this->params->get('menu-meta_description')) {
            $this->document->setDescription($this->params->get('menu-meta_description'));
        }

        if ($this->params->get('menu-meta_keywords')) {
            $this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
        }

        if ($this->params->get('robots')) {
            $this->document->setMetadata('robots', $this->params->get('robots'));
        }
    }


    /**
     * Generates the toolbar for the top of the view
     *
     * @return    string    Toolbar with buttons
     */
    protected function getToolbar()
    {
        $options = array();

        $options[] = array(
            'text' => 'JSAVE',
            'task' => $this->getName() . '.save');

        $options[] = array(
            'text' => 'COM_JOOMPROJECT_ACTION_2NEW',
            'task' => $this->getName() . '.save2new');

        $options[] = array(
            'text' => 'COM_JOOMPROJECT_ACTION_2COPY',
            'task' => $this->getName() . '.save2copy',
            'options' => array('access' => ($this->item->id > 0)));

        if (JPApplicationHelper::enabled('com_jptasks') && Factory::getApplication()->getIdentity()->authorise('core.create', 'com_jptasks')) {
            $options[] = array('text' => 'divider');

            $options[] = array(
                'text' => 'COM_JOOMPROJECT_ACTION_2TASKLIST',
                'task' => $this->getName() . '.save2tasklist');

            $options[] = array(
                'text' => 'COM_JOOMPROJECT_ACTION_2TASK',
                'task' => $this->getName() . '.save2task');
        }

        JPToolbar::dropdownButton($options,  array('class' => 'btn-success', 'icon' => 'fas fa-check'));

        JPToolbar::button(
            'JCANCEL',
            $this->getName() . '.cancel',
            false,
            array('class' => 'btn-danger', 'icon' => 'fas fa-times')
        );

        return JPToolbar::render();
    }
}
