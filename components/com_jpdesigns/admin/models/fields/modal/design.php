<?php
/**
 * @package      Joomproject Pro
 * @subpackage   Designs
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Form\FormField;


/**
 * Supports a modal design picker.
 *
 */
class JFormFieldModal_Design extends FormField
{
    /**
     * The form field type.
     *
     * @var    string    
     */
    protected $type = 'Modal_Design';

    /**
     * Method to get the field input markup.
     *
     * @return    string    The field input markup.
     */
    protected function getInput()
    {
        // Load the modal behavior script.
        HTMLHelper::_('behavior.modal', 'a.modal');

        // Build the script.
        $script = array();
        $script[] = '    function jSelectDesign_' . $this->id . '(id, title, catid, object) {';
        $script[] = '        document.getElementById("' . $this->id . '_id").value = id;';
        $script[] = '        document.getElementById("' . $this->id . '_name").value = title;';
      //  $script[] = '        SqueezeBox.close();';
        $script[] = '    }';

        // Add the script to the document head.
        Factory::getDocument()->addScriptDeclaration(implode("\n", $script));

        // Setup variables for display.
        $html = array();
        $link = 'index.php?option=com_jpdesigns&amp;view=designs&amp;layout=modal&amp;tmpl=component&amp;function=jSelectDesign_' . $this->id;

        $db = Factory::getDBO();
        $db->setQuery(
            'SELECT title' .
            ' FROM #__jp_designs' .
            ' WHERE id = '.(int) $this->value
        );

        try {
            $title = $db->loadResult();
        }
        catch (RuntimeException $e) {
            \Joomla\CMS\Factory::getApplication()->enqueueMessage( $e->getMessage(),'warning');
        }


        if (empty($title)) {
            $title = Text::_('COM_JPDESIGNS_SELECT_A_DESIGN');
        }

        $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

        // The current user display field.
        $html[] = '<span class="input-append">';
        $html[] = '<input type="text" class="input-medium" id="' . $this->id . '_name" value="' . $title . '" disabled="disabled" size="35" />';
        $html[] = '<a class="modal btn" title="'.Text::_('COM_JPDESIGNS_CHANGE_DESIGN').'"  href="' . $link . '&amp;' . Session::getFormToken() . '=1"';
        $html[] = ' rel="{handler: \'iframe\', size: {x: 800, y: 450}}"><i class="icon-file"></i> ' . Text::_('JSELECT') . '</a>';
        $html[] = '</span>';

        // The active design id field.
        $value = ((0 == (int) $this->value) ? '' : (int) $this->value);

        // class='required' for client side validation
        $class = ($this->required ? ' class="required modal-value"' : '');

        $html[] = '<input type="hidden" id="' . $this->id . '_id"' . $class . ' name="' . $this->name . '" value="' . $value . '" />';

        return implode("\n", $html);
    }
}
