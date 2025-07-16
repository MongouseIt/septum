<?php
/**
 * @package      Joomproject
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\Helpers\Bootstrap;
use Joomla\CMS\Form\FormField;

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomproject.framework');

/**
 * Form Field class for selecting a project.
 *
 */
class JFormFieldProject extends FormField
{
    /**
     * The form field type.
     *
     * @var    string
     */
    public $type = 'Project';


    /**
     * Method to get the field input markup.
     *
     * @return    string    The html field markup
     */
    protected function getInput()
    {
        // Load the modal behavior script
        HTMLHelper::_('bootstrap.modal', 'a.modal_' . $this->id);

        // Load the current project title a value is set.
        $title = ($this->value ? $this->getProjectTitle() : Text::_('COM_JOOMPROJECT_SELECT_A_PROJECT'));

        if ($this->value == 0) $this->value = '';

        $html = $this->getHTML($title);
        if(Factory::getApplication()->isClient('site')){
            return implode("\n", $html);
        }

        return $html;
    }


    /**
     * Method to generate the input markup.
     *
     * @param     string    $title    The title of the current value
     *
     * @return    string              The html field markup
     */
    protected function getHTML($title)
    {
        if (Factory::getApplication()->isClient('site')) {
            return $this->getHTMLSelect2($title);
            // return $this->getSiteHTML($title);
        }

        return $this->getAdminHTML($title);
    }


    protected function getHTMLSelect2($title)
    {
        HTMLHelper::_('jphtml.script.jQuerySelect2');

        static $field_id = 0;

        $field_id++;

        $doc = Factory::getDocument();
        $app = Factory::getApplication();


        // Prepare field attributes
        $can_change = isset($this->element['readonly']) ? (bool) $this->element['readonly'] : true;
        $onchange   = isset($this->element['onchange']) ? $this->element['onchange'] : '';



        $attr_read  = ($can_change ? '' : ' readonly="readonly"');

        $css_txt    = ($can_change ? '' : ' disabled muted') . (!empty($value) ? ' success' : ' warning');
        $value      = (int) $this->value;
        $placehold  = htmlspecialchars(Text::_('COM_JOOMPROJECT_SELECT_PROJECT'), ENT_COMPAT, 'UTF-8');
        $title      = htmlspecialchars($title, ENT_COMPAT, 'UTF-8');

        if (empty($title)) {
            $title = $placehold;
        }

        // Query url
        $url = Uri::base().'index.php?option=com_jpprojects&view=projects&tmpl=component&format=json&'. Session::getFormToken() .'=1&select2=1';

        // Prepare JS select2 script
        $js = array();
        $js[] = "jQuery(document).ready(function()";
        $js[] = "{";
        $js[] = "    jQuery('#" . $this->id . "_id').select2({";
        $js[] = "            theme: 'bootstrap',";
        $js[] = "        placeholder: '" . $placehold . "',";
        if ($value) $js[] = "        allowClear: true,";
        $js[] = "        minimumInputLength: 0,";
        $js[] = "        ajax: {";
        $js[] = "            url: '" . $url . "',";
        $js[] = "            dataType: 'json',";
        $js[] = "            quietMillis: 200,";
        $js[] = "            data: function (term, page) {return {filter_search: term, limit: 10, limitstart: ((page - 1) * 10)};},";
        $js[] = "            results: function (data, page) {var more = (page * 10) < data.total;return {results: data.items, more: more};}";
        $js[] = "        },";
        $js[] = "        escapeMarkup:function(markup) { return markup; },";
        $js[] = "        initSelection: function(element, callback) {";
        $js[] = "           callback({id:" . $value . ", text: '" . htmlspecialchars($title, ENT_QUOTES) . "'});";
        $js[] = "        }";
        $js[] = "    });";
        $js[] = "    jQuery('#" . $this->id . "_id').change(function(){" . $onchange . "});";
        $js[] = "});";

        // Prepare html output
        $html = array();

        $html[] = '<input type="hidden" id="' . $this->id . '_id" name="' . $this->name . '" placeholder="' . $title . '"';
        $html[] = ' value="' . $value . '" autocomplete="off"' . $attr_read . ' class="input-large" tabindex="-1" />';


        if ($can_change) {
            // Add script
            Factory::getDocument()->addScriptDeclaration(implode("\n", $js));
        }

        return $html;
    }

    /**
     * Method to generate the backend input markup.
     *
     * @param     string    $title    The title of the current value
     *
     * @return    array     $html     The html field markup
     */
    protected function getAdminHTML($title)
    {
        // Load language

        $allowNew       = ((string) $this->element['new'] == 'true');
        $allowEdit      = ((string) $this->element['edit'] == 'true');
        $allowClear     = ((string) $this->element['clear'] != 'false');
        $allowSelect    = ((string) $this->element['select'] != 'false');
        $allowPropagate = ((string) $this->element['propagate'] == 'true');

        $languages = LanguageHelper::getContentLanguages(array(0, 1), false);

        // Load language
        Factory::getLanguage()->load('com_joomproject', JPATH_ADMINISTRATOR);

        // The active article id field.
        $value = (int) $this->value ?: '';

        // Create the modal id.
        $modalId = 'Project_' . $this->id;

        /** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();

        // Add the modal field script to the document head.
        $wa->useScript('field.modal-fields');

        // Script to proxy the select modal function to the modal-fields.js file.
        if ($allowSelect)
        {
            static $scriptSelect = null;

            if (is_null($scriptSelect))
            {
                $scriptSelect = array();
            }

            $onchange = $this->element['onchange'] ? $this->element['onchange'] : '';

            if (!isset($scriptSelect[$this->id]))
            {

                $modalJS = <<<js
window.jpSelectProject_{$this->id} = function (id, title) {
    
    var old_id = document.getElementById("{$this->id}_id").value;       
    
	window.processModalSelect('Project', '{$this->id}', id, title);
    
    if (old_id != id) {          
        $onchange
    }
					
	
}
js;


                $wa->addInlineScript($modalJS,
                    [],
                    ['type' => 'module']
                );

                Text::script('JGLOBAL_ASSOCIATIONS_PROPAGATE_FAILED');

                $scriptSelect[$this->id] = true;
            }
        }

        if ($value)
        {
            $db = Factory::getDbo();
            $query = $db->getQuery(true);
            $query->select('j.title');
            $query->from('#__jp_projects j');
            $query->where($db->quoteName('j.id') . ' = ' . (int)$value);
            $db->setQuery($query);

            try
            {
                $title = $db->loadResult();
            }
            catch (RuntimeException $e)
            {
                Factory::getApplication()->enqueueMessage($e->getMessage(),'error');
            }
        }

        $title = empty($title) ? Text::_('COM_JOOMPROJECT_SELECT_PROJECT') : htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $html  = '';

        $html .= '<span class="input-group">';

        $html .= '<input class="form-control" id="' . $this->id . '_name" type="text" value="' . $title . '" readonly size="35" />';


        // html for the Select button
        if ($allowSelect)
        {
            $html .= '<button'
                . ' class="btn btn-primary d-block"'
                . ' id="' . $this->id . '_select"'
                . ' data-bs-toggle="modal"'
                . ' type="button"'
                . ' data-bs-target="#ModalSelect' . $modalId . '">'
                . '<span class="icon-file" aria-hidden="true"></span> ' . Text::_('JSELECT')
                . '</button>';
        }
        // html for the Clear button
        if ($allowClear)
        {
            $html .= '<button'
                . ' class="btn btn-secondary' . ($value ? '' : ' hidden') . '"'
                . ' id="' . $this->id . '_clear"'
                . ' type="button"'
                . ' onclick="window.processModalParent(\'' . $this->id . '\'); return false;">'
                . '<span class="icon-times" aria-hidden="true"></span> ' . Text::_('JCLEAR')
                . '</button>';
        }

        $html .= '</span>';

        // url for the iframe

        // url for the iframe
        $link = 'index.php?option=com_jpprojects&amp;view=projects&amp;layout=modal&amp;tmpl=component&amp;' . Session::getFormToken() . '=1';
        $urlSelect = $link . '&amp;function=jpSelectProject_' . $this->id;

        // title to go in the modal header
        $modalTitle    = Text::_('COM_JOOMPROJECT_SELECT_PROJECT');

        // html to set up the modal iframe
        $html .= Bootstrap::renderModal(
            'ModalSelect' . $modalId,
            array(
                'title'       => $modalTitle,
                'url'         => $urlSelect,
                'height'      => '400px',
                'width'       => '800px',
                'bodyHeight'  => 70,
                'modalWidth'  => 80,
                'footer'      => '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">'
                    . Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>',
            )
        );

        // class='required' for client side validation.
        $class = $this->required ? ' class="required modal-value"' : '';

        // hidden input field to store the helloworld record id
        $html .= '<input type="hidden" id="' . $this->id . '_id" ' . $class
            . ' data-required="' . (int) $this->required . '" name="' . $this->name
            . '" data-text="' . htmlspecialchars(Text::_('COM_JOOMPROJECT_SELECT_PROJECT', true), ENT_COMPAT, 'UTF-8')
            . '" value="' . $value . '" />';

        return $html;

    }


    /**
     * Method to generate the frontend input markup.
     *
     * @param     string    $title    The title of the current value
     *
     * @return    array     $html     The html field markup
     */
    protected function getSiteHTML($title)
    {
        $html = array();


        if (Factory::getApplication()->isClient('site')) {
            $link = JPprojectsHelperRoute::getProjectsRoute()
                . '&amp;layout=modal&amp;tmpl=component'
                . '&amp;function=jpSelectProject_' . $this->id;
        }
        else {
            $link = 'index.php?option=com_jpprojects&amp;view=projects'
                . '&amp;layout=modal&amp;tmpl=component'
                . '&amp;function=jpSelectProject_' . $this->id;
        }

        // Initialize some field attributes.
        $attr  = $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
        $attr .= $this->element['size']  ? ' size="'.(int) $this->element['size'].'"'      : '';


        $html[] = '<div class="input-append">';

        // Create a dummy text field with the project title.
        $html[] = '<input type="text" id="' . $this->id . '_name" value="' . htmlspecialchars($title, ENT_COMPAT, 'UTF-8') . '" disabled="disabled"' . $attr . ' />';

        // Create the project select button.
        if ($this->element['readonly'] != 'true') {
            $html[] = '<a class="modal_' . $this->id . ' btn" title="' . Text::_('COM_JOOMPROJECT_SELECT_PROJECT') . '"'
                . ' href="' . Route::_($link) . '" rel="{handler: \'iframe\', size: {x: 800, y: 500}}">';
            $html[] = Text::_('COM_JOOMPROJECT_SELECT_PROJECT') . '</a>';
        }


        $html[] = '</div>';


        // Create the hidden field, that stores the id.
        $html[] = '<input type="hidden" id="' . $this->id . '_id" name="' . $this->name . '" value="' . (int) $this->value . '" />';

        return $html;
    }




    /**
     * Method to get the title of the currently selected project
     *
     * @return    string    The project title
     */
    protected function getProjectTitle()
    {
        $default = Text::_('COM_JOOMPROJECT_SELECT_A_PROJECT');

        if (empty($this->value)) {
            return $default;
        }

        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select('title')
            ->from('#__jp_projects')
            ->where('id = ' . $db->quote($this->value));

        $db->setQuery((string) $query);
        $title = $db->loadResult();

        if (empty($title)) {
            return $default;
        }

        return $title;
    }
}
