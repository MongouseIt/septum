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
class JFormFieldEvents extends ListField
{
    /**
     * The form field type.
     *
     * @var    string
     */
    public $type = 'Events';

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

        // Setup the query
        $query->select('id AS value, name AS text')
            ->from('#__user_activity_events')
            ->order('name ASC');

        // Get the items
        $db->setQuery($query);
        $items = (array) $db->loadObjectList();

        // Go through each item
        foreach ($items AS &$item)
        {
            // Translate the component title
            $item->text = Text::_('COM_JPACTIVITIES_EVENT_' . strtoupper($item->text));
        }

        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $items);
        return $options;

    }
}
