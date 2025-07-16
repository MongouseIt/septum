<?php
/**
 * @package      Joomproject Pro
 * @subpackage   Designs
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
FormHelper::loadFieldClass('list');


/**
 * Form Field class for selecting a design album.
 *
 */
class JFormFieldDesignAlbum extends ListField
{
    /**
     * The form field type.
     *
     * @var    string
     */
    public $type = 'DesignAlbum';


    protected $project;


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
        $project = (int) $this->form->getValue('project_id');

        $this->project   = $project;

        if (!$project) {
            // Cant get list without at least a project id.
            $this->form->setValue($this->element['name'], null, '');
            return '<span class="readonly">' . Text::_('COM_JOOMPROJECT_FIELD_PROJECT_REQ') . '</span>' . $hidden;
        }

        // Get the field options.
        $options = $this->getOptions();

        // Return if no options are available.
        if (count($options) == 0) {
            $this->form->setValue($this->element['name'], null, '');
            return '<span class="readonly">' . Text::_('COM_JOOMPROJECT_FIELD_DESIGN_ALBUM_EMPTY') . '</span>' . $hidden;
        }

        return HTMLHelper::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);
    }


    /**
     * Method to get the field list options markup.
     *
     * @return    array      $options      The list options markup.
     */
    protected function getOptions()
    {
        $options = array();
        $user    = Factory::getApplication()->getIdentity();
        $db      = Factory::getDbo();
        $query   = $db->getQuery(true);

        $project   = $this->project;

        // Get field attributes for the database query
        $state = ($this->element['state']) ? (int) $this->element['state'] : NULL;

        // Build the query
        $query->select('a.id AS value, a.title AS text')
              ->from('#__jp_design_albums AS a')
              ->where('a.project_id = '. (int) $project);

        // Implement View Level Access.
        if (!$user->authorise('core.admin')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('a.access IN (' . $groups . ')');
        }

        // Filter by state
        if (!is_null($state)) $query->where('a.state = ' . $db->quote($state));

        $query->order('a.title');

        $db->setQuery((string) $query);
        $items = (array) $db->loadObjectList();

        // Generate the options
        if (count($items) > 0) {
            $options[] = HTMLHelper::_('select.option', '',
                Text::alt('JOPTION_SELECT_DESIGN_ALBUM',
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
