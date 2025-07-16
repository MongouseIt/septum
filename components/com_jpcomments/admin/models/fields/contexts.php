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
class JFormFieldContexts extends ListField
{
    /**
     * The form field type.
     *
     * @var    string
     */
    public $type = 'contexts';


    /**
     * Method to get the field list options markup.
     *
     * @return    array      $options      The list options markup.
     */
    public function getOptions() {


        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('DISTINCT a.context')
            ->from('#__jp_comments AS a')
            ->where('a.alias != ' . $db->quote('root'))
            ->order('a.context ASC');

        // Setup the query
        $db->setQuery($query, 0, 50);
        $items = (array) $db->loadColumn();

        $options = array();

        foreach($items AS $value)
        {
            $obj     = new stdClass();
            $context = str_replace('.', '_', strtoupper($value)) . '_TITLE';

            $obj->value = $value;
            $obj->text  = Text::_($context);

            $options[] = $obj;
        }


        // Merge any additional options in the XML definition.
        $result = array_merge(parent::getOptions(), $options);

        return $result;

    }
}
