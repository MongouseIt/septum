<?php
/**
 * @package      Joomproject.Library
 * @subpackage   Form
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 20012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */


defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Form\FormField;

jimport('joomla.html.html');
jimport('joomla.form.formfield');


/**
 * Field to enter a monetary, decimal value.
 *
 */
class JFormFieldMoney extends FormField
{
    /**
     * The form field type.
     *
     * @var    string
     */
    public $type = 'Money';


    /**
     * Method to get the user field input markup.
     *
     * @return    string    The field input markup.
     */
    protected function getInput()
    {
        static $js_loaded = false;

        if ($js_loaded === false) {
            // Make sure the JS is loaded only once
            $js_loaded = true;
            $script    = $this->getJavascript();

            Factory::getDocument()->addScriptDeclaration(implode("\n", $script));
        }

        $task_id = (int) $this->form->getValue('task_id');
        $id      = (int) $this->form->getValue('id');

        if ($task_id && (!$this->value || $this->value == '0.00')) {
            $db = Factory::getDbo();
            $query = $db->getQuery(true);

            $query->select('rate')
                  ->from('#__jp_tasks')
                  ->where('id = ' . $task_id);

            $db->setQuery($query);
            $task_rate = $db->loadResult();

            if ($task_rate) {
                $this->value = $task_rate;
            }
        }

        // Setup field values
        if ($this->value) {
            $v1 = substr($this->value, 0, -3);
            $v2 = substr($this->value, -2);
        }
        else {
            $this->value = '0.00';
            $v1 = '0';
            $v2 = '00';

            // Pre-fill from previously entered value
            if (empty($id) || $id === '0') {
                $cached = Factory::getApplication()->getUserState('com_joomproject.' . $this->id);

                if ($cached && $cached != '0.00') {
                    $this->value = $cached;

                    $v1 = substr($this->value, 0, -3);
                    $v2 = substr($this->value, -2);
                }
            }
        }

        // Get params
        $params = JPApplicationHelper::getProjectParams();
        $globalParams = ComponentHelper::getParams('com_joomproject');

        // Initialize some field attributes.
        $attribs = array();
        $attribs['readonly']  = ((string) $this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
        $attribs['disabled']  = ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
        $attribs['maxlength'] = ((int) $this->element['maxlength'] != '') ? $this->element['maxlength'] : 4;
        $attribs['currency']  = $params->get('currency_sign', $globalParams->get('currency_sign','$'));
        $attribs['decimal']   = $params->get('decimal_delimiter', $globalParams->get('decimal_delimiter','.'));
        $attribs['position']  = (int) $params->get('currency_position', $globalParams->get('currency_position',0));

        if ($attribs['readonly'] == '' && $attribs['disabled'] == '') {
            $attribs['onchange'] = ' onchange="setMoneyFieldValue(\'' . $this->id  . '\');"';
        }
        else {
            $attribs['onchange'] = '';
        }

        // Get HTML
        $html = $this->getHTML($v1, $v2, $attribs);

        // Return HTML
        return implode("\n", $html);
    }


    /**
     * Method to generate the input markup.
     *
     * @param     string    $v1
     * @param     string    $v2
     * @param     string    $attribs
     *
     * @return    string                The html field markup
     */
    protected function getHTML($v1, $v2, $attribs)
    {
        $params = \Joomla\CMS\Component\ComponentHelper::getParams('com_joomproject');

        $attribs['currency'] = is_null($attribs['currency']) ? $params->get('currency_sign','$') :  $attribs['currency'];
        $attribs['decimal'] = is_null($attribs['decimal']) ? $params->get('decimal_delimiter','.') :  $attribs['decimal'];


        $html = array();
        $html[] = '<div class="input-group moneyFieldGroup">';
        if ($attribs['position'] == 0) {
            $html[] = '<span class="input-group-text">' . $attribs['currency'] . '</span>';
        }


        $html[] = '<input type="text" name="' . $this->name . '_v1" id="' . $this->id . '_v1" size="10" class="span4 form-control" '
            . 'maxlength="' . $attribs['maxlength'] . '" value="' . $v1 . '" ' . $attribs['onchange'] . $attribs['disabled'] . $attribs['readonly'] . '/>';

        $html[] = '<span class="input-group-text">' . $attribs['decimal'] . '</span>';

        $html[] = '<input type="text" name="' . $this->name . '_v2" id="' . $this->id . '_v2" size="10" class="span2 form-control" '
            . 'maxlength="2" value="' . $v2 . '" ' . $attribs['onchange'] . $attribs['disabled'] . $attribs['readonly'] . '/>';
        $html[] = '<input type="hidden" name="' . $this->name . '" id="' . $this->id . '" value="'
            . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '" ' . $attribs['disabled'] . $attribs['readonly'] . '/>';

        if ($attribs['position'] == '1') {
            $html[] = '<span class="input-group-text">' . $attribs['currency'] . '</span>';
        }

        $html[] = '</div>';

        return $html;
    }




    protected function getJavascript()
    {
        $script = array();

        $script[] = "function setMoneyFieldValue(fid)";
        $script[] = "{";
        $script[] = "    var v1 = document.getElementById(fid + '_v1').value;";
        $script[] = "    var v2 = document.getElementById(fid + '_v2').value;";
        $script[] = "    document.getElementById(fid).value = v1 + '.' + v2;";
        $script[] = "}";

        return $script;
    }

    protected function getRenderer($layoutId = 'default')
    {

        $renderer = new FileLayout($layoutId, null,
            [
                "client" => 1,
                'component' => 'com_joomproject'
            ]
        );

        return $renderer;
    }
}
