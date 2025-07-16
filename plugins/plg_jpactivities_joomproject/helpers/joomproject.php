<?php
/**
 * @package      plg_jpactivities_joomproject
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();
use Joomla\CMS\Factory;

// Include translation helper class
require_once JPATH_PLUGINS . '/content/jpactivities/helpers/jpactivities.php';

if (Factory::getApplication()->isClient('site')) {
    jimport('joomproject.framework');
}


/**
 * Joomproject Activity Plugin Translation Helper Controller
 *
 */
class plgJPactivitiesJoomprojectHelper
{
    protected $config;


    public function __construct($config = array())
    {
        $this->config = $config;
    }


    public function translateItem($item)
    {
        $helper = $this->getHelper($item->extension);

        if (!$helper) return $item;

        return $helper->translateItem($item);
    }


    public function translateGroup($group)
    {
        $key    = key($group);
        $helper = $this->getHelper($group[$key]->extension);

        if (!$helper) return $group[$key];

        return $helper->translateGroup($group);
    }


    protected function getHelper($extension)
    {
        static $cache = array();

        // Check the cache
        if (isset($cache[$extension])) {
            return $cache[$extension];
        }

        $file  = null;
        $class = null;

        switch ($extension)
        {
            case 'com_jpprojects':
                $file  = dirname(__FILE__) . '/jpprojects.php';
                $class = 'plgJPactivitiesJPprojectsHelper';
                break;

            case 'com_jpmilestones':
                $file  = dirname(__FILE__) . '/jpmilestones.php';
                $class = 'plgJPactivitiesJPmilestonesHelper';
                break;

            case 'com_jptasks':
                $file  = dirname(__FILE__) . '/jptasks.php';
                $class = 'plgJPactivitiesJPtasksHelper';
                break;

            case 'com_jprepo':
                $file  = dirname(__FILE__) . '/jprepo.php';
                $class = 'plgJPactivitiesJPrepoHelper';
                break;

            case 'com_jptime':
                $file  = dirname(__FILE__) . '/jptime.php';
                $class = 'plgJPactivitiesJPtimeHelper';
                break;

            case 'com_jpforum':
                $file  = dirname(__FILE__) . '/jpforum.php';
                $class = 'plgJPactivitiesJPforumHelper';
                break;

            case 'com_jpcomments':
                $file  = dirname(__FILE__) . '/jpcomments.php';
                $class = 'plgJPactivitiesJPcommentsHelper';
                break;

            case 'com_jpdesigns':
                $file  = dirname(__FILE__) . '/jpdesigns.php';
                $class = 'plgJPactivitiesJPdesignsHelper';
                break;
        }

        if (is_null($file))      return false;
        if (!file_exists($file)) return false;

        require_once $file;

        $cache[$extension] = new $class($this->config);

        return $cache[$extension];
    }
}