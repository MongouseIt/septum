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


use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Form\Field\ListField;


FormHelper::loadFieldClass('list');

class JFormFieldJPtask extends ListField{

    public $type = 'jptask';

    private function getTasks(){

        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('t.id,t.title');
        $query->from('#__jp_tasks AS t');
        $query->where('t.state = 1');
        $db->setQuery($query);
        $list = $db->loadObjectList();
        $list[] = array('id' => 0,'title' => 'Select Task');

        return $list;
    }

    protected function getRtasks(){
        $id = Factory::getApplication()->input->get('id',0,'int');

        if(!$id)
            return false;

        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select('t.id,r.task_id,t.title');
        $query->from('#__jp_tasks AS t');
        $query->join('LEFT', '#__jp_reminders AS r ON r.task_id = t.id');
        $query->where('r.id ='.$id);
        $db->setQuery($query);

        return $db->loadObject();
    }

    public function getInput()
    {
        $rtask = $this->getRtasks();

        echo HTMLHelper::_('select.genericlist',$this->getTasks(),$this->name, array('class'=>'required'), 'id', 'title', isset($rtask->task_id) ? $rtask->task_id : 0);

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
