<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jpusers
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Access\Access;





/**
 * Groups rules view class.
 *
 */
class JPusersViewGroupRules extends HtmlView
{
    protected $item;

    protected $component;

    protected $section;

    protected $asset_id;

    protected $inherit;

    protected $rules;

    protected $public_groups;


    /**
     * Generates a list of JSON items.
     *
     * @return    void
     */
    public function display($tpl = null)
    {
        $model = $this->getModel();

        $this->item	= $this->get('Item');

        $this->component  = $model->getState($model->getName() . '.component');
        $this->section    = $model->getState($model->getName() . '.section');
        $this->asset_id   = $model->getState($model->getName() . '.asset_id');
        $this->project_id = $model->getState($model->getName() . '.project_id');
        $this->inherit    = $model->getState($model->getName() . '.inherit');

        if (!$this->asset_id && $this->inherit) {
            $this->asset_id = $this->getComponentProjectAssetId($this->component, $this->project_id);
        }

        $this->rules         = $this->getAssetRules();

        $this->public_groups = array('1', ComponentHelper::getParams('com_users')->get('guest_usergroup', 1));

        if (!Factory::getApplication()->getIdentity()->authorise('core.admin', $this->component)) {
            \Joomla\CMS\Factory::getApplication()->enqueueMessage( Text::_('JERROR_ALERTNOAUTHOR'),'error');
            return false;
        }
        // error
        if (count($errors = $this->get('Errors')))
        {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        parent::display($tpl);
    }


    public function getActionHTML($action, $component, $selected = '')
    {


    	$id   = 'jform_rules_' . $action->name . '_' . $this->item->id;

        if (!$this->inherit) {
            $name = 'jform[rules][' . $component . '][' . $action->name . '][' . $this->item->id . ']';
        }
        else {
            $name = 'jform[rules][' . $action->name . '][' . $this->item->id . ']';
        }

        $html = array();

        $opt1_text = Text::_($this->item->parent_id == 0 ? 'JLIB_RULES_NOT_SET' : 'JLIB_RULES_INHERITED');
        $opt1_sel  = ($selected == '' ? 'selected="selected"' : '');
        $opt2_sel  = ($selected == '1' ? 'selected="selected"' : '');
        $opt3_sel  = ($selected == '0' ? 'selected="selected"' : '');

        $html[] = '<select id="' . $id . '" name="' . $name . '" class="form-select form-select-sm w-auto">';

        if($action->name != 'core.view')
			$html[] = '<option ' . $opt1_sel . ' value="">' . $opt1_text . '</option>';

        $html[] = '<option ' . $opt2_sel . ' value="1">' . Text::_('JLIB_RULES_ALLOWED') . '</option>';
        $html[] = '<option ' . $opt3_sel . ' value="0">' . Text::_('JLIB_RULES_DENIED') . '</option>';
        $html[] = '</select>';

        return implode("\n", $html);
    }


    protected function getAssetRules($component = null, $asset_id = null)
    {
        static $cache  = array();
        static $assets = array();

        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        if (is_null($component)) $component = $this->component;
        if (is_null($asset_id))  $asset_id  = $this->asset_id;

        if (!$asset_id) {
            if (isset($assets[$component])) {
                $asset_id = (int) $assets[$component];
            }
            else {
                // This is a new item, get the asset id of the component
                $query->select($db->quoteName('id'))
                      ->from($db->quoteName('#__assets'))
                      ->where($db->quoteName('name') . ' = ' . $db->quote($component));

                $db->setQuery($query);
                $assets[$component] = $db->loadResult();

                $asset_id = (int) $assets[$component];
            }
        }

        if (!$asset_id) $asset_id = 1;

        if (isset($cache[$asset_id])) return $cache[$asset_id];

        $cache[$asset_id] = Access::getAssetRules($asset_id);

        return $cache[$asset_id];
    }


    protected function getParentGroupId($id){

	    $db    = Factory::getDbo();
	    $query = $db->getQuery(true);

	    // Get the group parent id of the current group.
	    $query->clear()
		    ->select($db->quoteName('parent_id'))
		    ->from($db->quoteName('#__usergroups'))
		    ->where($db->quoteName('id') . ' = ' . (int) $id);

	    $db->setQuery($query);

	    return (int) $db->loadResult();
    }


    protected function getComponentProjectAssetId($component, $project = 0)
    {
        static $cache = array();

        $cache_key = $component . '.' . $project;

        if (isset($cache[$cache_key])) return $cache[$cache_key];

        // if new project (0), get main component asset.
        $where = $project == 0 ? $component : $component . '.project.' . (int) $project;

        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select('id')
              ->from('#__assets')
              ->where('name = ' . $db->quote($where));

        $db->setQuery($query);
        $cache[$cache_key] = (int) $db->loadResult();

        return $cache[$cache_key];
    }


    protected function getCalculated($action, $calc, $rule)
    {

        $html = '';
        if (Access::checkGroup($this->item->id, 'core.admin') !== true) {
            if ($calc === null) {

                $html = '<span class="fas fa-ban text-danger"> ' . Text::_('JLIB_RULES_NOT_ALLOWED') . '</span>';
            }
            elseif ($calc === true) {

                $html = '<span class="fas fa-check text-success"> ' . Text::_('JLIB_RULES_ALLOWED') . '</span>';
            }
            elseif ($calc === false) {

                if ($rule === false) {

                    $html = '<span class="fas fa-ban text-danger"> ' . Text::_('JLIB_RULES_NOT_ALLOWED') . '</span>';
                }
                else {

                    $html = '<span class="fas fa-ban text-danger"><span class="fas fa-lock"> ' . Text::_('JLIB_RULES_NOT_ALLOWED_LOCKED') . '</span></span>';
                }
            }
        }
        elseif (!empty($this->component)) {

            $html = '<span class="fas fa-check text-success"> ' . Text::_('JLIB_RULES_ALLOWED_ADMIN') . '</span></span>';
        }
        else {
            // Special handling for  groups that have global admin because they can't be denied.
            // The admin rights can be changed.
            if ($action->name === 'core.admin') {

                $html = '<span class="fas fa-check text-success"> ' . Text::_('JLIB_RULES_ALLOWED') . '</span>';
            }
            elseif ($calc === false) {

                $html = '<span class="fas fa-ban text-success"><span class="fas fa-lock"> ' . Text::_('JLIB_RULES_NOT_ALLOWED_ADMIN_CONFLICT') . '</span></span>';
            }
            else {

                $html = '<span class="fas fa-check text-success"><span class="fas fa-lock"> ' . Text::_('JLIB_RULES_ALLOWED_ADMIN') . '</span></span>';
            }
        }

        return $html;
    }
}
