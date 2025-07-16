<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jpprojects
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Registry\Registry;


jimport('joomproject.framework');

// to support joomla other


class JPprojectsHelper
{
	/**
	 * The component name
	 *
	 * @var    string
	 */
	public static $extension = 'com_jpprojects';



    public static function adjustDataForSaveToCopy(&$data,$recordId){
        // Reset the repo dir when saving as copy
        if (isset($data['attribs']['repo_dir'])) {
            $dir = (int) $data['attribs']['repo_dir'];

            if ($dir) {
                $data['attribs']['repo_dir'] = 0;
            }
        }

        // Reset label id's
        if (isset($data['labels']) && is_array($data['labels'])) {
            foreach($data['labels'] AS $a => $g)
            {
                if (isset($g['id'])) {
                    foreach($g['id'] AS $k => $i)
                    {
                        $data['labels'][$a]['id'][$k] = 0;
                    }
                }
            }
        }

        if ($recordId) {
            // Store the current project id in session
            $context = "com_jpprojects.copy.project.id";
            $app     = Factory::getApplication();

            $app->setUserState($context, intval($recordId));

            $cfg = ComponentHelper::getParams('com_jpprojects');
            $create_group   = (int) $cfg->get('create_group');

            if ($create_group) {
                // Get the project attribs
                $db = Factory::getDbo();
                $query = $db->getQuery(true);

                $query->select('attribs')
                    ->from('#__jp_projects')
                    ->where('id = ' . (int) $recordId);

                $db->setQuery($query, 0, 1);
                $attribs = $db->loadResult();

                // Turn to JRegistry object
                $params = new Registry();
                $params->loadString((string)$attribs);

                // Get custom user group
                $group_id = (int) $params->get('usergroup');

                // Replicate existing custom group settings
                if ($group_id) {
                    // Copy component rules
                    if (isset($data['component_rules'])) {
                        $user     = Factory::getApplication()->getIdentity();
                        $is_admin = $user->authorise('core.admin');

                        foreach ($data['component_rules'] AS $component => $rules)
                        {
                            foreach ($rules AS $action => $groups)
                            {
                                if (!is_numeric($action) && is_array($groups)) {
                                    foreach ($groups AS $gid => $v)
                                    {
                                        if ($gid == $group_id) {
                                            if (!$is_admin && $action == 'core.admin') {
                                                // Dont allow non-admins to inject core admin permission
                                                unset($data['component_rules'][$component][$action]);
                                            }
                                            else {
                                                unset($data['component_rules'][$component][$action][$gid]);
                                                $data['component_rules'][$component][$action][0] = $v;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    // Copy item rules
                    if (isset($data['rules'])) {
                        foreach ($data['rules'] AS $action => $value)
                        {
                            if (is_numeric($action)) {
                                if ($value == $group_id) {
                                    $data['rules'][$action] = 0;
                                }
                            }
                            else {
                                foreach ($value AS $k => $v)
                                {
                                    if ($k == $group_id) {
                                        unset($data['rules'][$action][$k]);
                                        $data['rules'][$action][0] = $v;
                                    }
                                }
                            }
                        }
                    }

                    // Copy group members
                    $query->clear();
                    $query->select('user_id')
                        ->from('#__user_usergroup_map')
                        ->where('group_id = ' . (int) $group_id);

                    $db->setQuery($query);
                    $add_users = (array) $db->loadColumn();

                    $add_append = "";

                    if (!isset($data['add_groupuser'])) {
                        $data['add_groupuser'] = array();
                    }

                    if (isset($data['add_groupuser'][$group_id])) {
                        $add_append = $data['add_groupuser'][$group_id];

                        unset($data['add_groupuser'][$group_id]);
                    }

                    $data['add_groupuser'][0] = implode(',', $add_users) . ($add_append == '' ? '' : ',' . $add_append);
                }
            }
        }
    }


	/**
	 * Get a list of the activities
	 *
	 * @param jobject    The module parameters.
	 *
	 * @return    array
	 */
	public static function getAssignedUsers($filter_project = 0, $type = 0, $limit = null)
	{


		if ($filter_project == 0)
			$filter_project = JPApplicationHelper::getActiveProjectId();


		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('DISTINCT u.id, u.name, u.username,u.email');
		$query->from('#__users AS u');

		$joomAcitivitesEnabled = ComponentHelper::isInstalled('com_jpactivities') && ComponentHelper::isEnabled('com_jpactivities');

		if ($type == 0 && $joomAcitivitesEnabled)
		{ // participants

			$query->join('INNER', '#__user_activity AS ua ON ua.created_by = u.id');
			$query->join('INNER', '#__user_activity_items AS i ON i.asset_id = ua.item_id');
			// Filter by cross reference
			if (is_numeric($filter_project))
			{
				$query->where('i.xref_id = ' . (int) $filter_project);
			}

		}
		else
		{ // assigned users
			$query->join('LEFT', '#__jp_ref_observer AS rfo ON rfo.user_id = u.id');
			$query->where('rfo.project_id = ' . (int) $filter_project);
		}

		if (!is_null($limit) && is_numeric($limit))
		{
			$query->setLimit((int) $limit);
		}


		$db->setQuery($query);

		$data = $db->loadObjectList();


		$colorsList = ['#1abc9c', '#2ecc71', '#3498db', '#9b59b6', '#f1c40f', '#e67e22', '#e74c3c', '#f39c12', '#d35400', '#c0392b', '#27ae60', '#2980b9'];


		foreach ($data as &$item)
		{
			$item->backgroundColor = $colorsList[array_rand($colorsList, 1)];
			$item->link            = "index.php?option=com_jpusers&view=user&id=$item->id:$item->username";
			$item->img             = HTMLHelper::_('joomproject.avatar.path', $item->id);
			if ($item->img == '/joomproject/media/com_joomproject/joomproject/images/icons/avatar.jpg')
			{
				$item->img = substr(strtoupper($item->name), 0, 1);

				$item->hasImage = false;
			}
			else
			{
				$item->img = '<img class="rounded-circle"  title="' . $item->name . '" src="' . HTMLHelper::_('joomproject.avatar.path', $item->id) . '" />';
				$item->hasImage = true;
			}

		}

		return $data;
	}

	/**
	 * Configure the Linkbar.
	 *
	 * @param string $view The name of the active view.
	 *
	 * @return    void
	 */
	public static function addSubmenu($view)
	{
		JoomprojectHelper::addSubmenu($view);
	}


	/**
	 * Gets a list of actions that can be performed.
	 *
	 * @param int $id The item id
	 *
	 * @return    object    $result
	 */
	public static function getActions($id = 0)
	{
		$user   = Factory::getApplication()->getIdentity();
		$result = new CMSObject;
		$asset  = (empty($id) ? self::$extension : 'com_jpprojects.project.' . (int) $id);

		$actions = array(
			'core.view',
			'core.admin', 'core.manage',
			'core.create', 'core.edit',
			'core.edit.own', 'core.edit.state',
			'core.delete'
		);

		foreach ($actions as $action)
		{
			$result->set($action, $user->authorise($action, $asset));
		}

		return $result;
	}

	/**
	 * Returns a valid section for articles. If it is not valid then null
	 * is returned.
	 *
	 * @param string $section The section to get the mapping for
	 *
	 * @return  string|null  The new section
	 *
	 * @since   3.7.0
	 */
	public static function validateSection($section)
	{
		if (Factory::getApplication()->isClient('site'))
		{

			if ($section == 'form')
			{
				$section = 'project';
			}

		}

		return $section;
	}

	/**
	 * Returns valid contexts
	 *
	 * @return  array
	 *
	 * @since   3.7.0
	 */
	public static function getContexts()
	{
		Factory::getLanguage()->load('com_jpprojects', JPATH_ADMINISTRATOR);

		$contexts = array(
			'com_jpprojects.project' => Text::_('COM_JPPROJECTS_PROJECT')
		);

		return $contexts;
	}
}
