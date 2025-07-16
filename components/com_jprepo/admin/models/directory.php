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

use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Filesystem\Path;


jimport('joomla.application.component.modeladmin');



/**
 * Item Model for a Directory form.
 *
 */
class JPrepoModelDirectory extends AdminModel
{
    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     */
    protected $text_prefix = 'COM_JOOMPROJECT_DIRECTORY';


    /**
     * Returns a Table object, always creating it.
     *
     * @param     string    The table type to instantiate
     * @param     string    A prefix for the table class name. Optional.
     * @param     array     Configuration array for model. Optional.
     *
     * @return    jtable    A database object
     */
    public function getTable($type = 'Directory', $prefix = 'JPtable', $config = array())
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

            $item->labels   = $labels->getConnections('com_jprepo.directory', $item->id);
            $item->orphaned = $this->isOrphaned($item->project_id);
            $item->element_count = 0;
        }
        else {
            // New record
            $item->labels   = array();
            $item->orphaned = false;
            $item->element_count = $this->getElementCount($pk);
        }

        return $item;
    }


    /**
     * Counts the contents of the given folders
     *
     * @param    array      $pk       The folder primary key
     *
     * @retun    integer    $count    The element count
     */
    public function getElementCount($pk = null)
    {
        $pk    = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');
        $query = $this->_db->getQuery(true);
        $user  = Factory::getApplication()->getIdentity();
        $count = 0;

        if (empty($pk)) return $count;

        // Count sub-folders
        $query->select('COUNT(*)')
              ->from('#__jp_repo_dirs')
              ->where('parent_id = ' . (int) $pk);

        if (!$user->authorise('core.admin', 'com_jprepo')) {
            $levels = implode(',', $user->getAuthorisedViewLevels());
            $query->where('access IN (' . $levels . ')');
        }

        $query->group('parent_id');
        $this->_db->setQuery($query);

        try {
            $count += (int) $this->_db->loadResult();
        }
        catch (RuntimeException $e) {
            $this->setError($e->getMessage());
            return false;
        }

        // Count notes
        $query->clear()
              ->select('COUNT(*)')
              ->from('#__jp_repo_notes')
              ->where('dir_id = ' . (int) $pk);

        if (!$user->authorise('core.admin', 'com_jprepo')) {
            $levels = implode(',', $user->getAuthorisedViewLevels());
            $query->where('access IN (' . $levels . ')');
        }

        $query->group('dir_id');
        $this->_db->setQuery($query);

        try {
            $count += (int) $this->_db->loadResult();
        }
        catch (RuntimeException $e) {
            $this->setError($e->getMessage());
            return false;
        }

        // Count files
        $query->clear()
              ->select('COUNT(id)')
              ->from('#__jp_repo_files')
              ->where('dir_id = ' . (int) $pk);

        if (!$user->authorise('core.admin', 'com_jprepo')) {
            $levels = implode(',', $user->getAuthorisedViewLevels());
            $query->where('access IN (' . $levels . ')');
        }

        $query->group('dir_id');
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


    public function getItemFromProjectPath($project, $path)
    {
        $params   = JPApplicationHelper::getProjectParams((int) $project);
        $repo_dir = (int) $params->get('repo_dir');
        $query    = $this->_db->getQuery(true);

        // Remove trailing slash
        if (substr($path, -1) == '/') $path = substr($path, 0, -1);

        // Can't get a path without project repo dir
        if (!$repo_dir) return false;

        $query->select('alias')
              ->from('#__jp_repo_dirs')
              ->where('id = ' . (int) $repo_dir);

        $this->_db->setQuery($query);
        $alias = $this->_db->loadResult();

        $query->clear();
        $query->select('id')
              ->from('#__jp_repo_dirs')
              ->where('project_id = ' . (int) $project)
              ->where('path = ' . $this->_db->quote($alias . '/' . $path));

        $this->_db->setQuery($query);
        $id = (int) $this->_db->loadResult();

        if ($id) return $this->getItem($id);

        return false;
    }


    /**
     * Method to perform batch operations on an item or a set of items.
     *
     * @param     array      $commands    An array of commands to perform.
     * @param     array      $pks         An array of item ids.
     * @param     array      $contexts    An array of item contexts.
     *
     * @return    boolean                 Returns true on success, false on failure.
     */
    public function batch($commands, $pks, $contexts)
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
                $result = $this->batchCopy($commands['parent_id'], $pks, $contexts);

                if (is_array($result)) {
                    $pks = $result;
                }
                else {
                    return false;
                }
            }
            elseif ($cmd == 'm' && !$this->batchMove($commands['parent_id'], $pks, $contexts)) {
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
     * @param     integer    $value       The new parent ID.
     * @param     array      $pks         An array of row IDs.
     * @param     array      $contexts    An array of item contexts.
     *
     * @return    boolean                 True if successful, false otherwise and internal error is set.
     */
    protected function batchMove($value, $pks, $contexts)
    {
        $dest  = (int) $value;
        $table = $this->getTable();
        $user  = Factory::getApplication()->getIdentity();

        // Check that the destination exists
        if (empty($dest)) {
            $this->setError(Text::_('COM_JOOMPROJECT_ERROR_BATCH_MOVE_DIRECTORY_NOT_FOUND'));
            return false;
        }

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

        $project   = $table->project_id;
        $dest_path = $table->path;

        // Check that the user can create in the destination
        if (!$user->authorise('core.create', 'com_jprepo.directory.' . $dest)) {
            $this->setError(Text::_('COM_JOOMPROJECT_ERROR_BATCH_CANNOT_CREATE_DIRECTORY'));
            return false;
        }

        // Check that the user can edit the all selected items
        foreach ($pks as $pk)
        {
            if (!$user->authorise('core.edit', 'com_jprepo.directory.' . (int) $pk)) {
                $this->setError(Text::_('COM_JOOMPROJECT_ERROR_BATCH_CANNOT_EDIT_DIRECTORY'));
                return false;
            }
        }

        // Move each item
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

            $path = $table->path;

            // Set the new location in the tree for the node.
            $table->setLocation($dest, 'last-child');

            // Store the row.
            if (!$table->store()) {
                $this->setError($table->getError());
                return false;
            }

            // Rebuild the tree path.
            if (!$table->rebuildPath($table->id)) {
                $this->setError($table->getError());
                return false;
            }

            // Update physical path
            $this->savePhysical($project, $path, $table->path);

            // Rebuild the paths of the directory children
            if (!$table->rebuild($table->id, $table->lft, $table->level, $table->path)) {
                $this->setError($table->getError());
                return false;
            }
        }

        // Clean the cache
        $this->cleanCache();

        return true;
    }


    /**
     * Batch copy directories to a new directory.
     *
     * @param     integer    $value       The destination dir.
     * @param     array      $pks         An array of row IDs.
     * @param     array      $contexts    An array of item contexts.
     *
     * @return    mixed                   An array of new IDs on success, boolean false on failure.
     */
    protected function batchCopy($value, $pks, $contexts)
    {
        $dest  = (int) $value;
        $table = $this->getTable();
        $query = $this->_db->getQuery(true);
        $user  = Factory::getApplication()->getIdentity();

        $is_admin = $user->authorise('core.admin', 'com_jprepo');
        $levels   = $user->getAuthorisedViewLevels();

        // Check that the parent exists
        if (!$dest) {
            $this->setError(Text::_('COM_JOOMPROJECT_ERROR_BATCH_COPY_DIRECTORY_NOT_FOUND'));
            return false;
        }

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
        if (!$user->authorise('core.create', 'com_jprepo.directory.' . $dest)) {
            $this->setError(Text::_('COM_JOOMPROJECT_ERROR_BATCH_CANNOT_CREATE_DIRECTORY'));
            return false;
        }

        // Calculate the emergency stop count as a precaution against a runaway loop bug
        $query->select('COUNT(id)')
              ->from('#__jp_repo_dirs')
              ->where('project_id = ' . (int) $table->project_id);

        $this->_db->setQuery($query);
        $count = (int) $this->_db->loadResult();

        // We need to log the parent ID
        $rbid    = (int) $table->parent_id;
        $parents = array();
        $new_ids = array();
        $i       = 0;

        // Process each item
        while (!empty($pks) && $count > 0)
        {
            // Pop the first id off the stack
            $pk = array_shift($pks);

            $table->reset();

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

            // Copy is a bit tricky, because we also need to copy the children too
            $query->clear()
                  ->select('id, access')
                  ->from('#__jp_repo_dirs')
                  ->where('lft > ' . (int) $table->lft)
                  ->where('rgt < ' . (int) $table->rgt);

            $this->_db->setQuery($query);
            $sub_dirs = (array) $this->_db->loadObjectList();

            // Add sub dirs to the array only if they aren't already there.
            foreach ($sub_dirs as $dir)
            {
                if (!in_array($dir->id, $pks)) {
                    // Check viewing access
                    if ($is_admin || in_array($dir->access, $levels)) {
                        array_push($pks, $dir->id);
                    }
                }
            }

            // Make a copy of the old ID and Parent ID
            $old_id     = $table->id;
            $old_parent = $table->parent_id;
            $path       = $table->path;

            // Reset the id because we are making a copy.
            $table->id = 0;

            // If we are copying children, the Old ID will turn up in the parents list
            // otherwise it's a new top level item
            $table->parent_id = isset($parents[$old_parent]) ? $parents[$old_parent] : $dest;

            // Set the new location in the tree for the node.
            $table->setLocation($table->parent_id, 'last-child');

            $table->level    = null;
            $table->asset_id = null;
            $table->lft      = null;
            $table->rgt      = null;

            // Alter the title & alias
            list($title, $alias) = $this->generateNewTitle($table->parent_id, $table->title, '');
            $table->title = $title;
            $table->alias = $alias;

            // Store the row.
            if (!$table->store()) {
                $this->setError($table->getError());
                return false;
            }

            // Get the new item ID
            $new_id = $table->get('id');

            // Rebuild the path for the directory
            if (!$table->rebuildPath($new_id)) {
                $this->setError($table->getError());
                return false;
            }

            // Make a physical copy
            $this->copyPhysical($table->project_id, $path, $table->path);

            // Rebuild the paths of the directory children
            if (!$table->rebuild($table->id, $table->lft, $table->level, $table->path)) {
                $this->setError($table->getError());
                return false;
            }

            // Add the new ID to the array
            $new_ids[$i] = $new_id;
            $i++;

            // Now we log the old 'parent' to the new 'parent'
            $parents[$old_id] = $table->id;
            $count--;
        }

        // Rebuild the hierarchy.
        if (!$table->rebuild($rbid)) {
            $this->setError($table->getError());
            return false;
        }

        // Rebuild the tree path.
        if (!$table->rebuildPath($table->id)) {
            $this->setError($table->getError());
            return false;
        }

        // Copy the notes and files in the directories
        if (count($parents)) {
            $config     = array('ignore_request' => true);
            $suffix     = ((Factory::getApplication()->isClient('site')) ? 'Form' : '');
            $note_model = $this->getInstance('Note' . $suffix, 'JPrepoModel', $config);
            $file_model = $this->getInstance('File' . $suffix, 'JPrepoModel', $config);

            foreach($parents AS $old => $new)
            {
                // Get notes
                $query->clear()
                      ->select('id, access')
                      ->from('#__jp_repo_notes')
                      ->where('dir_id = ' . (int) $old);

                $this->_db->setQuery($query);
                $notes = (array) $this->_db->loadObjectList();

                // Get files
                $query->clear()
                      ->select('id, access')
                      ->from('#__jp_repo_files')
                      ->where('dir_id = ' . (int) $old);

                $this->_db->setQuery($query);
                $files = (array) $this->_db->loadObjectList();

                // Check notes access
                $pks = array();
                foreach ($notes AS $item)
                {
                    if ($is_admin || in_array($item->access, $levels)) {
                        $pks[] = $item->id;
                    }
                }

                $notes = $pks;

                // Check files access
                $pks = array();
                foreach ($files AS $item)
                {
                    if ($is_admin || in_array($item->access, $levels)) {
                        $pks[] = $item->id;
                    }
                }

                $files = $pks;

                // Process notes
                if (count($notes)) {
                    if (!$note_model->batchCopy($new, $notes, $contexts)) {
                        $this->setError($note_model->getError());
                        return false;
                    }
                }

                // Process files
                if (count($files)) {
                    if (!$file_model->batchCopy($new, $files, $contexts)) {
                        $this->setError($file_model->getError());
                        return false;
                    }
                }
            }
        }

        return $new_ids;
    }


    /**
     * Method to get the record form.
     *
     * @param     array      $data        Data for the form.
     * @param     boolean    $loadData    True if the form is to load its own data (default case), false if not.
     *
     * @return    mixed                   A JForm object on success, false on failure
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_jprepo.directory', 'directory', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) return false;

        $jinput = Factory::getApplication()->input;
        $user   = Factory::getApplication()->getIdentity();
        $id     = (int) $jinput->get('id', 0);

        // Always disable these fields while saving
        $form->setFieldAttribute('alias', 'filter', 'unset');

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
                $query = $this->_db->getQuery(true);

                $query->select('project_id')
                      ->from('#__jp_repo_dirs')
                      ->where('id = ' . $id);

                $this->_db->setQuery($query);
                $form->setValue('project_id', null, (int) $this->_db->loadResult());
            }
        }

        return $form;
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
        $date       = Factory::getDate();

        $table    = $this->getTable();
        $pk       = (!empty($data['id'])) ? $data['id'] : (int) $this->getState($this->getName() . '.id');
        $is_new   = true;
        $old_path = null;

        // Include the content plugins for the on save events.
        PluginHelper::importPlugin('content');

        // Load the row if saving an existing item.
        if ($pk > 0) {
            if ($table->load($pk)) {
                $is_new = false;

                if (!empty($table->path)) $old_path = $table->path;
            }
        }

        // Prevent project id override for existing items
        if (!$is_new) $data['project_id'] = $table->project_id;

        // Make sure the title and alias are always unique
        list($title, $alias) = $this->generateNewTitle($data['parent_id'], $data['title'], '', $pk);

        $data['title'] = $title;
        $data['alias'] = $alias;

        // If we're not creating a new project repo...
        if (!$this->getState('create_repo')) {
            // Don't allow the creation of new folders in root
            if ($data['parent_id'] <= 1 && $is_new) {
                $this->setError(Text::_('COM_JOOMPROJECT_ERROR_REPO_SAVE_ROOT_DIR'));
                return false;
            }

            // Don't allow new folders to be protected
            if (isset($data['protected'])) $data['protected'] = 0;
        }

        // Set the new parent id if parent id not matched OR while New/Save as Copy.
        if ($table->parent_id != $data['parent_id'] || $is_new) {
            // Fix: Folder cannot be parent of self
            if ($data['parent_id'] != $table->id) {
                $table->setLocation($data['parent_id'], 'last-child');
            }
            else {
                $data['parent_id'] = $table->parent_id;
            }
        }

        // Handle permissions and access level
        if (isset($data['rules'])) {
            $access = JPAccessHelper::getViewLevelFromRules($data['rules'], intval($data['access']));

            if ($access) $data['access'] = $access;
        }
        else {
            if ($is_new) {
                // Let the table class find the correct access level
                $data['access'] = 0;
            }
            elseif (isset($data['access'])) {
                // Keep the existing access in the table
                unset($data['access']);
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
        // Clear the cache
        $this->cleanCache();

        // Trigger the onContentAfterSave event.
        $dispatcher->triggerEvent($this->event_after_save, array($this->option . '.' . $this->name, &$table, $is_new,$data));

        // Add to watch list - if not opt-out
        if ($is_new) {
            $plugin  = PluginHelper::getPlugin('content', 'jpnotifications');
            $params  = new Registry($plugin->params);
            $opt_out = (int) $params->get('sub_method', 0);

            if (!$opt_out) {
                $cid = array($table->id);

                if (!$this->watch($cid, 1)) {
                    return false;
                }
            }
        }



        // Store the labels
        if (isset($data['labels'])) {
            $labels = $this->getInstance('Labels', 'JPModel', $config = array());

            $labels->getState('item.project');
            $labels->setState('item.project', $table->project_id);
            $labels->setState('item.id', $table->id);

            if (!$labels->saveRefs($data['labels'],'com_jprepo.directory')) {
                return false;
            }
        }

        // Rebuild the path for the directory
        if (!$table->rebuildPath($table->id)) {
            $this->setError($table->getError());
            return false;
        }

        // Update physical path
        if (!$is_new) {
            $this->savePhysical($table->project_id, $old_path, $table->path);
        }

        // Rebuild the paths of the directory children
        if (!$table->rebuild($table->id, $table->lft, $table->level, $table->path)) {
            $this->setError($table->getError());
            return false;
        }

        // Create physical path
        if ($is_new) {
            $this->savePhysical($table->project_id, $table->path);
        }

        // Set id state
        $this->setState($this->getName() . '.id', $table->id);



        return true;
    }


    /**
     * Method to delete one or more records.
     *
     * @param     array      $pks              An array of record primary keys.
     * @param     bool       $ignore_access    If true, ignore permission and just delete
     *
     * @return    boolean                      True if successful, false if an error occurs.
     */
    public function delete(&$pks, $ignore_access = false)
    {
        $dispatcher = \Joomla\CMS\Factory::getApplication();

        $pks   = (array) $pks;
        $query = $this->_db->getQuery(true);

        // Include the content plugins for the on delete events.
        PluginHelper::importPlugin('content');

        // Get model instances
        $config     = array('ignore_request' => true);
        $suffix     = (Factory::getApplication()->isClient('site') ? 'Form' : '');
        $note_model = $this->getInstance('Note' . $suffix, 'JPrepoModel', $config);
        $file_model = $this->getInstance('File' . $suffix, 'JPrepoModel', $config);
        $sub_table  = $this->getTable();
        $table      = $this->getTable();

        // Iterate over the items to delete each one.
        foreach ($pks as $i => $pk)
        {
            // Try to load the item from the db
            if (!$table->load($pk)) {
                $this->setError($table->getError());
                return false;
            }

            // Check delete permission (includes check on sub-dirs, notes and files)
            if (!$ignore_access) {
                if (!$this->canDelete($table)) {
                    // Prune items that you can't change.
                    unset($pks[$i]);

                    $error = $this->getError();

                    if ($error) {
                        \Joomla\CMS\Factory::getApplication()->enqueueMessage( $error,'warning');
                    }
                    else {
                        \Joomla\CMS\Factory::getApplication()->enqueueMessage( Text::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'),'warning');
                    }

                    return false;
                }
            }

            // Trigger the onContentBeforeDelete event.
            $context = $this->option . '.' . $this->name;
            $result  = $dispatcher->triggerEvent($this->event_before_delete, array($context, $table));

            if (in_array(false, $result, true)) {
                $this->setError($table->getError());
                return false;
            }

            // Get all sub-directories
            $query->clear()
                  ->select('id')
                  ->from('#__jp_repo_dirs')
                  ->where('lft > ' . (int) $table->lft)
                  ->where('rgt < ' . (int) $table->rgt)
                  ->order('level DESC');

            $this->_db->setQuery($query);
            $sub_dirs = (array) $this->_db->loadColumn();

            $dirs   = $sub_dirs;
            $dirs[] = (int) $table->id;

            $where = 'dir_id ' . (count($sub_dirs) == 1 ? '= ' . $dirs[0] : 'IN(' . implode(', ', $dirs) . ')');

            // Get all notes
            $query->clear()
                  ->select('id')
                  ->from('#__jp_repo_notes')
                  ->where($where);

            $this->_db->setQuery($query);
            $notes = (array) $this->_db->loadColumn();

            // Get all files
            $query->clear()
                  ->select('id')
                  ->from('#__jp_repo_files')
                  ->where($where);

            $this->_db->setQuery($query);
            $files = (array) $this->_db->loadColumn();

            // Delete all notes
            if (count($notes)) {
                if (!$note_model->delete($notes, $ignore_access)) {
                    $this->setError($note_model->getError());
                    return false;
                }
            }

            // Delete all files
            if (count($files)) {
                if (!$file_model->delete($files, $ignore_access)) {
                    $this->setError($file_model->getError());
                    return false;
                }
            }

            $basepath = JPrepoHelper::getBasePath();
            $cmp_path = Path::clean($basepath . '/');

            // Delete all sub-dirs
            if (count($sub_dirs)) {
                foreach ($sub_dirs AS $sub_dir)
                {
                    // Try to load the item from the db
                    if (!$sub_table->load($sub_dir)) {
                        $this->setError($sub_table->getError());
                        return false;
                    }

                    if (!$sub_table->delete((int) $sub_dir)) {
                        $this->setError($sub_table->getError());
                        return false;
                    }

                    // Delete physical path if exists

                    $fullpath = Path::clean($basepath . '/' . $sub_table->path);

                    if (\Joomla\CMS\Filesystem\Folder::exists($fullpath) && $fullpath != $cmp_path && strpos($fullpath, $cmp_path) === 0) {
                        \Joomla\CMS\Filesystem\Folder::delete($fullpath);
                    }
                }
            }

            // And finally, delete this dir
            if (!$table->delete($pk)) {
                $this->setError($table->getError());
                return false;
            }

            // Delete physical path if exists
            $fullpath = Path::clean($basepath . '/' . $table->path);

            if (\Joomla\CMS\Filesystem\Folder::exists($fullpath) && $fullpath != $cmp_path && strpos($fullpath, $cmp_path) === 0) {
                \Joomla\CMS\Filesystem\Folder::delete($fullpath);
            }

            // Trigger the onContentAfterDelete event.
            $dispatcher->triggerEvent($this->event_after_delete, array($context, $table));
        }

        // Clear the component's cache
        $this->cleanCache();

        return true;
    }


    /**
     * Method to watch an item
     *
     * @param    array      $pks      The items to watch
     * @param    integer    $value    1 to watch, 0 to unwatch
     * @param    integer    $uid      The user id to watch the item
     */
    public function watch(&$pks, $value = 1, $uid = null)
    {
        $user  = Factory::getUser($uid);
        $table = $this->getTable();
        $pks   = (array) $pks;

        $is_admin = $user->authorise('core.admin', $this->option);
        $my_views = $user->getAuthorisedViewLevels();
        $projects = array();

        $item_type = 'com_jprepo.directory';

        // Access checks.
        foreach ($pks as $i => $pk) {
            $table->reset();

            if ($table->load($pk)) {
                if (!$is_admin && !in_array($table->access, $my_views)) {
                    unset($pks[$i]);
                    \Joomla\CMS\Factory::getApplication()->enqueueMessage( Text::_('JERROR_ALERTNOAUTHOR'),'warning');
                    $this->setError(Text::_('JERROR_ALERTNOAUTHOR'));
                    return false;
                }

                $projects[$pk] = (int) $table->project_id;
            }
            else {
                unset($pks[$i]);
            }
        }

        // Attempt to watch/unwatch the selected items
        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        foreach ($pks AS $i => $pk)
        {
            $query->clear();

            if ($value == 0) {
                $query->delete('#__jp_ref_observer')
                      ->where('item_type = ' . $db->quote( $item_type ) )
                      ->where('item_id = ' . $db->quote((int) $pk))
                      ->where('user_id = ' . $db->quote((int) $user->get('id')));

                $db->setQuery($query);


                try
                {
                    $db->execute();
                }
                catch (RuntimeException $e)
                {
                    $this->setError($e->getMessage());
                    return false;
                }
            }
            else {
                $query->select('COUNT(*)')
                      ->from('#__jp_ref_observer')
                      ->where('item_type = ' . $db->quote( $item_type ) )
                      ->where('item_id = ' . $db->quote((int) $pk))
                      ->where('user_id = ' . $db->quote((int) $user->get('id')));

                $db->setQuery($query);
                $count = (int) $db->loadResult();

                if (!$count) {
                    $data = new stdClass;

                    $data->user_id   = (int) $user->get('id');
                    $data->item_type = $item_type;
                    $data->item_id   = (int) $pk;
                    $data->project_id= (int) $projects[$pk];



                    try
                    {
                        $db->insertObject('#__jp_ref_observer', $data);
                    }
                    catch (RuntimeException $e)
                    {
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
     * Method to physically create or move a directory
     *
     * @param     array      $data    The directory data
     *
     * @return    boolean             True on success
     */
    protected function savePhysical($project, $path, $dest = null)
    {
        if (!$project) return false;

        if (trim($path) == '') {
            return false;
        }

        $base        = JPrepoHelper::getBasePath();
        $path_exists = \Joomla\CMS\Filesystem\Folder::exists($base . '/' . $path);


        // Create new directory?
        if (empty($dest)) {
            if ($path_exists) return true;

            return \Joomla\CMS\Filesystem\Folder::create($base . '/' . $path);
        }

        $dest_exists = \Joomla\CMS\Filesystem\Folder::exists($base . '/' . $dest);

        if ($dest == $path) return true;

        // Move existing dir
        if ($path_exists && !$dest_exists) {
            return \Joomla\CMS\Filesystem\Folder::move($base . '/' . $path, $base . '/' . $dest);
        }

        // Create new dir at destination
        if (!$path_exists && !$dest_exists) {
            return \Joomla\CMS\Filesystem\Folder::create($base . '/' . $dest);
        }

        return true;
    }


    /**
     * Method to physically copy directory
     *
     * @param     array      $data    The directory data
     *
     * @return    boolean             True on success
     */
    protected function copyPhysical($project, $path, $dest)
    {
        if (!$project) return false;

        $base        = JPrepoHelper::getBasePath();
        $path_exists = \Joomla\CMS\Filesystem\Folder::exists($base . '/' . $path);
        $dest_exists = \Joomla\CMS\Filesystem\Folder::exists($base . '/' . $dest);

        // Do nothing if the path does not exist or if the destination already exists
        if (!$path_exists || $dest_exists) return true;

        return \Joomla\CMS\Filesystem\Folder::copy($base . '/' . $path, $base . '/' . $dest);
    }


    /**
     * Method to check if a project still exists
     *
     * @param     integer    $project    The project id to check
     *
     * @return    boolean                True if not found, False if found.
     */
    protected function isOrphaned($project)
    {
        static $cache = array();

        // Check the cache
        if (isset($cache[$project])) return $cache[$project];

        $query = $this->_db->getQuery(true);

        $query->select('id')
              ->from('#__jp_projects')
              ->where('id = ' . (int) $project);

        $this->_db->setQuery($query);
        $cache[$project] = ($this->_db->loadResult() > 0 ? false : true);

        return $cache[$project];
    }


    /**
     * Method to change the title.
     *
     * @param     integer    $parent_id    The parent directory
     * @param     string     $title        The directory title
     * @param     string     $alias        The current alias
     * @param     integer    $id           The directory id
     *
     * @return    string                   Contains the new title
     */
    protected function generateNewTitle($parent_id, $title, $alias = '', $id = 0)
    {
        $table = $this->getTable();
        $query = $this->_db->getQuery(true);

        if (empty($alias)) {
            $alias = Joomla\CMS\Application\ApplicationHelper::stringURLSafe($title);

            if (trim(str_replace('-', '', $alias)) == '') {
                $alias = Joomla\CMS\Application\ApplicationHelper::stringURLSafe(Factory::getDate()->format('Y-m-d-H-i-s'));
            }
        }

        $query->select('COUNT(id)')
              ->from($table->getTableName())
              ->where('alias = ' . $this->_db->quote($alias))
              ->where('parent_id = ' . (int) $parent_id);

        if ($id) $query->where('id != ' . intval($id));

        $this->_db->setQuery($query);
        $count = (int) $this->_db->loadResult();

        // No duplicates found?
        if (!$count) return array($title, $alias);

        // Generate new title
        while ($table->load(array('alias' => $alias, 'parent_id' => $parent_id)))
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

        return array($title, $alias);
    }


    /**
     * Custom clean the cache of com_joomproject and joomproject modules
     *
     * @return    void
     */
    protected function cleanCache($group = 'com_jprepo', $client_id = 0)
    {
        parent::cleanCache($group, $client_id);
    }


    /**
     * Method to test whether a record can be deleted.
     *
     * @param     object     A record object.
     *
     * @return    boolean    True if allowed to delete the record.
     */
    protected function canDelete($record)
    {
        if (empty($record->id) || ($record->protected == '1' && !$this->isOrphaned($record->project_id))) {
            return false;
        }

        $user   = Factory::getApplication()->getIdentity();
        $levels = $user->getAuthorisedViewLevels();

        // Check if admin first
        if ($user->authorise('core.admin')) {
            return true;
        }

        // Check delete permission on the folder
        if (!$user->authorise('core.delete', 'com_jprepo.directory.' . (int) $record->id)) {
            return false;
        }

        // Check delete permissions for sub-folders
        $query = $this->_db->getQuery(true);

        $query->select('id, access')
              ->from('#__jp_repo_dirs')
              ->where('lft > ' . (int) $record->lft)
              ->where('rgt < ' . (int) $record->rgt);

        $this->_db->setQuery($query);

        $items = (array) $this->_db->loadObjectList();
        $dirs  = array((int) $record->id);

        foreach ($items AS $i => $item)
        {
            $can_access = in_array($item->access, $levels);
            $can_delete = $user->authorise('core.delete', 'com_jprepo.directory.' . (int) $item->id);

            if (!$can_access || !$can_delete) return false;

            $dirs[] = (int) $item->id;
        }

        $count = count($dirs);
        $where = 'dir_id ' . ($count == 1 ? '= ' . $dirs[0] : 'IN(' . implode(', ', $dirs) . ')');

        // Check all notes
        $query->clear()
              ->select('id, access')
              ->from('#__jp_repo_notes')
              ->where($where);

        $this->_db->setQuery($query);
        $items = (array) $this->_db->loadObjectList();

        foreach ($items AS $i => $item)
        {
            $can_access = in_array($item->access, $levels);
            $can_delete = $user->authorise('core.delete', 'com_jprepo.note.' . (int) $item->id);

            if (!$can_access || !$can_delete) return false;
        }

        // Check all files
        $query->clear()
              ->select('id, access')
              ->from('#__jp_repo_files')
              ->where($where);

        $this->_db->setQuery($query);
        $items = (array) $this->_db->loadObjectList();

        foreach ($items AS $i => $item)
        {
            $can_access = in_array($item->access, $levels);
            $can_delete = $user->authorise('core.delete', 'com_jprepo.file.' . (int) $item->id);

            if (!$can_access || !$can_delete) return false;
        }

        return true;
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
        $user = Factory::getApplication()->getIdentity();

        // Check for existing item.
        if (!empty($record->id)) {
            return $user->authorise('core.edit.state', 'com_jprepo.directory.' . (int) $record->id);
        }
        elseif (!empty($record->parent_id)) {
            // New item, so check against the parent dir.
            return $user->authorise('core.edit.state', 'com_jprepo.directory.' . (int) $record->parent_id);
        }
        else {
            // Default to component settings.
            return parent::canEditState('com_jprepo');
        }
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
        $user = Factory::getApplication()->getIdentity();
        if (empty($record->id)) {
            return $user->authorise('core.edit', 'com_jprepo');
        }

        $access      = JPrepoHelper::getActions('file',$record->id);
        $asset = 'com_jprepo.directory.' . (int) $record->id;

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
        $data = Factory::getApplication()->getUserState('com_jprepo.edit.' . $this->getName() . '.data', array());

        if (empty($data)) {
            $data = $this->getItem();

            // Set default values
            if ($this->getState($this->getName() . '.id') == 0) {
                $active_id = JPApplicationHelper::getActiveProjectId();

                $data->set('project_id', $active_id);
                $data->set('parent_id', $this->getState($this->getName() . '.parent_id'));
            }
        }

        return $data;
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
        $pk     = \Joomla\CMS\Factory::getApplication()->input->getUInt($key);
        $option = \Joomla\CMS\Factory::getApplication()->input->get('option');

        $this->setState($this->getName() . '.id', $pk);

        if ($pk && $option == $this->option) {
            $table = $this->getTable();

            if ($table->load($pk)) {
                $project = (int) $table->project_id;
                $this->setState($this->getName() . '.project', $project);
                JPApplicationHelper::setActiveProject($project);

                $parent_id = (int) $table->parent_id;
                $this->setState($this->getName() . '.parent_id', $parent_id);
            }
        }
        else {
            $parent_id = \Joomla\CMS\Factory::getApplication()->input->getUInt('filter_parent_id', 0);
            $this->setState($this->getName() . '.parent_id', $parent_id);

            $project = JPApplicationHelper::getActiveProjectId('filter_project');

            if ($project) {
                $this->setState($this->getName() . '.project', $project);
            }
        }

        // Load the parameters.
        $value = ComponentHelper::getParams($this->option);
        $this->setState('params', $value);
    }
}
