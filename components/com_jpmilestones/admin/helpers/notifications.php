<?php
/**
 * @package      Joomproject
 * @subpackage   Milestones
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;


JLoader::register('JPmilestonesHelperRoute', JPATH_SITE . '/components/com_jpmilestones/helpers/route.php');
JLoader::register('JPtableMilestone', JPATH_ADMINISTRATOR . '/components/com_jpmilestones/tables/milestone.php');


/**
 * Email Notification Helper Class
 * This class is invoked by the Joomproject notifications plugin
 *
 */
abstract class JPmilestonesNotificationsHelper
{
    /**
     * Supported item contexts
     *
     * @var    array
     */
    protected static $contexts = array('com_jpmilestones.milestone', 'com_jpmilestones.form');

    /**
     * Email string prefix
     *
     * @var    string
     */
    protected static $prefix   = 'COM_JOOMPROJECT_MILESTONE_EMAIL';


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
        return 'milestone';
    }


    /**
     * Method to get a table class instance
     *
     * @return    object
     */
    public static function getMilestoneTable()
    {
        $table = Table::getInstance('Milestone', 'JPtable');

        return $table;
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
              ->where(
                '('
                . 'a.item_type = ' . $db->quote('com_jpmilestones.milestone')
                . ' AND a.item_id = ' . (int) $table->id
                . ')'
                . ' OR ('
                . 'a.item_type = ' . $db->quote('com_jpprojects.project')
                . ' AND a.item_id = ' . (int) $table->project_id
                . ')'
              );

        $db->setQuery($query);
        $users = (array) $db->loadColumn();

        if ($opt_out) {
            $blacklist = $users;
            $users     = array();

            $ms_groups = JPAccessHelper::getGroupsByAccessLevel($table->access);

            $query->clear()
                  ->select('access')
                  ->from('#__jp_projects')
                  ->where('id = ' . (int) $table->project_id);

            $db->setQuery($query);
            $project_access = $db->loadResult();

            $p_groups = JPAccessHelper::getGroupsByAccessLevel($project_access);
            $groups   = array_unique(array_merge($p_groups, $ms_groups));

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
    public static function getMilestoneSubject($lang, $receiver, $user, $after, $before, $is_new)
    {
        $txt_prefix = self::$prefix . '_' . ($is_new ? 'NEW' : 'UPD');

        $format  = $lang->_($txt_prefix . '_SUBJECT');
        $project = JPnotificationsHelper::translateValue('project_id', $after->project_id);
        $txt     = sprintf($format, $project, $user->name, $after->title);

        return $txt;
    }


    /**
     * Method to generate the email subject for completed milestones
     *
     * @param     object     $lang         Instance of the default user language
     * @param     object     $receiveer    Instance of the the receiving user
     * @param     object     $user         Instance of the user who made the change
     * @param     object     $table        Instance of the item table after it was updated
     *
     * @return    string
     */
    public static function getMilestoneCompletedSubject($lang, $receiver, $user, $table)
    {
        $txt_prefix = self::$prefix;

        $format  = $lang->_($txt_prefix . '_SUBJECT_COMPLETED');
        $project = JPnotificationsHelper::translateValue('project_id', $table->project_id);
        $txt     = sprintf($format, $project, $user->name, $table->title);

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
    public static function getMilestoneMessage($lang, $receiver, $user, $after, $before, $is_new)
    {
        // Get the changed fields
        $props = array(
            'description', 'created_by', 'access', array('start_date', 'NE-SQLDATE'), array('end_date', 'NE-SQLDATE')
        );

        $changes = array();

        if (is_object($before) && is_object($after)) {
            $changes = JPObjectHelper::getDiff($before, $after, $props);
        }

        if ($is_new) {
            $changes = JPObjectHelper::toArray($after, $props);
        }

        $txt_prefix = self::$prefix . '_' . ($is_new ? 'NEW' : 'UPD');

        $format  = $lang->_($txt_prefix . '_MESSAGE');
        $changes = JPnotificationsHelper::formatChanges($lang, $changes);
        $footer  = sprintf($lang->_('COM_JOOMPROJECT_EMAIL_FOOTER'), Uri::root());
        $link    = Route::_(Uri::root() . JPmilestonesHelperRoute::getMilestoneRoute($after->id, $after->project_id));
        $txt     = sprintf($format, $receiver->name, $user->name, $changes, $link);
        $txt     = str_replace('\n', "\n", $txt . "\n\n" . $footer);

        return $txt;
    }


    /**
     * Method to generate the email message for completed milestones
     *
     * @param     object     $lang         Instance of the default user language
     * @param     object     $receiveer    Instance of the the receiving user
     * @param     object     $user         Instance of the user who made the change
     * @param     object     $table        Instance of the item table after it was updated
     *
     * @return    string
     */
    public static function getMilestoneCompletedMessage($lang, $receiver, $user, $table)
    {
        // Get the changed fields
        $props = array(
            'description', 'created_by', 'access', array('start_date', 'NE-SQLDATE'), array('end_date', 'NE-SQLDATE')
        );

        $changes    = JPObjectHelper::toArray($table, $props);
        $txt_prefix = self::$prefix;

        $format  = $lang->_($txt_prefix . '_MESSAGE_COMPLETED');
        $changes = JPnotificationsHelper::formatChanges($lang, $changes);
        $footer  = sprintf($lang->_('COM_JOOMPROJECT_EMAIL_FOOTER'), Uri::root());
        $link    = Route::_(Uri::root() . JPmilestonesHelperRoute::getMilestoneRoute($table->id, $table->project_id));
        $txt     = sprintf($format, $receiver->name, $user->name, $changes, $link);
        $txt     = str_replace('\n', "\n", $txt . "\n\n" . $footer);

        return $txt;
    }
}
