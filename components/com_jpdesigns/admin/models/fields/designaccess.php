<?php
/**
 * @package      Joomproject Pro
 * @subpackage   Designs
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */



use Joomla\CMS\Table\Table;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\FormHelper;
FormHelper::loadFieldClass('InheritAccess');


/**
 * Provides a list of access levels. Access levels control what users in specific
 * groups can see.
 *
 */
class JFormFieldDesignAccess extends JFormFieldInheritAccess
{
    /**
     * The form field type.
     *
     * @var    string
     **/
    public $type = 'DesignAccess';


    /**
     * Method to get the field input markup.
     *
     * @return    string    The field input markup.
     **/
    protected function getInput()
    {
        // Get possible parent field values
        // Note that the order of the array elements matter!
        $parents = array();
        $parents['design']  = (int) $this->form->getValue('parent_id');
        $parents['album']   = (int) $this->form->getValue('album_id');
        $parents['project'] = (int) $this->form->getValue('project_id');

        Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jpdesigns/tables');

        // Initialize some field attributes.
        $attr  = '';
        $attr .= $this->element['class']                         ? ' class="' . (string) $this->element['class'] . '"'       : '';
        $attr .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"'                                    : '';
        $attr .= $this->element['size']                          ? ' size="' . (int) $this->element['size'] . '"'            : '';
        $attr .= $this->multiple                                 ? ' multiple="multiple"'                                    : '';
        $attr .= $this->element['onchange']                      ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';
        $attr .= ((string) $this->element['hidden'] == 'true')   ? ' style="display:none"'                                   : '';

        $this->hidden = ((string) $this->element['hidden'] == 'true');

        // Get the field options
        $this->parents = $parents;
        $options = $this->getOptions();

        // Generate the list
        return HTMLHelper::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);
    }
}
