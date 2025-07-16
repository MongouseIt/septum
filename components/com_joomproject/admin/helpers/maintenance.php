<?php
/**
 * @package      Joomproject
 * @subpackage   Dashboard
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */


defined('_JEXEC') or die();

use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

jimport('joomproject.framework');


class JoomprojectHelperMaintenance
{


    public static function executeSqlUpdateFile(){

        if(!JFactory::getUser()->authorise('core.admin'))
            return 'not allowed';

        $version = JFactory::getApplication()->input->get('version',0,'string');

        $file = JPATH_ADMINISTRATOR.'/components/com_joomproject/sql/updates/mysql/'.$version.'.sql';

        if(!file_exists($file))
            die('Invalid file');

        $query = file_get_contents($file);


        $db = JFactory::getDbo();

        $query = JDatabaseDriver::splitSql($query);

        if (!empty($query))
        {
            foreach ($query as $queryLine)
            {
                try
                {
                    $db->setQuery($queryLine)->execute();
                }
                catch (Exception $e)
                {

                    // Still render the error message from the Exception object
                    \Joomla\CMS\Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                }
            }
        }

        die('Done');

    }


	/*
	 * This method check if some projects still doesn't use view action instead of using Access Level
	 */

	public static function updateProjectsWithNoViewAction(){

		$db = Factory::getDbo();

		// get projects list without view action
		$query = $db->getQuery(true);
		$query->select('p.*,vl.rules');
		$query->from('#__jp_projects p');
		$query->leftJoin('#__viewlevels vl on p.access = vl.id');
		$query->where('p.id NOT IN(SELECT itemid FROM #__jp_groups_view_action)');
		//$query->where('va.groupid IS NULL');
		$db->setQuery($query);

		$projects = $db->loadObjectList();

		// if no projects found, no maintenance done
		if(is_null($projects))
			return false;

		// update projects to use view action

		$updatedProject = 0;

		foreach ($projects as $project){

			// if project has no rules skip it
			if(empty($project->rules))
				continue;

			$rules = json_decode($project->rules);

			// add rules to groups view action tab
			foreach ($rules as $rule){

				$components = ['forum','comments','designs','tasks','milestones','projects','repo','time'];

				foreach($components as $component){

					$viewaction = new stdClass();
					$viewaction->itemid = $project->id;
					$viewaction->groupid = $rule;
					$viewaction->type = 'project';
					$viewaction->component = $component;

					$result = Factory::getDbo()->insertObject('#__jp_groups_view_action', $viewaction);
				}

			}

			$updatedProject++;
		}


		if($updatedProject > 0){
			Factory::getApplication()->enqueueMessage($updatedProject.' projects updated to use view action','success');

		}

		return true;

	}

    /*
     * Add Menu of all views
     */
    public static function addMenu(){

        // Check if the menu exists.
        if (!self::checkMenuExists()) {
            self::writeMenu();
        }

    }

    /*
     * Create menu module on installation
     */
    public static function createMenuModule($route){

        // create module of menu
        if (strtolower($route) == 'install' && self::checkMenuExists()) {

            $mm_pos = 'sidebar-right';
            $mm_st = '1';

            $module = Table::getInstance('module');
            $module->set('title', 'JoomProject Menu');
            $module->set('module', 'mod_menu');
            $module->set('access', '1');
            $module->set('showtitle', $mm_st);
            $module->set('client_id', 0);
            $module->set('language', '*');
            $module->set('position', $mm_pos);
            $module->set('params', '{"menutype":"joomproject"}');

            $module->store();

            // Notify the user about the module position
            $format = 'A JoomProject navigation module has been created on position "%s". You may need to change it in the Module Manager to fit into your template.';
            $app = Factory::getApplication();

            $app->enqueueMessage(Text::sprintf($format, $mm_pos));

            return true;

        }

        return false;

    }


    /**
     * Method to create the JOOM menu.
     *
     * @access  public
     * @return  void
     */
    public static function writeMenu()
    {
        // Initialise variables.
        $app = Factory::getApplication();
        $db = Factory::getDbo();

        // Define the menu strings.
        $title = 'JoomProject';
        $desc = 'JoomProjet Menu';

        // Columns and values to insert.
        $columns = array('menutype', 'title', 'description');
        $values = array($db->quote('joomproject'), $db->quote($title), $db->quote($desc));

        $query = $db->getQuery(true)
            ->insert($db->quoteName('#__menu_types'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));
        try {
            $db->setQuery($query);
            $db->execute();
        } catch (RuntimeException $e) {
            $app->enqueueMessage($e->getMessage());

            return false;
        }

        // Add default menu items.
        self::addDefaultMenuItems();

        return true;
    }

    /**
     * Method to add default Joomproject menu items.
     *
     * @access  public
     * @return  void
     */
    public static function addDefaultMenuItems()
    {
        // Initialise variables.
        $app = Factory::getApplication();
        $db = Factory::getDbo();
        $file = JPATH_ROOT . '/administrator/components/com_joomproject/toolbar.xml';

        $xml = new SimpleXMLElement($file, null, true);

        if ($xml) {

            $items = $xml->items;


            foreach ($items->children() as $item) {


                if($item->name == 'Reminders')
                    continue;


                // use model to add menu items
                /** @var  Joomla\Component\Installer\Administrator\Model\ManageModel $menuItemModel */
                $menuItemModel = Factory::getApplication()->bootComponent('com_menus')
                    ->getMVCFactory()->createModel('Item', 'Administrator', ['ignore_request' => true]);


                $menuItemData = new stdClass();
                $menuItemData->title = empty($item->name) ? "" : "$item->name";
                $menuItemData->alias = empty($item->alias) ? "" : self::getAlias($item->alias);
                //$menuItemData->path = $menuItemData->alias;
                $menuItemData->link = empty($item->link) ? "" : "$item->link";
                $menuItemData->menutype = 'joomproject';
                $menuItemData->type = 'component';
                $menuItemData->component_id = ComponentHelper::getComponent('com_'.$item['component'])->id;
                $menuItemData->published = 1;
                $menuItemData->parent_id = 1;
                $menuItemData->access = 1;
                $menuItemData->home = 0;
                $menuItemData->language = '*';

                $menuItemModel->save((array) $menuItemData);


            }
        }

        return true;
    }

    public static function checkMenuExists()
    {
        // Initialise variables.
        $app = Factory::getApplication();
        $db = Factory::getDbo();

        $query = $db->getQuery(true)
            ->select('id')
            ->from('#__menu_types')
            ->where('menutype = ' . $db->quote('joomproject'));
        try {
            $db->setQuery($query);

            return $db->loadResult();
        } catch (RuntimeException $e) {
            $app->enqueueMessage($e->getMessage());

            return false;
        }
    }

    public static function fixBrokenMenuItems()
    {
        // Initialise variables.
        $app = Factory::getApplication();
        $db = Factory::getDbo();

        // Get component id.
        $component = ComponentHelper::getComponent('com_joomproject');
        $component_id = 0;

        if (is_object($component) && isset($component->id)) {
            $component_id = $component->id;
        }

        if ($component_id > 0) {
            // Re-associate JOOM menu items with component ID.
            $fields = array(
                $db->quoteName('component_id') . ' = ' . $db->quote($component_id)
            );

            $conditions = array(
                $db->quoteName('link') . ' LIKE ' . $db->Quote('%option=com_joomproject%')
            );

            $query = $db->getQuery(true)
                ->update($db->quoteName('#__menu'))->set($fields)->where($conditions);
            try {
                $db->setQuery($query);
                $db->execute();
            } catch (RuntimeException $e) {
                $app->enqueueMessage($e->getMessage());

                return false;
            }
        }

        return true;
    }

    /**
     * Method to validate the alias, and prevent duplciates.
     *
     * @access  public
     * @return  void
     */
    public static function getAlias($alias)
    {
        // Initialise variables.
        $app = Factory::getApplication();
        $db = Factory::getDbo();

        // Sanitise the alias.
        $alias = OutputFilter::stringURLSafe($alias);

        // Check for duplicates.
        $db = Factory::getDBO();
        $query = $db->getQuery(true)
            ->select('id')
            ->from('#__menu')
            ->where($db->quoteName('alias') . '=' . $db->quote($alias));
        try {
            $db->setQuery($query);
            $duplicate = $db->loadResult();
        } catch (RuntimeException $e) {
            $app->enqueueMessage($e->getMessage());

            return false;
        }

        if ($duplicate) {
            $alias = $alias . '-media';

            return self::getAlias($alias);
        } else {
            return $alias;
        }
    }







}