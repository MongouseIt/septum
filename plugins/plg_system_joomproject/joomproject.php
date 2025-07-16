<?php
/**
 * @package		JoomProject
 * @copyright	2013-2018 JoomBoost, https://www.joomboost.com
 * @license		GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

# No Permission
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die();

class plgSystemJoomproject extends CMSPlugin {


	public function enableDemoMode(){


		$params  = ComponentHelper::getParams("com_joomproject");


	
		// don't continue if enableDemo enabled
		if (!$params->get('enableDemo', 0) )
			return false;



		// don't continue if user is super admin
		if (Factory::getApplication()->getIdentity()->get('isRoot'))
			return false;

		// get current task name
		$currentTask = Factory::getApplication()->input->get('task','','string');

		$currentTask = explode('.',$currentTask);
		$currentTask = isset($currentTask[1]) ? $currentTask[1]: $currentTask[0];

		// get current component name
		$currentComponent = Factory::getApplication()->input->get('option','','string');

		// get current URL
		$currentUrl = Uri::getInstance()->toString();


		$rules = [
			'com_jpprojects' => ['save','apply','delete','vote'],
			'com_categories' => ['save','apply','delete','vote'],
			'com_jpmilestones' => ['save','apply','delete','vote'],
			'com_jptasks' => ['save','apply','delete','vote'],
			'com_jptime' => ['save','apply','delete','vote'],
			'com_jprepo' => ['save','apply','delete','vote'],
			'com_jpforum' => ['save','apply','delete','vote'],
			'com_jpdesigns' => ['save','apply','delete','vote'],
			'com_jpcomments' => ['save','apply','delete','vote'],
			'com_users' => ['save','apply','request','confirm','complete','remind','register'],
			'com_config' => ['save','apply']
		];


		foreach ($rules as $component => $tasks){
			// check component
			if($currentComponent != $component)
				continue;

			if(in_array($currentTask, $tasks)){
				// don't allow task performing
				Factory::getApplication()->enqueueMessage('This is a demo website, you can\'t add real content, thanks for testing our product','warning');

				Factory::getApplication()->redirect($currentUrl);
			}

		}




	}
    public function onAfterRoute(){
	    $this->enableDemoMode();
        $option = Factory::getApplication()->input->get('option');

        // force template theme oly in frontend
        if(Factory::getApplication()->isClient('site') && $option == 'com_joomproject'){

            JLoader::register('JPhtmlStyle', JPATH_LIBRARIES . '/joomproject/html/style.php');
            JPhtmlStyle::forceTemplateTheme();
        }

    }

}