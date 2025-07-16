<?php
/**
 * @package      pkg_joomproject
 * @subpackage   lib_joomproject
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2006-2013 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;

jimport('joomla.html.html');
jimport('joomla.form.formfield');


/**
 * Form Field class for managing labels.
 *
 */
class JFormFieldLabelManager extends FormField
{
    /**
     * The form field type.
     *
     * @var    string
     */
    public $type = 'LabelManager';


    /**
     * The existing label items
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
        // Add the script to the document head.
        $script = $this->getJavascript();
        Factory::getDocument()->addScriptDeclaration(implode("\n", $script));

        if (!is_array($this->value)) {
            $this->value = array();
        }

        $this->items = array();

        foreach ($this->value AS $item)
        {
            if (!array_key_exists($item->asset_group, $this->items)) {
                $this->items[$item->asset_group] = array();
            }

            $this->items[$item->asset_group][] = $item;
        }

        $assets = array(
            'com_jpprojects.project',
            'com_jpmilestones.milestone',
            'com_jptasks.task',
            'com_jpforum.topic',
            'com_jprepo.directory',
            'com_jprepo.note',
            'com_jprepo.file',
            'com_jpdesigns.design'
        );

        $html = $this->getHTML($assets);

        return implode("\n", $html);
    }


    /**
     * Method to generate the input markup.
     *
     * @return    string    The html field markup
     */
    protected function getHTML($assets)
    {
        if (Factory::getApplication()->isClient('site') || version_compare(JVERSION, '3.0.0', 'ge')) {
            return $this->getSiteHTML($assets);
        }

        return $this->getAdminHTML($assets);
    }


    /**
     * Method to generate the backend input markup.
     *
     * @return    array    $html    The html field markup
     */
    protected function getAdminHTML($assets)
    {
        $html = array();

        foreach ($assets AS $asset)
        {
            list($component, $asset_name) = explode('.', $asset, 2);

            if (!JPApplicationHelper::enabled($component)) {
                continue;
            }

            $asset_id = str_replace('.', '_', $asset);

            $html[] = '<ul class="list-unstyled">';
            $html[] = '<li class="">';
            $html[] = '<h3>' . Text::_(strtoupper($asset_id) . '_LABEL_TITLE') . '</h3>';
            $html[] = '';
            $html[] = '<ul id="' . $this->id . '_' . $asset_id . '" class="list-unstyled">';

            if (array_key_exists($asset, $this->items)) {
                foreach ($this->items[$asset] AS $item)
                {
                    $html[] = '<li>';
                    $html[] = '<input type="text" class=" form-control "';
                    $html[] = 'name="' . $this->name . '[' . $asset . '][title][]" placeholder="' . Text::_('COM_JOOMPROJECT_LABEL_TITLE_PLACEHOLDER') . '"';
                    $html[] = ' value="' . htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8') . '" maxlength="32"/>';
                    $html[] = '<select class=" form-control " name="' . $this->name . '[' . $asset . '][style][]">';
                    $html[] = '<option value="">' . Text::_('COM_JOOMPROJECT_LABEL_OPTION_LABEL_STYLE_DEFAULT') . '</option>';
                    $html[] = '<option value="bg-success"' . ($item->style == 'bg-success' ? ' selected="selected"' : '') . '>' . Text::_('COM_JOOMPROJECT_LABEL_OPTION_LABEL_STYLE_SUCCESS') . '</option>';
                    $html[] = '<option value="bg-warning"' . ($item->style == 'bg-warning' ? ' selected="selected"' : '') . '>' . Text::_('COM_JOOMPROJECT_LABEL_OPTION_LABEL_STYLE_WARNING') . '</option>';
                    $html[] = '<option value="bg-danger"' . ($item->style == 'bg-danger' ? ' selected="selected"' : '') . '>' . Text::_('COM_JOOMPROJECT_LABEL_OPTION_LABEL_STYLE_IMPORTANT') . '</option>';
                    $html[] = '<option value="bg-info"' . ($item->style == 'bg-info' ? ' selected="selected"' : '') . '>' . Text::_('COM_JOOMPROJECT_LABEL_OPTION_LABEL_STYLE_INFO') . '</option>';
                    $html[] = '<option value="bg-dark"' . ($item->style == 'bg-dark' ? ' selected="selected"' : '') . '>' . Text::_('COM_JOOMPROJECT_LABEL_OPTION_LABEL_STYLE_INVERSE') . '</option>';
                    $html[] = '</select>';
                    $html[] = '<div class="button2-left"><div class="blank">';
                    $html[] = '<a href="javascript:void(0);" onclick="jpRemoveLabel_' . $this->id . '(this)">' . Text::_('JACTION_DELETE') . '</a>';
                    $html[] = '</div></div>';
                    $html[] = '<input type="hidden" name="' . $this->name . '[' . $asset . '][id][]" value="' . intval($item->id) . '"/>';
                    $html[] = '<div class="clr"></li>';
                }
            }

            $html[] = '</ul>';
            $html[] = '<div class="button2-left"><div class="blank">';
            $html[] = '<a class="btn" href="javascript:void(0);" onclick="jpAddLabel_' . $this->id . '(\'' . $asset_id . '\', \'' . $asset . '\');">';
            $html[] = Text::_('JACTION_ADD_LABEL');
            $html[] = '</a>';
            $html[] = '</div></div>';
            $html[] = '<div class="clr"></div></li>';
            $html[] = '</ul>';
            $html[] = '<input type="hidden" name="' . $this->name . '[]" id="' . $this->id . '" value=""/>';
        }

        return $html;

    }


    /**
     * Method to generate the frontend input markup.
     *
     * @return    array    $html    The html field markup
     */
    protected function getSiteHTML($assets)
    {
        $html = array();

        $html[] = '<div class="row">';

        foreach ($assets AS $asset)
        {
            list($component, $asset_name) = explode('.', $asset, 2);

            if (!JPApplicationHelper::enabled($component)) {
                continue;
            }

            $asset_id = str_replace('.', '_', $asset);

            $html[] = '<div class="col-lg-6 col-xl-6">';
            $html[] = '<div class="card mb-4 shadow-sm border">';
            $html[] = '<div class="card-header border-bottom d-flex justify-content-between align-items-center py-2"><h4 class="m-0">' . Text::_(strtoupper($asset_id) . '_LABEL_TITLE') . '</h4>';

            $html[] = '<a class="btn btn-outline-success btn-sm" title="'.Text::_('JACTION_ADD_LABEL').'" href="javascript:void(0);" onclick="jpAddLabel_' . $this->id . '(\'' . $asset_id . '\', \'' . $asset . '\');">';
            $html[] = '<i class="fas fa-plus"></i> ';
            $html[] = '</a>';
            $html[] = '</div>';

            $html[] = '<div class="card-body">';
            $html[] = '<ul id="' . $this->id . '_' . $asset_id . '" class="list-unstyled ms-0">';

            if (array_key_exists($asset, $this->items)) {
                foreach ($this->items[$asset] AS $item)
                {
                    $html[] = '<li><div class="input-group input-group-sm mb-2">';
                    $html[] = '<button class="btn btn-outline-danger btn-sm" type="button" onclick="jpRemoveLabel_' . $this->id . '(this)"><i class="fas fa-times"></i></button>';
                    $html[] = '<input type="text" class="  form-control" onkeyup="jpPreviewLabel_' . $this->id . '(this, \'text\')"';
                    $html[] = 'name="' . $this->name . '[' . $asset . '][title][]" placeholder="' . Text::_('COM_JOOMPROJECT_LABEL_TITLE_PLACEHOLDER') . '"';
                    $html[] = ' value="' . htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8') . '" maxlength="32"/>';
                    $html[] = '<select class="form-select" name="' . $this->name . '[' . $asset . '][style][]" onchange="jpPreviewLabel_' . $this->id . '(this, \'style\')">';
                    $html[] = '<option value="bg-light text-dark">' . Text::_('COM_JOOMPROJECT_LABEL_OPTION_LABEL_STYLE_DEFAULT') . '</option>';
                    $html[] = '<option value="bg-success"' . ($item->style == 'bg-success' ? ' selected="selected"' : '') . '>' . Text::_('COM_JOOMPROJECT_LABEL_OPTION_LABEL_STYLE_SUCCESS') . '</option>';
                    $html[] = '<option value="bg-warning"' . ($item->style == 'bg-warning' ? ' selected="selected"' : '') . '>' . Text::_('COM_JOOMPROJECT_LABEL_OPTION_LABEL_STYLE_WARNING') . '</option>';
                    $html[] = '<option value="bg-danger"' . ($item->style == 'label-important' ? ' selected="selected"' : '') . '>' . Text::_('COM_JOOMPROJECT_LABEL_OPTION_LABEL_STYLE_IMPORTANT') . '</option>';
                    $html[] = '<option value="bg-info"' . ($item->style == 'bg-info' ? ' selected="selected"' : '') . '>' . Text::_('COM_JOOMPROJECT_LABEL_OPTION_LABEL_STYLE_INFO') . '</option>';
                    $html[] = '<option value="bg-dark"' . ($item->style == 'bg-dark' ? ' selected="selected"' : '') . '>' . Text::_('COM_JOOMPROJECT_LABEL_OPTION_LABEL_STYLE_INVERSE') . '</option>';
                    $html[] = '</select>';
                    $html[] = '<input type="hidden" name="' . $this->name . '[' . $asset . '][id][]" value="' . intval($item->id) . '"/>';

                    $html[] = '<div title="'.Text::_('COM_JOOMPROJECT_LABEL_PREVIEW').'" class="badge p-2 ' . $item->style . '">';
                    $html[] = '<i class="fas fa-bookmark"></i> ' . htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8');
                    $html[] = '</div>';

                    $html[] = '</div>';
                    $html[] = '</li>';
                }
            }

            $html[] = '</ul>';
            $html[] = '<div class="form-group">';

            $html[] = '</div>';
            $html[] = '</div>';
            $html[] = '</div>';
            $html[] = '</div>';
            $html[] = '<input type="hidden" name="' . $this->name . '[]" id="' . $this->id . '" value=""/>';
        }

        $html[] = '</div>';

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

        if (Factory::getApplication()->isClient('site') || version_compare(JVERSION, '3.0.0', 'ge')) {
            $script[] = 'function jpAddLabel_' . $this->id . '(asset, name)';
            $script[] = '{';
            $script[] = '    var l = jQuery("#' . $this->id . '_" + asset);';
            $script[] = '    var c = "<li>"';
            $script[] = '          + "<div class=\"input-group input-group-sm mb-2\">"';

            $script[] = '          + "<button class=\"btn btn-outline-danger\" type=\"button\" onclick=\"jpRemoveLabel_' . $this->id . '(this)\"><i class=\"fas fa-times\"></i></button>"';
            $script[] = '          + "<input type=\"text\" class=\" form-control \" onkeyup=\"jpPreviewLabel_' . $this->id . '(this, \'text\')\"  maxlength=\"32\""';
            $script[] = '          + "name=\"' . $this->name . '["+name+"][title][]\" placeholder=\"' . Text::_('COM_JOOMPROJECT_LABEL_TITLE_PLACEHOLDER') . '\"/>"';
            $script[] = '          + "<select class=\" form-select \" name=\"' . $this->name . '["+name+"][style][]\" onchange=\"jpPreviewLabel_' . $this->id . '(this, \'style\')\">"';
            $script[] = '          + "<option value=\"bg-light text-dark\">' . Text::_('COM_JOOMPROJECT_LABEL_OPTION_LABEL_STYLE_DEFAULT') . '</option>"';
            $script[] = '          + "<option value=\"bg-success\">' . Text::_('COM_JOOMPROJECT_LABEL_OPTION_LABEL_STYLE_SUCCESS') . '</option>"';
            $script[] = '          + "<option value=\"bg-warning\">' . Text::_('COM_JOOMPROJECT_LABEL_OPTION_LABEL_STYLE_WARNING') . '</option>"';
            $script[] = '          + "<option value=\"bg-danger\">' . Text::_('COM_JOOMPROJECT_LABEL_OPTION_LABEL_STYLE_IMPORTANT') . '</option>"';
            $script[] = '          + "<option value=\"bg-info\">' . Text::_('COM_JOOMPROJECT_LABEL_OPTION_LABEL_STYLE_INFO') . '</option>"';
            $script[] = '          + "<option value=\"bg-dark\">' . Text::_('COM_JOOMPROJECT_LABEL_OPTION_LABEL_STYLE_INVERSE') . '</option>"';
            $script[] = '          + "</select>"';
            $script[] = '          + "<input type=\"hidden\" name=\"' . $this->name . '["+name+"][id][]\" value=\"0\"/>"';

            $script[] = '          + "<div class=\"badge bg-light text-dark p-2\">"';
            $script[] = '          + "<span ><i class=\"fas fa-bookmark\"></i> ' . Text::_('COM_JOOMPROJECT_LABEL_TITLE_PLACEHOLDER') . '</span>"';

            $script[] = '          + "</div>"';
            $script[] = '          + "</div>";';

            $script[] = '          + "</li>";';
            $script[] = '    ';
            $script[] = '    l.append(c);';
            $script[] = '    ' . $onchange;
            $script[] = '}';
            $script[] = 'function jpRemoveLabel_' . $this->id . '(el)';
            $script[] = '{';
            $script[] = '    jQuery(el).parent().parent().remove();';
            $script[] = '}';
            $script[] = 'function jpPreviewLabel_' . $this->id . '(el, data)';
            $script[] = '{';
            $script[] = '    var lbl = jQuery(el).parent().parent().find(".badge");';
            $script[] = '    if (lbl.length) {';
            $script[] = '        if (data == "text") {';
            $script[] = '            var v = jQuery(el).val();';
            $script[] = '            if (v == "") v = "' . Text::_('COM_JOOMPROJECT_LABEL_TITLE_PLACEHOLDER') . '";';
            $script[] = '            lbl.html("<i class=\"fas fa-bookmark\"></i> " + v)';
            $script[] = '        }';
            $script[] = '        if (data == "style") {';
            $script[] = '            var v = jQuery(el).val();';
            $script[] = '            lbl.removeClass("bg-light text-dark");';
            $script[] = '            lbl.removeClass("bg-success");';
            $script[] = '            lbl.removeClass("bg-warning");';
            $script[] = '            lbl.removeClass("bg-danger");';
            $script[] = '            lbl.removeClass("bg-info");';
            $script[] = '            lbl.removeClass("bg-dark");';
            $script[] = '            if (v != "") {';
            $script[] = '                lbl.addClass(v);';
            $script[] = '            }';
            $script[] = '        }';
            $script[] = '    }';
            $script[] = '}';
        }
        else {
            $script[] = 'function jpAddLabel_' . $this->id . '(asset, name)';
            $script[] = '{';
            $script[] = '    var l = jQuery("#' . $this->id . '_" + asset);';
            $script[] = '    var c = "<li>"';
            $script[] = '          + "<input type=\"text\" class=\"form-control \" maxlength=\"32\""';
            $script[] = '          + "name=\"' . $this->name . '["+name+"][title][]\" placeholder=\"' . Text::_('COM_JOOMPROJECT_LABEL_TITLE_PLACEHOLDER') . '\"/>"';
            $script[] = '          + "<select class=\"form-select \" name=\"' . $this->name . '["+name+"][style][]\">"';
            $script[] = '          + "<option value=\"bg-light text-dark\">' . Text::_('COM_JOOMPROJECT_LABEL_OPTION_LABEL_STYLE_DEFAULT') . '</option>"';
            $script[] = '          + "<option value=\"bg-success\">' . Text::_('COM_JOOMPROJECT_LABEL_OPTION_LABEL_STYLE_SUCCESS') . '</option>"';
            $script[] = '          + "<option value=\"bg-warning\">' . Text::_('COM_JOOMPROJECT_LABEL_OPTION_LABEL_STYLE_WARNING') . '</option>"';
            $script[] = '          + "<option value=\"bg-danger\">' . Text::_('COM_JOOMPROJECT_LABEL_OPTION_LABEL_STYLE_IMPORTANT') . '</option>"';
            $script[] = '          + "<option value=\"bg-info\">' . Text::_('COM_JOOMPROJECT_LABEL_OPTION_LABEL_STYLE_INFO') . '</option>"';
            $script[] = '          + "<option value=\"bg-dark\">' . Text::_('COM_JOOMPROJECT_LABEL_OPTION_LABEL_STYLE_INVERSE') . '</option>"';
            $script[] = '          + "</select>"';
            $script[] = '          + "<div class=\"button2-left\"><div class=\"blank\">"';
            $script[] = '          + "<a href=\"javascript:void(0);\" onclick=\"jpRemoveLabel_' . $this->id . '(this)\">' . Text::_('JACTION_DELETE') . '</a>"';
            $script[] = '          + "</div></div>"';
            $script[] = '          + "<input type=\"hidden\" name=\"' . $this->name . '["+name+"][id][]\" value=\"0\"/>"';
            $script[] = '          + "<div class=\"clr\"></div></li>";';
            $script[] = '    ';
            $script[] = '    l.append(c);';
            $script[] = '    ' . $onchange;
            $script[] = '}';
            $script[] = 'function jpRemoveLabel_' . $this->id . '(el)';
            $script[] = '{';
            $script[] = '    jQuery(el).parent().parent().parent().remove();';
            $script[] = '}';
        }


        return $script;
    }
}
