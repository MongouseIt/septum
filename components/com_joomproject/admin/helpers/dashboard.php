<?php
/**
 * @package        JoomProject
 * @copyright      2013-2019 JoomBoost, joomboost.com
 * @license        GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
JLoader::register('JoomprojectHelperStats',JPATH_ADMINISTRATOR.'/components/com_joomproject/helpers/stats.php');


class JoomprojectHelperDashboard
{

	static public function getLeftWidgets(){

		//inits
		$leftWidgets = [];
		$recently = JoomprojectHelperStats::getRecently();

		$leftWidgets[] = [
			'title'       => 'COM_JOOMPROJECT_GRAPH_RECENTLY',
			'icon'        => 'chart-line',
			'bodyContent' => LayoutHelper::render('dashboard.recently', ['recent' => $recently])
		];

		if(Factory::getApplication()->getIdentity()->authorise('core.admin'))
		{
			// only super user can see update widget
			// left widgets
			$leftWidgets[] = [
				'title'       => 'COM_JOOMPROJECT_CONFIGURATION',
				'icon'        => 'list',
				'bodyContent' => LayoutHelper::render('dashboard.config'),
			];

			$leftWidgets[] = [
				'title'       => 'COM_JOOMPROJECT_PRODUCT_INFO',
				'icon'        => 'info',
				'bodyContent' => LayoutHelper::render('dashboard.productInfo'),
			];

			$leftWidgets[] = [
				'title'       => 'COM_JOOMPROJECT_FOLLOWUS',
				'icon'        => 'share',
				'bodyContent' => LayoutHelper::render('dashboard.socialLinks'),
			];
		}



		$leftWidgets[] = [
			'bodyContent' => LayoutHelper::render('dashboard.copyright'),
		];



		return $leftWidgets;
	}



	static public function getRightWidgets(){

		// inits
		$rightWidgets = [];
		$projectStats = JoomprojectHelperStats::getProjectStats(); // get project count stats by day, month, week , year ...

		$rightWidgets[] = [
			'title'       => 'COM_JOOMPROJECT_PROJECT_STATS',
			'icon'        => 'chart-bar',
			'bodyContent' => LayoutHelper::render('dashboard.projectStats', ['pstats' => $projectStats])

		];

		if(Factory::getApplication()->getIdentity()->authorise('core.admin')){ // only super user can see joomboost company social widgets

			$rightWidgets[] = [
				'bodyContent' => LayoutHelper::render('dashboard.facebookPage')
			];

			$rightWidgets[] = [
				'bodyContent' => LayoutHelper::render('dashboard.twitterFollow')
			];


		}

		return $rightWidgets;

	}


	static public function getCountCards(){


		$buttons = self::getButtons();


		// attach stats and some other layout data
		foreach ($buttons as $component => &$button){

			if (!JPApplicationHelper::enabled($component)){
				unset($buttons[$component]);
			}

		}

		return $buttons;
	}

	public static function getButtons()
	{
		$components = JPapplicationHelper::getComponents('com_jpreminders');
		$buttons    = array();


		foreach ($components AS $component)
		{
			if (!JPApplicationHelper::enabled($component->element)) {
				continue;
			}

			$helper = JPATH_ADMINISTRATOR . '/components/' . $component->element . '/helpers/dashboard.php';
			$class  = str_replace('com_jp', 'JP', $component->element) . 'HelperDashboard';

			if (!\Joomla\CMS\Filesystem\File::exists($helper)) {
				continue;
			}

			JLoader::register($class, $helper);

			if (class_exists($class)) {
				if (in_array('getAdminButtons', get_class_methods($class))) {
					$com_buttons = (array) call_user_func(array($class, 'getAdminButtons'));

					$buttons[$component->element] = array();

					foreach ($com_buttons AS $button)
					{
						$buttons[$component->element] = $button;
					}
				}
			}
		}

		return $buttons;
	}


	static public function getSkeleton(){
		return LayoutHelper::render(
			'dashboard.skeleton.container',
			[
				'counts' => self::getCountCards(),
				'left'   => self::getLeftWidgets(),
				'right'  => self::getRightWidgets()
			]
		);
	}


}