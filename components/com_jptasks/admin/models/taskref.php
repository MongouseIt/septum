<?php
/**
 * @package      Joomproject
 * @subpackage   Tasks
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;


jimport('joomla.application.component.modeladmin');


/**
 * Item Model for a task dependency.
 *
 */
class JPtasksModelTaskRef extends AdminModel
{
    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     */
    protected $text_prefix = 'COM_JOOMPROJECT_TASK_DEPENDENCY';


    /**
     * Constructor.
     *
     * @param    array          $config    An optional associative array of configuration settings.
     *
     * @see      jcontroller
     */
    public function __construct($config = array())
    {
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
    public function getTable($type = 'TaskRef', $prefix = 'JPtable', $config = array())
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
     * Custom clean the cache of com_joomproject and joomproject modules
     *
     */
    protected function cleanCache($group = 'com_jptasks', $client = 0)
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
        if (!empty($record->task_id)) {
            $user  = Factory::getApplication()->getIdentity();
            $asset = 'com_jptasks.task.' . (int) $record->task_id;

            return $user->authorise('core.delete', $asset);
        }

        return parent::canDelete($record);
    }
}
