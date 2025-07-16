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
class JPusersModelTeam extends AdminModel
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
    public function getTable($type = 'Teams', $prefix = 'JPTable', $config = array())
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
        $form = $this->loadForm('com_jpusers.team', 'team', array('control' => 'jform', 'load_data' => $loadData));


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
        if ($id != 0 && (!$user->authorise('core.edit.state', 'com_joomproject.team.' . (int)$id))
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
        $data = $app->getUserState('com_jpusers.edit.team.data', array());


        if (empty($data)) {

            $data = $this->getItem();

            // Pre-select some filters (Status, Category, Language, Access) in edit form if those have been selected in Article Manager: Articles
            if ($this->getState('a.team') == 0) {
                $filters = (array)$app->getUserState('com_jpusers.team.filter');
                $data->set(
                    'state',
                    $app->input->getInt(
                        'state',
                        ((isset($filters['published']) && $filters['published'] !== '') ? $filters['published'] : null)
                    )
                );
            }
        }

        $this->preprocessData('com_jpusers.team', $data);

        return $data;
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



        return true;
    }



}
