<?php
/**
 * @package        JoomProject
 * @copyright      2013-2020 JoomBoost, joomboost.com
 * @license        GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die;


class JoomProjectHelperWebasset
{

    static $wa;

    public static function init(){

        static::$wa = \Joomla\CMS\Factory::getApplication()->getDocument()->getWebAssetManager();

        $wr =  static::$wa->getRegistry();

        $assets = [
            'com_joomproject',
            'mod_jp_gantt',
            'mod_jp_calendar',
            'com_jpdesigns'
        ];

        foreach ($assets as $asset){

            static::$wa->getRegistry()->addExtensionRegistryFile($asset);
            $wr->addRegistryFile(JPATH_ROOT.'/media/'.$asset.'/joomla.asset.json');
        }


    }

}