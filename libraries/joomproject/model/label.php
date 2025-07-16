<?php
/**
 * @package      Joomproject.Library
 * @subpackage   Model
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 20012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;



/**
 * Item Model for a project label.
 *
 */
class JPModelLabel extends AdminModel
{
    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     */
    protected $text_prefix = 'COM_JOOMPROJECT_LABEL';


    /**
     * Constructor.
     *
     * @param    array          $config    An optional associative array of configuration settings.
     *
     * @see      jcontroller
     */
    public function __construct($config = array())
    {
       // Register dependencies
       JLoader::register('JoomprojectHelperAccess', JPATH_ADMINISTRATOR . '/components/com_joomproject/helpers/access.php');

       parent::__construct($config);
    }


    /**
     * Returns a Table object, always creating it.
     *
     * @param     string    The table type to instantiate
     * @param     string    A prefix for the table class name. Optional.
     * @param     array     Configuration array for model. Optional.
     *
     * @return    jtable    A database object
     */
    public function getTable($type = 'Label', $prefix = 'JPTable', $config = array())
    {
        return Table::getInstance($type, $prefix, $config);
    }


    public function getForm($data = array(), $loadData = true)
    {
        return false;
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
            // TODO?
        }

        return $item;
    }


    /**
     * Method to save a label reference.
     *
     * @param     array      $data    The form data.
     *
     * @return    boolean             True on success, False on error.
     */
    public function saveRef($data)
    {
        $dispatcher = \Joomla\CMS\Factory::getApplication();
        $table      = $this->getTable('LabelRef');
        $key        = $table->getKeyName();
        $pk         = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
        $isNew      = true;

        // Include the content plugins for the on save events.
        PluginHelper::importPlugin('content');

        // Allow an exception to be thrown.
        try {
            // Load the row if saving an existing record.
            if ($pk > 0) {
                $table->load($pk);
                $isNew = false;
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
            $result = $dispatcher->triggerEvent(
                $this->event_before_save,
                array($this->option . '.' . $this->name,
                    &$table,
                    $isNew,
                    (array) $data
                ));

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
            $dispatcher->triggerEvent($this->event_after_save, array($this->option . '.' . $this->name, &$table, $isNew));
        }
        catch (Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }

        $pkName = $table->getKeyName();

        if (isset($table->$pkName)) {
            $this->setState($this->getName() . '.id', $table->$pkName);
        }

        $this->setState($this->getName() . '.new', $isNew);

        return true;
    }


    /**
     * Method to delete one or more label references.
     *
     * @param     array  &    $pks    An array of record primary keys.
     *
     * @return    boolean             True if successful, false if an error occurs.
     */
    public function deleteRef(&$pks)
    {
        // Initialise variables.
        $dispatcher = \Joomla\CMS\Factory::getApplication();
        $pks = (array) $pks;
        $table = $this->getTable('LabelRef');

        // Include the content plugins for the on delete events.
        PluginHelper::importPlugin('content');

        // Iterate the items to delete each one.
        foreach ($pks as $i => $pk)
        {
            if ($table->load($pk)) {
                if ($this->canDelete($table)) {

                    $context = $this->option . '.' . $this->name;

                    // Trigger the onContentBeforeDelete event.
                    $result = $dispatcher->triggerEvent($this->event_before_delete, array($context, $table));

                    if (in_array(false, $result, true)) {
                        $this->setError($table->getError());
                        return false;
                    }

                    if (!$table->delete($pk)) {
                        $this->setError($table->getError());
                        return false;
                    }

                    // Trigger the onContentAfterDelete event.
                    $dispatcher->triggerEvent($this->event_after_delete, array($context, $table));

                }
                else {
                    // Prune items that you can't change.
                    unset($pks[$i]);
                    $error = $this->getError();

                    if ($error) {
                        \Joomla\CMS\Factory::getApplication()->enqueueMessage( $error,'warning');
                        return false;
                    }
                    else {
                        \Joomla\CMS\Factory::getApplication()->enqueueMessage( Text::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'),'warning');
                        return false;
                    }
                }
            }
            else {
                $this->setError($table->getError());
                return false;
            }
        }

        // Clear the component's cache
        $this->cleanCache();

        return true;
    }


    /**
     * Custom clean the cache of com_joomproject and joomproject modules
     *
     */
    protected function cleanCache($group = 'com_joomproject', $client = 0)
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
        return true;
    }
}
