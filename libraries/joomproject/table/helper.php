<?php
/**
 * @package      pkg_joomproject
 * @subpackage   lib_joomproject
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2006-2013 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Table\Table;
use Joomla\CMS\Filesystem\Path;


abstract class JPTableHelper
{
    /**
     * Table instance cache
     *
     * @var    array
     */
    protected static $table_cache = array();

    /**
     * Table methods list cache
     *
     * @var    array
     */
    protected static $methods_cache = array();


    /**
     * Table context list cache
     *
     * @var    array
     */
    protected static $context_cache = array();



    public static function getInstance($context)
    {
        if (isset(self::$table_cache[$context])) {
            return self::$table_cache[$context];
        }

        return null;
    }


    public static function getMethods($context)
    {
        if (isset(self::$methods_cache[$context])) {
            return self::$methods_cache[$context];
        }

        return array();
    }


    public static function getContexts()
    {
        return self::$context_cache;
    }


    /**
     * Discovers the table classes in all Joomproject related components.
     * Stores an instance in $table_cache.
     *
     * @return    void
     */
    public static function discover()
    {
        static $loaded = false;

        if ($loaded) return;

        $coms = JPApplicationHelper::getComponents();

        foreach ($coms AS $com)
        {
            $path     = Path::clean(JPATH_ADMINISTRATOR . '/components/' . $com->element . '/tables');
            $prefixes = array('JPtable', 'JTable', ucfirst(substr($com->element, 3)) . 'Table');

            if (!is_dir($path)) continue;

            $files = \Joomla\CMS\Filesystem\Folder::files($path, '.php$');

            if (!count($files)) continue;

            // Discover the table class names with some guessing about the prefix
            foreach ($prefixes AS $prefix)
            {
                JLoader::discover($prefix, $path, false);

                foreach ($files AS $file)
                {
                    $name    = \Joomla\CMS\Filesystem\File::stripExt($file);
                    $class   = $prefix . $name;
                    $context = strtolower($com->element . '.' . $name);

                    if (class_exists($class)) {
                        // Class found, try to get an instance
                        $instance = Table::getInstance($name, $prefix);

                        if (!$instance) continue;

                        self::$context_cache[] = $context;

                        self::$table_cache[$context]   = $instance;
                        self::$methods_cache[$context] = array();

                        $methods = get_class_methods($instance);

                        foreach ($methods AS $method)
                        {
                            self::$methods_cache[$context][] = strtolower($method);
                        }
                    }
                }
            }
        }

        $loaded = true;
    }
}