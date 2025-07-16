<?php
/**
 * @package      Joomproject
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-1018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;


class JPnotificationsHelper
{
    public static function formatChanges(&$lang, $changes)
    {
        $format = array();

        foreach ($changes AS $field => $value)
        {

        	if($field == 'complete' and $value == 1){ // for task completion status set to yes
		        $label = '* ' . $lang->_('COM_JOOMPROJECT_FIELD_COMPLETION_LABEL') . ': ';
		        $data  = $lang->_('COM_JOOMPROJECT_FIELD_COMPLETED_LABEL');

	        }elseif($field == 'complete' and $value == 0){ // for task completion status set to no
		        $label = '* ' . $lang->_('COM_JOOMPROJECT_FIELD_COMPLETION_LABEL') . ': ';
		        $data  = $lang->_('COM_JOOMPROJECT_FIELD_NCOMPLETED_LABEL');
	        }else{
		        $label = '* ' . $lang->_('COM_JOOMPROJECT_EMAIL_LABEL_' . strtoupper($field)) . ': ';
		        $data  = self::translateValue($field, $value);
	        }

            $format[] = $label . "\n" . $data;
        }

        return implode("\n", $format);
    }


    public static function translateValue($field, $value)
    {
        static $access_titles  = array();
        static $cat_titles     = array();
        static $project_titles = array();
        static $ms_titles      = array();
        static $list_titles    = array();
        static $topic_titles   = array();
        static $task_titles    = array();
        static $dir_titles     = array();
        static $note_titles    = array();
        static $file_titles    = array();
        static $user_names     = array();

        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        switch ($field)
        {
            case 'description':
                $data = strip_tags($value);
                break;

            case 'start_date':
            case 'end_date':
                $data = HTMLHelper::_('date', $value, Text::_('DATE_FORMAT_LC3'));
                break;

            case 'access':
                if (array_key_exists($value, $access_titles)) {
                    $data = $access_titles[$value];
                }
                else {
                    $query->clear();
                    $query->select('title')
                          ->from('#__viewlevels')
                          ->where('id = ' . $db->quote((int) $value));

                    $db->setQuery($query);
                    $title = $db->loadResult();

                    $access_titles[$value] = $title;
                    $data = $access_titles[$value];
                }
                break;

            case 'catid':
                if (array_key_exists($value, $cat_titles)) {
                    $data = $cat_titles[$value];
                }
                else {
                    if ($value > 0) {
                        $query->clear();
                        $query->select('title')
                              ->from('#__categories')
                              ->where('id = ' . $db->quote((int) $value));

                        $db->setQuery($query);
                        $title = $db->loadResult();
                    }
                    else {
                        $title = '-';
                    }

                    $cat_titles[$value] = $title;
                    $data = $cat_titles[$value];
                }
                break;

            case 'project_id':
                if (array_key_exists($value, $project_titles)) {
                    $data = $project_titles[$value];
                }
                else {
                    if ($value > 0) {
                        $query->clear();
                        $query->select('title')
                              ->from('#__jp_projects')
                              ->where('id = ' . $db->quote((int) $value));

                        $db->setQuery($query);
                        $title = $db->loadResult();
                    }
                    else {
                        $title = '-';
                    }

                    $project_titles[$value] = $title;
                    $data = $project_titles[$value];
                }
                break;

            case 'milestone_id':
                if (array_key_exists($value, $ms_titles)) {
                    $data = $ms_titles[$value];
                }
                else {
                    if ($value > 0) {
                        $query->clear();
                        $query->select('title')
                              ->from('#__jp_milestones')
                              ->where('id = ' . $db->quote((int) $value));

                        $db->setQuery($query);
                        $title = $db->loadResult();
                    }
                    else {
                        $title = '-';
                    }

                    $ms_titles[$value] = $title;
                    $data = $ms_titles[$value];
                }
                break;

            case 'list_id':
                if (array_key_exists($value, $list_titles)) {
                    $data = $list_titles[$value];
                }
                else {
                    if ($value > 0) {
                        $query->clear();
                        $query->select('title')
                              ->from('#__jp_task_lists')
                              ->where('id = ' . $db->quote((int) $value));

                        $db->setQuery($query);
                        $title = $db->loadResult();
                    }
                    else {
                        $title = '-';
                    }

                    $list_titles[$value] = $title;
                    $data = $list_titles[$value];
                }
                break;

            case 'task_id':
                if (array_key_exists($value, $task_titles)) {
                    $data = $task_titles[$value];
                }
                else {
                    if ($value > 0) {
                        $query->clear();
                        $query->select('title')
                              ->from('#__jp_tasks')
                              ->where('id = ' . $db->quote((int) $value));

                        $db->setQuery($query);
                        $title = $db->loadResult();
                    }
                    else {
                        $title = '-';
                    }

                    $task_titles[$value] = $title;
                    $data = $task_titles[$value];
                }
                break;

            case 'topic_id':
                if (array_key_exists($value, $topic_titles)) {
                    $data = $topic_titles[$value];
                }
                else {
                    if ($value > 0) {
                        $query->clear();
                        $query->select('title')
                              ->from('#__jp_topics')
                              ->where('id = ' . $db->quote((int) $value));

                        $db->setQuery($query);
                        $title = $db->loadResult();
                    }
                    else {
                        $title = '-';
                    }

                    $topic_titles[$value] = $title;
                    $data = $topic_titles[$value];
                }
                break;

            case 'directory_id':
                if (array_key_exists($value, $dir_titles)) {
                    $data = $dir_titles[$value];
                }
                else {
                    if ($value > 0) {
                        $query->clear();
                        $query->select('title')
                              ->from('#__jp_repo_dirs')
                              ->where('id = ' . $db->quote((int) $value));

                        $db->setQuery($query);
                        $title = $db->loadResult();
                    }
                    else {
                        $title = '-';
                    }

                    $dir_titles[$value] = $title;
                    $data = $dir_titles[$value];
                }
                break;

            case 'file_id':
                if (array_key_exists($value, $file_titles)) {
                    $data = $file_titles[$value];
                }
                else {
                    if ($value > 0) {
                        $query->clear();
                        $query->select('title')
                              ->from('#__jp_repo_files')
                              ->where('id = ' . $db->quote((int) $value));

                        $db->setQuery($query);
                        $title = $db->loadResult();
                    }
                    else {
                        $title = '-';
                    }

                    $file_titles[$value] = $title;
                    $data = $file_titles[$value];
                }
                break;

            case 'note_id':
                if (array_key_exists($value, $note_titles)) {
                    $data = $note_titles[$value];
                }
                else {
                    if ($value > 0) {
                        $query->clear();
                        $query->select('title')
                              ->from('#__jp_repo_notes')
                              ->where('id = ' . $db->quote((int) $value));

                        $db->setQuery($query);
                        $title = $db->loadResult();
                    }
                    else {
                        $title = '-';
                    }

                    $note_titles[$value] = $title;
                    $data = $note_titles[$value];
                }
                break;

            case 'created_by':
                if (array_key_exists($value, $user_names)) {
                    $data = $user_names[$value];
                }
                else {
                    if ($value > 0) {
                        $query->clear();
                        $query->select('name')
                              ->from('#__users')
                              ->where('id = ' . $db->quote((int) $value));

                        $db->setQuery($query);
                        $name = $db->loadResult();
                    }
                    else {
                        $name = '-';
                    }

                    $user_names[$value] = $name;
                    $data = $user_names[$value];
                }
                break;

            case 'priority':
                if (class_exists('JPtasksHelper')) {
                    $data = JPtasksHelper::priority2string($value);
                }
                else {
                    $data = $value;
                }
                break;

            default:
                $data = $value;
                break;
        }

        return $data;
    }


    public static function isSupported($context, $params = null)
    {
        list($component, $item) = explode('.', $context, 2);

        $components = JPApplicationHelper::getComponents('com_jpreminders');

        if (!array_key_exists($component, $components)) {
            return false;
        }

        if (!self::getComponentHelper($component)) {
            return false;
        }

        $class_name = 'JP' . str_replace('com_jp', '', $component) . 'NotificationsHelper';
        $methods    = get_class_methods($class_name);

        if (!in_array('isSupported', $methods)) {
            return false;
        }

        if (is_object($params)) {
            $settings = array(
                'com_jpprojects'   => (int) $params->get('projects_enabled', 1),
                'com_jpmilestones' => (int) $params->get('milestones_enabled', 1),
                'com_jptasks'      => (int) $params->get('tasks_enabled', 1),
                'com_jpforum'      => (int) $params->get('forum_enabled', 1),
                'com_jprepo'       => (int) $params->get('repo_enabled', 1),
                'com_jpdesigns'    => (int) $params->get('designs_enabled', 1)
            );

            if (array_key_exists($component, $settings)) {
                if (!$settings[$component]) {
                    return false;
                }
            }
        }

        $supported = call_user_func(array($class_name, 'isSupported'), $context);

        return $supported;
    }


    public static function getComponentHelper($component)
    {
        $helper_file = JPATH_ADMINISTRATOR . '/components/' . $component . '/helpers/notifications.php';
        $class_name  = 'JP' . str_replace('com_jp', '', $component) . 'NotificationsHelper';

        if (file_exists($helper_file)) {
            JLoader::register($class_name, $helper_file);
        }

        if (!class_exists($class_name)) {
            return false;
        }

        return true;
    }
}