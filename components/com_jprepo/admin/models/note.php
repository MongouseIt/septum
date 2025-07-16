<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jprepo
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\AdminModel;


jimport('joomla.application.component.modeladmin');


/**
 * Item Model for a topic reply form.
 *
 */
class JPrepoModelNote extends AdminModel
{
    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     */
    protected $text_prefix = 'COM_JOOMPROJECT_NOTE';


    /**
     * Returns a Table object, always creating it.
     *
     * @param     string    The table type to instantiate
     * @param     string    A prefix for the table class name. Optional.
     * @param     array     Configuration array for model. Optional.
     *
     * @return    jtable    A database object
     */
    public function getTable($type = 'Note', $prefix = 'JPtable', $config = array())
    {
        return Table::getInstance($type, $prefix, $config);
    }


    /**
     * Method to perform batch operations on an item or a set of items.
     *
     * @param     array      $commands    An array of commands to perform.
     * @param     array      $pks         An array of item ids.
     * @param     array      $contexts         An array of item contexts.
     *
     * @return    boolean                 Returns true on success, false on failure.
     */
    public function batch($commands, $pks, $contexts = array())
    {
        // Sanitize user ids.
        $pks = array_unique($pks);
        \Joomla\Utilities\ArrayHelper::toInteger($pks);

        // Remove any values of zero.
        if (array_search(0, $pks, true)) {
            unset($pks[array_search(0, $pks, true)]);
        }

        if (empty($pks)) {
            $this->setError(Text::_('JGLOBAL_NO_ITEM_SELECTED'));
            return false;
        }

        $done = false;

        if (!empty($commands['parent_id']))
        {
            $cmd = \Joomla\Utilities\ArrayHelper::getValue($commands, 'move_copy', 'c');

            if ($cmd == 'c') {
                $result = $this->batchCopy($commands['parent_id'], $pks);

                if (is_array($result)) {
                    $pks = $result;
                }
                else {
                    return false;
                }
            }
            elseif ($cmd == 'm' && !$this->batchMove($commands['parent_id'], $pks)) {
                return false;
            }
            $done = true;
        }

        if (!$done) {
            $this->setError(Text::_('JLIB_APPLICATION_ERROR_INSUFFICIENT_BATCH_INFORMATION'));
            return false;
        }

        // Clear the cache
        $this->cleanCache();

        return true;
    }


    /**
     * Batch move items to a new directory
     *
     * @param     integer    $value    The new parent ID.
     * @param     array      $pks      An array of row IDs.
     * @param     array      $contexts      An array of row contexts.
     *
     * @return    boolean              True if successful, false otherwise and internal error is set.
     */
    protected function batchMove($value, $pks, $contexts = array())
    {
        $dest = (int) $value;

        $table = $this->getTable('Directory');

        // Check that the destination exists
        if ($dest) {
            if (!$table->load($dest)) {
                if ($error = $dest->getError()) {
                    // Fatal error
                    $this->setError($error);
                    return false;
                }
                else {
                    $this->setError(Text::_('COM_JOOMPROJECT_ERROR_BATCH_MOVE_DIRECTORY_NOT_FOUND'));
                    return false;
                }
            }
        }

        if (empty($dest)) {
            $this->setError(Text::_('COM_JOOMPROJECT_ERROR_BATCH_MOVE_DIRECTORY_NOT_FOUND'));
            return false;
        }

        // Check that user has create and edit permission
        $access = JPrepoHelper::getActions('directory', $dest);

        if (!$access->get('core.create')) {
            $this->setError(Text::_('COM_JOOMPROJECT_ERROR_BATCH_CANNOT_CREATE_NOTE'));
            return false;
        }

        $table = $this->getTable();

        // Parent exists so we let's proceed
        foreach ($pks as $pk)
        {
            // Check that the row actually exists
            if (!$table->load($pk)) {
                if ($error = $table->getError()) {
                    // Fatal error
                    $this->setError($error);
                    return false;
                }
                else {
                    // Not fatal error
                    $this->setError(Text::sprintf('JGLOBAL_BATCH_MOVE_ROW_NOT_FOUND', $pk));
                    continue;
                }
            }

            // Set the new location in the tree for the node.
            $table->dir_id = (int) $dest;

            // Store the row.
            if (!$table->store()) {
                $this->setError($table->getError());
                return false;
            }
        }

        // Clean the cache
        $this->cleanCache();

        return true;
    }


    /**
     * Batch copy notes to a new directory.
     *
     * @param     integer    $value    The destination dir.
     * @param     array      $pks      An array of row IDs.
     * @param     array      $contexts      An array of row contexts.
     *
     * @return    mixed                An array of new IDs on success, boolean false on failure.
     */
    protected function batchCopy($value, $pks, $contexts = array())
    {
        $dest = (int) $value;
        $rbid = null;

        $table = $this->getTable('Directory');
        $db    = $this->getDbo();
        $user  = Factory::getApplication()->getIdentity();

        $i = 0;

        // Check that the parent exists
        if ($dest) {
            if (!$table->load($dest)) {
                if ($error = $table->getError()) {
                    $this->setError($error);
                    return false;
                }
                else {
                    $this->setError(Text::_('COM_JOOMPROJECT_ERROR_BATCH_COPY_DIRECTORY_NOT_FOUND'));
                    return false;
                }
            }

            // Check that user has create permission for parent directory
            $access = JPrepoHelper::getActions('directory', $dest);

            if (!$access->get('core.create')) {
                // Error since user cannot create in parent dir
                $this->setError(Text::_('COM_JOOMPROJECT_ERROR_BATCH_CANNOT_CREATE_NOTE'));
                return false;
            }
        }

        $table  = $this->getTable();
        $newIds = array();

        // Parent exists so we let's proceed
        foreach ($pks as $pk)
        {
            // Check that the row actually exists
            if (!$table->load($pk)) {
                if ($error = $table->getError()) {
                    // Fatal error
                    $this->setError($error);
                    return false;
                }
                else {
                    // Not fatal error
                    $this->setError(Text::sprintf('JGLOBAL_BATCH_MOVE_ROW_NOT_FOUND', $pk));
                    continue;
                }
            }

            // Reset the id because we are making a copy.
            $table->id = 0;

            // Set the new location in the tree for the node.
            $table->dir_id = (int) $dest;

            // Alter the title & alias
            list($title, $alias) = $this->generateNewTitle($table->dir_id, $table->title, $table->alias);
            $table->title = $title;
            $table->alias = $alias;

            // Store the row.
            if (!$table->store()) {
                $this->setError($table->getError());
                return false;
            }

            // Get the new item ID
            $newId = $table->get('id');

            // Add the new ID to the array
            $newIds[] = $newId;
        }

        return $newIds;
    }


    /**
     * Method to get a single record.
     *
     * @param     integer $pk   The id of the primary key.
     * @return    mixed   $item   Object on success, false on failure.
     */
    public function getItem($pk = null)
    {
        $item = parent::getItem($pk);

        if ($item == false) return false;

        if (property_exists($item, 'attribs')) {
            // Convert the params field to an array.
            $registry = new Registry();

            $registry->loadString((string)$item->attribs);

            $item->params  = $registry;
            $item->attribs = $registry->toArray();
        }

        if ($item->id > 0) {
            // Existing record
            $labels = $this->getInstance('Labels', 'JPModel');

            $item->labels = $labels->getConnections('com_jprepo.note', $item->id);
            $item->revision_count = 0;

            $rev = (int) $this->getState($this->getName() . '.rev');

            if ($rev) {
                $cfg = array('ignore_request' => true);
                $rev_model = $this->getInstance('NoteRevision', 'JPrepoModel', $cfg);

                $rev_item = $rev_model->getItem($rev);

                if (!$rev_item || $rev_item->parent_id != $item->id) return false;

                // Override properties of item
                $props = array('title', 'description', 'attribs', 'params', 'created', 'created_by');

                foreach ($props AS $prop)
                {
                    $item->$prop = $rev_item->$prop;
                }

                // Check out the note so it can't be edited
                $item->checked_out = 1;
                $item->checked_out_time = 1;
            }
        }
        else {
            // New record
            $item->labels = array();
            $item->revision_count = $this->getRevisionCount($pk);
        }

        return $item;
    }


    /**
     * Counts the revisions of the given file
     *
     * @param    array      $pk       The file primary key
     *
     * @retun    integer    $count    The revision count
     */
    public function getRevisionCount($pk = null)
    {
        $pk    = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');
        $query = $this->_db->getQuery(true);
        $count = 0;

        if (empty($pk)) return $count;

        // Count revs
        $query->select('COUNT(*)')
              ->from('#__jp_repo_note_revs')
              ->where('parent_id = ' . (int) $pk);

        $query->group('parent_id');
        $this->_db->setQuery($query);

        try {
            $count += (int) $this->_db->loadResult();
        }
        catch (RuntimeException $e) {
            $this->setError($e->getMessage());
            return false;
        }

        return $count;
    }


    /**
     * Method to delete one or more records.
     *
     * @param     array  &    $pks              An array of record primary keys.
     * @param     bool        $ignore_access    If true, ignore permission and just delete
     *
     * @return    boolean                       True if successful, false if an error occurs.
     */
    public function delete(&$pks, $ignore_access = false)
    {
        $dispatcher = \Joomla\CMS\Factory::getApplication();
        $pks = (array) $pks;
        $table = $this->getTable();

        // Include the content plugins for the on delete events.
        PluginHelper::importPlugin('content');

        // Iterate the items to delete each one.
        foreach ($pks as $i => $pk)
        {
            if ($table->load($pk))
            {
                if ($ignore_access || $this->canDelete($table))
                {
                    $context = $this->option . '.' . $this->name;

                    // Trigger the onContentBeforeDelete event.
                    $result = $dispatcher->triggerEvent($this->event_before_delete, array($context, $table));
                    if (in_array(false, $result, true))
                    {
                        $this->setError($table->getError());
                        return false;
                    }

                    if (!$table->delete($pk))
                    {
                        $this->setError($table->getError());
                        return false;
                    }

                    // Trigger the onContentAfterDelete event.
                    $dispatcher->triggerEvent($this->event_after_delete, array($context, $table));

                }
                else
                {
                    // Prune items that you can't change.
                    unset($pks[$i]);
                    $error = $this->getError();
                    if ($error)
                    {
                        Log::add($error, Log::WARNING, 'jerror');
                        return false;
                    }
                    else
                    {
                        Log::add(Text::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'), Log::WARNING, 'jerror');
                        return false;
                    }
                }
            }
            else
            {
                $this->setError($table->getError());
                return false;
            }
        }

        // Clear the component's cache
        $this->cleanCache();

        return true;
    }


    /**
     * Method to save an item
     *
     * @param     array      $data    The item data
     *
     * @return    boolean             True on success, False on error
     */
    public function save($data)
    {
        $dispatcher = \Joomla\CMS\Factory::getApplication();

        $table  = $this->getTable();
        $pk     = (!empty($data['id'])) ? $data['id'] : (int) $this->getState($this->getName() . '.id');
        $date   = Factory::getDate();
        $is_new = true;

        $old_path = null;

        // Include the content plugins for the on save events.
        PluginHelper::importPlugin('content');

        // Load the row if saving an existing item.
        if ($pk > 0) {
            if ($table->load($pk)) {
                $is_new = false;

                if (!empty($table->path)) {
                    $old_path = $table->path;
                }
            }
            else {
                $pk = 0;
            }
        }

        // Save revision if not new
        if (!$is_new) {
            $head_data = $table->getProperties(true);
            $config    = array('ignore_request' => true);
            $rev_model = $this->getInstance('NoteRevision', 'JPrepoModel', $config);

            $head_data['parent_id']  = $head_data['id'];
            $head_data['id']         = null;
            $head_data['created_by'] = Factory::getApplication()->getIdentity()->id;

            if (!$rev_model->save($head_data)) {
                $this->setError($rev_model->getError());
                return false;
            }
        }

        // Make sure the title and alias are always unique
        $data['alias'] = '';
        list($title, $alias) = $this->generateNewTitle($data['dir_id'], $data['title'], $data['alias'], $pk);

        $data['title'] = $title;
        $data['alias'] = $alias;

        // Handle permissions and access level
        if (isset($data['rules'])) {
            $access = JPAccessHelper::getViewLevelFromRules($data['rules'], intval($data['access']));

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

        // Trigger the onContentAfterSave event.
        $dispatcher->triggerEvent($this->event_after_save, array($this->option . '.' . $this->name, &$table, $is_new));

        $this->setState($this->getName() . '.id', $table->id);

        $updated = $this->getTable();
        if ($updated->load($table->id) === false) return false;

        // Store the labels
        if (isset($data['labels'])) {
            $labels = $this->getInstance('Labels', 'JPModel');

            if ((int) $labels->getState('item.project') == 0) {
                $labels->setState('item.project', $updated->project_id);
            }

            $labels->setState('item.type', 'com_jprepo.note');
            $labels->setState('item.id', $updated->id);

            if (!$labels->saveRefs($data['labels'])) {
                return false;
            }
        }

        // Clear the cache
        $this->cleanCache();

        return true;
    }


    /**
     * Method to get the record form.
     *
     * @param     array      Data for the form.
     * @param     boolean    True if the form is to load its own data (default case), false if not.
     * @return    mixed      A JForm object on success, false on failure
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_jprepo.note', 'note', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) return false;

        $jinput = Factory::getApplication()->input;
        $user   = Factory::getApplication()->getIdentity();
        $id     = (int) $jinput->get('id', 0);

        // Disable these fields if not an admin
        if (!$user->authorise('core.admin', 'com_jprepo') && !$user->authorise('core.manage', 'com_jprepo')) {
            $form->setFieldAttribute('access', 'disabled', 'true');
            $form->setFieldAttribute('access', 'filter', 'unset');

            $form->setFieldAttribute('rules', 'disabled', 'true');
            $form->setFieldAttribute('rules', 'filter', 'unset');
        }

        // Disable these fields when updating
        if ($id) {
            $form->setFieldAttribute('project_id', 'disabled', 'true');
            $form->setFieldAttribute('project_id', 'filter', 'unset');
            $form->setFieldAttribute('project_id', 'required', 'false');

            // We still need to inject the project id when reloading the form
            if (!isset($data['project_id'])) {
                $db    = Factory::getDbo();
                $query = $db->getQuery(true);

                $query->select('project_id')
                      ->from('#__jp_repo_notes')
                      ->where('id = ' . $db->quote($id));

                $db->setQuery($query);
                $form->setValue('project_id', null, (int) $db->loadResult());
            }
        }

        return $form;
    }


    /**
     * Method to get the data that should be injected in the form.
     *
     * @return    mixed    The data for the form.
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = Factory::getApplication()->getUserState('com_jprepo.edit.' . $this->getName() . '.data', array());

        if (empty($data)) {
            $data = $this->getItem();

            // Set default values
            if ($this->getState($this->getName() . '.id') == 0) {
                $active_id = JPApplicationHelper::getActiveProjectId();

                $data->set('project_id', $active_id);
                $data->set('dir_id', $this->getState($this->getName() . '.dir_id'));
            }
        }

        return $data;
    }


    /**
     * Custom clean the cache of com_joomproject and joomproject modules
     *
     */
    protected function cleanCache($group = 'com_jprepo', $client_id = 0)
    {
        parent::cleanCache($group);
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
        if (empty($record->id)) {
            return parent::canDelete($record);
        }

        $user = Factory::getApplication()->getIdentity();

        if (!$user->authorise('core.admin') && !in_array($record->access, $user->getAuthorisedViewLevels())) {
            return false;
        }

        return $user->authorise('core.delete', 'com_jprepo.note.' . (int) $record->id);
    }


    /**
     * Method to change the title.
     *
     * @param     integer    $dir_id    The parent directory
     * @param     string     $title     The directory title
     * @param     string     $alias     The current alias
     * @param     integer    $id        The note id
     *
     * @return    string                Contains the new title
     */
    protected function generateNewTitle($dir_id, $title, $alias = '', $id = 0)
    {
        // Alter the title & alias
        $table = $this->getTable();
        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        if (empty($alias)) {
            $alias = Joomla\CMS\Application\ApplicationHelper::stringURLSafe($title);

            if (trim(str_replace('-', '', $alias)) == '') {
                $alias = Joomla\CMS\Application\ApplicationHelper::stringURLSafe(Factory::getDate()->format('Y-m-d-H-i-s'));
            }
        }

        $query->select('COUNT(id)')
              ->from($table->getTableName())
              ->where('alias = ' . $db->quote($alias))
              ->where('dir_id = ' . $db->quote($dir_id));

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
            while ($table->load(array('alias' => $alias, 'dir_id' => $dir_id)))
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
     * Method to test whether a record can have its state edited.
     * Defaults to the permission set in the component.
     *
     * @param     object     A record object.
     *
     * @return    boolean    True if allowed to delete the record.
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

        return $user->authorise('core.edit.state', 'com_jprepo.note.' . (int) $record->id);
    }


    /**
     * Method to test whether a record can be edited.
     * Defaults to the permission for the component.
     *
     * @param     object     A record object.
     *
     * @return    boolean    True if allowed to edit the record.
     */
    protected function canEdit($record)
    {
        if (empty($record->id)) {
            return $user->authorise('core.edit', 'com_jprepo');
        }

        $user  = Factory::getApplication()->getIdentity();
        $asset = 'com_jprepo.note.' . (int) $record->id;

        if (!$user->authorise('core.admin') && !in_array($record->access, $user->getAuthorisedViewLevels())) {
            return false;
        }

        return ($user->authorise('core.edit', $asset) || ($access->get('core.edit.own', $asset) && $record->created_by == $user->id));
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
        $pk  = \Joomla\CMS\Factory::getApplication()->input->getUInt($key);
        $rev = \Joomla\CMS\Factory::getApplication()->input->getUInt('rev');

        $this->setState($this->getName() . '.id', $pk);
        $this->setState($this->getName() . '.rev', $rev);

        if ($pk) {
            $table = $this->getTable();

            if ($table->load($pk)) {
                $project = (int) $table->project_id;
                $this->setState($this->getName() . '.project', $project);
                JPApplicationHelper::setActiveProject($project);

                $dir_id = (int) $table->dir_id;
                $this->setState($this->getName() . '.dir_id', $dir_id);
            }


        }
        else {
            $dir_id = \Joomla\CMS\Factory::getApplication()->input->getUInt('filter_parent_id', 0);
            $this->setState($this->getName() . '.dir_id', $dir_id);

            $project = JPApplicationHelper::getActiveProjectId('filter_project');

            if ($project) {
                $this->setState($this->getName() . '.project', $project);
                JPApplicationHelper::setActiveProject($project);
            }
            elseif ($parent_id) {
                $table = $this->getTable('Directory');

                if ($table->load($parent_id)) {
                    $project = (int) $table->project_id;

                    $this->setState($this->getName() . '.project', $project);
                    JPApplicationHelper::setActiveProject($project);
                }
            }
        }

        // Load the parameters.
        $value = ComponentHelper::getParams($this->option);
        $this->setState('params', $value);
    }
}
