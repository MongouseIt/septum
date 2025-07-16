<?php
/**
 * @package      Joomproject
 * @subpackage   Milestones
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Form\Field\ListField;


jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
FormHelper::loadFieldClass('list');


/**
 * Form Field class for selecting a milestone.
 *
 */
class JFormFieldMilestone extends ListField
{
    /**
     * The form field type.
     *
     * @var    string
     */
    public $type = 'Milestone';


    protected $project;


    /**
     * Method to get the field input markup.
     *
     * @return    string    The field input markup.
     */
    protected function getInput()
    {
        $attr   = '';
        $hidden = '<input type="hidden" id="' . $this->id . '_id" name="' . $this->name . '" value="" />';

        // Initialize some field attributes.
        $attr .= $this->element['class']                         ? ' class="'.(string) $this->element['class'].'"'           : '';
        $attr .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"'                                    : '';
        $attr .= $this->element['size']                          ? ' size="'.(int) $this->element['size'].'"'                : '';
        $attr .= $this->multiple                                 ? ' multiple="multiple"'                                    : '';
        $attr .= $this->element['onchange']                      ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

        // Get parent item field values.
        $project = (int) $this->form->getValue('project_id');
        $list    = (int) $this->form->getValue('list_id');

        $this->project = $project;

        if (!$project) {
            // Cant get milestone list without a project id.
            $this->form->setValue($this->element['name'], null, '');
            return '<span class="readonly">' . Text::_('COM_JOOMPROJECT_FIELD_PROJECT_REQ') . '</span>' . $hidden;
        }

        // Get the field options.
        $options = $this->getOptions();

        // Override the selected value based on the selected list
        if ($list && count($options)) {
            $this->value = $this->getListMilestone($list);
        }

        // Return if no options are available.
        if (count($options) == 0) {
            $this->form->setValue($this->element['name'], null, '');
            return '<span class="readonly">' . Text::_('COM_JOOMPROJECT_FIELD_MILESTONE_EMPTY') . '</span>' . $hidden;
        }

        // Generate the list.
        return HTMLHelper::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);
    }


    /**
     * Method to get the milestone of a task list.
     *
     * @param     integer    $list         The currently selected task list
     *
     * @return    integer    $milestone    The milestone id
     */
    protected function getListMilestone($list)
    {
        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select('milestone_id')
              ->from('#__jp_task_lists')
              ->where('id = ' . (int) $list);

        $db->setQuery((string) $query);
        $milestone = (int) $db->loadResult();

        if (!$milestone) {
            $milestone = $this->value;
        }

        return $milestone;
    }


    /**
     * Method to get the field list options markup.
     *
     * @return    array      $options    The list options markup.
     */
    protected function getOptions()
    {

        $options = array();
        $user    = Factory::getApplication()->getIdentity();
        $db      = Factory::getDbo();
        $query   = $db->getQuery(true);
        $project = $this->project;

        // Get field attributes for the database query
        $state = ($this->element['state']) ? (int) $this->element['state'] : NULL;

        // Build the query
        $query->select('a.id AS value, a.title AS text')
              ->from('#__jp_milestones AS a')
              ->where('a.project_id = '. (int) $project);

        // Implement View Level Access
        if (!$user->authorise('core.admin', 'com_joomproject')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('a.access IN (' . $groups . ')');
        }

        // Filter state
        if (!is_null($state)){
            $query->where('a.state = ' . $db->quote($state));
        }else{



            $displayArchivedMilestones = ComponentHelper::getParams('com_jpmilestones')->get('display_archived_milestones',1);

            if($displayArchivedMilestones)
                $state = 'a.state IN (1,2)';
            else
                $state = 'a.state = 1';

            $query->where($state);

        }

        $query->group('a.id')
              ->order('a.title');

        $db->setQuery((string) $query);
        $items = (array) $db->loadObjectList();


        // Generate the options
        if (count($items) > 0) {
            $options[] = HTMLHelper::_('select.option', '',
                Text::alt('COM_JOOMPROJECT_OPTION_SELECT_MILESTONE',
                preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)),
                'value',
                'text'
            );
        }

        foreach($items AS $item)
        {
            // Create a new option object based on the <option /> element.
            $opt = HTMLHelper::_('select.option', (string) $item->value,
                Text::alt(trim((string) $item->text),
                preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)),
                'value',
                'text'
            );

            // Add the option object to the result set.
            $options[] = $opt;
        }

        reset($options);

        return $options;
    }
}
