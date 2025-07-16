<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jpprojects
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */


defined('_JEXEC') or die();

use Joomla\CMS\Date\Date;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Object\CMSObject;


jimport('joomla.application.component.modeladmin');
jimport('joomproject.application.helper');
jimport('joomproject.access.helper');

if (JPApplicationHelper::exists('com_jprepo')) {
    JLoader::register('JPrepoHelper', JPATH_ADMINISTRATOR . '/components/com_jprepo/helpers/jprepo.php');
}

/**
 * Item Model for a Project form.
 *
 */
class JPprojectsModelProject extends AdminModel
{
    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     */
    protected $text_prefix = 'COM_JOOMPROJECT_PROJECT';

    /**
     * Constructor.
     *
     * @param array $config An optional associative array of configuration settings.
     *
     * @see      jcontroller
     */
    public function __construct($config = array())
    {
        parent::__construct($config);

        $this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_jpprojects/tables');
        //$this->addTablePath(JPATH_ADMINISTRATOR.'/components/com_jprepo/tables');
        //BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_jprepo/models');

    }


    /**
     * Returns a Table object, always creating it.
     *
     * @param string    The table type to instantiate
     * @param string    A prefix for the table class name. Optional.
     * @param array     Configuration array for model. Optional.
     *
     * @return    jtable    A database object
     */
    public function getTable($type = 'Project', $prefix = 'JPtable', $config = array())
    {
        return Table::getInstance($type, $prefix, $config);
    }


    /*
     * We override batch copy cuz we use it differently, we should
     */
    protected function batchCopy($value, $pks, $contexts)
    {

        // Initialize re-usable member properties, and re-usable local variables
        $this->initBatch();

        $categoryId = $value;

        if (!$this->checkCategoryId($categoryId)) {
            return false;
        }

        foreach ($pks as $pk) {

            // get item
            $item = $this->getItem($pk);

            // attachments list
            $attachments = array_map(function($att) {
                return $att->attachment ?? '';
            }, $item->attachment);


            // Handle labels with correct grouping
            $labels = [];
            $seenGroups = [];

            foreach ($item->labels as $index => $label) {
                $group = $label->asset_group;

                if (!isset($labels[$group])) {
                    // If this is a new group, add any pending numeric keys
                    while (count($seenGroups) < $index) {
                        $labels[] = '';
                        $seenGroups[] = count($seenGroups);
                    }

                    $labels[$group] = [
                        'title' => [],
                        'style' => [],
                        'id' => []
                    ];
                    $seenGroups[] = $group;
                }

                $labels[$group]['title'][] = $label->title;
                $labels[$group]['style'][] = $label->style;
                $labels[$group]['id'][] = (string)$label->id;  // Convert to string to match the example
            }


            $batchData = Factory::getApplication()->getInput()->get('batch', array(), 'ARRAY');


            // Shift date
            $oldStartDate = $item->start_date;
            $oldEndDate = $item->end_date;
            $newStartDate = empty($batchData['start_date']) ? $item->start_date : $batchData['start_date'];
            $shiftedDates = $this->shiftDates($oldStartDate, $oldEndDate, $newStartDate);

            // new category
            $newCategory = $categoryId ?: $item->catid;


            // build data
            $data = [
                'id'=> 0,
                'title' => $item->title,
                'description' => $item->description,
                'state' => 0,
                'catid' => $newCategory,
                'created_by' => $item->created_by,
                'created' => $item->created,
                'access' => $item->access,
                'start_date' => $shiftedDates['start_date'],
                'end_date' => $shiftedDates['end_date'],
                'modified' => $item->modified,
                'attachment' => $attachments,
                'attribs' => $item->params->toArray(),
                'labels' => $labels

            ];

            JPprojectsHelper::adjustDataForSaveToCopy($data,$pk);

            // save item as save2copy
            $this->save($data);

        }


        return [];

    }

    public function shiftDates($oldStartDate, $oldEndDate, $newStartDate)
    {
        // Convert dates to Joomla Date objects
        $oldStart = new Date($oldStartDate);
        $newStart = new Date($newStartDate);

        // Calculate the number of days shifted
        $daysShifted = $oldStart->diff($newStart)->days;

        // Store the shift information in the session
        $session = Factory::getSession();
        $session->set('batch_date_shift_days', $daysShifted);

        // If oldEndDate is empty, return only the new start date
        if (empty($oldEndDate)) {
            return [
                'start_date' => $newStart->toSql(),
                'end_date' => null,
                'days_shifted' => $daysShifted
            ];
        }

        $oldEnd = new Date($oldEndDate);

        // Calculate the interval between old start and end dates
        $interval = $oldStart->diff($oldEnd);

        // Add this interval to the new start date to get the new end date
        $newEnd = clone $newStart;
        $newEnd->add($interval);

        return [
            'start_date' => $newStart->toSql(),
            'end_date' => $newEnd->toSql(),
            'days_shifted' => $daysShifted
        ];
    }


    /**
     * Method to get a single record.
     *
     * @param integer $pk The id of the primary key.
     *
     * @return    mixed   $item   Object on success, false on failure.
     */
    public function getItem($pk = null)
    {
        $pk = (!empty($pk)) ? (int)$pk : (int)$this->getState($this->getName() . '.id');
        $table = $this->getTable();

        if ($pk > 0) {
            // Attempt to load the row.
            $return = $table->load($pk);

            // Check for a table object error.
            if ($return === false && $table->getError()) {
                $this->setError($table->getError());
                return false;
            }
        }
        // Convert to the JObject before adding other data.
        $properties = $table->getProperties(1);
        $item = ArrayHelper::toObject($properties, CMSObject::class);

        // Convert attributes to JRegistry params
        $item->params = new Registry();

        $item->params->loadString((string)$item->attribs);
        $item->attribs = $item->params->toArray();

        // Get the attachments
        $item->attachment = array();

        if (JPApplicationHelper::exists('com_jprepo')) {
            $attachments = $this->getInstance('Attachments', 'JPrepoModel');
            $item->attachment = $attachments->getItems('com_jpprojects.project', $item->id);
        }

        // Get the labels
        $model_labels = $this->getInstance('Labels', 'JPModel');
        $item->labels = $model_labels->getItems($item->id);

        return $item;
    }


    /**
     * Method to get the user groups assigned to a project
     *
     * @param integer    The project id
     *
     * @return    array      The user groups
     **/
    public function getUserGroups($pk = NULL)
    {
        $pk = (!empty($pk)) ? $pk : (int)$this->getState($this->getName() . '.id');
        $table = $this->getTable();

        if ($pk > 0) {
            // Attempt to load the row.
            $return = $table->load($pk);

            // Check for a table object error.
            if ($return === false && $table->getError()) {
                $this->setError($table->getError());
                return false;
            }

            return JPAccessHelper::getGroupsByAccessLevel($table->access);
        }

        return false;
    }


    /**
     * Method to delete a project logo
     *
     * @param integer    The project id
     *
     * @return    boolean    True on success, False on error
     **/
    public function deleteLogo($pk = NULL)
    {
        $pk = (!empty($pk)) ? (int)$pk : (int)$this->getState($this->getName() . '.id');


        $base_path = JPATH_ROOT . '/media/com_joomproject/repo/0/logo';
        $img_path = NULL;

        if (File::exists($base_path . '/' . $pk . '.jpg')) {
            $img_path = $base_path . '/' . $pk . '.jpg';
        } elseif (File::exists($base_path . '/' . $pk . '.jpeg')) {
            $img_path = $base_path . '/' . $pk . '.jpeg';
        } elseif (File::exists($base_path . '/' . $pk . '.png')) {
            $img_path = $base_path . '/' . $pk . '.png';
        } elseif (File::exists($base_path . '/' . $pk . '.gif')) {
            $img_path = $base_path . '/' . $pk . '.gif';
        }

        // No image found
        if (!$img_path) {
            return true;
        }

        if (!File::delete($img_path)) {
            return false;
        }

        return true;
    }


    public function saveLogo($file = NULL, $pk = NULL)
    {


        $pk = (!empty($pk)) ? (int)$pk : (int)$this->getState($this->getName() . '.id');

        if (empty($file)) {
            $file_form = Factory::getApplication()->input->files->get('jform', [], 'array');


            if (is_array($file_form)) {
                if (isset($file_form['attribs']['logo'])) {
                    if ($file_form['attribs']['logo']['name'] == '') {
                        return true;
                    }

                    $file = array();

                    $file['name'] = $file_form['attribs']['logo']['name'];
                    $file['type'] = $file_form['attribs']['logo']['type'];
                    $file['tmp_name'] = $file_form['attribs']['logo']['tmp_name'];
                    $file['error'] = $file_form['attribs']['logo']['error'];
                    $file['size'] = $file_form['attribs']['logo']['size'];

                    if ($file['error']) {
                        if (JPApplicationHelper::exists('com_jprepo')) {
                            $error = JPrepoHelper::getFileErrorMsg($file['error'], $file['name']);
                            $this->setError($error);
                        }

                        return false;
                    }
                }
            }

            if (empty($file)) {
                return true;
            }
        }

        if (!$pk) {
            return false;
        }

        if (empty($file)) {
            $this->setError(Text::_('COM_JOOMPROJECT_WARNING_NO_FILE_SELECTED'));
            return false;
        }

        if (!JPImage::isValid($file['name'], $file['tmp_name'])) {
            $this->setError(Text::_('COM_JOOMPROJECT_WARNING_NOT_AN_IMAGE'));
            return false;
        }

        // Delete any previous logo
        if (!$this->deleteLogo($pk)) {
            return false;
        }

        $uploadpath = JPATH_ROOT . '/media/com_joomproject/repo/0/logo';
        $name = $pk . '.' . strtolower(File::getExt($file['name']));

        if (File::upload($file['tmp_name'], $uploadpath . '/' . $name) === true) {
            return true;
        }

        return false;
    }


    /**
     * Method to get the record form.
     *
     * @param array      Data for the form.
     * @param boolean    True if the form is to load its own data (default case), false if not.
     *
     * @return    mixed      A JForm object on success, false on failure
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_jpprojects.project', 'project', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) return false;

        // inits
        $cparams = ComponentHelper::getParams('com_jpprojects');
        $jinput = Factory::getApplication()->input;
        $user = Factory::getApplication()->getIdentity();
        $id = (int)$jinput->get('id', 0);



        // Check for existing item.
        // Modify the form based on Edit State access controls.
        if ($id != 0 && (!$user->authorise('core.edit.state', 'com_jpprojects.project.' . $id)) || ($id == 0 && !$user->authorise('core.edit.state', 'com_jpprojects'))) {
            // Disable fields for display.
            $form->setFieldAttribute('state', 'disabled', 'true');
            $form->setFieldAttribute('start_date', 'disabled', 'true');
            $form->setFieldAttribute('end_date', 'disabled', 'true');

            // Disable fields while saving.
            $form->setFieldAttribute('state', 'filter', 'unset');
            $form->setFieldAttribute('start_date', 'filter', 'unset');
            $form->setFieldAttribute('end_date', 'filter', 'unset');
        }

        // Always disable these fields while saving
        $form->setFieldAttribute('alias', 'filter', 'unset');

        // Disable these fields if not an admin or owner with permitted permission to change access and permissions

        $canChangePermission = JoomprojectHelperAccess::canChangePermissions(null, $this->getItem($id));

		// disable rules and access if user can't change permission
        if (!$canChangePermission) {

            $form->setFieldAttribute('access', 'disabled', 'true');
            $form->setFieldAttribute('access', 'filter', 'unset');

            $form->setFieldAttribute('rules', 'disabled', 'true');
            $form->setFieldAttribute('rules', 'filter', 'unset');
        }

	    // Set the project as active when editing
        if ($id) {
            JPApplicationHelper::setActiveProject($id);
		}

        // remove gallery field if not enabled from config
        if(!$cparams->get('enable_project_gallery',0)){
            $form->removeGroup('images_gallery');
            $form->removeField('gallery','images_gallery');
        }

        return $form;
    }


    /**
     * Method to save the form data.
     *
     * @param array      The form data
     *
     * @return    boolean    True on success
     */
    public function save($data)
    {

        $dispatcher = Factory::getApplication();
        $user = Factory::getApplication()->getIdentity();
        $table = $this->getTable();
        $key = $table->getKeyName();
        $pk = (!empty($data[$key])) ? $data[$key] : (int)$this->getState($this->getName() . '.id');
        $is_new = true;
        $old = null;
        $context = $this->option . '.' . $this->name;

        // save original rules to use it after save
        $rules = isset($data['component_rules']) ? $data['component_rules'] : [];
        $rules['com_jpprojects'] = isset($data['rules']) ? $data['rules'] : [];

        // Include the content plugins for the on save events.
        PluginHelper::importPlugin($this->events_map['save']);

        $cfg = ComponentHelper::getParams('com_jpprojects');
        $create_group = (int)$cfg->get('create_group');
        $group_location = (int)$cfg->get('group_location');
        $group_id = 0;

        if (!$group_location) $group_location = 1;

        // Allow an exception to be thrown.
        try {
            // Load the row if saving an existing record.
            if ($pk > 0) {
                if ($table->load($pk)) {
                    $is_new = false;
                    $old = clone $table;
                }
            }

            // Make sure the title and alias are always unique
            $data['alias'] = '';
            list($title, $alias) = $this->generateNewTitle($data['title'], $data['alias'], $pk);

            $data['title'] = $title;
            $data['alias'] = $alias;

            // Create new user group?
            if ($is_new && $create_group) {
                $group_users = array(Factory::getApplication()->getIdentity()->get('id'));
                $group_id = $this->createUserGroup($data['title'], $group_location, $group_users);

                if ($group_id) {
                    if (!isset($data['attribs'])) $data['attribs'] = array();
                    $data['attribs']['usergroup'] = $group_id;

                    // Inject non-existant group if no rules are set
                    if (!isset($data['rules'])) {
                        $data['rules'] = array(0 => 0);
                    }
                }
            }

            // Inject group into component rules
            $is_admin = $user->authorise('core.admin');

            if (isset($data['component_rules']) && $is_new && $create_group) {
                foreach ($data['component_rules'] as $component => $rules) {
                    foreach ($rules as $action => $groups) {
                        if (!is_numeric($action) && is_array($groups)) {
                            foreach ($groups as $gid => $v) {
                                if ($gid == 0) {
                                    if (!$is_admin && $action == 'core.admin') {
                                        // Dont allow non-admins to inject core admin permission
                                        unset($data['component_rules'][$component][$action]);
                                    } else {
                                        if ($group_id) {
                                            unset($data['component_rules'][$component][$action][$gid]);
                                            $data['component_rules'][$component][$action][$group_id] = $v;
                                        } else {
                                            unset($data['component_rules'][$component][$action][$gid]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // Inject group into "add_groupuser" field
            if (isset($data['add_groupuser']) && $is_new && $create_group) {
                if (isset($data['add_groupuser'][0])) {
                    $data['add_groupuser'][$group_id] = $data['add_groupuser'][0];
                    unset($data['add_groupuser'][0]);
                }
            }

            // Add users to groups
            if (isset($data['add_groupuser'])) {
                $this->addGroupUsers($data['add_groupuser']);
            }

            // Remove users from groups
            if (isset($data['rm_groupuser'])) {
                $this->removeGroupUsers($data['rm_groupuser']);
            }

            // Rename project group
            if (!$is_new && $create_group) {
                $reg = new Registry();
                $reg->loadString((string)$table->attribs);

                $group_id = (int)$reg->get('usergroup');

                if ($group_id) {
                    // Re-inject the group and other attribs
                    if (!isset($data['attribs'])) {
                        $data['attribs'] = $reg->toArray();
                    } else {
                        $data['attribs']['usergroup'] = $group_id;
                    }

                    // Rename
                    $this->renameUserGroup($group_id, $data['title']);
                }
            }


            // Handle permissions and access level of project
            if (isset($data['rules'])) {
                // Inject newly created group
                if ($is_new && $create_group) {
                    foreach ($data['rules'] as $action => $groups) {
                        if (is_numeric($action) && is_numeric($groups) && $groups == 0) {
                            unset($data['rules'][$action]);

                            if ($group_id) {
                                $data['rules'][$action] = $group_id;
                            }
                        }

                        if (!is_numeric($action) && is_array($groups)) {
                            foreach ($groups as $gid => $v) {
                                if ($gid == 0) {
                                    if ($group_id) {
                                        unset($data['rules'][$action][$gid]);
                                        $data['rules'][$action][$group_id] = $v;
                                    } else {
                                        unset($data['rules'][$action][$gid]);
                                    }
                                }
                            }
                        }
                    }
                }

                $prev_access = ($is_new ? 0 : $table->access);
                $access = JPAccessHelper::getViewLevelFromRules($data['rules'], $prev_access);

                if ($access) {
                    $data['access'] = $access;

                    // If we created a new group, we must inject the new access level
                    if ($create_group) {
                        $user = Factory::getApplication()->getIdentity();
                        $levels = $user->getAuthorisedViewLevels();

                        if (!in_array($access, $user->getAuthorisedViewLevels())) {
                            $levels[] = (int)$access;
                            $user->set('_authLevels', $levels);
                        }
                    }
                }
            } else {
                if ($is_new) {
                    $data['access'] = (int)Factory::getConfig()->get('access');
                } else {
                    if (isset($data['access'])) {
                        unset($data['access']);
                    }
                }
            }


            // Delete logo?
            if (isset($data['attribs']['logo']['delete']) && $pk && !$is_new) {
                $this->deleteLogo($pk);
            }

            // Make item published by default if new
            if (!isset($data['state']) && $is_new) {
                $data['state'] = 1;
            }


            // Bind the data.
            if (!$table->bind($data)) {
                return false;
            }

            // Prepare the row for saving
            $this->prepareTable($table);

            // Check the data.
            if (!$table->check()) {
                $this->setError($table->getError());
                return false;
            }

            // Trigger the before save event.
            $result = $dispatcher->triggerEvent($this->event_before_save, array($context, &$table, $is_new, $data));

            if (in_array(false, $result, true)) {
                $this->setError($table->getError());

                return false;
            }

            // Store the data.
            if (!$table->store()) {

                $this->setError($table->getError());
                return false;
            }

            // Clean the cache.
            $this->cleanCache();

            // Trigger the after save event.
            $dispatcher->triggerEvent($this->event_after_save, array($context, $table, $is_new, $data));

            $pk_name = $table->getKeyName();

            if (isset($table->$pk_name)) {
                $this->setState($this->getName() . '.id', $table->$pk_name);
            }

            $this->setState($this->getName() . '.new', $is_new);

            $id = $this->getState($this->getName() . '.id');

            $this->setActive(array('id' => $id));

            // add groups with view action
            JPUserHelper::updateGroupsViewAction('project', $rules, $id);

            // Add to watch list - if not opt-out
            if ($is_new) {
                $plugin = PluginHelper::getPlugin('content', 'jpnotifications');
                $params = new Registry($plugin->params);
                $opt_out = (int)$params->get('sub_method', 0);

                if (!$opt_out) {
                    $cid = array($id);

                    if (!$this->watch($cid, 1)) {
                        return false;
                    }
                }
            }

            // Create repo base and attachments folder
            if (JPApplicationHelper::exists('com_jprepo')) {
                if (!$this->createRepository($table)) {
                    return false;
                }

                // Store the attachments
                if (isset($data['attachment']) && !$is_new) {
                    $attachments = $this->getInstance('Attachments', 'JPrepoModel', array('ignore_request' => true));

                    $attachments->setState('item.type', 'com_jpprojects.project');
                    $attachments->setState('item.id', $id);
                    $attachments->setState('item.project', $id);

                    if (!$attachments->save($data['attachment'])) {
                        $this->setError($attachments->getError());
                        return false;
                    }
                }
            }

            // Store the labels
            if (isset($data['labels'])) {
                $labels = $this->getInstance('Labels', 'JPModel', array('ignore_request' => true));
                $lbl_project = (int)$labels->getState('item.project');

                $labels->setState('item.project', $id);
                $labels->setState('item.id', $id);

                if (!$labels->save($data['labels'])) {
                    /*$this->setError($labels->getError());
                    return false;*/
                }
            }

            // Handle project logo
            if (!$this->saveLogo()) {
                return false;
            }

        } catch (Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }

        return true;
    }


    /**
     * Method to watch an item
     *
     * @param array $pks The items to watch
     * @param integer $value 1 to watch, 0 to unwatch
     * @param integer $uid The user id to watch the item
     */
    public function watch(&$pks, $value = 1, $uid = null)
    {
        $user = Factory::getUser($uid);
        $table = $this->getTable();
        $pks = (array)$pks;

        $is_admin = $user->authorise('core.admin', $this->option);
        $levels = $user->getAuthorisedViewLevels();
        $projects = array();

        $item_type = 'com_jpprojects.project';

        // Access checks.
        foreach ($pks as $i => $pk) {
            $table->reset();

            if ($table->load($pk)) {
                if (!$is_admin && !in_array($table->access, $levels)) {
                    unset($pks[$i]);
                    Factory::getApplication()->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'warning');
                    $this->setError(Text::_('JERROR_ALERTNOAUTHOR'));
                    return false;
                }

                $projects[$pk] = (int)$table->id;
            } else {
                unset($pks[$i]);
            }
        }

        // Attempt to watch/unwatch the selected items
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        foreach ($pks as $i => $pk) {
            $query->clear();

            if ($value == 0) {
                $query->delete('#__jp_ref_observer')
                    ->where('item_type = ' . $db->quote($item_type))
                    ->where('item_id = ' . $db->quote((int)$pk))
                    ->where('user_id = ' . $db->quote((int)$user->get('id')));

                $db->setQuery($query);


                try {
                    $db->execute();
                } catch (RuntimeException $e) {
                    $this->setError($e->getMessage());
                    return false;
                }
            } else {
                $query->select('COUNT(*)')
                    ->from('#__jp_ref_observer')
                    ->where('item_type = ' . $db->quote($item_type))
                    ->where('item_id = ' . $db->quote((int)$pk))
                    ->where('user_id = ' . $db->quote((int)$user->get('id')));

                $db->setQuery($query);
                $count = (int)$db->loadResult();

                if (!$count) {
                    $data = new stdClass;

                    $data->user_id = (int)$user->get('id');
                    $data->item_type = $item_type;
                    $data->item_id = (int)$pk;
                    $data->project_id = (int)$projects[$pk];


                    try {
                        $db->insertObject('#__jp_ref_observer', $data);
                    } catch (RuntimeException $e) {
                        $this->setError($e->getMessage());
                        return false;
                    }
                }
            }
        }

        // Clear the component's cache
        $this->cleanCache();

        return true;
    }


    /**
     * Method to set a project to active on the current user
     *
     * @param array      The form data
     *
     * @return    boolean    True on success
     */
    public function setActive($data)
    {
        if (!isset($data['id'])) {
            return false;
        }

        $app = Factory::getApplication();
        $id = (int)$data['id'];

        if ($id) {
            // Load the project and verify the access
            $user = Factory::getApplication()->getIdentity();
            $table = $this->getTable();

            if ($table->load($id) === false) {
                if ($table->getError()) {
                    $this->setError($table->getError());
                }

                return false;
            }

            if (!$user->authorise('core.admin')) {
                if (!in_array($table->access, $user->getAuthorisedViewLevels())) {
                    $this->setError(Text::_('COM_JOOMPROJECT_ERROR_PROJECT_ACCESS_DENIED'));
                    return false;
                }
            }

            $app->setUserState('com_joomproject.project.active.id', $id);
            $app->setUserState('com_joomproject.project.active.title', $table->title);
            $app->setUserState('com_joomproject.project.active.info', ['startDate' => $table->start_date, 'endDate' => $table->end_date]);

        } else {
            $app->setUserState('com_joomproject.project.active.info', []);
        }

        return true;
    }


    /**
     * Method to delete one or more records.
     *
     * @param array      An array of record primary keys.
     *
     * @return    boolean    True if successful, false if an error occurs.
     */
    public function delete(&$pks)
    {
        $pks = (array)$pks;
        $table = $this->getTable();
        $query = $this->_db->getQuery(true);

        $active_id = JPApplicationHelper::getActiveProjectId();
        $repo_exists = JPApplicationHelper::exists('com_jprepo');

        if ($repo_exists) {
            $base_path = JPrepoHelper::getBasePath();
        }

        // Include the content plugins for the on delete events.
        $dispatcher = Factory::getApplication();
        PluginHelper::importPlugin('content');

        // Iterate the items to delete each one.
        foreach ($pks as $i => $pk) {
            // Try to load from the db
            if ($table->load($pk) === false) {
                $this->setError($table->getError());
                return false;
            }

            // Check delete permission
            if (!$this->canDelete($table)) {
                unset($pks[$i]);

                $error = $this->getError();

                if ($error) {
                    Factory::getApplication()->enqueueMessage($error, 'warning');
                } else {
                    Factory::getApplication()->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'), 'warning');
                }

                return false;
            }

            // Trigger the onContentBeforeDelete event.
            $context = $this->option . '.' . $this->name;
            $result = $dispatcher->triggerEvent($this->event_before_delete, array($context, $table));

            if (in_array(false, $result, true)) {
                $this->setError($table->getError());
                return false;
            }


            // if enabled or table exist we should remove
            if ($repo_exists || JPApplicationHelper::dbTableExists('#__jp_repo_dirs')) {
                $params = new Registry;
                $params->loadString((string)$table->attribs);

                $repo_dir = (int)$params->get('repo_dir');

                $query->clear()
                    ->select('path')
                    ->from('#__jp_repo_dirs')
                    ->where('id = ' . $repo_dir);

                $this->_db->setQuery($query);
                $repo_path = $this->_db->loadResult();
            }

            // Delete the item
            if (!$table->delete($pk)) {
                $this->setError($table->getError());
                return false;
            }

            // delete group view actions
            JPUserHelper::deleteActions('project', $pk);

            // Delete the repo directory
            if ($repo_exists || JPApplicationHelper::dbTableExists('#__jp_repo_dirs')) {
                if ($repo_path && $repo_dir) {
                    // Delete repo 4.1
                    $repo = $base_path . '/' . $repo_path;
                    if (Folder::exists($repo) && $repo != $base_path) Folder::delete($repo);

                    // Delete repo 4.0
                    $repo = $base_path . '/' . $pk;
                    if (Folder::exists($repo)) Folder::delete($repo);

                    // Delete repo 3.0
                    $repo = $base_path . '/project_' . $pk;
                    if (Folder::exists($repo)) Folder::delete($repo);
                }
            }

            // Delete the logo
            $this->deleteLogo($pk);

            // Check if the currently active project is being deleted.
            // If so, clear it from the session
            if ($active_id == $pk) $this->setActive(array('id' => 0));

            // Trigger the onContentAfterDelete event.
            $dispatcher->triggerEvent($this->event_after_delete, array($context, $table));
        }

        // Clear the component's cache
        $this->cleanCache();

        return true;
    }


    /**
     * Custom clean the cache
     *
     */
    protected function cleanCache($group = null, $client_id = 0)
    {
        parent::cleanCache('com_jpprojects');
    }


    /**
     * Method to create a project repository
     *
     * @param object $item The project JTable object
     *
     * @return    boolean             True on success, otherwise False
     */
    protected function createRepository($item)
    {
        if (empty($item)) return false;

        // Get Repo dir model
        $suffix = (Factory::getApplication()->isClient('site') ? 'Form' : '');
        $config = array('ignore_request' => true);
        $dir_class = $this->getInstance('Directory' . $suffix, 'JPrepoModel', $config);

        // Indicate to the model that we possibly want to create a new repo
        $dir_class->setState('create_repo', true);

        // Load attributes into JRegistry
        $registry = new Registry;
        $registry->loadString((string)$item->attribs);

        // Get the repo dir pk
        $repo_dir = (int)$registry->get('repo_dir');

        // Check if the dir exists
        if ($repo_dir) {
            $record = $dir_class->getItem($repo_dir);

            if ($record === false || empty($record->id)) {
                // Record not found
                $repo_dir = 0;
            } else {
                // Record found, update it
                $data = array();
                $data['id'] = $record->id;
                $data['title'] = $item->title;
                $data['project_id'] = $item->id;
                $data['parent_id'] = $record->id;

                if (!$dir_class->save($data)) {
                    $this->setError($dir_class->getError());
                    return false;
                }

                return true;
            }
        }

        // Create repo dir
        $query = $this->_db->getQuery(true);
        $data = array();

        $data['id'] = 0;
        $data['protected'] = 1;
        $data['title'] = $item->title;
        $data['project_id'] = $item->id;
        $data['created'] = $item->created;
        $data['created_by'] = $item->created_by;
        $data['access'] = $item->access;
        $data['parent_id'] = 1;

        if (!$dir_class->save($data)) {
            $this->setError($dir_class->getError());
            return false;
        }

        $repo_dir = $dir_class->getState($dir_class->getName() . '.id');
        $registry->set('repo_dir', $repo_dir);

        if (!$repo_dir) return false;

        // Update the project attribs
        $query->clear();
        $query->update('#__jp_projects')
            ->set('attribs = ' . $this->_db->quote((string)$registry))
            ->where('id = ' . (int)$item->id);

        $this->_db->setQuery($query);

        try {
            $this->_db->execute();
        } catch (RuntimeException $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            return false;
        }


        return true;
    }


    /**
     * Method to change the title & alias.
     * Overloaded from JModelAdmin class
     *
     * @param string     The title
     * @param string     The alias
     * @param integer    The item id
     *
     * @return    array      Contains the modified title and alias
     */
    protected function generateNewTitle($title, $alias = '', $id = 0)
    {
        $table = $this->getTable();
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        if (empty($alias)) {
            $alias = Joomla\CMS\Application\ApplicationHelper::stringURLSafe($title);

            if (trim(str_replace('-', '', $alias)) == '') {
                $alias = Joomla\CMS\Application\ApplicationHelper::stringURLSafe(Factory::getDate()->format('Y-m-d-H-i-s'));
            }
        }

        $query->select('COUNT(id)')
            ->from($table->getTableName())
            ->where('alias = ' . $db->quote($alias));

        if ($id) {
            $query->where('id != ' . intval($id));
        }

        $db->setQuery((string)$query);
        $count = (int)$db->loadResult();

        if ($id > 0 && $count == 0) {
            return array($title, $alias);
        } elseif ($id == 0 && $count == 0) {
            return array($title, $alias);
        } else {
            while ($table->load(array('alias' => $alias))) {
                $m = null;

                if (preg_match('#-(\d+)$#', $alias, $m)) {
                    $alias = preg_replace('#-(\d+)$#', '-' . ($m[1] + 1) . '', $alias);
                } else {
                    $alias .= '-2';
                }

                if (preg_match('#\((\d+)\)$#', $title, $m)) {
                    $title = preg_replace('#\(\d+\)$#', '(' . ($m[1] + 1) . ')', $title);
                } else {
                    $title .= ' (2)';
                }
            }
        }

        return array($title, $alias);
    }


    /**
     * Method to generate a unique group title.
     *
     * @param string $title The title
     * @param integer $parent_id The parent group id
     * @param integer $id The group id
     *
     * @return    array                    Contains the modified title
     */
    protected function generateNewGroupTitle($title, $parent_id, $id = 0)
    {
        $table = $this->getTable('UserGroup', 'JTable');
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select('COUNT(id)')
            ->from($table->getTableName())
            ->where('parent_id = ' . (int)$parent_id);

        if ($id) {
            $query->where('id != ' . intval($id));
        }

        $db->setQuery($query);
        $count = (int)$db->loadResult();

        if ($id > 0 && $count == 0) {
            return $title;
        } elseif ($id == 0 && $count == 0) {
            return $title;
        } else {
            while ($table->load(array('title' => $title, 'parent_id' => $parent_id))) {
                $m = null;

                if (preg_match('#\((\d+)\)$#', $title, $m)) {
                    $title = preg_replace('#\(\d+\)$#', '(' . ($m[1] + 1) . ')', $title);
                } else {
                    $title .= ' (2)';
                }
            }
        }

        return $title;
    }


    /**
     * Method to create a new user group
     *
     * @param string $title the name of the group
     * @param integer $parent_id The parenting group
     * @param array $users The users to add to the group
     *
     * @return    integer    $id           The id of the new group
     */
    protected function createUserGroup($title, $parent_id, $users = array())
    {
        $dispatcher = Factory::getApplication();

        $table = $this->getTable('Usergroup', 'JTable');

        // Include the content plugins for the on save events.
        PluginHelper::importPlugin('content');

        // Generate unique title
        $title = $this->generateNewGroupTitle($title, $parent_id);

        $data = array();
        $data['title'] = $title;
        $data['parent_id'] = (int)$parent_id;

        // Allow an exception to be thrown.
        try {
            // Bind the data.
            if (!$table->bind($data)) {
                $this->setError($table->getError());
                return false;
            }

            // Check the data.
            if (!$table->check()) {
                $this->setError($table->getError());
                return false;
            }

            // Trigger the onContentBeforeSave event.
            $result = $dispatcher->triggerEvent($this->event_before_save, array('com_users.group', $table, true, $data));

            if (in_array(false, $result, true)) {
                $this->setError($table->getError());
                return false;
            }

            // Store the data.
            if (!$table->store()) {
                $this->setError($table->getError());
                return false;
            }

            // Clean the cache.
            $this->cleanCache();

            // Trigger the onContentAfterSave event.
            $dispatcher->triggerEvent($this->event_after_save, array('com_users.group', $table, true));
        } catch (Exception $e) {
            $this->setError($e->getMessage());

            return false;
        }

        $pkName = $table->getKeyName();

        if (!isset($table->$pkName)) {
            return false;
        }

        $gid = (int)$table->$pkName;

        // Add users to the group
        $looped = array();

        foreach ($users as $uid) {
            if (in_array($uid, $looped)) continue;

            $looped[] = $uid;

            $obj = new stdClass();
            $obj->user_id = (int)$uid;
            $obj->group_id = (int)$gid;

            $this->_db->insertObject('#__user_usergroup_map', $obj);
        }

        return $gid;
    }


    /**
     * Method to rename a user group
     *
     * @param integer $id The group id to rename
     * @param string $title The new group title
     *
     * @return    boolean              True on success.
     */
    protected function renameUserGroup($id, $title)
    {
        $query = $this->_db->getQuery(true);

        $query->select('id, title, parent_id')
            ->from('#__usergroups')
            ->where('id = ' . (int)$id);

        $this->_db->setQuery($query);
        $group = $this->_db->loadObject();

        if (empty($group)) return false;

        if ($title != $group->title) {
            $title = $this->generateNewGroupTitle($title, $group->parent_id, $group->id);

            $query->clear()
                ->update('#__usergroups')
                ->set('title = ' . $this->_db->quote($title))
                ->where('id = ' . (int)$id);

            $this->_db->setQuery($query);
            $this->_db->execute();
        }

        return true;
    }


    /**
     * Method to add users to groups
     *
     * @param array $data Assoc array (group => users)
     *
     * @return    boolean             True on success
     */
    protected function addGroupUsers($data)
    {
        $user = Factory::getApplication()->getIdentity();
        $is_admin = $user->authorise('core.admin');
        $query = $this->_db->getQuery(true);

        foreach ($data as $gid => $users) {
            $gid = (int)$gid;

            if (empty($users)) continue;

            $users = explode(',', $users);

            // If we are not an admin, but the group grants admin privileges, don't allow user manipulation
            if (!$is_admin) {
                if (Access::checkGroup($gid, 'core.admin')) {
                    continue;
                }
            }

            // Loop through the users
            foreach ($users as $uid) {
                $uid = (int)$uid;

                if (!$uid) continue;

                // Check if the users is already in the group
                $query->clear()
                    ->select('user_id')
                    ->from('#__user_usergroup_map')
                    ->where('user_id = ' . $uid)
                    ->where('group_id = ' . $gid);

                $this->_db->setQuery($query);
                $exists = (int)$this->_db->loadResult();

                if ($exists) continue;

                $obj = new stdClass();

                $obj->user_id = $uid;
                $obj->group_id = $gid;

                // Add user to group
                $this->_db->insertObject('#__user_usergroup_map', $obj);
            }
        }

        return true;
    }


    /**
     * Method to remove users from groups
     *
     * @param array $data Assoc array (group => users)
     *
     * @return    boolean             True on success
     */
    protected function removeGroupUsers($data)
    {
        $user = Factory::getApplication()->getIdentity();
        $is_admin = $user->authorise('core.admin');
        $query = $this->_db->getQuery(true);

        foreach ($data as $gid => $users) {
            $gid = (int)$gid;

            if (empty($users)) continue;

            // If we are not an admin, but the group grants admin privileges, don't allow user manipulation
            if (!$is_admin) {
                if (Access::checkGroup($gid, 'core.admin')) {
                    continue;
                }
            }

            $users = explode(',', $users);
            $clean = array();

            // Loop through the users
            foreach ($users as $uid) {
                $uid = (int)$uid;

                if (!$uid) continue;

                $clean[] = $uid;
            }

            if (!count($clean)) continue;

            $query->clear()
                ->delete('#__user_usergroup_map')
                ->where('group_id = ' . $gid)
                ->where('user_id IN(' . implode(', ', $clean) . ')');

            $this->_db->setQuery($query);
            $this->_db->execute();
        }

        return true;
    }


    /**
     * Method to test whether a record can be deleted.
     * Defaults to the permission set in the component.
     *
     * @param object     A record object.
     *
     * @return    boolean    True if allowed to delete the record.
     */
    protected function canDelete($record)
    {
        if (empty($record->id)) {
            return parent::canDelete($record);
        }

        if ($record->state != -2) {
            return false;
        }

        $user = Factory::getApplication()->getIdentity();

        if (!$user->authorise('core.admin') && !in_array($record->access, $user->getAuthorisedViewLevels())) {
            return false;
        }

        return $user->authorise('core.delete', 'com_jpprojects.project.' . (int)$record->id);
    }


    /**
     * Method to test whether a record can have its state edited.
     * Defaults to the permission set in the component.
     *
     * @param object     A record object.
     *
     * @return    boolean    True if allowed to edit the state of the record.
     */
    protected function canEditState($record)
    {
        if (empty($record->id)) {
            return parent::canEditState($record);
        }

        $user = Factory::getApplication()->getIdentity();

        if (!$user->authorise('core.admin') && !in_array($record->access, $user->getAuthorisedViewLevels())) {
            return false;
        }

        return $user->authorise('core.edit.state', 'com_jpprojects.project.' . (int)$record->id);
    }


    /**
     * Method to test whether a record can be edited.
     * Defaults to the permission set in the component.
     *
     * @param object     A record object.
     *
     * @return    boolean    True if allowed to edit the record.
     */
    protected function canEdit($record)
    {
        $user = Factory::getApplication()->getIdentity();

        if (empty($record->id)) {
            return $user->authorise('core.edit', 'com_jpprojects');
        }

        $access = JPprojectsHelper::getActions($record->id);
        $asset = 'com_jpprojects.project.' . (int)$record->id;

        if (!$user->authorise('core.admin') && !in_array($record->access, $user->getAuthorisedViewLevels())) {
            return false;
        }

        return ($user->authorise('core.edit', $asset) || ($access->get('core.edit.own', $asset) && $record->created_by == $user->id));
    }


    /**
     * Method to get the data that should be injected in the form.
     *
     * @return    mixed    The data for the form.
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = Factory::getApplication()->getUserState('com_jpprojects.edit.' . $this->getName() . '.data', array());

        if (empty($data)) $data = $this->getItem();

        return $data;
    }
}
