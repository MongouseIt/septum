<?php
/**
 * @package      Joomproject Pro
 * @subpackage   Designs
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Application\ApplicationHelper;



if (JPApplicationHelper::exists('com_jprepo')) {
    JLoader::register('JPrepoHelper', JPATH_ADMINISTRATOR . '/components/com_jprepo/helpers/jprepo.php');
}

/**
 * Item Model for a design album form.
 *
 */
class JPdesignsModelAlbum extends AdminModel
{
    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     */
    protected $text_prefix = 'COM_JOOMPROJECT_DESIGN_ALBUM';


    /**
     * Returns a Table object, always creating it.
     *
     * @param     string    The table type to instantiate
     * @param     string    A prefix for the table class name. Optional.
     * @param     array     Configuration array for model. Optional.
     *
     * @return    jtable    A database object
     */
    public function getTable($type = 'Album', $prefix = 'JPtable', $config = array())
    {
        return Table::getInstance($type, $prefix, $config);
    }


    /**
     * Method to get a single record.
     *
     * @param     integer    The id of the primary key.
     *
     * @return    mixed      Object on success, false on failure.
     */
    public function getItem($pk = null)
    {
        if ($item = parent::getItem($pk)) {
            // Convert the params field to an array.
            $registry = new Registry;
            $registry->loadString((string)$item->attribs);
            $item->attribs = $registry->toArray();
        }

        return $item;
    }


    /**
     * Method to get the record form.
     *
     * @param     array      Data for the form.
     * @param     boolean    True if the form is to load its own data (default case), false if not.
     *
     * @return    mixed      A JForm object on success, false on failure
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_jpdesigns.album', 'album', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) return false;

        $jinput = Factory::getApplication()->input;
        $user   = Factory::getApplication()->getIdentity();
        $id     = (int) $jinput->get('id', 0);
        $task   = $jinput->get('task');

        // Check for existing item.
        // Modify the form based on Edit State access controls.
        if ($id != 0 && (!$user->authorise('core.edit.state', 'com_jpdesigns.album.' . $id)) || ($id == 0 && !$user->authorise('core.edit.state', 'com_jpdesigns')))
        {
            // Disable fields for display.
            $form->setFieldAttribute('state', 'disabled', 'true');

            // Disable fields while saving.
            $form->setFieldAttribute('state', 'filter', 'unset');
        }

        // Always disable these fields while saving
        $form->setFieldAttribute('alias', 'filter', 'unset');

        // Disable these fields if not an admin
        if (!$user->authorise('core.admin', 'com_jpdesigns')) {
            $form->setFieldAttribute('access', 'disabled', 'true');
            $form->setFieldAttribute('access', 'filter', 'unset');

            $form->setFieldAttribute('rules', 'disabled', 'true');
            $form->setFieldAttribute('rules', 'filter', 'unset');
        }

        // Disable these fields when updating
        if ($id) {
            $form->setFieldAttribute('project_id', 'readonly', 'true');
            $form->setFieldAttribute('project_id', 'required', 'false');

            if ($task != 'save2copy') {
                $form->setFieldAttribute('project_id', 'disabled', 'true');
                $form->setFieldAttribute('project_id', 'filter', 'unset');
            }

            // We still need to inject the project id when reloading the form
            if (!isset($data['project_id'])) {
                $db    = Factory::getDbo();
                $query = $db->getQuery(true);

                $query->select('project_id')
                      ->from('#__jp_design_albums')
                      ->where('id = ' . $db->quote($id));

                $db->setQuery($query);
                $form->setValue('project_id', null, (int) $db->loadResult());
            }
        }

        return $form;
    }


    /**
     * A protected method to get a set of ordering conditions.
     *
     * @param     object    A record object.
     *
     * @return    array     An array of conditions to add to add to ordering queries.
     */
    protected function getReorderConditions($table)
    {
        $condition = array();

        $condition[] = 'project_id = ' . (int) $table->project_id;

        return array(implode(' AND ', $condition));
    }


    /**
     * Prepare and sanitise the table data prior to saving.
     *
     * @param     jtable    A JTable object.
     *
     * @return    void
     */
    protected function prepareTable($table)
    {
        $condition = array();

        $condition[] = 'project_id = ' . (int) $table->project_id;

        $condition = implode(' AND ', $condition);

        // Reorder the items within the category so the new item is first
        if (empty($table->id)) {
            $table->reorder($condition);
        }
    }


    /**
     * Method to auto-populate the model state.
     * Note: Calling getState in this method will result in recursion.
     *
     * @return    void
     */
    protected function populateState()
    {
        // Initialise variables.
        $app   = Factory::getApplication();
        $table = $this->getTable();
        $key   = $table->getKeyName();

        // Get the pk of the record from the request.
        $pk = Factory::getApplication()->input->getInt($key);
        $this->setState($this->getName() . '.id', $pk);

        if ($pk) {
            $table = $this->getTable();

            if ($table->load($pk)) {
                $project = (int) $table->project_id;
                $this->setState($this->getName() . '.project', $project);
                JPApplicationHelper::setActiveProject($project);
            }
        }
        else {
            $project = JPApplicationHelper::getActiveProjectId('filter_project');

            if ($project) {
                $this->setState($this->getName() . '.project', $project);
            }
        }

        // Load the parameters.
        $value = ComponentHelper::getParams($this->option);
        $this->setState('params', $value);
    }


    /**
     * Method to save the form data.
     *
     * @param     array      The form data
     *
     * @return    boolean    True on success
     */
    public function save($data)
    {
        $table  = $this->getTable();
        $key    = $table->getKeyName();
        $pk     = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
        $is_new = true;
        $old    = null;

        // Include the content plugins for the on save events.
        PluginHelper::importPlugin('content');
        $dispatcher = Factory::getApplication();

        // Allow an exception to be thrown.
        try {
            // Load the row if saving an existing record.
            if ($pk > 0) {
                if ($table->load($pk)) {
                    $is_new = false;
                    $old    = clone $table;
                }
            }

            if (!$is_new) {
                $data['project_id'] = $table->project_id;
            }

            // Make sure the title and alias are always unique
            $data['alias'] = '';
            list($title, $alias) = $this->generateNewTitle($data['title'], $data['project_id'], $pk);

            $data['title'] = $title;
            $data['alias'] = $alias;

            // Handle permissions and access level
            if (isset($data['rules'])) {
                $prev_access = ($is_new ? 0 : $table->access);
                $access = JPAccessHelper::getViewLevelFromRules($data['rules'], $prev_access);

                if ($access) {
                    $data['access'] = $access;
                }
            }
            else {
                if ($is_new) {
                    // Let the table class find the correct access level
                    $data['access'] = 0;
                }
                else {
                    // Keep the existing access in the table
                    if (isset($data['access'])) {
                        unset($data['access']);
                    }
                }
            }

            // Make item published by default if new
            if (!isset($data['state']) && $is_new) {
                $data['state'] = 1;
            }

            // Bind the data.
            if (!$table->bind($data)) {
                $this->setError($table->getError());
                return false;
            }

            // Prepare the row for saving
            $this->prepareTable($table);

            // Check the data.
            if (!$table->check()) {
                $this->setError($table->getError());
                return false;
            }

            // Trigger the onContentBeforeSave event.
            $result = $dispatcher->triggerEvent($this->event_before_save, array($this->option . '.' . $this->name, &$table, $is_new,$data));

            if (in_array(false, $result, true)) {
                $this->setError($table->getError());
                return false;
            }

            // Store the data.
            if (!$table->store()) {
                $this->setError($table->getError());
                return false;
            }

            $pk_name = $table->getKeyName();

            if (isset($table->$pk_name)) {
                $this->setState($this->getName() . '.id', $table->$pk_name);
            }

            $this->setState($this->getName() . '.new', $is_new);

            $id = $this->getState($this->getName() . '.id');

            // Clean the cache.
            $this->cleanCache();

            // Trigger the onContentAfterSave event.
            $dispatcher->triggerEvent($this->event_after_save, array($this->option . '.' . $this->name, &$table, $is_new));
        }
        catch (Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }

        return true;
    }


    /**
	 * Method to delete one or more records.
	 *
	 * @param   array  &$pks  An array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 */
    public function delete(&$pks)
    {
        $pks   = (array) $pks;
        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        // Iterate the items to delete each one.
		foreach ($pks as $i => $pk)
		{
            $query->clear();
            $query->select('COUNT(a.id)')
                  ->from('#__jp_designs AS a')
                  ->where('a.album_id = ' . $db->quote((int) $pk));

            $db->setQuery($query);
            $count = (int) $db->loadResult();

            if ($count) {
                $this->setError(Text::_('COM_JOOMPROJECT_DESIGNS_ALBUM_ERROR_DELETE_NOT_EMPTY_DELETE'));
                return false;
            }
        }

        return parent::delete($pks);
    }


    /**
     * Custom clean the cache
     *
     */
    protected function cleanCache($group = 'com_jpdesigns', $client_id = 0)
    {
        parent::cleanCache($group, $client_id);
    }


    /**
     * Method to change the title & alias.
     * Overloaded from JModelAdmin class
     *
     * @param     string     The title
     * @param     integer    The project id
     * @param     integer    The item id
     *
     * @return    array      Contains the modified title and alias
     */
    protected function generateNewTitle($title, $project, $id = 0)
    {
        $table = $this->getTable();
        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        $alias   =  ApplicationHelper::stringURLSafe($title);
        $project = (int) $project;

        if (trim(str_replace('-', '', $alias)) == '') {
            $alias = ApplicationHelper::stringURLSafe(Factory::getDate()->format('Y-m-d-H-i-s'));
        }

        $query->select('COUNT(id)')
              ->from($table->getTableName())
              ->where('project_id = ' . $db->quote($project))
              ->where('alias = ' . $db->quote($alias));

        if ($id) {
            $query->where('id != ' . intval($id));
        }

        $db->setQuery((string) $query);
        $count = (int) $db->loadResult();

        if ($id > 0 && $count == 0) {
            return array($title, $alias);
        }
        elseif ($id == 0 && $count == 0) {
            return array($title, $alias);
        }
        else {
            while ($table->load(array('project_id' => $project, 'alias' => $alias)))
            {
                $m = null;

                if (preg_match('#-(\d+)$#', $alias, $m)) {
                    $alias = preg_replace('#-(\d+)$#', '-'.($m[1] + 1).'', $alias);
                }
                else {
                    $alias .= '-2';
                }

                if (preg_match('#\((\d+)\)$#', $title, $m)) {
                    $title = preg_replace('#\(\d+\)$#', '('.($m[1] + 1).')', $title);
                }
                else {
                    $title .= ' (2)';
                }
            }
        }

        return array($title, $alias);
    }


    /**
     * Method to test whether a record can be deleted.
     * Defaults to the permission set in the component.
     *
     * @param     object     A record object.
     *
     * @return    boolean    True if allowed to delete the record.
     */
    protected function canDelete($record)
    {
        if (!empty($record->id)) {
            if ($record->state != -2) return false;

            $user  = Factory::getApplication()->getIdentity();
            $asset = 'com_jpdesigns.album.' . (int) $record->id;

            return $user->authorise('core.delete', $asset);
        }

        return parent::canDelete($record);
    }


    /**
     * Method to test whether a record can have its state edited.
     * Defaults to the permission set in the component.
     *
     * @param     object     A record object.
     *
     * @return    boolean    True if allowed to edit the state of the record.
     */
    protected function canEditState($record)
    {
        if (!empty($record->id)) {
            $user  = Factory::getApplication()->getIdentity();
            $asset = 'com_jpdesigns.album.' . (int) $record->id;

            return $user->authorise('core.edit.state', $asset);
        }

        return parent::canEditState($record);
    }


    /**
     * Method to test whether a record can be edited.
     * Defaults to the permission set in the component.
     *
     * @param     object     A record object.
     *
     * @return    boolean    True if allowed to edit the record.
     */
    protected function canEdit($record)
    {
        $user = Factory::getApplication()->getIdentity();

        // Check for existing item.
        if (!empty($record->id)) {
            $asset  = 'com_jpdesigns.album.' . (int) $record->id;

            return ($user->authorise('core.edit', $asset) || ($access->get('core.edit.own', $asset) && $record->created_by == $user->id));
        }

        return $user->authorise('core.edit', 'com_jpdesigns');
    }


    /**
     * Method to get the data that should be injected in the form.
     *
     * @return    mixed    The data for the form.
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = Factory::getApplication()->getUserState('com_jpdesigns.edit.' . $this->getName() . '.data', array());

        if (empty($data)) {
            $data = $this->getItem();

            // Set default values
            if ($this->getState($this->getName() . '.id') == 0) {
                $active_id = JPApplicationHelper::getActiveProjectId();

                $data->set('project_id', $active_id);
            }
        }

        return $data;
    }
}
