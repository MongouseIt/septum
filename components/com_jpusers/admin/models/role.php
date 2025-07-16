<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_joomproject
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;



class JPusersModelRole extends AdminModel
{

    /**
     * Prepare and sanitise the table data prior to saving.
     *
     * @param JTable $table A JTable object.
     *
     * @return  void
     *
     * @since   1.6
     */


    /**
     * Returns a Table object, always creating it.
     *
     * @param string $type The table type to instantiate
     * @param string $prefix A prefix for the table class name. Optional.
     * @param array $config Configuration array for model. Optional.
     *
     * @return  JTable    A database object
     */
    public function getTable($type = 'Roles', $prefix = 'JPTable', $config = array())
    {
        return Table::getInstance($type, $prefix, $config);
    }


    /**
     * Method to get the record form.
     *
     * @param array $data Data for the form.
     * @param boolean $loadData True if the form is to load its own data (default case), false if not.
     *
     * @return  JForm|boolean  A JForm object on success, false on failure
     *
     * @since   1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
        $app = Factory::getApplication();
        $user = Factory::getApplication()->getIdentity();

        // Get the form.
        $form = $this->loadForm('com_jpusers.role', 'role', array('control' => 'jform', 'load_data' => $loadData));


        if (empty($form)) {
            return false;
        }

        $jinput = Factory::getApplication()->input;

        /*
         * The front end calls this model and uses a_id to avoid id clashes so we need to check for that first.
         * The back end uses id so we use that the rest of the time and set it to 0 by default.
         */
        $id = $jinput->get('a_id', $jinput->get('id', 0));

        // Determine correct permissions to check.

        // Check for existing article.
        // Modify the form based on Edit State access controls.
        if ($id != 0 && (!$user->authorise('core.edit.state', 'com_joomproject.role.' . (int)$id))
            || ($id == 0 && !$user->authorise('core.edit.state', 'com_joomproject'))) {
            // Disable fields for display.
            $form->setFieldAttribute('ordering', 'disabled', 'true');
            $form->setFieldAttribute('published', 'disabled', 'true');

            // Disable fields while saving.
            // The controller has already verified this is an article you can edit.
            $form->setFieldAttribute('ordering', 'filter', 'unset');
            $form->setFieldAttribute('published', 'filter', 'unset');
        }

        // Prevent messing with article language and category when editing existing article with associations


        return $form;
    }


    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed  The data for the form.
     *
     * @since   1.6
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $app = Factory::getApplication();
        $input = $app->input;
        $data = $app->getUserState('com_jpusers.edit.role.data', array());


        if (empty($data)) {
            $data = $this->getItem();

            // Pre-select some filters (Status, Category, Language, Access) in edit form if those have been selected in Article Manager: Articles
            if ($this->getState('a.role') == 0) {
                $filters = (array)$app->getUserState('com_jpusers.role.filter');
                $data->set(
                    'state',
                    $app->input->getInt(
                        'state',
                        ((isset($filters['published']) && $filters['published'] !== '') ? $filters['published'] : null)
                    )
                );
            }
        }else{

            $data['permissions'] = $this->formatActions($data['permissions']);

        }

        $this->preprocessData('com_jpusers.role', $data);

        return $data;
    }

    /**
     * Method to validate the form data.
     *
     * @param JForm $form The form to validate against.
     * @param array $data The data to validate.
     * @param string $group The name of the field group to validate.
     *
     * @return  array|boolean  Array of filtered data if valid, false otherwise.
     *
     * @see     JFormRule
     * @see     InputFilter
     * @since   3.7.0
     */
    public function validate($form, $data, $group = null)
    {

        return parent::validate($form, $data, $group);
    }

    public function getItem($pk = null)
    {
        $item = parent::getItem($pk); // TODO: Change the autogenerated stub

        $item->permissions = $this->getPermissions();

        return $item;
    }

    public function getPermissions(){
        // Get a db connection.
        $db = Factory::getDbo();

        $query = $db->getQuery(true);

        $query->select('*');
        $query->from('#__jp_users_role_action_map');
        $query->where('role_id = ' . $this->getState($this->getName() . '.id'));

        $db->setQuery($query);

// Load the results as a list of stdClass objects (see later for more options on retrieving data).
        $permissions = $db->loadObjectList();

        foreach ($permissions as &$permission){
            $permission = $permission->context.'.'.$permission->action;
        }


        return $permissions;

    }

    public function save($data)
    {
        $input = Factory::getApplication()->input;
        $filter = InputFilter::getInstance();
        $date = Factory::getDate();


        if (isset($data['created_by_alias'])) {
            $data['created_by_alias'] = $filter->clean($data['created_by_alias'], 'TRIM');
        }
        if (empty($data['created_by'])) {
            $data['created_by'] = Factory::getApplication()->getIdentity()->id;
        }
        if (empty($data['created'])) {
            $data['created'] = $date->toSql();
        }

        $data['modified'] = $date->toSql();

        $result = parent::save($data);

        if (!$result)
            return false;

        $this->updateActions($data['permissions']);


        return true;
    }


    private function updateActions($permissions)
    {

        // remove any old permissions
        $this->removeActions();

        // add new permissions
        $this->addActions($permissions);
    }

    private function formatActions($permissions){


        $formattedPermissions = [];

        foreach ($permissions as $extension => $list){

            foreach ($list as $type => $permission){

                foreach ($permission as $name => $v){

                    if($v != 'on')
                        continue;

                    $formattedPermissions[] = "$extension.$type.$name";

                }


            }


        }

        return $formattedPermissions;

    }


    private function removeActions()
    {
        $db = Factory::getDbo();

        $query = $db->getQuery(true);

// delete all custom keys for user 1001.
        $conditions = array(
            $db->quoteName('role_id') . ' = ' . $this->getState($this->getName() . '.id')
        );

        $query->delete($db->quoteName('#__jp_users_role_action_map'));
        $query->where($conditions);

        $db->setQuery($query);

        $result = $db->execute();


    }

    private function addActions($extensionsPermissions)
    {

        foreach ($extensionsPermissions as $extension => $permissions) {

            foreach ($permissions as $type => $selectedPermissions) {

                $item = new stdClass();
                $item->role_id = $this->getState($this->getName() . '.id');
                $item->context = "$extension.$type";


                foreach ($selectedPermissions as $permission => $value) {

                    if ($value != 'on')
                        continue;

                    $item->action = $permission;

                    Factory::getDbo()->insertObject('#__jp_users_role_action_map', $item, 'role_id');

                }


            }


        }

    }


}
