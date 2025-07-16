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
class JFormFieldExtensions extends ListField
{
    /**
     * The form field type.
     *
     * @var    string
     */
    public $type = 'Extensions';

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

        $user  = Factory::getApplication()->getIdentity();

        $query->select('a.name AS value')
            ->from('#__extensions AS a')
            ->join('INNER', '#__user_activity_item_types AS t ON t.extension = a.name')
            ->where('a.enabled = 1');

        // Implement View Level Access
        if (!$user->authorise('core.admin', 'com_jpactivities')) {
            $levels   = $user->getAuthorisedViewLevels();
            $levels[] = 0;

            $query->where('a.access IN (' . implode(',', $levels) . ')');
        }

        $query->group('a.name');
        $query->order('a.name ASC');

        $db->setQuery($query);
        $values = $db->loadColumn();

        if (empty($values)) return parent::getOptions();

        $items = array();

        // Go through each item
        foreach ($values AS $i => $value)
        {
            $items[$i] = new stdClass();

            $items[$i]->value = $value;
            $items[$i]->text  = Text::_(strtoupper($value) . '_ACTIVITY');
        }

        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $items);

        return $options;

    }
}
