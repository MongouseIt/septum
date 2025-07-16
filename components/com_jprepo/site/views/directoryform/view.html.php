<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jprepo
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
 * Directory Form View Class for Joomproject component
 *
 */
class JPrepoViewDirectoryForm extends HtmlView
{
    protected $form;
    protected $item;
    protected $return_page;
    protected $state;
    protected $toolbar;


    public function display($tpl = null)
    {
        if(!\JoomProject\Permission\GlobalAccess::check('com_jprepo'))
            return;

        // Initialise variables.
        $app  = Factory::getApplication();
        $user = Factory::getApplication()->getIdentity();

        // Get model data.
        $this->state       = $this->get('State');
        $this->item        = $this->get('Item');
        $this->form        = $this->get('Form');
        $this->return_page = $this->get('ReturnPage');
        $this->toolbar     = $this->getToolbar();

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        // Permission check.
        if (empty($this->item->id)) {
            $access = JPrepoHelper::getActions('directory');
            $authorised = $access->get('core.create');
        }
        else {
            $authorised = $this->item->params->get('access-edit');
        }

        if ($authorised !== true) {
           Factory::getApplication()->enqueueMessage( Text::_('JERROR_ALERTNOAUTHOR'),'error');
            return false;
        }

        // Create a shortcut to the parameters.
        $params = &$this->state->params;

        //Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx',''));

        $this->params = $params;
        $this->user   = $user;

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
        $menus   = $app->getMenu();
        $pathway = $app->getPathway();
        $title   = null;

        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menu = $menus->getActive();

        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        }
        else {
            $this->params->def('page_heading', Text::_('COM_JOOMPROJECT_FORM_EDIT_DIRECTORY'));
        }

        $title = $this->params->def('page_title', Text::_('COM_JOOMPROJECT_FORM_EDIT_DIRECTORY'));

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

        JPToolbar::dropdownButton($options,  array('class' => 'btn-success', 'icon' => 'fas fa-success'));

        JPToolbar::button(
            'JCANCEL',
            $this->getName() . '.cancel',
            false,
            array('class' => 'btn-danger', 'icon' => 'fas fa-times')
        );

        return JPToolbar::render();
    }
}
