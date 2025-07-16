<?php

/**
 * @package      pkg_joomproject
 * @subpackage   com_joomproject
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\HTML\Helpers\Sidebar;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Helper\ContentHelper;
class JPusersViewRoles extends HtmlView
{
    protected $state;

    protected $users;

    protected $pagination;

    public function display($tpl = null)
    {

        $this->items         = $this->get('Items');
        $this->pagination    = $this->get('Pagination');
        $this->state         = $this->get('State');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
        $this->canDo         = ContentHelper::getActions('com_jpprojects');
        $this->db            = Factory::getDbo();
        // Check for errors.
        if (count($errors = $this->get('Errors')))
        {
            throw new Exception(implode("\n", $errors), 500);
        }
        $this->addToolbar();
        JPdesignsHelper::addSubmenu('roles');
        $this->sidebar = Sidebar::render();
        parent::display($tpl);
    }

    protected function addToolbar()
    {
        $canDo = $this->canDo;



        $user  = Factory::getApplication()->getIdentity();

        // Get the toolbar object instance
        $bar = Toolbar::getInstance('toolbar');

        ToolbarHelper::title(Text::_('COM_JOOMPROJECT_ROLES'), 'lock');

        if ($canDo->get('core.manage.roles'))
        {

            ToolbarHelper::addNew('role.add');
            ToolbarHelper::editList('role.edit');
            ToolbarHelper::publish('roles.publish', 'JTOOLBAR_PUBLISH', true);
            ToolbarHelper::unpublish('roles.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            ToolbarHelper::archiveList('roles.archive');
        }

        if ($this->state->get('filter.published') == -2 && $canDo->get('core.manage.roles'))
        {
            ToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'roles.delete', 'JTOOLBAR_EMPTY_TRASH');
        }
        elseif ($canDo->get('core.manage.roles'))
        {
            ToolbarHelper::trash('roles.trash');
        }



        if ($canDo->get('core.admin') || $canDo->get('core.options'))
        {
            ToolbarHelper::divider();
        }

    }

}
