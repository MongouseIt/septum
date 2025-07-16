<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_joomproject
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Helper\ModuleHelper;

class JoomprojectHelperFrontend{

    public static function autoCheckin($table,$id){

        $db = Factory::getDBO();

        $checkOut = new stdClass();
        $checkOut->id = $id;
        $checkOut->checked_out = 0;
        $checkOut->checked_out_time = NULL;

        $db->updateObject('#__'.$table,$checkOut,'id');

    }

    public static function getEmptyTaskLists()
    {
        $app = Factory::getApplication();
        $params = ComponentHelper::getParams('com_jptasks');

        // Return empty if setting is disabled or filtering by specific list
        if (!$params->get('show_empty_tasklists', 0) || $app->input->getInt('filter_tasklist', 0) > 0) {
            return [];
        }

        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        // Get current filters
        $projectId = $app->input->getInt('filter_project', 0);
        $milestoneId = $app->input->getInt('filter_milestone', 0);

        // If no project selected, return empty array
        if ($projectId <= 0) {
            return [];
        }

        // Base query for task lists
        $query->select('tl.id, tl.title, tl.description, tl.alias, tl.checked_out as checked_out_list, tl.created_by')
            ->from('#__jp_task_lists AS tl')
            ->where('tl.project_id = ' . $projectId)
            ->where('tl.state = 1');

        // Exclude lists that have tasks
        $query->leftJoin('#__jp_tasks AS t ON t.list_id = tl.id');

        // If milestone filter is active, only include lists without tasks in that milestone
        if ($milestoneId > 0) {
            $query->where('(t.milestone_id != ' . $milestoneId . ' OR t.milestone_id IS NULL)');
        } else {
            $query->where('t.id IS NULL');
        }

        $query->group('tl.id')
            ->having('COUNT(t.id) = 0')
            ->order('tl.ordering ASC');

        $db->setQuery($query);
        return $db->loadAssocList('id');
    }


    public static function groupTasksByTaskLists($tasks){

        $grouped_tasks = [];

        foreach ($tasks as $task) {

            $list_id = $task->list_id;

            if (!isset($grouped_tasks[$list_id])) {
                $grouped_tasks[$list_id] = [
                    'title' => $task->list_title,
                    'description' => $task->list_description,
                    'alias' => $task->list_alias,
                    'created_by' => $task->list_created_by,
                    'checked_out_list' => $task->checked_out_list,
                    'tasks' => []
                ];
            }

            $grouped_tasks[$list_id]['tasks'][] = $task;
        }


        return $grouped_tasks;


    }


	public static function arrayGroupBy($arr, $key)
	{
		if (!is_array($arr)) {
			trigger_error('array_group_by(): The first argument should be an array', E_USER_ERROR);
		}
		if (!is_string($key) && !is_int($key) && !is_float($key) && !is_callable($key)) {
			trigger_error('array_group_by(): The key should be a string, an integer, a float, or a function', E_USER_ERROR);
		}

		$isFunction = !is_string($key) && is_callable($key);

		// Load the new array, splitting by the target key
		$grouped = [];
		foreach ($arr as $value) {
			$groupKey = null;

			if ($isFunction) {
				$groupKey = $key($value);
			} else if (is_object($value)) {
				$groupKey = $value->{$key};
			} else {
				$groupKey = $value[$key];
			}

			$grouped[$groupKey][] = $value;
		}

		// Recursively build a nested grouping if more parameters are supplied
		// Each grouped array value is grouped according to the next sequential key
		if (func_num_args() > 2) {
			$args = func_get_args();

			foreach ($grouped as $groupKey => $value) {
				$params = array_merge([$value], array_slice($args, 2, func_num_args()));
				$grouped[$groupKey] = call_user_func_array('array_group_by', $params);
			}
		}

		return $grouped;
	}

    public static function renderModules($position) {

        $modules = ModuleHelper::getModules($position);
        $isRoot    = Factory::getApplication()->getIdentity()->get('isRoot');

        if (count($modules) > 0) {

            foreach ($modules as $mod) {

                $moduleContent = LayoutHelper::render(
                    'common.modulecard',
                    ['mod' => $mod],
                    JPATH_ADMINISTRATOR.'/components/com_joomproject/layouts/'
                );


                echo $moduleContent;
            }


        }elseif($isRoot){
            echo '<p class="alert alert-warning">No module published in position <span class="badge bg-primary">'.$position.'</span></p>';
        }
    }

	/*
	 * File extension to fontawesome icon
	 */
    public static function extToIcon($ext){

	    $mapping = array(
		    'ai'      => 'pen-nib',
		    'aif'     => 'file-audio',
		    'aifc'    => 'file-audio',
		    'aiff'    => 'file-audio',
		    'atom'    => 'rss',
		    'au'      => 'file-audio',
		    'avi'     => 'file-video',
		    'bmp'     => 'image/bmp',
		    'csv'     => 'file-csv',
		    'doc'     => 'file-word',
		    'docx'     => 'file-word',
		    'gif'     => 'file-image',
		    'htm'     => 'file-code',
		    'html'    => 'file-code',
		    'css'    => 'file-code',
		    'jpe'     => 'file-image',
		    'jpeg'    => 'file-image',
		    'jpg'     => 'file-image',
		    'js'      => 'file-code',
		    'json'    => 'file-code',
		    'kar'     => 'file-audio',
		    'm3u'     => 'file-audio',
		    'mid'     => 'file-audio',
		    'midi'    => 'file-audio',
		    'mov'     => 'file-video',
		    'movie'   => 'file-video',
		    'mp2'     => 'file-audio',
		    'mp3'     => 'file-audio',
		    'mpe'     => 'file-video',
		    'mp4'     => 'file-video',
		    'webm'     => 'file-video',
		    'mpeg'    => 'file-video',
		    'mpg'     => 'file-video',
		    'mpga'    => 'file-video',
		    'mxu'     => 'file-video',
		    'ogg'     => 'file-video',
		    'pbm'     => 'file-image',
		    'pdf'     => 'file-pdf',
		    'pgm'     => 'file-image',
		    'png'     => 'file-image',
		    'pnm'     => 'file-image',
		    'ppm'     => 'file-image',
		    'ppt'     => 'file-powerpoint',
		    'qt'      => 'file-video',
		    'ra'      => 'file-audio',
		    'ram'     => 'file-audio',
		    'rgb'     => 'file-image',
		    'rss'     => 'rss',
		    'rtf'     => 'file-alt',
		    'rtx'     => 'file-alt',
		    'sgm'     => 'file-alt',
		    'sgml'    => 'file-alt',
		    'svg'     => 'file-image',
		    'svgz'    => 'file-image',
		    'tar'     => 'file-archive',
		    'tif'     => 'file-image',
		    'tiff'    => 'file-image',
		    'webp'    => 'file-image',
		    'txt'     => 'file-alt',
		    'wav'     => 'file-audio',
		    'wbmp'    => 'file-image',
		    'xbm'     => 'file-image',
		    'xht'     => 'file-code',
		    'xhtml'   => 'file-code',
		    'xls'     => 'file-excel',
		    'xml'     => 'file-code',
		    'xpm'     => 'file-image',
		    'xsl'     => 'file-excel',
		    'xslt'    => 'file-excel',
		    'zip'     => 'file-archive'
	    );

	    if(array_key_exists($ext,$mapping))
	    	return $mapping[$ext];
	    else
	    	return 'file';

    }


}