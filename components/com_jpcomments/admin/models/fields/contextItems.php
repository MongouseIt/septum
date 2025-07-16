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

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Form\Field\ListField;

FormHelper::loadFieldClass('list');


/**
 * Form Field class for selecting a task list.
 *
 */
class JFormFieldContextItems extends ListField
{
    /**
     * The form field type.
     *
     * @var    string
     */
    public $type = 'ContextItems';


    /**
     * Method to get the field list options markup.
     *
     * @return    array      $options      The list options markup.
     */
    public function getOptions() {


        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        $context = \Joomla\CMS\Factory::getApplication()->input->getString('filter_context','');
        $project =  \Joomla\CMS\Factory::getApplication()->input->getInt('filter_project',0);


        // Context and project filters must be set.
        if (empty($context) || intval($project) <= 0) {
            return array();
        }

        // Construct the query
        $query->select('a.item_id AS value, a.title AS text')
            ->from('#__jp_comments AS a')
            ->where('a.context = ' . $this->_db->quote($context))
            ->where('a.project_id = ' . $this->_db->quote($project))
            ->where('a.alias != ' . $this->_db->quote('root'))
            ->group('a.item_id')
            ->order('a.title ASC');

        // Return the result
        $db->setQuery($query, 0, 50);
        $rows =  (array) $db->loadObjectList();


        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $rows);

        return $options;

    }
}
