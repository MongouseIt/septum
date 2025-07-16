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
jimport('joomproject.framework');
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\Helpers\Sidebar;


class JoomprojectHelper
{

    public static $extension = 'com_joomproject';
	
    public static function getVersion() {
    	$table		= Table::getInstance('Extension');
    	$table->load(array('element' => 'com_joomproject'));
    	$registry	= new Registry($table->manifest_cache);
    	
    	return $registry->get('version');
    }

    public static function loadSidebarAssets(){

	    HTMLHelper::_('jquery.framework');
        HTMLHelper::stylesheet( Uri::root().'media/com_joomproject/joomproject/css/menuBuilder.css');
        HTMLHelper::script(Uri::root().'media/com_joomproject/joomproject/js/menuBuilder.js', array('version' => 'auto', 'relative' => true));

    }
    
    
    public static function getCopyright(){
    	
    	return '<span class="badge bg-success">Current Version '.self::getVersion().'</span> Copyright 2013 - '.date('Y').' by <strong><a href="https://www.joomboost.com" target="_blank">JoomBoost</a></strong>';
    	
    }
    
    public static function addBadges($stats){
    	
    	foreach ($stats as &$stat){
    		if($stat == 0)
    			$stat = "<span class='badge px-2 rounded-pill bg-secondary'>$stat</span>";
    			else
    				$stat = "<span class='badge px-2 rounded-pill bg-success'>$stat</span>";
    	}
    	
    	return $stats;
    	
    }	

    /**
     * Configure the Linkbar.
     *
     * @param     string    $view    The name of the active view.
     *
     * @return    void
     */    
    public static function addSubmenu($view)
    {
		self::loadSidebarAssets();

        $extension = Factory::getApplication()->input->get->get('extension','','string');


		// get context for custom fields to check if view is active
    	$context = Factory::getApplication()->input->get('context','','string');

		// dashboard
	    Sidebar::addEntry(
		    '<i class="fas fa-tachometer-alt isParent"></i>  '.Text::_('COM_JOOMPROJECT_DASHBOARD_TITLE'),
		    'index.php?option=com_joomproject&view=dashboard',
		    $view == 'dashboard'
	    );

	    // projects
	    if (ComponentHelper::isEnabled('com_jpprojects')){
		    Sidebar::addEntry(
			    '<i class="fas fa-briefcase isParent"></i>  '.Text::_('COM_JOOMPROJECT_SUBMENU_PROJECTS'),
			    '#',
			    $view == 'projects'
		    );

		    Sidebar::addEntry(
			    '<i class="fas fa-list isChild"></i>  '.Text::_('COM_JOOMPROJECT_SUBMENU_MANAGE_PROJECTS'),
			    'index.php?option=com_jpprojects&view=projects',
			    $view == 'projects'
		    );
		    Sidebar::addEntry(
			    '<i class="fas fa-folder isChild"></i>  '.Text::_('COM_JOOMPROJECT_SUBMENU_CATEGORIES'),
			    'index.php?option=com_categories&extension=com_jpprojects',
			    $view == 'categories' && $extension == 'com_jpprojects'
		    );

		    if (ComponentHelper::isEnabled('com_fields')) {
			    Sidebar::addEntry(
				    '<i class="fas fa-cube isChild"></i>  '.Text::_('COM_JOOMPROJECT_SUBMENU_CUSTOMFIELDS'),
				    'index.php?option=com_fields&context=com_jpprojects.project',
				    $context == 'com_jpprojects.project' && $view == "fields.fields"
			    );

			    Sidebar::addEntry(
				    '<i class="fas fa-cubes isChild"></i>  '.Text::_('COM_JOOMPROJECT_SUBMENU_CUSTOMFIELDS_GROUP'),
				    'index.php?option=com_fields&view=groups&context=com_jpprojects.project',
				    $context == 'com_jpprojects.project' && $view == "fields.groups"
			    );
		    }


		    Sidebar::addEntry(
			    '<i class="fas fa-cog isChild"></i>  '.Text::_('COM_JOOMPROJECT_SETTINGS'),
			    'index.php?option=com_config&view=component&component=com_jpprojects',
			    $view == 'component'
		    );
	    }


	    // milestones
	    if (ComponentHelper::isEnabled('com_jpmilestones')){
		    Sidebar::addEntry(
			    '<i class="fas fa-flag isParent"></i>  '.Text::_('COM_JOOMPROJECT_SUBMENU_MILESTONES'),
			    '#',
			    $view == 'milestones'
		    );

		    Sidebar::addEntry(
			    '<i class="fas fa-list isChild"></i>  '.Text::_('COM_JOOMPROJECT_SUBMENU_MANAGE_MILESTONES'),
			    'index.php?option=com_jpmilestones&view=milestones',
			    $view == 'milestones'
		    );



            Sidebar::addEntry(
                '<i class="fas fa-folder isChild"></i>  '.Text::_('COM_JOOMPROJECT_SUBMENU_CATEGORIES'),
                'index.php?option=com_categories&extension=com_jpmilestones',
                $view == 'categories' && $extension == 'com_jpmilestones'
            );

		    if (ComponentHelper::isEnabled('com_fields')) {
			    Sidebar::addEntry(
				    '<i class="fas fa-cube isChild"></i>  '.Text::_('COM_JOOMPROJECT_SUBMENU_CUSTOMFIELDS'),
				    'index.php?option=com_fields&context=com_jpmilestones.milestone',
				    $context == 'com_jpmilestones.milestone' && $view == "fields.fields"
			    );

			    Sidebar::addEntry(
				    '<i class="fas fa-cubes isChild"></i>  '.Text::_('COM_JOOMPROJECT_SUBMENU_CUSTOMFIELDS_GROUP'),
				    'index.php?option=com_fields&view=groups&context=com_jpmilestones.milestone',
				    $context == 'com_jpmilestones.milestone' && $view == "fields.groups"
			    );
		    }

		    Sidebar::addEntry(
			    '<i class="fas fa-cog isChild"></i>  '.Text::_('COM_JOOMPROJECT_SETTINGS'),
			    'index.php?option=com_config&view=component&component=com_jpmilestones',
			    $view == 'component'
		    );
	    }



	    // Tasks
	    if (ComponentHelper::isEnabled('com_jptasks')){
		    Sidebar::addEntry(
			    '<i class="fas fa-tasks isParent"></i>  '.Text::_('COM_JOOMPROJECT_SUBMENU_TASKS'),
			    '#',
			    $view == 'tasks'
		    );
		    Sidebar::addEntry(
			    '<i class="fas fa-list isChild"></i>  '.Text::_('COM_JOOMPROJECT_SUBMENU_MANAGE_TASKS'),
			    'index.php?option=com_jptasks&view=tasks',
			    $view == 'tasks'
		    );
		    Sidebar::addEntry(
			    '<i class="fas fa-list-alt isChild"></i>  '.Text::_('COM_JOOMPROJECT_SUBMENU_TASKLISTS'),
			    'index.php?option=com_jptasks&view=tasklists',
			    $view == 'tasklists'
		    );


		    if (ComponentHelper::isEnabled('com_jpreminders')){
			    Sidebar::addEntry(
				    '<i class="fas fa-bell isChild"></i>  '.Text::_('COM_JOOMPROJECT_SUBMENU_REMINDERS'),
				    'index.php?option=com_jpreminders&view=reminders',
				    $view == 'reminders'
			    );
		    }

		    if (ComponentHelper::isEnabled('com_fields')) {
			    Sidebar::addEntry(
				    '<i class="fas fa-cube isChild"></i>  '.Text::_('COM_JOOMPROJECT_SUBMENU_CUSTOMFIELDS'),
				    'index.php?option=com_fields&context=com_jptasks.task',
				    $context == 'com_jptasks.task' && $view == "fields.fields"
			    );

			    Sidebar::addEntry(
				    '<i class="fas fa-cubes isChild"></i>  '.Text::_('COM_JOOMPROJECT_SUBMENU_CUSTOMFIELDS_GROUP'),
				    'index.php?option=com_fields&view=groups&context=com_jptasks.task',
				    $context == 'com_jptasks.task' && $view == "fields.groups"
			    );
		    }


            Sidebar::addEntry(
                '<i class="fas fa-upload isChild"></i>  '.Text::_('COM_JOOMPROJECT_SUBMENU_IMPORT'),
                'index.php?option=com_jptasks&view=import',
                $view == 'import'
            );


		    Sidebar::addEntry(
			    '<i class="fas fa-cog isChild"></i>  '.Text::_('COM_JOOMPROJECT_SETTINGS'),
			    'index.php?option=com_config&view=component&component=com_jptasks',
			    $view == 'component'
		    );
	    }


	    // Time tracking
	    if (ComponentHelper::isEnabled('com_jptime')){
		    Sidebar::addEntry(
			    '<i class="fas fa-clock isParent"></i>  '.Text::_('COM_JOOMPROJECT_SUBMENU_TIME_TRACKING'),
			    '#',
			    $view == 'timesheet'
		    );
		    Sidebar::addEntry(
			    '<i class="fas fa-list isChild"></i>  '.Text::_('COM_JOOMPROJECT_SUBMENU_MANAGE_TIMESHEET'),
			    'index.php?option=com_jptime&view=timesheet',
			    $view == 'timesheet'
		    );

		    Sidebar::addEntry(
			    '<i class="fas fa-cog isChild"></i>  '.Text::_('COM_JOOMPROJECT_SETTINGS'),
			    'index.php?option=com_config&view=component&component=com_jptime',
			    $view == 'component'
		    );
	    }


	    // repo
	    if(ComponentHelper::isEnabled('com_jprepo')){
		    Sidebar::addEntry(
			    '<i class="fas fa-folder-open isParent"></i>  '.Text::_('COM_JOOMPROJECT_SUBMENU_REPO'),
			    '#',
			    $view == 'repository'
		    );
		    Sidebar::addEntry(
			    '<i class="fas fa-list isChild"></i>  '.Text::_('COM_JOOMPROJECT_SUBMENU_MANAGE_REPOSITORY'),
			    'index.php?option=com_jprepo&view=repository',
			    $view == 'repository'
		    );

		    if (ComponentHelper::isEnabled('com_fields')) {
			    Sidebar::addEntry(
				    '<i class="fas fa-cube isChild"></i>  '.Text::_('COM_JOOMPROJECT_SUBMENU_FILE_CUSTOMFIELDS'),
				    'index.php?option=com_fields&context=com_jprepo.file',
				    $context == 'com_jprepo.file' && $view == "fields.fields"
			    );

			    Sidebar::addEntry(
				    '<i class="fas fa-cubes isChild"></i>  '.Text::_('COM_JOOMPROJECT_SUBMENU_FILE_CUSTOMFIELDS_GROUP'),
				    'index.php?option=com_fields&view=groups&context=com_jprepo.file',
				    $context == 'com_jprepo.file' && $view == "fields.groups"
			    );

			    Sidebar::addEntry(
				    '<i class="fas fa-cube isChild"></i>  '.Text::_('COM_JOOMPROJECT_SUBMENU_DIR_CUSTOMFIELDS'),
				    'index.php?option=com_fields&context=com_jprepo.directory',
				    $context == 'com_jprepo.directory' && $view == "fields.fields"
			    );

			    Sidebar::addEntry(
				    '<i class="fas fa-cubes isChild"></i>  '.Text::_('COM_JOOMPROJECT_SUBMENU_DIR_CUSTOMFIELDS_GROUP'),
				    'index.php?option=com_fields&view=groups&context=com_jprepo.directory',
				    $context == 'com_jprepo.directory' && $view == "fields.groups"
			    );
		    }

		    Sidebar::addEntry(
			    '<i class="fas fa-cog isChild"></i>  '.Text::_('COM_JOOMPROJECT_SETTINGS'),
			    'index.php?option=com_config&view=component&component=com_jprepo',
			    $view == 'component'
		    );
	    }


	    // forum
	    if(ComponentHelper::isEnabled('com_jpforum')){
		    Sidebar::addEntry(
			    '<i class="fas fa-comments isParent"></i>  '.Text::_('COM_JOOMPROJECT_SUBMENU_FORUM'),
			    '#',
			    $view == 'topics'
		    );
		    Sidebar::addEntry(
			    '<i class="fas fa-list isChild"></i>  '.Text::_('COM_JOOMPROJECT_SUBMENU_MANAGE_TOPICS'),
			    'index.php?option=com_jpforum&view=topics',
			    $view == 'topics'
		    );

		    Sidebar::addEntry(
			    '<i class="fas fa-comment-dots isChild"></i>  '.Text::_('COM_JOOMPROJECT_SUBMENU_REPLIES'),
			    'index.php?option=com_jpforum&view=replies',
			    $view == 'replies'
		    );

		    Sidebar::addEntry(
			    '<i class="fas fa-cog isChild"></i>  '.Text::_('COM_JOOMPROJECT_SETTINGS'),
			    'index.php?option=com_config&view=component&component=com_jpforum',
			    $view == 'component'
		    );
	    }


	    // Designs
	    if(ComponentHelper::isEnabled('com_jpdesigns')){
		    Sidebar::addEntry(
			    '<i class="fas fa-object-ungroup isParent"></i>  '.Text::_('COM_JOOMPROJECT_SUBMENU_DESIGNS'),
			    '#',
			    $view == 'designs'
		    );
		    Sidebar::addEntry(
			    '<i class="fas fa-list isChild"></i>  '.Text::_('COM_JOOMPROJECT_SUBMENU_MANAGE_DESIGNS'),
			    'index.php?option=com_jpdesigns&view=designs',
			    $view == 'designs'
		    );

		    Sidebar::addEntry(
			    '<i class="fas fa-images isChild"></i>  '.Text::_('COM_JOOMPROJECT_SUBMENU_DESIGN_ALBUMS'),
			    'index.php?option=com_jpdesigns&view=albums',
			    $view == 'albums'
		    );

		    Sidebar::addEntry(
			    '<i class="fas fa-history isChild"></i>  '.Text::_('COM_JOOMPROJECT_SUBMENU_DESIGN_REVISIONS'),
			    'index.php?option=com_jpdesigns&view=revisions',
			    $view == 'revisions'
		    );

		    Sidebar::addEntry(
			    '<i class="fas fa-upload isChild"></i>  '.Text::_('COM_JOOMPROJECT_SUBMENU_DESIGN_IMPORT'),
			    'index.php?option=com_jpdesigns&view=import',
			    $view == 'import'
		    );

		    Sidebar::addEntry(
			    '<i class="fas fa-cog isChild"></i>  '.Text::_('COM_JOOMPROJECT_SETTINGS'),
			    'index.php?option=com_config&view=component&component=com_jpdesigns',
			    $view == 'component'
		    );
	    }

	    // Comments
	    if(ComponentHelper::isEnabled('com_jpcomments')){
		    Sidebar::addEntry(
			    '<i class="fas fa-comment isParent"></i>  '.Text::_('COM_JOOMPROJECT_SUBMENU_COMMENTS'),
			    '#',
			    $view == 'comments'
		    );
		    Sidebar::addEntry(
			    '<i class="fas fa-list isChild"></i>  '.Text::_('COM_JOOMPROJECT_SUBMENU_MANAGE_COMMENTS'),
			    'index.php?option=com_jpcomments&view=comments',
			    $view == 'comments'
		    );

		    Sidebar::addEntry(
			    '<i class="fas fa-cog isChild"></i>  '.Text::_('COM_JOOMPROJECT_SETTINGS'),
			    'index.php?option=com_config&view=component&component=com_jpcomments',
			    $view == 'component'
		    );
	    }

        // jpactivities
        Sidebar::addEntry(
            '<i class="fas fa-clipboard-list isParent"></i>  '.Text::_('COM_JOOMPROJECT_ACTIVITIES'),
            'index.php?option=com_jpactivities&view=activities',
            $view == 'activities'
        );
        Sidebar::addEntry(
            '<i class="fas fa-list isChild"></i>  '.Text::_('COM_JOOMPROJECT_MANAGE_ACTIVITIES'),
            'index.php?option=com_jpactivities&view=activities',
            $view == 'activities'
        );
        Sidebar::addEntry(
            '<i class="fas fa-cogs isChild"></i>  '.Text::_('COM_JOOMPROJECT_CONFIGURATION'),
            'index.php?option=com_config&view=component&component=com_jpactivities',
            $view == 'component'
        );

	    // Users
        if(ComponentHelper::getParams('com_jpprojects')->get('permissions_type',0)){

            Sidebar::addEntry(
                '<i class="fas fa-users isParent"></i>  '.Text::_('COM_JOOMPROJECT_TEAMS_ROLES'),
                'index.php?option=com_jpusers&view=roles',
                $view == 'roles'
            );

            Sidebar::addEntry(
                '<i class="fas fa-user-lock isChild"></i>  '.Text::_('COM_JOOMPROJECT_ROLES'),
                'index.php?option=com_jpusers&view=roles',
                $view == 'roles'
            );

            Sidebar::addEntry(
                '<i class="fas fa-users isChild"></i>  '.Text::_('COM_JOOMPROJECT_TEAMS'),
                'index.php?option=com_jpusers&view=teams',
                $view == 'teams'
            );

            Sidebar::addEntry(
                '<i class="fas fa-user-plus isChild"></i>  '.Text::_('COM_JOOMPROJECT_USERS'),
                'index.php?option=com_jpusers&view=users',
                $view == 'users'
            );


        }


	    Sidebar::addEntry(
		    '<i class="fas fa-toolbox isParent"></i>  '.Text::_('COM_JOOMPROJECT_MAINTENANCE_TITLE'),
		    'index.php?option=com_joomproject&view=maintenance',
		    $view == 'maintenance'
	    );


	    // config
	    Sidebar::addEntry(
		    '<i class="fas fa-cogs isParent"></i>  '.Text::_('COM_JOOMPROJECT_CONFIGURATION'),
		    'index.php?option=com_config&view=component&component=com_joomproject',
		    $view == 'component'
	    );

    }

    
    public function storeConfig($config) {
    	$config = $config->toString();
    	
    	$db = Factory::getDBO();
    	$db->setQuery('UPDATE `#__extensions` SET `params` = '.$db->Quote($config).' WHERE `element` = "com_joomproject" AND `type` = "component"');
    	$db->execute();
    }
    
}
