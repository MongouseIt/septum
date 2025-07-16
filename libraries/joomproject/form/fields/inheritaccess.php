<?php
/**
 * @package      Joomproject
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 20012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Form\Field\ListField;


jimport('joomla.form.helper');
FormHelper::loadFieldClass('list');
require_once JPATH_ADMINISTRATOR . '/components/com_joomproject/helpers/joomproject.php';


/**
 * Provides a list of access levels. Access levels control what users in specific
 * groups can see.
 *
 */
class JFormFieldInheritAccess extends ListField
{
    /**
     * The form field type.
     *
     * @var    string
     **/
    public $type = 'InheritAccess';

    protected $parents;

    protected $hidden;


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
        $parents['project']   = (int) $this->form->getValue('project_id');
        $parents['milestone'] = (int) $this->form->getValue('milestone_id');
        $parents['tasklist']  = (int) $this->form->getValue('list_id');
        $parents['task']      = (int) $this->form->getValue('task_id');
        $parents['topic']     = (int) $this->form->getValue('topic_id');

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
        $count   = count($options);

        $html = '';

        if ($count == 1) {
            $this->value = $options[0]->value;
            $this->form->setValue($this->fieldname, $this->group, $this->value);
        }
        else {
            $this->value = (int) Factory::getConfig()->get('access');
            $this->form->setValue($this->fieldname, $this->group, $this->value);
        }

        // Generate the list
        return HTMLHelper::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id) . $html;
    }


    /**
     * Method to get the field list options markup.
     *
     * @return    array    $options    The list options markup
     */
    protected function getOptions()
    {
        $parents   = $this->parents;
        $options   = array();
        $user      = Factory::getApplication()->getIdentity();
        $view      = (string) \Joomla\CMS\Factory::getApplication()->input->getCmd('view');
        $parent_el = 'project';
        $parent_id = $parents['project'];
        $is_admin  = $user->authorise('core.admin', 'com_joomproject');
        $groups    = $user->getAuthorisedViewLevels();

        foreach($parents AS $key => $value)
        {
            if ($value > 0) {
                $parent_el = $key;
                $parent_id = $value;
                break;
            }
        }

        // Get the table of the parent element
        if ($parent_id) {
            Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_joomproject/tables');

            $table = Table::getInstance($parent_el, 'JPTable');
            if (!$table) return $options;

            // Load the parent item
            if (!$table->load($parent_id)) return $options;

            if ($this->hidden) {
                $options[] = HTMLHelper::_('select.option', (int) $table->access,
                    'access',
                    'value',
                    'text'
                );

                return $options;
            }

            // Load access level title
            $db    = Factory::getDbo();
            $query = $db->getQuery(true);

            $query->select('a.title')
                  ->from('#__viewlevels AS a')
                  ->where('a.id = '.(int) $table->access);

            $db->setQuery((string) $query);
            $access_title = $db->loadResult();

            // Add option to inherit access from parent element
            $text = 'COM_JOOMPROJECT_OPTION_INHERIT_FROM_' . strtoupper($parent_el);

            $options[] = HTMLHelper::_('select.option', (int) $table->access,
                sprintf(Text::alt($text,
                preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)), $access_title),
                'value',
                'text'
            );

            // Find access level children
            $levels = array();
            $tree   = JoomprojectHelperAccess::getAccessTree($table->access);

            if (count($tree)) {
                $query->clear();
                $query->select('a.id AS value, a.title AS text')
                      ->from('#__viewlevels AS a');

                if (count($tree) > 1) {
                    $query->where('a.id IN(' . implode(',', $tree) . ')');
                }
                else {
                    $query->where('a.id = ' . $db->quote($tree[0]));
                }

                $db->setQuery((string) $query);
                $levels = (array) $db->loadObjectList();
            }
        }
        else {
            // Load access levels
            $db    = Factory::getDbo();
            $query = $db->getQuery(true);

            $query->select('a.id AS value, a.title AS text')
                  ->from('#__viewlevels AS a');

            $db->setQuery((string) $query);
            $levels = (array) $db->loadObjectList();
        }

        $pks = array();

        foreach($levels AS $level)
        {
            if (!$is_admin && !isset($groups[$level->value])) {
                continue;
            }

            $pks[] = (int) $level->value;

            // Create a new option object based on the <option /> element.
            $opt = HTMLHelper::_('select.option', (int) $level->value,
                Text::alt(trim((string) $level->text),
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
