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

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;


jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomproject.library');


/**
 * Form Field class for selecting a task dependency.
 *
 */
class JFormFieldTaskDependency extends FormField
{
    /**
     * The form field type.
     *
     * @var    string
     */
    public $type = 'TaskDependency';


    /**
     * Method to get the field input markup.
     *
     * @return    string    The html field markup
     */
    protected function getInput()
    {
        // Add the script to the document head.
        $script = $this->getJavascript();
        Factory::getDocument()->addScriptDeclaration(implode("\n", $script));

        if (!is_array($this->value)) {
            $this->value = array();
        }

        $project = (int) $this->form->getValue('project_id');
        $hidden  = '<input type="hidden" name="' . $this->name . '[]" value="" />';

        if (!$project) {
            $project = JPApplicationHelper::getActiveProjectId();
        }

        if (!$project) {
            return '<span class="readonly">' . Text::_('COM_JOOMPROJECT_FIELD_PROJECT_REQ') . '</span>' . $hidden;
        }

        $html = $this->getHTML($project);

        return implode("\n", $html);
    }


    /**
     * Method to generate the input markup.
     *
     * @return    string    The html field markup
     */
    protected function getHTML($project)
    {
        if (Factory::getApplication()->isClient('site') || version_compare(JVERSION, '3.0.0', 'ge')) {
            return $this->getSiteHTML($project);
        }

        return $this->getAdminHTML($project);
    }


    /**
     * Method to generate the backend input markup.
     *
     * @return    array    $html    The html field markup
     */
    protected function getAdminHTML($project)
    {
        $html    = array();
        $options = $this->getOptions($project);

        $html[] = '<ul id="' . $this->id . '_list" class="unstyled"> style="margin-top:20px"';

        foreach($this->value AS $item)
        {
            $html[] = '<li>';
            $html[] = '<div class="button2-left form-group"><div class="blank input-group">';
            $html[] = HTMLHelper::_('select.genericlist', $options, $this->name . '[]', 'class="form-control"', 'value', 'text', $item);
            $html[] = '<a class="btn btn-danger btn-min btn-sm" href="javascript:void(0);" onclick="jpRemoveTaskDependency_' . $this->id . '(this);">';
            $html[] = Text::_('COM_JOOMPROJECT_FIELD_CLEAR_LABEL');
            $html[] = '</a>';
            $html[] = '</div></div>';
            $html[] = '<div class="clr"></div>';
            $html[] = '</li>';
        }

        $html[] = '</ul>';

        // Add a hidden list and dropdown which serves as template for the JS-add butotn
        $html[] = '<ul style="display:none !important;margin-top: 20px">';
        $html[] = '<li>';
        $html[] = '<div class="button2-left form-group"><div class="blank input-group">';
        $html[] = HTMLHelper::_('select.genericlist', $options, $this->name . '[]', 'class="form-control"', 'value', 'text', null, $this->id);
        $html[] = '<a class="btn btn-danger btn-min btn-sm" href="javascript:void(0);" onclick="jpRemoveTaskDependency_' . $this->id . '(this);">';
        $html[] = Text::_('COM_JOOMPROJECT_FIELD_CLEAR_LABEL');
        $html[] = '</a>';
        $html[] = '</div></div>';
        $html[] = '<div class="clr"></div>';
        $html[] = '</li>';
        $html[] = '</ul>';

        // Create the "add" button.
        if ($this->element['readonly'] != 'true') {
            $html[] = '<div class="button2-left"><div class="blank">';
            $html[] = '<a class="btn btn-primary" href="javascript:void(0);" onclick="jpAddTaskDependency_' . $this->id . '();">';
            $html[] = Text::_('JACTION_ADD_DEPENDENCY');
            $html[] = '</a>';
            $html[] = '</div></div>';
            $html[] = '<div class="clr"></div>';
        }

        return $html;
    }


    /**
     * Method to generate the frontend input markup.
     *
     * @return    array    $html    The html field markup
     */
    protected function getSiteHTML($project)
    {
        $html    = array();
        $options = $this->getOptions($project);

        $html[] = '<ul id="' . $this->id . '_list" class="unstyled" style="margin-top:20px">';

        foreach($this->value AS $item)
        {
            $html[] = '<li>';

            $html[] = '<div class="form-group"><div class="input-group">';
            $html[] = HTMLHelper::_('select.genericlist', $options, $this->name . '[]', 'class="form-control"', 'value', 'text', $item);
            $html[] = '<a class="btn btn-danger btn-min btn-sm" href="javascript:void(0);" onclick="jpRemoveTaskDependency_' . $this->id . '(this);">';
            $html[] = '<i class="fas fa-times"></i> ';
            $html[] = '</a>';
            $html[] = '</div></div>';
            $html[] = '</li>';
        }

        $html[] = '</ul>';

        // Add a hidden list and dropdown which serves as template for the JS-add butotn
        $html[] = '<ul style="display:none !important;margin-top: 20px">';
        $html[] = '<li>';
        $html[] = '<div class="form-group"><div class="input-group">';
        $html[] = HTMLHelper::_('select.genericlist', $options, $this->name . '[]', 'class="form-control"', 'value', 'text', null, $this->id);
        $html[] = '<a class="btn btn-danger btn-min btn-sm" href="javascript:void(0);" onclick="jpRemoveTaskDependency_' . $this->id . '(this);">';
        $html[] = '<i class="fas fa-times"></i> ';
        $html[] = '</a>';
        $html[] = '</div></div>';
        $html[] = '</li>';
        $html[] = '</ul>';

        // Create the "add" button.
        if ($this->element['readonly'] != 'true') {
            $html[] = '<a class="btn btn-primary" href="javascript:void(0);" onclick="jpAddTaskDependency_' . $this->id . '();">';
            $html[] = Text::_('JACTION_ADD_DEPENDENCY');
            $html[] = '</a>';
        }

        return $html;
    }


    /**
     * Generates the javascript needed for this field
     *
     * @param     boolean    $submit    Whether to submit the form or not
     * @param     string     $view      The name of the view
     *
     * @return    array      $script    The generated javascript
     */
    protected function getJavascript()
    {
        $script   = array();
        $onchange = $this->element['onchange'] ? $this->element['onchange'] : '';

        $script[] = 'function jpAddTaskDependency_' . $this->id . '()';
        $script[] = '{';
        $script[] = '    var l = jQuery("#' . $this->id . '_list");';
        $script[] = '    var s = jQuery("#' . $this->id . '").closest("li").clone();';
        $script[] = '    l.append(s);';
        $script[] = '    ' . $onchange;
        $script[] = '}';
        $script[] = 'function jpRemoveTaskDependency_' . $this->id . '(el)';
        $script[] = '{';
        $script[] = '    jQuery(el).closest("li").remove();';
        $script[] = '}';

        return $script;
    }


    /**
     * Method to get the task select options
     *
     * @param     integer    $project    The project id
     * @return    array                  The options
     */
    protected function getOptions($project)
    {
        $app   = Factory::getApplication();
        $db    = Factory::getDbo();
        $query = $db->getQuery(true);
        $opts  = array();

        $ms_exists = JPApplicationHelper::enabled('com_jpmilestones');

        $id   = (int) $this->form->getValue('id');
        $view = $app->input->get('view');

        $query->select('a.id AS value, a.title AS t_title')
              ->select('l.title AS l_title')
              ->from('#__jp_tasks AS a')
              ->join('left', '#__jp_task_lists AS l ON l.id = a.list_id');

        if ($ms_exists) {
            $query->select('m.title AS m_title')
                  ->join('left', '#__jp_milestones AS m ON m.id = a.milestone_id');
        }

        $query->where('a.project_id = ' . (int) $project);

        if ($ms_exists) {
            $query->order('m.title, l.title, a.title ASC');
        }
        else {
            $query->order('l.title, a.title ASC');
        }


        $db->setQuery($query);
        $items = $db->loadObjectList();

        $opts[] = HTMLHelper::_('select.option', '', Text::_('COM_JOOMPROJECT_OPTION_SELECT_TASK'));

        if (empty($items)) return $opts;

        foreach ($items AS $item)
        {
            if ($view == 'taskform' && $id == (int) $item->value) {
                // Skip if the value is the same as the task we're editing
                continue;
            }

            $value = array();

            if ($ms_exists) {
                $value[] = (empty($item->m_title) ? '-' : $item->m_title);
            }

            $value[] = (empty($item->l_title) ? '-' : $item->l_title);
            $value[] = $item->t_title;

            $text = implode('/', $value);

            $opts[] = HTMLHelper::_('select.option', $item->value, $text);
        }

        return $opts;
    }
}
