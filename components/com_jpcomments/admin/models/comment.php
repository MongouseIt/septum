<?php
/**
 * @package      Joomproject
 * @subpackage   Comments
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\AdminModel;


jimport('joomla.application.component.modeladmin');


/**
 * Item Model for a Comment form.
 *
 */
class JPcommentsModelComment extends AdminModel
{
    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     */
    protected $text_prefix = 'COM_JOOMPROJECT_COMMENT';


    /**
     * Returns a Table object, always creating it.
     *
     * @param     string    The table type to instantiate
     * @param     string    A prefix for the table class name. Optional.
     * @param     array     Configuration array for model. Optional.
     *
     * @return    jtable    A database object
     */
    public function getTable($type = 'Comment', $prefix = 'JPtable', $config = array())
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
        $form = $this->loadForm('com_jpcomments.comment' . 'comment', 'comment', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) return false;

        $jinput = Factory::getApplication()->input;
        $id     = $jinput->get('id', 0);

        $item_access = JPcommentsHelper::getActions($id);
        $access      = JPcommentsHelper::getActions();

        // Check for existing item.
        // Modify the form based on Edit State access controls.
        if (($id != 0 && !$item_access->get('core.edit.state')) || ($id == 0 && !$access->get('core.edit.state'))) {
            // Disable fields for display.
            $form->setFieldAttribute('state', 'disabled', 'true');
            $form->setFieldAttribute('state', 'filter', 'unset');
        }

        // Check if the project, context and context item are given
        $project_id = (int) $form->getValue('project_id');
        $item_id    = $form->getValue('item_id');
        $context    = $form->getValue('context');

        if (!$project_id) {
            $form->setValue('project_id', null, $this->getState($this->getName() . '.project'));
        }
        if (!$item_id) {
            $form->setValue('item_id', null, $this->getState($this->getName() . '.item_id'));
        }
        if (!$context) {
            $form->setValue('context', null, $this->getState($this->getName() . '.context'));
        }

        return $form;
    }


    /**
     * Method to save a comment
     *
     * @param     array      $data    The comment data
     *
     * @return    boolean             True on success, False on error
     */
    public function save($data)
    {



        // Initialise variables;
        $table = $this->getTable();
        $pk    = (!empty($data['id'])) ? $data['id'] : (int) $this->getState($this->getName() . '.id');
        $date  = Factory::getDate();
        $isNew = true;

        // Include the content plugins for the on save events.
        PluginHelper::importPlugin('content');
        $dispatcher = \Joomla\CMS\Factory::getApplication();

        // Load the row if saving an existing comment.
        if ($pk > 0) {
            $table->load($pk);
            $isNew = false;
        }

        // Set the new parent id if parent id not matched OR while New/Save as Copy.
        if ($table->parent_id != $data['parent_id'] || $data['id'] == 0) {
            $table->setLocation($data['parent_id'], 'last-child');
        }

        // Get title if not set
        if (!isset($data['title'])) {
            $data['title'] = $this->generateNewTitle($data['item_id'], $data['context']);
        }
        elseif (empty($data['title'])) {
            $data['title'] = $this->generateNewTitle($data['item_id'], $data['context']);
        }

        // Generate an alias if not set
        if (!isset($data['alias'])) {
            $data['alias'] = $date->toSql();
        }
        elseif (empty($data['alias'])) {
            $data['alias'] = $date->toSql();
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
        $result = $dispatcher->triggerEvent($this->event_before_save, array($this->option . '.' . $this->name, &$table, $isNew,$data));




        // Store the data.
        if (!$table->store()) {

            $this->setError($table->getError());
            return false;
        }


        // Rebuild the path for the comment:
        if (!$table->rebuildPath($table->id)) {
            $this->setError($table->getError());
            return false;
        }

        // Rebuild the paths of the comment children:
        if (!$table->rebuild($table->id, $table->lft, $table->level, $table->path)) {
            $this->setError($table->getError());
            return false;
        }

        $this->setState($this->getName() . '.id', $table->id);

        // Clear the cache
        $this->cleanCache();

        // Trigger the onContentAfterSave event.
        $dispatcher->triggerEvent($this->event_after_save, array($this->option . '.' . $this->name, &$table, $isNew));

        return true;
    }


    /**
     * Method to change the title.
     *
     * @param     integer    $item_id    The id of the context item.
     * @param     string     $context    The context.
     * @param     mixed      $strict     Not used.
     *
     * @return    string                 Contains the new title
     */
    protected function generateNewTitle($item_id, $context, $strict = null)
    {
        $db    = Factory::getDbo();
        $query = $db->getQuery(true);
        $asset = $context . '.' . $item_id;

        // Handle user profile comments exception
        if ($context == 'com_jpusers.user') {
            $query->select('name')
                  ->from('#__users')
                  ->where('id = ' . $db->quote($item_id));

            $db->setQuery((string) $query);
            $title = $db->loadResult();
        }
        else {
            // Lookup the assets table for the title
            $query->select('title')
                  ->from('#__assets')
                  ->where('name = ' . $db->quote($asset));

            $db->setQuery((string) $query);
            $title = $db->loadResult();
        }

        if (empty($title)) {
            // No title found.
            $title = $asset;
        }

        return $title;
    }


    /**
     * Custom clean the cache of com_joomproject and joomproject modules
     *
     */
    protected function cleanCache($group = 'com_jpcomments', $client = 0)
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
        if (!empty($record->id)) {
            if ($record->state != -2) return false;

            $user  = Factory::getApplication()->getIdentity();
            $asset = 'com_jpcomments.comment.' . (int) $record->id;

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
     * @return    boolean    True if allowed to delete the record.
     */
    protected function canEditState($record)
    {
        if (!empty($record->id)) {
            $user  = Factory::getApplication()->getIdentity();
            $asset = 'com_jpcomments.comment.' . (int) $record->id;

            return ($user->authorise('core.edit', $asset) || ($user->authorise('core.edit.own', $asset) && $record->created_by == $user->id));
        }

        return parent::canEditState($record);
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

        // Check for existing item.
        if (!empty($record->id)) {
            $asset  = 'com_jpcomments.comment.' . (int) $record->id;

            return ($user->authorise('core.edit', $asset) || ($user->authorise('core.edit.own', $asset) && $record->created_by == $user->id));
        }

        return $user->authorise('core.edit', 'com_jpcomments');
    }


    /**
     * Method to get the data that should be injected in the form.
     *
     * @return    mixed    The data for the form.
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = Factory::getApplication()->getUserState('com_jpcomments.edit.' . $this->getName() . '.data', array());

        if (empty($data)) $data = $this->getItem();

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
		$pk = \Joomla\CMS\Factory::getApplication()->input->getInt($key);
		$this->setState($this->getName() . '.id', $pk);

        if ($pk) {
            $table = $this->getTable();

            if ($table->load($pk)) {
                $project = (int) $table->project_id;
                $this->setState($this->getName() . '.project', $project);
                JPApplicationHelper::setActiveProject($project);

                $item_id = (int) $table->item_id;
                $this->setState($this->getName() . '.item_id', $item_id);

                $context = $table->context;
                $this->setState($this->getName() . '.context', $context);
            }
        }
        else {
            $item_id = \Joomla\CMS\Factory::getApplication()->input->getUInt('filter_item_id', 0);
            $this->setState($this->getName() . '.item_id', $item_id);

            $context = \Joomla\CMS\Factory::getApplication()->input->getCmd('filter_context', '');
            $this->setState($this->getName() . '.context', $context);

            $project = (int) JPApplicationHelper::getActiveProject('filter_project');

            if ($project) {
                $this->setState($this->getName() . '.project', $project);
                JPApplicationHelper::setActiveProject($project);
            }

        }

		// Load the parameters.
		$value = ComponentHelper::getParams($this->option);
		$this->setState('params', $value);
    }
}
