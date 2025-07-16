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





class JPtasksViewTask extends HtmlView
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


	    $reminderTabActive = Factory::getApplication()->getUserState('reminderTabActive');
	    $reminderSaved = Factory::getApplication()->getUserState('reminderSaved');

	    if(isset($reminderSaved) && $reminderSaved){
		    Factory::getApplication()->enqueueMessage(Text::_('COM_JPREMINDERS_REMINDER_SAVED'),'success');
		    Factory::getApplication()->setUserState('reminderSaved',0);
	    }

	    if(isset($reminderTabActive) && $reminderTabActive){
		    $this->tab_active =  array('active' => 'reminders');
		    Factory::getApplication()->setUserState('reminderTabActive',0);
	    }else{
		    $this->tab_active = ['active' => 'site', 'recall' => true, 'breakpoint' => 768];
	    }

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
        $access      = JPtasksHelper::getActions($this->item->id);
        $checked_out = !($this->item->checked_out == 0 || $this->item->checked_out == $uid);
        $is_new      = ($this->item->id == 0);

        ToolbarHelper::title(Text::_('COM_JOOMPROJECT_PAGE_' . ($checked_out ? 'VIEW_TASK' : ($is_new ? 'ADD_TASK' : 'EDIT_TASK'))), 'article-add.png');

        // Build the actions for new and existing records.
        // For new records, check the create permission.
        if ($is_new) {
            ToolbarHelper::apply('task.apply');
            ToolbarHelper::save('task.save');
            ToolbarHelper::save2new('task.save2new');
            ToolbarHelper::cancel('task.cancel');
        }
        else {
            // Can't save the record if it's checked out.
            if (!$checked_out) {
                if ($access->get('core.edit') || ($access->get('core.edit.own') && $this->item->created_by == $uid)) {
                    ToolbarHelper::apply('task.apply');
                    ToolbarHelper::save('task.save');
                    ToolbarHelper::save2new('task.save2new');
                }
            }

            ToolbarHelper::save2copy('task.save2copy');
            ToolbarHelper::cancel('task.cancel', 'JTOOLBAR_CLOSE');
        }
    }
}
