<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jpprojects
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

jimport('joomla.application.component.controllerform');


/**
 * Joomproject Project Form Controller
 *
 */
class JPprojectsControllerForm extends FormController
{
	/**
	 * Default item view
	 *
	 * @var    string
	 */
	protected $view_item = 'form';

	/**
	 * Default list view
	 *
	 * @var    string
	 */
	protected $view_list = 'projects';


	/**
	 * Constructor
	 *
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		// Register additional tasks
		$this->registerTask('save2milestone', 'save');
		$this->registerTask('save2tasklist', 'save');
		$this->registerTask('save2task', 'save');
	}


	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param string $name   The model name. Optional.
	 * @param string $prefix The class prefix. Optional.
	 * @param array  $config Configuration array for model. Optional.
	 *
	 * @return    object               The model.
	 */
	public function &getModel($name = 'Form', $prefix = '', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}


	/**
	 * Method to add a new record.
	 *
	 * @return    boolean    True if the article can be added, false if not.
	 */
	public function add()
	{
		if (!parent::add())
		{
			// Redirect to the return page.
			$this->setRedirect($this->getReturnPage());
		}
	}


	/**
	 * Method to cancel an edit.
	 *
	 * @param string $key The name of the primary key of the URL variable.
	 *
	 * @return  boolean  True if access level checks pass, false otherwise.
	 */
	public function cancel($key = 'a_id')
	{
		parent::cancel($key);

		// Redirect to the return page.
		$this->setRedirect(Route::_($this->getReturnPage()));
	}


	/**
	 * Method to save a record.
	 *
	 * @param string $key    The name of the primary key of the URL variable.
	 * @param string $urlVar The name of the URL variable if different from the primary key.
	 *
	 * @return    boolean               True if successful, false otherwise.
	 */
	public function save($key = null, $urlVar = null)
	{

		$data = Factory::getApplication()->input->post->get('jform', array(), 'array');

		$model = $this->getModel();
		$table = $model->getTable();

		$task = $this->getTask();

        $projectId = $this->input->getInt('id');


        // if user not allowed to change permissions unset it
        if(!JoomprojectHelperAccess::canChangePermissions($projectId,JPprojectsHelperProject::getInfo($projectId)))
            unset($data['rules']);


		// Separate the different component rules before passing on the data
		if (isset($data['rules']))
		{
			$rules = $data['rules'];

			if (isset($data['rules']['com_jpprojects']))
			{
				$data['rules'] = $data['rules']['com_jpprojects'];

				unset($rules['com_jpprojects']);
			}

			$data['component_rules'] = $rules;
		}



		// Reset the repo dir when saving as copy
		if ($task == 'save2copy')
		{
			// Reset the repo dir when saving as copy
			if (isset($data['attribs']['repo_dir']))
			{
				$dir = (int) $data['attribs']['repo_dir'];

				if ($dir)
				{
					$data['attribs']['repo_dir'] = 0;
				}
			}

			// Reset label id's
			if (isset($data['labels']) && is_array($data['labels']))
			{
				foreach ($data['labels'] as $a => $g)
				{
					if (isset($g['id']))
					{
						foreach ($g['id'] as $k => $i)
						{
							$data['labels'][$a]['id'][$k] = 0;
						}
					}
				}
			}

			// Store the current project id in session
			$recordId = Factory::getApplication()->input->getUInt('id');

			if ($recordId)
			{
				// Store the current project id in session
				$context = "$this->option.copy.$this->context.id";
				$app     = Factory::getApplication();

				$app->setUserState($context, intval($recordId));

				$cfg          = ComponentHelper::getParams('com_jpprojects');
				$create_group = (int) $cfg->get('create_group');

				if ($create_group)
				{
					// Get the project attribs
					$db    = Factory::getDbo();
					$query = $db->getQuery(true);

					$query->select('attribs')
						->from('#__jp_projects')
						->where('id = ' . (int) $recordId);

					$db->setQuery($query, 0, 1);
					$attribs = $db->loadResult();

					// Turn to JRegistry object
					$params = new Registry();
					$params->loadString( (string) $attribs);

					// Get custom user group
					$group_id = (int) $params->get('usergroup');

					// Replicate existing custom group settings
					if ($group_id)
					{
						// Copy component rules
						if (isset($data['component_rules']))
						{
							$user     = Factory::getApplication()->getIdentity();
							$is_admin = $user->authorise('core.admin');

							foreach ($data['component_rules'] as $component => $rules)
							{
								foreach ($rules as $action => $groups)
								{
									if (!is_numeric($action) && is_array($groups))
									{
										foreach ($groups as $gid => $v)
										{
											if ($gid == $group_id)
											{
												if (!$is_admin && $action == 'core.admin')
												{
													// Dont allow non-admins to inject core admin permission
													unset($data['component_rules'][$component][$action]);
												}
												else
												{
													unset($data['component_rules'][$component][$action][$gid]);
													$data['component_rules'][$component][$action][0] = $v;
												}
											}
										}
									}
								}
							}
						}

						// Copy item rules
						if (isset($data['rules']))
						{
							foreach ($data['rules'] as $action => $value)
							{
								if (is_numeric($action))
								{
									if ($value == $group_id)
									{
										$data['rules'][$action] = 0;
									}
								}
								else
								{
									foreach ($value as $k => $v)
									{
										if ($k == $group_id)
										{
											unset($data['rules'][$action][$k]);
											$data['rules'][$action][0] = $v;
										}
									}
								}
							}
						}

						// Copy group members
						$query->clear();
						$query->select('user_id')
							->from('#__user_usergroup_map')
							->where('group_id = ' . (int) $group_id);

						$db->setQuery($query);
						$add_users = (array) $db->loadColumn();

						$add_append = "";

						if (!isset($data['add_groupuser']))
						{
							$data['add_groupuser'] = array();
						}

						if (isset($data['add_groupuser'][$group_id]))
						{
							$add_append = $data['add_groupuser'][$group_id];

							unset($data['add_groupuser'][$group_id]);
						}

						$data['add_groupuser'][0] = implode(',', $add_users) . ($add_append == '' ? '' : ',' . $add_append);
					}
				}
			}
		}

		$this->input->post->set('jform', $data);

		return parent::save($key, $urlVar);
	}


	/**
	 * Method to check if you can add a new record.
	 *
	 * @param array $data An array of input data.
	 *
	 * @return    boolean
	 */
	protected function allowAdd($data = array())
	{
		return Factory::getApplication()->getIdentity()->authorise('core.create', 'com_jpprojects');
	}


	/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param array  $data An array of input data.
	 * @param string $key  The name of the key for the primary key.
	 *
	 * @return    boolean
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		// Get form input
		$id = (int) isset($data[$key]) ? $data[$key] : 0;

		$user  = Factory::getApplication()->getIdentity();
		$uid   = $user->get('id');
		$asset = 'com_jpprojects.project.' . $id;

		//todo: to be checked this condition
        // Check if the user has viewing access when not a super admin
		if (!$user->authorise('core.admin'))
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);

			$query->select('access')
				->from('#__jp_projects')
				->where('id = ' . $id);

			$db->setQuery($query);
			$lvl = $db->loadResult();

			if (!in_array($lvl, $user->getAuthorisedViewLevels()))
			{
				return false;
			}
		}

		// Check edit permission first
		if ($user->authorise('core.edit', $asset))
		{
			return true;
		}

		// Fall back on edit.own.
		// First test if the permission is available.
		if (!$user->authorise('core.edit.own', $asset))
		{
			return false;
		}

		// Now test the owner is the user.
		$owner = (int) isset($data['created_by']) ? (int) $data['created_by'] : 0;

		if (!$owner && $id)
		{
			// Need to do a lookup from the model.
			$record = $this->getModel()->getItem($id);

			if (empty($record)) return false;

			$owner = $record->created_by;
		}

		// If the owner matches 'me' then do the test.
		return ($owner == $uid && $uid > 0);
	}


	/**
	 * Gets the URL arguments to append to an item redirect.
	 *
	 * @param int    $id      The primary key id for the item.
	 * @param string $url_var The name of the URL variable for the id.
	 *
	 * @return    string                The arguments to append to the redirect URL.
	 */
	protected function getRedirectToItemAppend($id = null, $url_var = 'id')
	{
		// Need to override the parent method completely.
		$tmpl    = Factory::getApplication()->input->getCmd('tmpl');
		$layout  = Factory::getApplication()->input->getCmd('layout', 'edit');
		$item_id = Factory::getApplication()->input->getUInt('Itemid');
		$return  = $this->getReturnPage();
		$append  = '';

		// Setup redirect info.
		if ($tmpl) $append .= '&tmpl=' . $tmpl;

		$append .= '&layout=edit';
		if ($id) $append .= '&' . $url_var . '=' . $id;
		if ($item_id) $append .= '&Itemid=' . $item_id;
		if ($return) $append .= '&return=' . base64_encode($return);

		return $append;
	}


	/**
	 * Get the return URL.
	 * If a "return" variable has been passed in the request
	 *
	 * @return    string    The return URL.
	 */
	protected function getReturnPage()
	{
		$return = Factory::getApplication()->input->get('return', null, 'base64');

		if (empty($return) || !Uri::isInternal(base64_decode($return)))
		{
			return Route::_(JPprojectsHelperRoute::getProjectsRoute(), false);
		}
		else
		{
			return base64_decode($return);
		}
	}


	/**
	 * Function that allows child controller access to model data after the data has been saved.
	 *
	 * @param jmodel $model The data model object.
	 * @param array  $data  The validated data.
	 *
	 * @return    void
	 */
	protected function postSaveHook(BaseDatabaseModel $model, $data = array())
	{
		$task = $this->getTask();

		switch ($task)
		{
			case 'save2copy':
			case 'save2new':
				// No redirect because its already set
				break;

			case 'save2milestone':
				$link = Route::_(JPmilestonesHelperRoute::getMilestonesRoute() . '&task=form.add');
				$this->setRedirect($link);
				break;

			case 'save2tasklist':
				$link = Route::_(JPtasksHelperRoute::getTasksRoute() . '&task=tasklistform.add');
				$this->setRedirect($link);
				break;

			case 'save2task':
				$link = Route::_(JPtasksHelperRoute::getTasksRoute() . '&task=taskform.add');
				$this->setRedirect($link);
				break;

			default:
				$this->setRedirect(Route::_($this->getReturnPage()));
				break;
		}
	}
}
