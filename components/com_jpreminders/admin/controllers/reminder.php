<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_reminders
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die;


use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Uri\Uri;

/**
 * Directories list controller class.
 *
 */
class JPremindersControllerReminder extends FormController
{
	/**
	 * The URL view list variable.
	 *
	 * @var    string
	 */
	protected $view_list = 'reminders';


	/**
	 * Proxy for getModel.
	 *
	 * @param string $name   The name of the model.
	 * @param string $prefix The prefix for the PHP class name.
	 *
	 * @return    jmodel
	 */

	public function __construct($config = array())
	{
		parent::__construct($config);
	}

	/**
	 * Method to run batch operations.
	 *
	 * @param object $model The model.
	 *
	 * @return  boolean   True if successful, false otherwise and internal error is set.
	 *
	 * @since   1.6
	 */

	public function batch($model = null)
	{
		$this->checkToken();

		// Set the model
		/** @var ContentModelArticle $model */
		$model = $this->getModel('Reminder', '', array());

		// Preset the redirect
		$this->setRedirect(Route::_('index.php?option=com_jpreminders&view=reminders' . $this->getRedirectToListAppend(), false));

		return parent::batch($model);
	}

	public function reminderSend()
	{

		$model = $this->getModel('Reminder');
		$items = $model->getDates();

		foreach ($items as $item)
		{
			if ($item->date > 0)
			{
				$model->updateReminder();
			}
		}
	}

    /**
     * Method to check if you can add a new record.
     *
     * @param     array      $data    An array of input data.
     *
     * @return    boolean
     */
    protected function allowAdd($data = array())
    {
        // Get form input
        $project = isset($data['project_id'])   ? (int) $data['project_id']   : JPApplicationHelper::getActiveProjectId();
        $ms      = isset($data['milestone_id']) ? (int) $data['milestone_id'] : 0;
        $list    = isset($data['list_id'])      ? (int) $data['list_id']      : 0;

        $user   = Factory::getApplication()->getIdentity();
        $db     = Factory::getDbo();
        $is_sa  = $user->authorise('core.admin');
        $levels = $user->getAuthorisedViewLevels();
        $query  = $db->getQuery(true);
        $asset  = 'com_jptasks';
        $access = true;

        // Check if the user has access to the project
        if ($project) {
            // Check if in allowed projects when not a super admin
            if (!$is_sa) {
                $access = in_array($project, JPUserHelper::getAuthorisedProjects());
            }

            // Change the asset name
            $asset  .= '.project.' . $project;
        }

        // Check if the user can access the selected milestone when not a super admin
        if (!$is_sa && $ms && $access) {
            $query->select('access')
                ->from('#__jp_milestones')
                ->where('id = ' . $db->quote((int) $ms));

            $db->setQuery($query);
            $lvl = $db->loadResult();

            $access = in_array($lvl, $levels);
        }

        // Check if the user can access the selected task list when not a super admin
        if (!$is_sa && $list && $access) {
            $query->clear()
                ->select('access')
                ->from('#__jp_task_lists')
                ->where('id = ' . $list);

            $db->setQuery($query);
            $lvl = $db->loadResult();

            $access = in_array($lvl, $levels);

            // Change asset to list
            $asset = 'com_jptasks.tasklist.' . $list;
        }

        return ($user->authorise('core.create', $asset) && $access);

    }


    /**
     * Method override to check if you can edit an existing record.
     *
     * @param     array      $data    An array of input data.
     * @param     string     $key     The name of the key for the primary key.
     *
     * @return    boolean
     */
    protected function allowEdit($data = array(), $key = 'taskid')
    {
        // Get form input
        $id = (int) isset($data[$key]) ? $data[$key] : 0;

        $user  = Factory::getApplication()->getIdentity();
        $uid   = $user->get('id');
        $asset = 'com_jptasks.task.' . $id;

        // Check if the user has viewing access when not a super admin
        if (!$user->authorise('core.admin')) {
            $db    = Factory::getDbo();
            $query = $db->getQuery(true);

            $query->select('access')
                ->from('#__jp_tasks')
                ->where('id = ' . $id);

            $db->setQuery($query);
            $lvl = $db->loadResult();

            if (!in_array($lvl, $user->getAuthorisedViewLevels())) {
                return false;
            }
        }

        // Check edit permission first
        if ($user->authorise('core.edit', $asset)) {
            return true;
        }

        // Fallback on edit.own.
        // First test if the permission is available.
        if (!$user->authorise('core.edit.own', $asset)) {
            return false;
        }

        // Get the task of this reminder item
        $record = $this->getTask($id);

        // Abort if not found
        if (empty($record) || is_null($record)) return false;

        // Now test the owner is the user.
        $owner = (int) isset($data['task_created_by']) ? (int) $data['task_created_by'] : $record->created_by;

        // If the owner matches 'me' then do the test.
        return ($owner == $uid && $uid > 0);
    }

	public function save($key = null, $urlVar = null)
	{


		// Check for request forgeries.
		$this->checkToken();

		$app   = Factory::getApplication();
		$model = $this->getModel();
		$table = $model->getTable();
		$data  = $this->input->post->get('jform', array(), 'array');
		$checkin = property_exists($table, $table->getColumnAlias('checked_out'));
		$context = "$this->option.edit.$this->context";
		$task = $this->getTask();

		// Determine the name of the primary key for the data.
		if (empty($key))
		{
			$key = $table->getKeyName();
		}

		// To avoid data collisions the urlVar may be different from the primary key.
		if (empty($urlVar))
		{
			$urlVar = $key;
		}

		$recordId = $this->input->getInt($urlVar);

		// Populate the row id from the session.
		$data[$key] = $recordId;

		// Access check.
		if (!$this->allowSave($data, $key))
		{
            $app->enqueueMessage(Text::sprintf('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED', $model->getError()));
			$this->setRedirect(
				Route::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_list
					. $this->getRedirectToListAppend(), false
				)
			);

			return false;
		}


		// Validate the posted data.
		// Sometimes the form needs some posted data, such as for plugins and modules.
		$form = $model->getForm($data, false);

		if (!$form)
		{
			$app->enqueueMessage($model->getError(), 'error');

			return false;
		}

		// Send an object which can be modified through the plugin event
		$objData = (object) $data;
		$data = (array) $objData;

		// Test whether the data is valid.
		$validData = $model->validate($form, $data);

		// Check for validation errors.
		if ($validData === false)
		{
			// Get the validation messages.
			$errors = $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof \Exception)
				{
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else
				{
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			/**
			 * We need the filtered value of calendar fields because the UTC normalision is
			 * done in the filter and on output. This would apply the Timezone offset on
			 * reload. We set the calendar values we save to the processed date.
			 */
			$filteredData = $form->filter($data);

			foreach ($form->getFieldset() as $field)
			{
				if ($field->type === 'Calendar')
				{
					$fieldName = $field->fieldname;

					if (isset($filteredData[$fieldName]))
					{
						$data[$fieldName] = $filteredData[$fieldName];
					}
				}
			}

			// Save the data in the session.
			$app->setUserState($context . '.data', $data);

			// Redirect back to the edit screen.
			$this->setRedirect(
				Route::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_item
					. $this->getRedirectToItemAppend($recordId, $urlVar), false
				)
			);

			return false;
		}



		// Attempt to save the data.
		if (!$model->save($validData))
		{
			// Save the data in the session.
			$app->setUserState($context . '.data', $validData);

			// Redirect back to the edit screen.
            $app->enqueueMessage(Text::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
			$this->setRedirect(
				Route::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_item
					. $this->getRedirectToItemAppend($recordId, $urlVar), false
				)
			);

			return false;
		}

		$langKey = $this->text_prefix . ($recordId === 0 && $app->isClient('site') ? '_SUBMIT' : '') . '_SAVE_SUCCESS';
		$prefix  = Factory::getLanguage()->hasKey($langKey) ? $this->text_prefix : 'JLIB_APPLICATION';

		$this->setMessage(Text::_($prefix . ($recordId === 0 && $app->isClient('site') ? '_SUBMIT' : '') . '_SAVE_SUCCESS'));


		// Set the record data in the session.
		$recordId = $model->getState($this->context . '.id');
		$this->holdEditId($context, $recordId);
		$app->setUserState($context . '.data', null);

		$reminderObject = $model->getItem($recordId);

        // for modals
        if(Factory::getApplication()->input->get('tmpl') == 'component'){

            $reminderItem = LayoutHelper::render(
                'reminders.item',
                ['reminder' => $reminderObject],
                JPATH_ADMINISTRATOR . '/components/com_joomproject/layouts/');

            $this->closeModal($reminderItem,$recordId);

            return true;
        }

        // for edit task
        switch ($task)
        {
            case 'apply':
                // Set the record data in the session.
                $recordId = $model->getState($this->context . '.id');
                $this->holdEditId($context, $recordId);
                $app->setUserState($context . '.data', null);
                $model->checkout($recordId);

                // Redirect back to the edit screen.
                $this->setRedirect(
                    Route::_(
                        'index.php?option=' . $this->option . '&view=' . $this->view_item
                        . $this->getRedirectToItemAppend($recordId, $urlVar), false
                    )
                );
                break;

            default:
                // Clear the record id and data from the session.
                $this->releaseEditId($context, $recordId);
                $app->setUserState($context . '.data', null);

                $url = 'index.php?option=' . $this->option . '&view=' . $this->view_list
                    . $this->getRedirectToListAppend();

                // Check if there is a return value
                $return = $this->input->get('return', null, 'base64');

                if (!is_null($return) && Uri::isInternal(base64_decode($return)))
                {
                    $url = base64_decode($return);
                }

                // Redirect to the list screen.
                $this->setRedirect(Route::_($url, false));
                break;
        }

        return true;

	}

	public function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
	{

		$append = parent::getRedirectToItemAppend($recordId, $urlVar); // TODO: Change the autogenerated stub
		$jinput = Factory::getApplication()->input;

		if ($jinput->get('tmpl', '', 'WORD') == 'component')
		{
			$append .= '&tmpl=component&task_id=' . $jinput->get('taskid', 0, 'INT');
		}

		return $append;
	}

	/*
	 * This method close modal, and add the new reminder to reminders list in task edit form -> tab reminders
	 */
	protected function closeModal($reminderItem,$reminderId)
	{
		echo '<div style="display:none">';
		echo $reminderItem;
		echo '</div>';

		echo '<script>

			  // close modal
              parent.document.querySelector(".iziModal-button-close").click();
              
              // inits
              let newReminderId = "#reminder-'.$reminderId.'"; // new reminder id class
              let newReminder = document.querySelector(newReminderId); // get new reminder block
              let existingReminder = parent.document.querySelector(newReminderId); // get existing reminder block
              let remindersList = parent.document.querySelector(".remindersList > .row"); // get reminders list block
              
              // if reminder item already exist in replace it with new updated one
              if(remindersList.contains(existingReminder)){              
                  remindersList.replaceChild(newReminder,existingReminder);
              }else{
                  // move new added reminder to reminders list        
              remindersList.appendChild(newReminder);
              }             
              
              
		    </script>
		';
		exit;
	}

	// Delete Reminder
	public function deleteReminder()
	{
		$model = $this->getModel('reminder');
		$model->deleteReminder();
	}

    public function getReminderTask($id){

        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select('*')
            ->from('#__jp_tasks')
            ->where('id = ' . $id);

        $db->setQuery($query);

        return $db->loadObject();

    }


}

