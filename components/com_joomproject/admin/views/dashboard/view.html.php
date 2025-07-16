<?php
/**
 * @package      Joomproject
 * @subpackage   Dashboard
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\HTML\Helpers\Sidebar;
use Joomla\CMS\Toolbar\ToolbarHelper;



JLoader::register('JoomprojectHelperDashboard',JPATH_ADMINISTRATOR.'/components/com_joomproject/helpers/dashboard.php');

class JoomprojectViewDashboard extends HtmlView
{
    /**
     * The list of available components
     *
     * @var    array
     */
    protected $components;

    /**
     * The current user object
     *
     * @var    object
     */
    protected $user;

    /**
     * The available buttons for rendering
     *
     * @var    array
     */
    protected $buttons;

    protected $modules;


    /**
     * Display the view
     *
     */
    public function display($tpl = null)
    {
    	$this->downloadid	= ComponentHelper::getParams('com_joomproject')->get('downloadid','');
    	$this->components = JPapplicationHelper::getComponents('com_jpreminders');
        $this->user       = Factory::getApplication()->getIdentity();
        $this->modules    = Factory::getDocument()->loadRenderer('modules');

        if ($this->getLayout() !== 'modal') $this->addToolbar();
        $this->sidebar = Sidebar::render();
        parent::display($tpl);
    }


    /**
     * Add the page title and toolbar.
     *
     */
    protected function addToolbar()
    {
        ToolbarHelper::title(Text::_('COM_JOOMPROJECT_DASHBOARD_TITLE'),'dashboard');


        ToolbarHelper::addNew('dashboard.addproject'	,'COM_JOOMPROJECT_ADD_NEWPROJECT');
        ToolbarHelper::addNew('dashboard.addmilestone'	,'COM_JOOMPROJECT_ADD_NEWMILESTONE');
        ToolbarHelper::addNew('dashboard.addnewtask'	,'COM_JOOMPROJECT_ADD_NEWTASK');
        ToolbarHelper::addNew('dashboard.addtopic'		,'COM_JOOMPROJECT_ADD_NEWTOPIC');
        ToolbarHelper::addNew('dashboard.adddesign'	,'COM_JOOMPROJECT_ADD_NEWDESIGN');
        if (Factory::getApplication()->getIdentity()->authorise('core.admin')) {
            ToolbarHelper::preferences('com_joomproject');
        }
    }



}
