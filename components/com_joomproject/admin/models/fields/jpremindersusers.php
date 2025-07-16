<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_reminders
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die;


use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Form\Field\ListField;


FormHelper::loadFieldClass('list');

class JFormFieldJPremindersusers extends ListField{

    public $type = 'JPremindersusers';

    private function getUsers(){


        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('DISTINCT u.id,u.name');
        $query->from('#__users AS u');
        $query->join('INNER', '#__user_usergroup_map AS ugm ON ugm.user_id = u.id');
        $query->where('u.block = 0');
        $db->setQuery($query);

        return $db->loadObjectList();
    }

    protected function getReminderUsers(){
        $id = Factory::getApplication()->input->get('id',0,'int');

        if(!$id)
            return false;

        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select('r.id,r.assigned_users');
        $query->from('#__jp_reminders AS r');
       // $query->join('LEFT', '#__jp_reminders AS r ON r.task_id = t.id');
        $query->where('r.id ='.$id);
        $db->setQuery($query);
        return $db->loadObject();
       
    }

    public function getInput()
    {

    	$reminderUsers = $this->getReminderUsers();

        if(!empty($reminderUsers->assigned_users)){
            $users = implode(',',json_decode($reminderUsers->assigned_users, true));
            $reminderUsers->assigned_users  = explode(',',$users);
        }

        echo HTMLHelper::_(
        	'select.genericlist',
	        $this->getUsers(),
	        $this->name,
	        array('multiple'=>'multiple'),
	        'id',
	        'name',
	        isset($reminderUsers->assigned_users) ? $reminderUsers->assigned_users : ''
        );

    }

    protected function getRenderer($layoutId = 'default')
    {

        $renderer = new FileLayout($layoutId, null,
            [
                "client" => 1,
                'component' => 'com_joomproject'
            ]
        );


        return $renderer;
    }
}
