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


use Joomla\CMS\Form\FormField;



/**
 * Field to enter the design upload path
 *
 */
class JFormFieldMemLimit extends FormField
{
    /**
     * The form field type.
     *
     * @var    string
     */
    public $type = 'MemLimit';


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

        $limit = $this->getMemoryLimit();

        if ($limit) {
            $limit = round(($limit / 1024) / 1024);
        }

        // Get HTML
        $html = array();
        $html[] = '<span class="readonly">From ' . $limit . ' Mb to:</span>';
        $html[] = '<input class="inputbox input-small" type="text" name="' . $this->name . '" id="' . $this->id . '" maxlength="4" size="10" value="'
                . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '" ' . $attribs . '/>';

        // Return HTML
        return implode("\n", $html);
    }


    protected function getMemoryLimit()
    {
        $mem = ini_get('memory_limit');

        if (empty($mem)) return false;

        $mem   = trim($mem);
        $short = strtolower($mem[strlen($mem)-1]);

        $mem = (int) $mem;

        switch($short)
        {
            case 'g':
                $mem *= 1024;

            case 'm':
                $mem *= 1024;

            case 'k':
                $mem *= 1024;
                break;
        }

        return $mem;
    }
}
