<?php
/**
 * @package      Joomproject
 * @subpackage   Tasks
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Form\Field\ListField;

FormHelper::loadFieldClass('list');


/**
 * Form Field class for selecting a task list.
 *
 */
class JFormFieldAssigneduser extends ListField
{
    /**
     * The form field type.
     *
     * @var    string
     */
    public $type = 'Assigneduser';

    protected $project;

    protected $milestone;




    /**
     * Method to get the field list options markup.
     *
     * @return    array      $options      The list options markup.
     */
    public function getOptions() {


        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        $items = array();

        $item = new stdClass();
        $item->value = '0';
        $item->text  = '* ' . Text::_('COM_JPTASKS_UNASSIGNED') . ' *';

        $items[] = $item;

        // Load only if project filter is set
        $project = (int) \Joomla\CMS\Factory::getApplication()->input->getInt('filter_project',0);
        if ($project <= 0) {
            // Make an exception if we are logged in...
            $user = Factory::getApplication()->getIdentity();

            if ($user->id) {
                $item = new stdClass();
                $item->value = $user->id;
                $item->text  = $user->name;

                $items[] = $item;
            }
            return array_merge(parent::getOptions() , $items);
        }


        // Construct the query
        $query->select('u.id AS value, u.name AS text')
            ->from('#__users AS u')
            ->join('INNER', '#__jp_ref_users AS a ON a.user_id = u.id')
            ->join('INNER', '#__jp_tasks AS t ON a.id = a.item_id')
            ->where('a.item_type = ' . $db->quote('com_jptasks.task'))
            ->where('t.project_id = ' . $db->quote($project))
            ->group('u.id')
            ->order('u.name');

        // Return the result
        $db->setQuery($query);
        $result = array_merge($items, (array) $db->loadObjectList());
        return array_merge(parent::getOptions(), $result);

    }
}
