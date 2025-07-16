<?php
/**
 * @package      Joomproject.Library
 * @subpackage   Fields
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 20012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;



/**
 * Form Field class for selecting labels.
 *
 */
class JFormFieldLabels extends FormField
{
    /**
     * The form field type.
     *
     * @var    string
     */
    public $type = 'Labels';


    /**
     * The selected labels
     *
     * @var    array
     */
    protected $items;


    /**
     * Method to get the field input markup.
     *
     * @return    string    The html field markup
     */
    protected function getInput()
    {
        if (!is_array($this->value)) {
            $this->value = array();
        }

        $this->items = array();

        foreach ($this->value AS $item)
        {
            if (isset($item->label_id)) {
                $this->items[] = $item->label_id;
            }
        }

        $html = $this->getHTML();

        return implode("\n", $html);
    }


    /**
     * Method to generate the input markup.
     *
     * @return    string              The html field markup
     */
    protected function getHTML()
    {
        if (Factory::getApplication()->isClient('site') || version_compare(JVERSION, '3.0.0', 'ge')) {
            return $this->getSiteHTML();
        }

        return $this->getAdminHTML();
    }


    /**
     * Method to generate the backend input markup.
     *
     * @return    array     $html     The html field markup
     */
    protected function getAdminHTML()
    {
        $html  = array();
        $items = $this->getLabels();

        $html[] = '<fieldset class="checkboxes">';
        $html[] = '<ul>';

        foreach ($items AS $item)
        {
            $checked = (in_array($item->id, $this->items) ? ' checked="checked"' : '');

            $html[] = '<li>';
            $html[] = '<label>';
            $html[] = '<input type="checkbox" class="inputbox" name="' . $this->name . '[]" value="' . (int) $item->id . '"' . $checked . '/>';
            $html[] = htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8');
            $html[] = '</label>';
            $html[] = '</li>';
        }

        $html[] = '</ul>';
        $html[] = '</fieldset>';
        $html[] = '<input type="hidden" name="' . $this->name . '[]" id="' . $this->id . '" value=""/>';

        return $html;
    }


    /**
     * Method to generate the frontend input markup.
     *
     * @return    array     $html     The html field markup
     */
    protected function getSiteHTML()
    {
        $html  = array();
        $items = $this->getLabels();

        $html[] = '<fieldset class="checkboxes">';
        $html[] = '<div class="border p-3 rounded">';

        foreach ($items AS $k => $item)
        {
            $checked = (in_array($item->id, $this->items) ? ' checked="checked"' : '');
            $class   = ($item->style != '' ? ' ' . $item->style : '');


            $html[] = '<div class="form-check">';
            $html[] = '<input type="checkbox" class="form-check-input" id="' . $this->name.'-'. $k . '" name="' . $this->name . '[]" value="' . (int) $item->id . '"' . $checked . '/>';
            $html[] = '<label class="form-check-label" for="' . $this->name.'-'. $k . '">';
            $html[] = '<span class="badge ' . $class . '"><i class="fas fa-bookmark"></i> ' . htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8') . '</span>';
            $html[] = '</label>';
            $html[] = '</div>';
        }

        $html[] = '</div>';
        $html[] = '<div class="clearfix clr"></div>';
        $html[] = '</fieldset>';
        $html[] = '<input type="hidden" name="' . $this->name . '[]" id="' . $this->id . '" value=""/>';

        return $html;
    }


    /**
     * Method to get the the available labels
     *
     * @return    array      $script    The generated javascript
     */
    protected function getLabels()
    {
        $asset   = $this->element['asset'] ? $this->element['asset'] : 'com_jpprojects.project';
        $project = (int) $this->form->getValue('project_id');

        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        if (!$project) {
            return array();
        }

        $query->select('a.id, a.title, a.style')
              ->from('#__jp_labels AS a')
              ->where('a.project_id = ' . $db->quote((int) $project));

        if ($asset) {
            $query->where('(a.asset_group = ' . $db->quote($db->escape($asset)) . ' OR a.asset_group = ' . $db->quote('com_jpprojects.project') . ')');
        }
        else {
            $query->where('a.asset_group = ' . $db->quote('com_jpprojects.project'));
        }

        $query->order('a.asset_group, a.title ASC');

        $db->setQuery($query);
        $items = (array) $db->loadObjectList();

        return $items;
    }
}
