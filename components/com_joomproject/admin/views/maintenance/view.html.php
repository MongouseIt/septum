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

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\HTML\Helpers\Sidebar;
use Joomla\CMS\Toolbar\ToolbarHelper;



class JoomprojectViewMaintenance extends HtmlView
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

	    // Access check.
	    if (!Factory::getApplication()->getIdentity()->authorise('core.admin'))
	    {
		    throw new   NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
	    }

        $this->sidebar = Sidebar::render();
        parent::display($tpl);
    }


    /**
     * Add the page title and toolbar.
     *
     */
    protected function addToolbar()
    {
        ToolbarHelper::title('<i class="fas fa-toolbox"></i>  '.Text::_('COM_JOOMPROJECT_MAINTENANCE_TITLE'));

        if (Factory::getApplication()->getIdentity()->authorise('core.admin')) {
            ToolbarHelper::preferences('com_joomproject');
        }
        
        ToolbarHelper::addNew('dashboard.addproject'	,'COM_JOOMPROJECT_ADD_NEWPROJECT');
        ToolbarHelper::addNew('dashboard.addmilestone'	,'COM_JOOMPROJECT_ADD_NEWMILESTONE');
        ToolbarHelper::addNew('dashboard.addnewtask'	,'COM_JOOMPROJECT_ADD_NEWTASK');
        ToolbarHelper::addNew('dashboard.addtopic'		,'COM_JOOMPROJECT_ADD_NEWTOPIC');
        ToolbarHelper::addNew('dashboard.adddesign'	,'COM_JOOMPROJECT_ADD_NEWDESIGN');
    }



}
