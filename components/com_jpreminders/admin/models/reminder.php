<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_joomproject
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2019 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die;


use Joomla\CMS\Date\Date;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\Utilities\ArrayHelper;

/**
 * Reminder Model for an Reminder.
 *
 * @since  1.6
 */
class JPremindersModelReminder extends AdminModel
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_JPREMINDERS';

	/**
	 * The type alias for this content type (for example, 'com_jpreminders.reminder').
	 *
	 * @var    string
	 * @since  3.2
	 */
	public $typeAlias = 'com_jpreminders.reminder';


	/**
	 * Method to test whether a record can have its state edited.
	 *
	 * @param object $record A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission set in the component.
	 *
	 * @since   1.6
	 */
	protected function canEditState($record)
	{
		$user = Factory::getApplication()->getIdentity();

		// Check for existing article.
		if (!empty($record->id))
		{
			return $user->authorise('core.edit.state', 'com_jpreminders.reminder.' . (int) $record->id);
		}

		// Default to component settings if neither article nor category known.
		return parent::canEditState($record);
	}


	/**
	 * Returns a Table object, always creating it.
	 *
	 * @param string $type   The table type to instantiate
	 * @param string $prefix A prefix for the table class name. Optional.
	 * @param array  $config Configuration array for model. Optional.
	 *
	 * @return  JTable    A database object
	 */
	public function getTable($type = 'Reminder', $prefix = 'Table', $config = array())
	{
		return Table::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get a single record.
	 *
	 * @param integer $pk The id of the primary key.
	 *
	 * @return  mixed  Object on success, false on failure.
	 */
	public function getItem($pk = null)
	{

		$pk    = (!empty($pk)) ? (int) $pk : (int) $this->getState($this->getName() . '.id');
		$table = $this->getTable();

		if ($pk > 0)
		{
			// Attempt to load the row.
			$return = $table->load($pk);

			// Check for a table object error.
			if ($return === false && $table->getError())
			{
				$this->setError($table->getError());

				return false;
			}
		}

		// Convert to the JObject before adding other data.
		$properties = $table->getProperties(1);
		$item       = \Joomla\Utilities\ArrayHelper::toObject($properties, CMSObject::class);

		// Convert attributes to JRegistry params
		$item->params = new Registry();

		$item->params->loadString((string)$item->attribs);
		$item->attribs = $item->params->toArray();


		return $item;
	}


	/**
	 * Method to get the record form.
	 *
	 * @param array   $data     Data for the form.
	 * @param boolean $loadData True if the form is to load its own data (default case), false if not.
	 *
	 * @return  JForm|boolean  A JForm object on success, false on failure
	 *
	 * @since   1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$app  = Factory::getApplication();
		$user = Factory::getApplication()->getIdentity();

		// Get the form.
		$form = $this->loadForm('com_jpreminders.reminder', 'reminder', array('control' => 'jform', 'load_data' => $loadData));


		if (empty($form))
		{
			return false;
		}


		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$app  = Factory::getApplication();
		$data = $app->getUserState('com_jpreminders.edit.reminder.data', array());

		if (empty($data))
		{
			$data                 = $this->getItem();
			$users                = implode(',', (array) json_decode((string)$data->assigned_users, true));
			$data->assigned_users = explode(',', $users);

		}

		// Pre-select some filters (Status, Category, Language, Access) in edit form if those have been selected in Article Manager: Articles


		// If there are params fieldsets in the form it will fail with a registry object
		if (isset($data->params) && $data->params instanceof Registry)
		{
			$data->params = $data->params->toArray();
		}

		$this->preprocessData('com_jpreminders.reminder', $data);

		return $data;
	}

	/**
	 * Method to validate the form data.
	 *
	 * @param JForm  $form  The form to validate against.
	 * @param array  $data  The data to validate.
	 * @param string $group The name of the field group to validate.
	 *
	 * @return  array|boolean  Array of filtered data if valid, false otherwise.
	 *
	 * @see     JFormRule
	 * @see     JFilterInput
	 * @since   3.7.0
	 */
	public function validate($form, $data, $group = null)
	{
		// Don't allow to change the users if not allowed to access com_users.
		if (Factory::getApplication()->isClient('administrator') && !Factory::getApplication()->getIdentity()->authorise('core.manage', 'com_users'))
		{
			if (isset($data['created_by']))
			{
				unset($data['created_by']);
			}

			if (isset($data['modified_by']))
			{
				unset($data['modified_by']);
			}
		}

		return parent::validate($form, $data, $group);
	}

	/**
	 * Allows preprocessing of the JForm object.
	 *
	 * @param JForm  $form  The form object
	 * @param array  $data  The data to be merged into the form object
	 * @param string $group The plugin group to be executed
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	protected function preprocessForm(Form $form, $data, $group = 'reminder')
	{

		parent::preprocessForm($form, $data, $group);
	}

	/**
	 * Method to save the form data.
	 *
	 * @param array $data The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.6
	 */

	public function save($data)
	{

		// preprocess data before saving
		$jinput                 = Factory::getApplication()->input;
		$dates                  = JPremindersHelper::reminderDates($data['start_date'], $data['amount'], $data['types'], $data['repeats']);
		$data['assigned_users'] = empty($data['assigned_users']) ? '' : json_encode($data['assigned_users']);
		$data['task_id']        = $jinput->get('taskid', 0, 'INT');


		// save reminder and all dates
		if (parent::save($data))
		{
			$id = $this->getState($this->getName() . '.id');

			if ($this->getState($this->getName() . '.new'))
			{
				$this->insertDates($id, $dates);

			}
			else
			{
				$this->deleteDates($id);
				$this->insertDates($id, $dates);

			}

			return true;
		}

		return false;
	}

	// Insert Dates
	public function insertDates($reminder_id, $dates)
	{

		foreach ($dates as $date)
		{
			// Create and populate an object.
			$reminder              = new stdClass();
			$reminder->reminder_id = $reminder_id;
			$reminder->date        = $date;
			if (Factory::getDate($reminder->date)->format('Y-m-d H:i') >= Factory::getDate('now')->format('Y-m-d H:i'))
			{
				Factory::getDbo()->insertObject('#__jp_reminders_dates', $reminder);
			}
		}
	}

	// delete dates of specified reminder
	public function deleteDates($reminder_id, $sendemail = false)
	{

		$db = Factory::getDbo();

		$query       = $db->getQuery(true);
		$date        = new Date('now');
		$currentDate = $date->format('Y-m-d H');

        // delete all custom keys for reminder.
		/*if ($sendemail == false)
		{
			$conditions = array(
				$db->quoteName('reminder_id') . ' =' . (int) $reminder_id

			);
		}
		else
		{
			$conditions = array(
				$db->quoteName('reminder_id') . ' =' . (int) $reminder_id,
				("(DATE_FORMAT(date,'%Y-%m-%d %H') = '" . $currentDate . "')")
			);
		}*/


        $conditions = array(
            $db->quoteName('reminder_id') . ' =' . (int) $reminder_id

        );


		$query->delete($db->quoteName('#__jp_reminders_dates'));
		$query->where($conditions);
		$db->setQuery($query);

		return $db->execute();

	}


	public function getDates($limit = null)
	{
		// Get a db connection.
		$db = Factory::getDbo();

		// Create a new query object.
		$query       = $db->getQuery(true);

		$date        = new Date('now');
		$currentDate = $date->format('Y-m-d');

		$query->select('*,d.id as date_id,r.id as reminder_id,u.id as userid,u.name,u.email,r.state');
		$query->from('#__jp_reminders_dates AS d');
		$query->join('LEFT', '#__jp_reminders AS r ON d.reminder_id = r.id');
		$query->join('LEFT', '#__users AS u ON u.id = r.created_by');


		if (empty($limit) || is_null($limit))
		{
			$db->setQuery($query);
		}
		else
		{
			$db->setQuery($query, 0, $limit);
		}

		// Load the results as a list of stdClass objects
		return $db->loadObjectList();
	}


	/* public function countReminder($reminder_id)
	 {
		 // Get a db connection.
		 $db = Factory::getDbo();

 // Create a new query object.
		 $query = $db->getQuery(true);

 // Select all records from the user profile table where key begins with "custom.".
 // Order it by the ordering field.
		 $query->select('count(*) AS total');
		 $query->from($db->quoteName('#__jp_reminders_dates'));
		 $query->where($db->quoteName('reminder_id'). ' = '. $reminder_id);

 // Reset the query using our newly populated query object.
		 $db->setQuery($query);

 // Load the results as a list of stdClass objects (see later for more options on retrieving data).
		 return $db->loadObject();

	 }
	*/

	public function reminderDate($reminder_id)
	{
		// Get a db connection.
		$db = Factory::getDbo();

		// Create a new query object.
		$query = $db->getQuery(true);

		// Select all records from the Reminder Date table where key = reminder_id".
		$query->select('date,count(*) AS total');
		$query->from($db->quoteName('#__jp_reminders_dates'));
		$query->where($db->quoteName('reminder_id') . ' = ' . $reminder_id);
		// Order it by the ordering field.
		$query->order('date ASC');

		$db->setQuery($query);

		return $db->loadObject();

	}


	// Delete Expired dates
	public function expiredDate()
	{

		$date        = new Date('now');
		$currentDate = $date->format('Y-m-d H');

		$db = Factory::getDbo();

		$query = $db->getQuery(true);

		$conditions = array(
			("(DATE_FORMAT(date,'%Y-%m-%d %H') < '" . $currentDate . "')")
		);

		$query->delete($db->quoteName('#__jp_reminders_dates'));
		$query->where($conditions);

		$db->setQuery($query);

		return $db->execute();

	}

	// Delete Expired dates

	public function updateRstate($reminder_id)
	{

		$db = Factory::getDbo();

		$query = $db->getQuery(true);

// Fields to update.
		$fields = array(
			$db->quoteName('state') . ' = 0',
		);

// Conditions for which records should be updated.
		$conditions = array(
			$db->quoteName('state') . ' = 1',
			$db->quoteName('id') . ' = ' . (int) $reminder_id
		);

		$query->update($db->quoteName('#__jp_reminders'))->set($fields)->where($conditions);

		$db->setQuery($query);

		return $db->execute();

	}


	// Delete Reminder
	public function deleteReminder()
	{

		$id = Factory::getApplication()->input->get('id', 0, 'INT');

		$db = Factory::getDbo();

		$query = $db->getQuery(true);

		$query->delete('#__jp_reminders');
		$query->where('id = ' . $id[0]);

		$db->setQuery($query);

		$db->execute();

		$this->deleteDates($id[0]);

	}

	/*
	 * Delete reminder without tasks
	 */
	public function deleteReminders($task_id)
	{

		// get reminders ids without task
		$ids = $this->getRemindersbyTaskId($task_id);

		if (!is_null($ids))
		{

			// removes reminders dates
			foreach ($ids as $id)
			{
				$this->deleteDates($id);
			}

			// removes reminders
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);

			$query->delete('#__jp_reminders');
			$query->where('task_id = ' . $task_id);
			$query->where('created_by = ' . Factory::getApplication()->getIdentity()->id);

			$db->setQuery($query);

			$db->execute();

			return true;

		}

		return false;


	}

	public function getRemindersByTaskId($id)
	{

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id');
		$query->from($db->quoteName('#__jp_reminders'));
		$query->where($db->quoteName('task_id') . ' = ' . $id);
		$query->where($db->quoteName('created_by') . ' = ' . Factory::getApplication()->getIdentity()->id);

		$db->setQuery($query);

		return $db->loadColumn();
	}

}
