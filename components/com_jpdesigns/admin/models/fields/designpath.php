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
use Joomla\CMS\Form\FormField;



/**
 * Field to enter the design upload path
 *
 */
class JFormFieldDesignpath extends FormField
{
    /**
     * The form field type.
     *
     * @var    string
     */
    public $type = 'Designpath';


    /**
     * Method to get the user field input markup.
     *
     * @return    string    The field input markup.
     */
    protected function getInput()
    {
        // Initialize some field attributes.
        $attribs = '';
        $attribs .= ((string) $this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
        $attribs .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';

        // Get HTML
        $html = array();
        $html[] = '<span class="readonly" title="::' . str_replace('\\', '/', JPATH_ROOT) .'/' . '">' . Text::_('COM_JPDESIGNS_CONFIG_DESIGN_BASEPATH_SITE_ROOT') . '/</span>';
        $html[] = '<input class="form-control" data-bs-toggle="tooltip" type="text" name="' . $this->name . '" id="' . $this->id . '" size="40" value="'
                . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '" ' . $attribs . '/>';

        // Return HTML
        return implode("\n", $html);
    }
}
