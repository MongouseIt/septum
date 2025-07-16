<?php
/**
 * @package      Joomproject
 * @subpackage   Tasks
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;




abstract class JHtmlJPtasks
{
    static public function assignedLabel($id, $i = 0, $users = null)
    {
        if (!is_array($users) || !count($users)) {
            return '<span id="assigned_' . $i . '_label"></span>'
                 . '<input type="hidden" id="assigned' . $i . '" name="assigned[' . $id . ']" />';
        }

        $html  = array();
        $count = count($users);

        if ($count == 1) {
            $html[] = '<span id="assigned_' . $i . '_label" class="label user">';
            $html[] = '<span aria-hidden="true" class="fas fa-user text-white"></span> ';
            $html[] = htmlspecialchars($users[0]->name, ENT_COMPAT, 'UTF-8');
            $html[] = '</span>';
        }
        else {
            $count = $count - 1;
            $rev   = array_reverse($users);
            $first = array_pop($rev);
            $names = array();

            foreach ($users AS $user)
            {
                $names[] = htmlspecialchars($user->name, ENT_COMPAT, 'UTF-8');
            }

            $tooltip = '::' . htmlspecialchars(implode('<br/>', $names), ENT_COMPAT, 'UTF-8');

            $html[] = '<span id="assigned_' . $i . '_label" class="label user" data-bs-toggle="tooltip" data-placement="top" title="' . $tooltip . '" style="cursor: help">';
            $html[] = '<span aria-hidden="true" class="fas fa-user text-white"></span> ';
            $html[] = htmlspecialchars($first->name, ENT_COMPAT, 'UTF-8') . ' +' . $count;
            $html[] = '</span>';
        }

        $html[] = '<input type="hidden" id="assigned' . $i . '" name="assigned[' . $id . ']" />';

        return implode('', $html);
    }


    static public function priorityLabel($id, $i = 0, $value = null,$noHTML = false)
    {
        switch((int) $value)
        {
            case 2:
                $class = 'bg-success low-priority';
                $text  = Text::_('COM_JOOMPROJECT_PRIORITY_LOW');
                break;

            case 3:
                $class = 'bg-info medium-priority';
                $text  = Text::_('COM_JOOMPROJECT_PRIORITY_MEDIUM');
                break;

            case 4:
                $class = 'bg-warning high-priority';
                $text  = Text::_('COM_JOOMPROJECT_PRIORITY_HIGH');
                break;

            case 5:
                $class = 'bg-danger very-high-priority';
                $text  = Text::_('COM_JOOMPROJECT_PRIORITY_VERY_HIGH');
                break;

            default:
            case 1:

                if($noHTML)
                    return '';

                return '<span id="priority_' . $i . '_label"></span>'
                     . '<input type="hidden" name="priority[' . $id . ']" id="priority' . $i . '" value="1"/>';
                break;
        }

        if($noHTML){
            return $text;
        }

        $html = '<span id="priority_' . $i . '_label" class="badge ' . $class . '"><span aria-hidden="true" class="fas fa-exclamation-triangle"></span> ' . $text . '</span>'
              . '<input type="hidden" name="priority[' . $id . ']" id="priority' . $i . '" value="' . (int) $value . '"/>';

        return $html;
    }


    public static function complete($i, $complete = 0, $can_change = false, $parents = array(), $users = array(), $start = null)
    {

        HTMLHelper::_('bootstrap.tooltip', '.hasTooltip');

        $html = array();
        $uid  = Factory::getApplication()->getIdentity()->get('id');
        $nd   = Factory::getDbo()->getNullDate();

        $p_tooltip = null;
        $u_tooltip = null;
        $s_tooltip = null;


        if ($can_change) {
            // Check if the user is assigned to the task
            if (count($users)) {
                $can_change = false;

                foreach ($users AS $user)
                {
                    if ((int) $user->user_id == $uid) {
                        $can_change = true;
                    }
                }

                if (!$can_change) {
                    $u_tooltip = Text::_('COM_JOOMPROJECT_TASKS_NOT_ASSIGNED');
                }
            }

            // Check if all dependencies are completed
            if ($can_change && $complete == 0) {
                if (count($parents)) {
                    $req = array();

                    foreach ($parents AS $parent)
                    {
                        if ($parent->complete != '1') {
                            $can_change = false;
                            $req[] = htmlspecialchars($parent->title, ENT_COMPAT, 'UTF-8');
                        }
                    }

                    if (!$can_change) {
                        $p_tooltip = Text::_('COM_JOOMPROJECT_TASKS_DEPENDS_ON') . '::' . implode('<br/>', $req);
                    }
                }
            }

            // Check if the task can be started
            if ($can_change && $complete == 0) {
                if ($start && $start != $nd) {
                    $now = time();
                    $ts  = strtotime($start);

                    if ($ts > $now) {
                        $can_change = false;
                        $s_tooltip  = Text::_('COM_JOOMPROJECT_TASKS_NOT_STARTED') . '::' . JPDate::relative($start);
                    }
                }
            }
        }



        if ($can_change) {


            $class = ($complete ? ' btn-success active' : ' btn-secondary');
            $title = ($complete ? '' : Text::_('COM_JOOMPROJECT_FIELD_COMPLETE_LABEL'));
            $icon = ($complete ? 'checkbox-unchecked' : 'checkbox-unchecked');

            $html[] = '<div class="btn-group m-0">';
            $html[] = '<a id="complete-btn-' . $i . '" class=" btn btn-sm' . $class . '" data-bs-toggle="tooltip" data-bs-placement="top"  title="' . $title . '" href="javascript:void(0);" onclick="JPtask.complete(' . $i . ');">';
            $html[] = '<span aria-hidden="true" class="fas fa-check"></span>';
            $html[] = '</a>';
            $html[] = '</div>';
            $html[] = '<input type="hidden" id="complete' . $i . '" value="' . (int) $complete . '"/>';
        }
        else {
            $class = ($complete ? ' bg-success' : '');
            $icon  = ($complete ? 'fas fa-check' : 'fas fa-check');
            $title = '';

            if ($p_tooltip || $u_tooltip || $s_tooltip) {
                $class .= ' ';

                if ($p_tooltip) $title = ' title="' . $p_tooltip . '"';
                if ($u_tooltip) $title = ' title="' . $u_tooltip . '"';
                if ($s_tooltip) $title = ' title="' . $s_tooltip . '"';
            }

            $html[] = '<div class="hasTooltip btn-group m-0" "'.$title.'">';
            $html[] = '<a id="complete-btn-' . $i . '" class="btn btn-sm disabled' . $class . '" aria-disabled="true">';
            $html[] = '<span aria-hidden="true" class="' . $icon . '"></span>';
            $html[] = '</a>';
            $html[] = '</div>';
            $html[] = '<input type="hidden" id="complete' . $i . '" value="' . (int) $complete . '"/>';
        }

        return implode('', $html);
    }


    static public function priorityOptions()
    {
        $options   = array();

        $options[] =  HTMLHelper::_('select.option', '1', Text::_('COM_JOOMPROJECT_PRIORITY_VERY_LOW'));
        $options[] =  HTMLHelper::_('select.option', '2', Text::_('COM_JOOMPROJECT_PRIORITY_LOW'));
        $options[] =  HTMLHelper::_('select.option', '3', Text::_('COM_JOOMPROJECT_PRIORITY_MEDIUM'));
        $options[] =  HTMLHelper::_('select.option', '4', Text::_('COM_JOOMPROJECT_PRIORITY_HIGH'));
        $options[] =  HTMLHelper::_('select.option', '5', Text::_('COM_JOOMPROJECT_PRIORITY_VERY_HIGH'));

        return $options;
    }


    static public function completeOptions()
    {
        $options   = array();

        $options[] =  HTMLHelper::_('select.option', '0', Text::_('JNO'));
        $options[] =  HTMLHelper::_('select.option', '1', Text::_('JYES'));

        return $options;
    }
}