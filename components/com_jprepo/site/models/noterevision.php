<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jprepo
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;

// Base this model on the backend version.
require_once dirname(__FILE__) . '/noteform.php';


/**
 * Joomproject Component Note Revision Form Model
 *
 */
class JPrepoModelNoteRevision extends JPrepoModelNoteForm
{
    /**
     * Returns a Table object, always creating it.
     *
     * @param     string    The table type to instantiate
     * @param     string    A prefix for the table class name. Optional.
     * @param     array     Configuration array for model. Optional.
     *
     * @return    jtable    A database object
     */
    public function getTable($type = 'NoteRevision', $prefix = 'JPtable', $config = array())
    {
        return Table::getInstance($type, $prefix, $config);
    }


    /**
     * Method to get a single record.
     *
     * @param     integer    The id of the primary key.
     * @return    mixed      Object on success, false on failure.
     */
    public function getItem($pk = null)
    {
        $pk    = (!empty($pk)) ? (int) $pk : (int) $this->getState($this->getName() . '.id');
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

        // Get the author name
        $query = $this->_db->getQuery(true);

        $query->select('name')
              ->from('#__users')
              ->where('id = ' . (int) $item->created_by);

        $this->_db->setQuery($query);
        $item->author_name = $this->_db->loadResult();

        return $item;
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
        return false;
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
        // Initialise variables;
        $table  = $this->getTable();
        $pk     = (!empty($data['id'])) ? $data['id'] : (int) $this->getState($this->getName() . '.id');
        $is_new = true;

        // Load the row if saving an existing item.
        if ($pk > 0) {
            if ($table->load($pk)) {
                $is_new = false;
            }
            else {
                $pk = 0;
            }
        }

        // Make sure the title and alias are always unique
        $data['alias'] = '';
        list($title, $alias) = $this->generateNewTitle($data['parent_id'], $data['title'], $data['alias'], $pk);

        $data['title'] = $title;
        $data['alias'] = $alias;

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

        // Store the data.
        if (!$table->store()) {
            $this->setError($table->getError());
            return false;
        }

        $this->setState($this->getName() . '.id', $table->id);

        // Clear the cache
        $this->cleanCache();

        return true;
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
        $condition = 'parent_id = '  . (int) $table->parent_id;

        return array($condition);
    }


    /**
     * Prepare and sanitise the table data prior to saving.
     *
     * @param     jtable    A JTable object.
     *
     * @return    void
     */
    protected function prepareTable(&$table)
    {
        // Reorder the items within the category so the new item is first
        if (empty($table->id)) {
            $condition = 'parent_id = ' . (int) $table->parent_id;
            $query     = $this->_db->getQuery(true);

            $query->select('ordering')
                  ->from('#__jp_repo_note_revs')
                  ->where('parent_id = ' . (int) $table->parent_id)
                  ->order('ordering DESC');

            $this->_db->setQuery($query, 0, 1);
            $ordering = (int) $this->_db->loadResult();
            $ordering++;

            $table->ordering = $ordering;

            $table->reorder($condition);
        }
    }


    /**
     * Method to change the title.
     *
     * @param     integer    $parent_id    The parent file
     * @param     string     $title     The directory title
     * @param     string     $alias     The current alias
     * @param     integer    $id        The note id
     *
     * @return    string                Contains the new title
     */
    protected function generateNewTitle($parent_id, $title, $alias = '', $id = 0)
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
              ->where('parent_id = ' . (int) $parent_id);

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
        }

        return array($title, $alias);
    }


    /**
     * Method to auto-populate the model state.
     * Note. Calling getState in this method will result in recursion.
     *
     * @return    void
     */
    protected function populateState()
    {
        $app = Factory::getApplication();

        // Load state from the request.
        $pk = \Joomla\CMS\Factory::getApplication()->input->getInt('id');
        $this->setState($this->getName() . '.id', $pk);

        $return = \Joomla\CMS\Factory::getApplication()->input->get('return', null, 'base64');
        $this->setState('return_page', base64_decode($return));

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

            $project = (int) $app->getUserStateFromRequest('com_joomproject.project.active.id', 'filter_project', '');

            if ($project) {
                $this->setState($this->getName() . '.project', $project);
                JPApplicationHelper::setActiveProject($project);
            }
            elseif ($dir_id) {
                $table = $this->getTable('Directory');

                if ($table->load($dir_id)) {
                    $project = (int) $table->project_id;

                    $this->setState($this->getName() . '.project', $project);
                    JPApplicationHelper::setActiveProject($project);
                }
            }
        }

        // Load the parameters.
        $params = $app->getParams();
        $this->setState('params', $params);

        $this->setState('layout', \Joomla\CMS\Factory::getApplication()->input->getCmd('layout'));
    }
}
