<?php
/**
 * @package      Joomproject
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

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
 * Form Field class for selecting a repository directory.
 *
 */
class JFormFieldRepodir extends ListField
{
    /**
     * The form field type.
     *
     * @var    string
     */
    public $type = 'Repodir';


    /**
     * Method to get the field input markup.
     *
     * @return    string    The field input markup.
     */
    protected function getInput()
    {
        // Initialize variables.
        $attr   = '';
        $hidden = '<input type="hidden" id="' . $this->id . '_id" name="' . $this->name . '" value="" />';

        // Initialize some field attributes.
        $attr .= $this->element['class']                         ? ' class="'.(string) $this->element['class'].'"'          : '';
        $attr .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"'                                   : '';
        $attr .= $this->element['size']                          ? ' size="'.(int) $this->element['size'].'"'               : '';
        $attr .= $this->multiple                                 ? ' multiple="multiple"'                                   : '';
        $attr .= $this->element['onchange']                      ? ' onchange="' .(string) $this->element['onchange'] . '"' : '';

        // Get parent item field values.
        $project   = (int) $this->form->getValue('project_id');

        if (!$project) {
            // Cant get list without at least a project id.
            $this->form->setValue($this->element['name'], null, '');
            return '<span class="readonly">' . Text::_('COM_JOOMPROJECT_FIELD_PROJECT_REQ') . '</span>' . $hidden;
        }

        // Get the field options.
        $options = $this->getOptions($project);

        // Return if no options are available.
        if (count($options) == 0) {
            $this->form->setValue($this->element['name'], null, '');
            return '<span class="readonly">' . Text::_('COM_JOOMPROJECT_FIELD_DIRECTORY_EMPTY') . '</span>' . $hidden;
        }

        return HTMLHelper::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);
    }


    /**
     * Method to get the field list options markup.
     *
     * @param     integer    $project      The currently selected project
     *
     * @return    array      $options      The list options markup.
     */
    protected function getOptions($project = 0)
    {
        $options = array();
        $user    = Factory::getApplication()->getIdentity();
        $db      = Factory::getDbo();
        $query   = $db->getQuery(true);

        // Construct the query
        $query->select('a.id AS value, a.path AS text')
              ->from('#__jp_repo_dirs AS a')
              ->where('a.project_id = ' . $project);

        // Implement View Level Access
        if (!$user->authorise('core.admin')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('a.access IN (' . $groups . ')');
        }

        $query->order('a.path');

        $db->setQuery((string) $query);
        $items = (array) $db->loadObjectList();

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
