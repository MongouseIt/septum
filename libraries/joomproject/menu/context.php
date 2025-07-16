<?php
/**
 * @package      Joomproject.Library
 * @subpackage   Menu
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 20012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;


class JPMenuContext
{
    protected $component;

    protected $items;


    public function __construct($component = null)
    {
        $this->items = array();

        $this->component = (empty($component) ? \Joomla\CMS\Factory::getApplication()->input->get('option') : $component);
    }


    protected function addItem($html)
    {
        $this->items[] = $html;
    }


    public function render($disabled_options = array())
    {
        $class = '';

        if (isset($disabled_options['class']) && $disabled_options['class'] != '') {
            $class = ' ' . $disabled_options['class'];
        }

        if (count($this->items) <= 2) {
            $this->items = array();

            $html = array();
            $html[] = '<div class="btn-group">';
            $html[] = '    <a class="btn ms-2 disabled' . $class . '"  aria-disabled="true" href="javascript: void(0);"><!--<span class="caret"></span>--> <span class="fas fa-caret-down"></span> </a>';
            $html[] = '</div>';

            return implode("\n", $html);
        }
        else {
            $html = implode("\n", $this->items);

            $this->items = array();

            return $html;
        }
    }


    public function start($options = array(), $return = false)
    {
        $class  = '';
        $title  = '';
        $pull   = '';
        $id= $target = '';
        $single = false;

        if (isset($options['class']) && $options['class'] != '') {
            $class = ' ' . $options['class'];
        }

        if (isset($options['title']) && $options['title'] != '') {
            $title = $options['title'] . ' ';
        }

        if (isset($options['single-button']) && $options['single-button'] != '') {
            $single = (bool) $options['single-button'];
        }

        if (isset($options['pull']) && $options['pull'] != '') {
            $pull = ' float-' . $options['pull'];
        }

	    if (isset($options['id']) && $options['id'] != '') {
		    $id = "id='dropdown-item-{$options['id']}'";
		    $target= "data-target='#dropdown-item-{$options['id']}'";
	    }

        $html = array();

        if (!$single) {
            $html[] = '<div class="btn-group m-0 ' . $pull . '">';
            $html[] = '    <button class="px-2 btn-outline-light border btn-sm btn dropdown-toggle ' . $class.'" '.$target.' data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">' . $title . '<!--<span class="caret"></span>--></button>';
            $html[] = '    <ul '.$id.' class="dropdown-menu">';
        }
        else {
            $html[] = '<div class="btn btn-sm' . $class.'">' . $title . '</div>';
        }

        if ($return) return implode("\n", $html);

        $this->addItem(implode("\n", $html));
    }


    public function itemLink($icon, $title, $action, $return = false,$extraAttrs = '',$extraClass = '')
    {
        $html = array();

       $html[] = '<li>';
        $html[] = '<a class="dropdown-item '.$extraClass.'" '.$extraAttrs.' href="' . $action . '"><span aria-hidden="true" class="' . $icon . '"></span> ' . Text::_($title) . '</a>';
        $html[] = '</li>';

        if ($return) return implode("\n", $html);

        $this->addItem(implode("\n", $html));
    }
    
    public function itemCollapse($icon, $title, $action, $return = false)
    {
        $html = array();

       $html[] = '<li>';
        $html[] = '            <a class="dropdown-item" href="' . $action . '" data-bs-toggle="collapse"><span aria-hidden="true" class="' . $icon . '"></span> ' . Text::_($title) . '</a>';
       $html[] = '</li>';

        if ($return) return implode("\n", $html);

        $this->addItem(implode("\n", $html));
    }

    public function customCode($code){

    	$html[] = $code;

	    $this->addItem(implode("\n", $html));

    }


    public function itemPlaceholder($icon, $title, $return = false)
    {
        $html = array();

        $html[] = '<li>';
        $html[] = '            <span aria-hidden="true" class="' . $icon . '"></span> ' . Text::_($title);
        $html[] = '</li>';

        if ($return) return implode("\n", $html);

        $this->addItem(implode("\n", $html));
    }


    public function itemModal($icon, $title, $action, $click = null, $size_x = '800', $size_y = '500', $return = false,$id = null)
    {
        //static $modal;

        $onclick = (empty($click) ? '' : ' onclick="' . $click . '"');

        $titleAttr = strip_tags(Text::_($title));

        // Load the modal behavior script.
        //if (!isset($modal)) HTMLHelper::_('behavior.modal', 'a.modal_item');
        $html = array();

       // $html[] = '        <li class="dropdown-item">';
        $html[] = '            <a title="'.$titleAttr.'" class="dropdown-item  '.$id.'"  href="' . $action . '">';
        $html[] = '                <span aria-hidden="true" class="' . $icon.'"></span> ' . Text::_($title);
        $html[] = '            </a>';


       $html[] = '        </li>';

        if ($return) return implode("\n", $html);

        $this->addItem(implode("\n", $html));
    }


    public function itemJavaScript($icon, $title, $action, $return = false,$class="")
    {
        $html = array();

     $html[] = '        <li>';
        $html[] = '            <a class="dropdown-item '.$class.'"  onclick="' . $action . '" href="javascript:void(0);"><span aria-hidden="true" class="' . $icon . '"></span> ' . Text::_($title) . '</a>';
       $html[] = '        </li>';

        if ($return) return implode("\n", $html);

        $this->addItem(implode("\n", $html));
    }


    public function itemDivider($return = false)
    {
        if ($return) return '        <div class="dropdown-divider"></div>';
        $this->addItem('        <div class="dropdown-divider"></div>');
    }


    public function itemEdit($asset, $id = 0, $access = false)
    {
        if (!$access) return '';

        $icon   = 'fas fa-edit';
        $action = Route::_('index.php?option=' . $this->component . '&task=' . strval($asset) . '.edit&id=' . intval($id));
        $title  = Text::_('COM_JOOMPROJECT_ACTION_EDIT');

        return $this->itemLink($icon, $title, $action);
    }

    public function itemNew($asset, $title = "COM_JOOMPROJECT_ACTION_NEW" , $access = false, $modal = false,$extraActionParams = [])
    {
        if (!$access) return '';

        $icon   = 'fas fa-plus';
        $action = 'index.php?option=' . $this->component . '&task=' . strval($asset) . '.edit&id=0';

        $title  = Text::_($title);

        if(count($extraActionParams) > 0){

            foreach ($extraActionParams as $key => $value){

                $action .= '&' . $key . '=' . $value;

            }

        }

        if($modal){

            echo HTMLHelper::_(
                'bootstrap.renderModal',
                'newTaskModal',
                [
                    'url'    => Route::_($action.'&tmpl=component'),
                    'title'  => $title,
                    'height' => '100%',
                    'width'  => '100%',
                    'modalWidth'  => '500',
                    'bodyHeight'  => '500',
                    'footer' => '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-hidden="true">'
                        . Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>'
                ]
            );

            return $this->itemLink($icon, $title, '#',false,'data-bs-toggle="modal" data-bs-target="#newTaskModal"');


        }

        return $this->itemLink($icon, $title, Route::_($action));
    }


    public function itemTrash($asset, $id, $access = false)
    {
        if (!$access) return '';

        $icon   = 'fas fa-trash';
        $action = "return Joomla.listItemTask('cb" . $id . "','" . $asset . ".trash');";
        $title  = Text::_('COM_JOOMPROJECT_ACTION_TRASH');

        return $this->itemJavaScript($icon, $title, $action);
    }


    public function itemDelete($asset, $id, $access = false)
    {
        if (!$access) return '';

        $icon   = 'fas fa-times';
        $action = "return Joomla.listItemTask('cb" . $id . "','" . $asset . ".delete');";
        $title  = Text::_('COM_JOOMPROJECT_ACTION_DELETE');

        return $this->itemJavaScript($icon, $title, $action);
    }


    public function priorityList($i, $id, $asset, $selected = 0, $access = false, $css_class = 'btn-sm btn-mini')
    {
        $priorities = HTMLHelper::_('joomproject.priorityOptions');
        $html  = array();
        $title = '';
        $class = 'very-low-priority';

        // Find the current priority and class
        foreach($priorities AS $priority)
        {
            if ($priority->value == $selected) {
                $title = $priority->text;

                switch($priority->value)
                {
                    case 0:
                        $class = 'very-low-priority';
                        break;

                    case 2:
                        $class = 'btn-info low-priority';
                        break;

                    case 3:
                        $class = 'btn-primary medium-priority';
                        break;

                    case 4:
                        $class = 'btn-warning high-priority';
                        break;

                    case 5:
                        $class = 'btn-danger very-high-priority';
                        break;

                    default:
                        $class = 'very-low-priority';
                        break;
                }
            }
        }

        $class .= ' ' . $css_class;


        if ($access) {
            $html[] = $this->start(array('title' => $title, 'class' => $class), true);
            foreach($priorities AS $priority)
            {
                if ($title == $priority->text) continue;
                $action = "document.getElementById('priority" . $i . "').set('value', " . intval($priority->value) . "); return listItemTask('cb" . $i . "','" . $asset . ".savePriority');";
                $html[] = $this->itemJavaScript('fas fa-flag', $priority->text, $action, true);
            }
            $html[] = $this->end(true);
        }
        else {
            $html[] = $this->start(array('title' => $title, 'class' => $class, 'single-button' => true), true);
        }

        $html[] = '<input type="hidden" id="priority' . $i . '" name="priority[' . $id . ']" value="' . intval($selected) . '"/>';

        return implode("\n", $html);
    }

	public function itemNotApplicable($i, $id, $access = false, $current_state = 0, $enable_not_applicable = false)
	{
		if (!$access || !$enable_not_applicable) return '';

		$icon = 'fas fa-ban';

		if ($current_state == 1) {
			// Task is currently not applicable - show option to mark as applicable
			$action = "return Joomla.listItemTask('cb" . $i . "','tasks.notApplicable');";
			$title = 'COM_JPTASKS_MARK_AS_APPLICABLE';
		} else {
			// Task is currently applicable - show option to mark as not applicable
			$action = "return Joomla.listItemTask('cb" . $i . "','tasks.notApplicable');";
			$title = 'COM_JPTASKS_MARK_AS_NOT_APPLICABLE';
		}

		return $this->itemJavaScript($icon, $title, $action);
	}


    public function assignedUsers($i, $id, $asset, $assigned, $access = false, $css_class = 'btn-sm btn-mini')
    {
        $count = count($assigned);
        $class = '';

        $title = ($count > 0) ? $assigned[0]->name : Text::_('COM_JOOMPROJECT_UNASSIGNED');
        $title .= ($count > 1) ? ' +'.($count - 1) : '';

        $class .= ' ' . $css_class;
        $link  = JPusersHelperRoute::getUsersRoute() . '&amp;layout=modal&amp;tmpl=component&amp;field=assigned' . $i;

        if (!$access && $count < 2) {
            $html[] = $this->start(array('title' => $title, 'class' => $class, 'single-button' => true), true);
            $html[] = '<input type="hidden" id="assigned' . $i . '" name="assigned[' . $id . ']" value="0"/>';

            return implode("\n", $html);
        }
        else {
            // Build the script.
            $script = array();
            $script[] = '    function jSelectUser_assigned' . $i . '(id, title) {';
            $script[] = '        document.getElementById("assigned' . $i . '").value = id';
           // $script[] = '        SqueezeBox.close();';
            $script[] = '        return listItemTask("cb' . $i . '","' . $asset . '.addUsers");';
            $script[] = '    }';

            // Add the script to the document head.
            Factory::getDocument()->addScriptDeclaration(implode("\n", $script));

            $html[] = $this->start(array('title' => $title, 'class' => $class), true);
        }

        if ($access) {
            $html[] = $this->itemModal('fas fa-plus', 'COM_JOOMPROJECT_ASSIGN_TO_USER', $link, 800, 500, true);
            if ($count > 1) $html[] = $this->itemDivider(true);
        }

        foreach($assigned AS $user)
        {
            $action = "$('filter_assigned').set('value', " . intval($user->user_id) . "); submitbutton();";
            $html[] = $this->itemJavaScript('fas fa-user', $user->name, $action, true);
        }

        $html[] = $this->end(true);
        $html[] = '<input type="hidden" id="assigned' . $i . '" name="assigned[' . $id . ']" value="0"/>';

        return implode("\n", $html);
    }


    public function bulkItems($actions)
    {
        $message = addslashes(Text::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'));
        $html    = array();

        if (count($actions) == 0) {
            $html[] = '<div class="btn-group" id="bulk-action-menu">';
            $html[] = '    <a class="btn btn-primary disabled" aria-disabled="true" href="javascript: void(0);"><!--<span class="caret"></span>--></a>';
            $html[] = '</div>';

            return implode("\n", $html);
        }

        $html[] = '<div class="btn-group" id="bulk-action-menu">';
        $html[] = '<a class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" href="#"><!--<span class="caret"></span>--></a>';
        $html[] = '    <ul class="dropdown-menu">';

        foreach($actions AS $action)
        {
            $js = "if (document.adminForm.boxchecked.value==0){alert('" . $message . "');}"
                . "else{Joomla.submitbutton('" . $action->value . "')}";

            $icon = 'fas fa-chevron-right';

            if (strpos($action->value, '.publish') !== false)   $icon = 'fas fa-eye';
            if (strpos($action->value, '.unpublish') !== false) $icon = 'fas fa-eye-slash';
            if (strpos($action->value, '.archive') !== false)   $icon = 'fas fa-folder-open';
            if (strpos($action->value, '.trash') !== false)     $icon = 'fas fa-trash';
            if (strpos($action->value, '.delete') !== false)    $icon = 'fas fa-times';
            if (strpos($action->value, '.checkin') !== false)   $icon = 'fas fa-thumbs-up';
            $html[] = $this->itemJavaScript($icon, $action->text, $js, true);
        }

        $html[] = '</ul>';
        $html[] = '</div>';

        return implode("\n", $html);
    }




    public function end($return = false)
    {
        $html = array();

        $html[] = '</ul>';
        $html[] = '</div>';

        if ($return) return implode("\n", $html);

        $this->addItem(implode("\n", $html));
    }
}
