<?php
/**
 * @package      Joomproject
 * @subpackage   Tasks
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */


defined('_JEXEC') or die();

use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;



class JPtasksViewTasklist extends HtmlView
{
    protected $form;
    protected $item;
    protected $state;


    /**
     * Display the view
     *
     */
    public function display($tpl = null)
    {

        // Initialiase variables.
        $this->form  = $this->get('Form');
        $this->item  = $this->get('Item');
        $this->state = $this->get('State');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        $this->addToolbar();

        parent::display($tpl);
    }


    /**
     * Add the page title and toolbar.
     *
     */
    protected function addToolbar()
    {
        \Joomla\CMS\Factory::getApplication()->input->set('hidemainmenu', true);

        $uid         = Factory::getApplication()->getIdentity()->get('id');
        $access      = JPtasksHelper::getListActions($this->item->id);
        $checked_out = !($this->item->checked_out == 0 || $this->item->checked_out == $uid);
        $is_new      = ($this->item->id == 0);

        ToolbarHelper::title(Text::_('COM_JOOMPROJECT_PAGE_' . ($checked_out ? 'VIEW_TASKLIST' : ($is_new ? 'ADD_TASKLIST' : 'EDIT_TASKLIST'))), 'article-add.png');

        // Built the actions for new and existing records.
        // For new records, check the create permission.
        if ($is_new) {
            ToolbarHelper::apply('tasklist.apply');
            ToolbarHelper::save('tasklist.save');
            ToolbarHelper::save2new('tasklist.save2new');
            ToolbarHelper::cancel('tasklist.cancel');
        }
        else {
            // Can't save the record if it's checked out.
            if (!$checked_out) {
                if ($access->get('core.edit') || ($access->get('core.edit.own') && $this->item->created_by == $uid)) {
                    ToolbarHelper::apply('tasklist.apply');
                    ToolbarHelper::save('tasklist.save');
                    ToolbarHelper::save2new('tasklist.save2new');
                }
            }

            ToolbarHelper::save2copy('tasklist.save2copy');
            ToolbarHelper::cancel('tasklist.cancel', 'JTOOLBAR_CLOSE');
        }
    }
}
