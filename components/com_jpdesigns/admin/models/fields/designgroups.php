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

use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\FormHelper;

FormHelper::loadFieldClass('GroupAccess');


/**
 * Form Field class for Joomproject.
 * Field for assigning permissions to groups for a given asset
 * and assigning groups to a new access level
 *
 */
class JFormFieldDesignGroups extends JFormFieldGroupAccess
{
    /**
     * The form field type.
     *
     * @var    string
     */
    public $type = 'DesignGroups';


    /**
     * Method to get the field input markup for Access Control Lists.
     * Optionally can be associated with a specific component and section.
     *
     * @return    string    The field input markup.
     */
    protected function getInput()
    {
        if (defined('JPVERSION') && version_compare(JPVERSION, '4.2', 'ge')) {
            return parent::getInput();
        }

        static $count;

        HTMLHelper::_('bootstrap.tooltip');

        // Initialise some field attributes.
        $section    = $this->element['section']     ? (string) $this->element['section']     : 'component';
        $component  = $this->element['component']   ? (string) $this->element['component']   : 'com_jpdesigns';
        $asset      = $this->element['asset_field'] ? (string) $this->element['asset_field'] : 'asset_id';
        $inherit    = $this->element['inheritonly'] ? strval($this->element['inheritonly'])  : "true";
        $inherit    = (trim(strtolower($inherit)) == 'true' ? true : false);

        if ($inherit) {
            // Get possible parent field values
            // Note that the order of the array elements matter!
            $parents = array();
            $parents['design']  = (int) $this->form->getValue('parent_id');
            $parents['album']   = (int) $this->form->getValue('album_id');
            $parents['project'] = (int) $this->form->getValue('project_id');

            $parent_el = 'project';
            $parent_id = $parents['project'];

            foreach($parents AS $key => $value)
            {
                if ($value > 0) {
                    $parent_el = $key;
                    $parent_id = $value;
                    break;
                }
            }

            if ($parent_id) {
                Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jpdesigns/tables');
                Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_joomproject/tables');

                $table = Table::getInstance($parent_el, 'JPTable');
                if (!$table) {
                    $this->access_level  = (int) $this->form->getValue('access');
                }
                else {
                    // Load the parent item
                    if (!$table->load($parent_id)) {
                        $this->access_level  = (int) $this->form->getValue('access');
                    }
                    else {
                        $this->access_level = $table->access;
                    }
                }
            }
        }
        else {
            $this->access_level  = (int) $this->form->getValue('access');
        }

        // Initialize variables
        $this->is_admin      = Factory::getApplication()->getIdentity()->authorise('core.admin', $component);
        $this->auth_groups   = Factory::getApplication()->getIdentity()->getAuthorisedGroups();
        $this->groups        = $this->getUserGroups($inherit);
        $this->actions       = $this->getActions($component, $section);
        $this->selected      = $this->getAccessRules((int) $this->form->getValue('access'));

        $this->getAccessRules($this->access_level);

        $this->getAssetRules($component, $section, $asset);

        $html = $this->getHTML($component, $section, $asset);

        return implode("\n", $html);
    }
}
