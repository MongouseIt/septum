<?php
/**
* @package      pkg_joomproject
* @subpackage   lib_joomproject
*
* @author       JoomBoost
* @copyright    Copyright (C) 2006-2013 JoomBoost. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/


defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;


jimport('joomla.application.component.helper');


/**
 * Utility class for Joomproject javascript behaviors
 *
 */
abstract class JPhtmlScript
{
    /**
     * Array containing information for loaded files
     *
     * @var    array    $loaded
     */
    protected static $loaded = array();


    /**
     * Method to load jQuery JS
     *
     * @return    void
     */
    public static function jQuery()
    {
        // Only load once
        if (!empty(self::$loaded[__METHOD__])) {
            return;
        }


        // Load only of doc type is HTML
        if (Factory::getDocument()->getType() == 'html') {


            HTMLHelper::_('jquery.framework');


        }

        self::$loaded[__METHOD__] = true;
    }


    /**
     * Method to load jQuery UI JS
     *
     * @return    void
     */
    public static function jQueryUI()
    {
        // Only load once
        if (!empty(self::$loaded[__METHOD__])) {
            return;
        }

        // Load dependencies
        if (empty(self::$loaded['jQuery'])) {
            self::jQuery();
        }

        // Load only of doc type is HTML
        if (Factory::getDocument()->getType() == 'html') {
            $scripts = (array) array_keys(Factory::getDocument()->_scripts);
            $string  = implode('', $scripts);

            if (stripos($string, 'jquery.ui') === false) {
                JoomProjectHelperWebasset::$wa
                    ->useScript('com_joomproject.jquery.ui.core');

            }
        }

        self::$loaded[__METHOD__] = true;
    }


    /**
     * Method to load jQuery Sortable JS
     *
     * @return    void
     */
    public static function jQuerySortable()
    {
        // Only load once
        if (!empty(self::$loaded[__METHOD__])) {
            return;
        }

        // Load dependencies
        if (empty(self::$loaded['jQueryUI'])) {
            self::jQueryUI();
        }

        // Load only of doc type is HTML
        if (Factory::getDocument()->getType() == 'html') {
            $scripts = (array) array_keys(Factory::getDocument()->_scripts);
            $string  = implode('', $scripts);

            if (stripos($string, 'jquery.ui.sortable') === false) {
                JoomProjectHelperWebasset::$wa
                    ->useScript('com_joomproject.jquery.ui.sortable');

            }
        }

        self::$loaded[__METHOD__] = true;
    }


    /**
     * Method to load jQuery Chosen JS
     *
     * @return    void
     */
    public static function jQueryChosen()
    {
        // Only load once
        if (!empty(self::$loaded[__METHOD__])) {
            return;
        }

        // Load dependencies
        if (empty(self::$loaded['jQuery'])) {
            self::jQuery();
        }

        // Load only of doc type is HTML
        if (Factory::getDocument()->getType() == 'html') {
            if (version_compare(JVERSION, '3', 'ge')) {
                HTMLHelper::_('script', 'jui/chosen.jquery.min.js', array('version' => 'auto', 'relative' => true));
                HTMLHelper::_('stylesheet', 'jui/chosen.css');
            }
            else {
                HTMLHelper::_('script', 'com_joomproject/chosen/chosen.jquery.min.js', array('version' => 'auto', 'relative' => true));
                HTMLHelper::_('stylesheet', 'com_joomproject/chosen/chosen.css');
            }
        }

        self::$loaded[__METHOD__] = true;
    }


    /**
     * Method to load jQuery Select2 JS
     *
     * @return    void
     */
    public static function jQuerySelect2()
    {
        // Only load once
        if (!empty(self::$loaded[__METHOD__])) {
            return;
        }

        // Load dependencies
        if (empty(self::$loaded['jQuery'])) {
            self::jQuery();
        }

        // Load only of doc type is HTML
        if (Factory::getDocument()->getType() == 'html') {
            JoomProjectHelperWebasset::$wa
                ->useScript('com_joomproject.select2')
                ->useStyle('com_joomproject.select2')
                ->useStyle('com_joomproject.select2-bootstrap4');
        }

        self::$loaded[__METHOD__] = true;
    }


    /**
     * Method to load bootstrap JS
     *
     * @return    void
     */
    public static function bootstrap()
    {
        // Only load once
        if (!empty(self::$loaded[__METHOD__])) {
            return;
        }

        // Load dependencies
        if (empty(self::$loaded['jQuery'])) {
            self::jQuery();
        }

        $params = ComponentHelper::getParams('com_joomproject');

        // Load only of doc type is HTML
        if (Factory::getDocument()->getType() == 'html' && $params->get('force_bs',0))
            HTMLHelper::_('bootstrap.framework');



        self::$loaded[__METHOD__] = true;
    }


    /**
     * Method to load jQuery flot JS
     *
     * @return    void
     */
    public static function flot()
    {
        // Only load once
        if (!empty(self::$loaded[__METHOD__])) {
            return;
        }

        // Load dependencies
        if (empty(self::$loaded['jQuery'])) {
            self::jQuery();
        }

        // Load only of doc type is HTML
        if (Factory::getDocument()->getType() == 'html') {
            JoomProjectHelperWebasset::$wa
                ->useScript('com_joomproject.flot')
                ->useScript('com_joomproject.flot-pie')
                ->useScript('com_joomproject.flot-resize');
        }

        self::$loaded[__METHOD__] = true;
    }


    /**
     * Method to load Joomproject comments JS
     *
     * @return    void
     */
    public static function comments()
    {
        // Only load once
        if (!empty(self::$loaded[__METHOD__])) {
            return;
        }

        // Load dependencies
        if (empty(self::$loaded['jQuery'])) {
            self::jQuery();
        }

        if (empty(self::$loaded['joomproject'])) {
            self::joomproject();
        }

        // Load only of doc type is HTML
        if (Factory::getDocument()->getType() == 'html') {
            JoomProjectHelperWebasset::$wa
                ->useScript('com_joomproject.comments');
        }

        self::$loaded[__METHOD__] = true;
    }


    /**
     * Method to load Joomproject form JS
     *
     * @return    void
     */
    public static function form()
    {

        // Only load once
        if (!empty(self::$loaded[__METHOD__])) {
            return;
        }

        // Load dependencies
        if (empty(self::$loaded['jQuery'])) {
            self::jQuery();
        }

        if (empty(self::$loaded['joomproject'])) {
            self::joomproject();
        }

        // Load only of doc type is HTML
        if (Factory::getDocument()->getType() == 'html') {
            JoomProjectHelperWebasset::$wa
                ->useScript('com_joomproject.form');

        }

        self::$loaded[__METHOD__] = true;
    }


    /**
     * Method to load Joomproject list form JS
     *
     * @return    void
     */
    public static function listForm()
    {
        // Only load once
        if (!empty(self::$loaded[__METHOD__])) {
            return;
        }

        // Load dependencies
        if (empty(self::$loaded['jQuery'])) {
            self::jQuery();
        }

        if (empty(self::$loaded['joomproject'])) {
            self::joomproject();
        }

        // Load only of doc type is HTML
        if (Factory::getDocument()->getType() == 'html') {
              JoomProjectHelperWebasset::$wa
              ->useScript('com_joomproject.list');
        }

        self::$loaded[__METHOD__] = true;
    }


    /**
     * Method to load Joomproject task JS
     *
     * @return    void
     */
    public static function task()
    {
        // Only load once
        if (!empty(self::$loaded[__METHOD__])) {
            return;
        }

        // Load dependencies
        if (empty(self::$loaded['jQuery'])) {
            self::jQuery();
        }

        if (empty(self::$loaded['joomproject'])) {
            self::joomproject();
        }


        // Load only of doc type is HTML
        if (Factory::getDocument()->getType() == 'html') {
            JoomProjectHelperWebasset::$wa
                ->useScript('com_joomproject.task');

        }

        self::$loaded[__METHOD__] = true;
    }


    /**
     * Method to load Joomproject time recording JS
     *
     * @return    void
     */
    public static function timerec()
    {
        // Only load once
        if (!empty(self::$loaded[__METHOD__])) {
            return;
        }

        // Load dependencies
        if (empty(self::$loaded['jQuery'])) {
            self::jQuery();
        }

        if (empty(self::$loaded['joomproject'])) {
            self::joomproject();
        }

        // Load only of doc type is HTML
        if (Factory::getDocument()->getType() == 'html') {
            JoomProjectHelperWebasset::$wa
                ->useScript('com_joomproject.recorder');

        }

        self::$loaded[__METHOD__] = true;
    }


    /**
     * Method to load upload JS
     *
     * @return    void
     */
    public static function upload()
    {
        // Only load once
        if (!empty(self::$loaded[__METHOD__])) {
            return;
        }

        // Load only of doc type is HTML
        if (Factory::getDocument()->getType() == 'html') {
            JoomProjectHelperWebasset::$wa
                ->useScript('com_joomproject.upload');

        }

        self::$loaded[__METHOD__] = true;
    }


    /**
     * Method to load Joomproject base JS
     *
     * @return    void
     */
    public static function joomproject()
    {
        // Only load once
        if (!empty(self::$loaded[__METHOD__])) {
            return;
        }

        self::bootstrap();

        // Load only of doc type is HTML
        if (Factory::getDocument()->getType() == 'html') {
            JoomProjectHelperWebasset::$wa
                ->useScript('com_joomproject.joomproject')
                ->useStyle('com_joomproject.iziModal');
        //    HTMLHelper::_('script', 'com_joomproject/joomproject/joomproject.js', false, true, false, false, false);
        }



        self::$loaded[__METHOD__] = true;
    }
}






