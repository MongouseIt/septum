<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * View to edit an reminder.
 *
 * @since  1.6
 */
class JPremindersViewForm extends HtmlView
{
	/**
	 * The JForm object
	 *
	 * @var  JForm
	 */
	protected $form;

	/**
	 * The active item
	 *
	 * @var  object
	 */
	protected $item;

	/**
	 * The model state
	 *
	 * @var  object
	 */
	protected $state;

	/**
	 * The actions the user is authorised to perform
	 *
	 * @var  JObject
	 */
	protected $canDo;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since   1.6
	 */
	public function display($tpl = null)
	{

		$this->form  = $this->get('Form');
		$this->item  = $this->get('Item');
		$this->state = $this->get('State');

        // get task id of reminder
        $taskId = $this->item->task_id > 0 ? $this->item-> task_id : Factory::getApplication()->input->get('task_id', 0, 'INT');

        // get task details
        $this->task = $this->getReminderTask($taskId);

        // check if task id exist , if not don't continue;
        if(empty($this->task) || is_null($this->task)){
            \Joomla\CMS\Factory::getApplication()->enqueueMessage(Text::_('Invalid Task'),'error');
           return false;
        }

		// if user allowed to add/edit task assigned to this reminder is allowed to do for reminder too
		// Permission check.
		if ($this->item->id <= 0) { // if adding a new reminder

			JLoader::register('JPtasksHelper',JPATH_ADMINISTRATOR.'/components/com_jptasks/helpers/jptasks.php');

			$access = JPtasksHelper::getActions();
			$authorised = $access->get('core.create');

		}
		else { // if edit existing reminder

			$taskModel = BaseDatabaseModel::getInstance('TaskForm', 'JPtasksModel', array('ignore_request' => true));
			$assigned_task = $taskModel->getItem($this->item->task_id);
			$authorised = $assigned_task->params->get('access-edit');
		}

		if ($authorised !== true) {
			\Joomla\CMS\Factory::getApplication()->enqueueMessage( Text::_('JERROR_ALERTNOAUTHOR'),'error');
			return false;
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		return parent::display($tpl);
	}

    protected function getReminderTask($taskid){
        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select('*')
            ->from('#__jp_tasks')
            ->where('id = ' . $taskid);

        $db->setQuery($query);

        return $db->loadObject();
    }

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		Factory::getApplication()->input->set('hidemainmenu', true);
		$user       = Factory::getApplication()->getIdentity();
		$userId     = $user->id;
		$isNew      = ($this->item->id == 0);
		$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $userId);

		// Built the actions for new and existing records.
		$canDo = $this->canDo;

		ToolbarHelper::title(
			Text::_('COM_JPREMINDERS_PAGE_' . ($checkedOut ? 'VIEW_REMINDER' : ($isNew ? 'ADD_REMINDER' : 'EDIT_REMINDER'))),
			'pencil-2 article-add'
		);

		// For new records, check the create permission.
		if ($isNew && (count($user->getAuthorisedCategories('com_jpreminders', 'core.create')) > 0))
		{
			ToolbarHelper::apply('form.apply');
			ToolbarHelper::save('form.save');
			ToolbarHelper::cancel('form.cancel');
		}
		else
		{
			// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
			$itemEditable = $canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId);

			// Can't save the record if it's checked out and editable
			if (!$checkedOut && $itemEditable)
			{
				ToolbarHelper::apply('form.apply');
				ToolbarHelper::save('form.save');

			}



			if (Associations::isEnabled() && ComponentHelper::isEnabled('com_associations'))
			{
				ToolbarHelper::custom('form.editAssociations', 'contract', 'contract', 'JTOOLBAR_ASSOCIATIONS', false, false);
			}

			ToolbarHelper::cancel('form.cancel', 'JTOOLBAR_CLOSE');
		}

		ToolbarHelper::divider();
		ToolbarHelper::help('JHELP_CONTENT_ARTICLE_MANAGER_EDIT');
        return JToolBar::getInstance('toolbar')->render();
	}
}
