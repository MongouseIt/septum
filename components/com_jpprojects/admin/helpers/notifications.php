<?php
/**
 * @package      Joomproject
 * @subpackage   Projects
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;


JLoader::register('JPprojectsHelperRoute', JPATH_SITE . '/components/com_jpprojects/helpers/route.php');


/**
 * Email Notification Helper Class
 * This class is invoked by the Joomproject notifications plugin
 *
 */
abstract class JPprojectsNotificationsHelper
{
    /**
     * Supported item contexts
     *
     * @var    array
     */
    protected static $contexts = array('com_jpprojects.project', 'com_jpprojects.form');

    /**
     * Email string prefix
     *
     * @var    string
     */
    protected static $prefix   = 'COM_JOOMPROJECT_PROJECT_EMAIL';


    /**
     * Method that checks if the given context is supported by this component
     *
     * @param     string     $context    The item context
     *
     * @return    boolean
     */
    public static function isSupported($context)
    {
        return in_array($context, self::$contexts);
    }


    /**
     * Method to get the proper context item name
     * This is heljpul if the frontend context differs from the backend.
     * For example: com_jpprojects.project vs com_jpprojects.form
     *
     * @param     string    $context    The item context
     *
     * @return    string
     */
    public static function getItemName($context)
    {
        return 'project';
    }


    /**
     * Method to get a list of user id's which are observing the item
     *
     * @param     string     $context    The item context
     * @param     object     $table      Instance of the item table
     * @param     boolean    $is_new     True if the item is new
     *
     * @return    array
     */
    public static function getObservers($context, $table, $is_new = false)
    {
        $plugin  = PluginHelper::getPlugin('content', 'jpnotifications');
        $params  = new Registry($plugin->params);
        $opt_out = (int) $params->get('sub_method', 0);

        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select('a.user_id')
              ->from('#__jp_ref_observer AS a')
              ->where('a.item_type = ' . $db->quote('com_jpprojects.project'))
              ->where('a.item_id = ' . $db->quote((int) $table->id));

        $db->setQuery($query);
        $users = (array) $db->loadColumn();

        if ($opt_out) {
            $blacklist = $users;
            $users     = array();

            $groups = JPAccessHelper::getGroupsByAccessLevel($table->access);

            if (!count($groups)) {
                return array();
            }

            $query->clear()
                  ->select('a.user_id')
                  ->from('#__user_usergroup_map AS a')
                  ->innerJoin('#__users AS u ON u.id = a.user_id');

            if (count($blacklist)) {
                $query->where('a.user_id NOT IN(' . implode(', ', $blacklist) . ')');
            }

            $query->where('a.group_id IN(' . implode(', ', $groups) . ')')
                  ->group('a.user_id')
                  ->order('a.user_id ASC');

            $db->setQuery($query);
            $users = (array) $db->loadColumn();
        }


        return $users;
    }


    /**
     * Method to generate the email subject
     *
     * @param     object     $lang         Instance of the default user language
     * @param     object     $receiveer    Instance of the the receiving user
     * @param     object     $user         Instance of the user who made the change
     * @param     object     $after        Instance of the item table after it was updated
     * @param     object     $before       Instance of the item table before it was updated
     * @param     boolean    $is_new       True if the item is new ($before will be null)
     *
     * @return    string
     */
    public static function getProjectSubject($lang, $receiver, $user, $after, $before, $is_new)
    {
        $txt_prefix = self::$prefix . '_' . ($is_new ? 'NEW' : 'UPD');

        $format = $lang->_($txt_prefix . '_SUBJECT');
        $txt    = sprintf($format, $user->name, $after->title);

        return $txt;
    }


    /**
     * Method to generate the email message
     *
     * @param     object     $lang         Instance of the default user language
     * @param     object     $receiveer    Instance of the the receiving user
     * @param     object     $user         Instance of the user who made the change
     * @param     object     $after        Instance of the item table after it was updated
     * @param     object     $before       Instance of the item table before it was updated
     * @param     boolean    $is_new       True if the item is new ($before will be null)
     *
     * @return    string
     */
    public static function getProjectMessage($lang, $receiver, $user, $after, $before, $is_new)
    {
        $txt_prefix = self::$prefix . '_' . ($is_new ? 'NEW' : 'UPD');

        // Get the changed fields
        $props = array(
            'description', 'catid', 'created_by', 'access',
            'start_date', 'end_date'
        );

        $changes = array();

        if (is_object($before) && is_object($after)) {
            $changes = JPObjectHelper::getDiff($before, $after, $props);
        }

        if (!count($changes)) {
            return false;
        }

        $format  = $lang->_($txt_prefix . '_MESSAGE');
        $changes = JPnotificationsHelper::formatChanges($lang, $changes);
        $footer  = sprintf($lang->_('COM_JOOMPROJECT_EMAIL_FOOTER'), Uri::root());
        $link    = Route::_(Uri::root() . JPprojectsHelperRoute::getDashboardRoute($after->id . ':' . $after->alias));
        $txt     = sprintf($format, $receiver->name, $user->name, $changes, $link);
        $txt     = str_replace('\n', "\n", $txt . "\n\n" . $footer);

        return $txt;
    }
}
