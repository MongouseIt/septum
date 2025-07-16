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

use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Component\ComponentHelper;

abstract class JPhtmlNav
{

    public static function loadMain(){

    	$params = ComponentHelper::getParams('com_joomproject');

    	// if internal main navigation disabled return nothing
    	if(!$params->get('internal_main_nav',1)) return '';

    	// if internal main navigation activated return navbar layout
	    return LayoutHelper::render('navigation.main',null,'',['component' => 'com_joomproject', 'client' => 0]);

    }

	public static function loadProject(){

		$params = ComponentHelper::getParams('com_joomproject');

		// if internal project tabs navigation disabled return nothing
		if(!$params->get('internal_project_nav',1)) return '';

		// if internal project tabs navigation activated return tabs layout
		return LayoutHelper::render('navigation.project',null,'',['component' => 'com_joomproject', 'client' => 0]);


	}

	public static function getButtons(){
		$components = JPApplicationHelper::getComponents('com_jpreminders');
		$buttons    = array();

		foreach ($components AS $component)
		{

			// skip main component
			if($component->element == 'com_joomproject')
				continue;

			// skip component if not enabled
			if (!JPApplicationHelper::enabled($component->element)) {
				continue;
			}


			// Register component route helper if exists
			$router = JPATH_SITE . '/components/' . $component->element . '/helpers/route.php';
			$class  = str_replace('com_jp', 'JP', $component->element) . 'HelperRoute';

			if (\Joomla\CMS\Filesystem\File::exists($router)) {
				JLoader::register($class, $router);
			}

			// Register component dashboard helper if exists
			$helper = JPATH_ADMINISTRATOR . '/components/' . $component->element . '/helpers/dashboard.php';
			$class  = str_replace('com_jp', 'JP', $component->element) . 'HelperDashboard';


			if (!\Joomla\CMS\Filesystem\File::exists($helper)) {
				continue;
			}

			JLoader::register($class, $helper);

			// Get the dashboard button
			if (class_exists($class)) {
				if (in_array('getSiteButtons', get_class_methods($class))) {
					$com_buttons = (array) call_user_func(array($class, 'getSiteButtons'));

					$buttons[$component->element] = array();

					foreach ($com_buttons AS $button)
					{
						$buttons[$component->element][] = $button;
					}
				}
			}
		}

		return $buttons;
	}

	public static function loadHeader($params){

		// if internal project tabs navigation enabled don't display page header
		if(ComponentHelper::getParams('com_joomproject')->get('internal_project_nav',1)) return '';

		return LayoutHelper::render('common.header',['params' => $params],'',['component' => 'com_joomproject', 'client' => 0]);

	}


}