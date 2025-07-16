<?php
/**
* @package      Joomproject
* @subpackage   Library.html
*
* @author       JoomBoost
* @copyright    Copyright (C) 20012-2018 JoomBoost. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/


defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;

jimport('joomla.application.component.helper');


/**
 * Utility class for Joomproject style sheets
 *
 */
abstract class JPhtmlStyle
{
    /**
     * Array containing information for loaded files
     *
     * @var    array    $loaded
     */
    protected static $loaded = array();


    /**
     * Method to load bootstrap CSS
     *
     * @return    void
     */
    public static function bootstrap()
    {
        // Only load once
        if (!empty(self::$loaded[__METHOD__])) {
            return;
        }

        $params = ComponentHelper::getParams('com_joomproject');

        // force bootstrap loading
        if (Factory::getDocument()->getType() == 'html' && $params->get('force_bs', 0))
            Factory::getDocument()->getWebAssetManager()->useStyle('bootstrap.css');


        self::$loaded[__METHOD__] = true;
    }


    /**
     * Method to load Joomproject CSS
     *
     * @return    void
     */
    public static function joomproject()
    {
        // Only load once
        if (!empty(self::$loaded[__METHOD__])) {
            return;
        }

        $params = ComponentHelper::getParams('com_joomproject');

        // load bs
        self::bootstrap();

        // Load only if doc type is HTML
        if (Factory::getDocument()->getType() == 'html' && $params->get('joomproject_css', '1') == '1') {
            JoomProjectHelperWebasset::$wa->useStyle('com_joomproject.styles');

        }





        self::$loaded[__METHOD__] = true;
    }

	/*
	 * This method force component to load on a specified theme
	 */
	public static function forceTemplateTheme($theme_id = 0){



		// get selected theme from config if 0
		$theme_id = ($theme_id > 0 ) ? $theme_id : ComponentHelper::getParams('com_joomproject')->get('force_template_theme','');

		// return if theme id is 0
		if(!$theme_id) return false;

		// get template data
		$app = Factory::getApplication();
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from('`#__template_styles`')
			->where("`id`= $theme_id");
		$db->setQuery($query);
		$theme = $db->loadObject();

		// load template
		$app->setTemplate($theme);

		return true;
	}


}



/**
 * Stupid but necessary way of adding joomproject CSS to the document head.
 * This function is called by the "onCompileHead" system event and makes sure that the CSS is loader after bootstrap
 *
 */
function triggerJoomprojectStyleCore()
{
    HTMLHelper::_('stylesheet', 'com_joomproject/joomproject/styles.css');
}
