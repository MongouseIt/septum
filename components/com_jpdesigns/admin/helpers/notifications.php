<?php
/**
 * @package      Joomproject Pro
 * @subpackage   Designs
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;


require_once(JPATH_SITE . '/components/com_jpdesigns/helpers/route.php');

/**
 * Email Notification Helper Class
 * This class is invoked by the Joomproject notifications plugin
 *
 */
abstract class JPdesignsNotificationsHelper
{
    /**
     * Supported item contexts
     *
     * @var    array
     */
    protected static $contexts = array('com_jpdesigns.design', 'com_jpdesigns.designform',
                                       'com_jpdesigns.revision', 'com_jpdesigns.revisionform'
                                      );

    /**
     * Email string prefix
     *
     * @var    string
     */
    protected static $prefix   = 'COM_JOOMPROJECT_DESIGN_EMAIL';


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
        switch ($context)
        {
            case 'com_jpdesigns.revisionform':
            case 'com_jpdesigns.revision':
                return 'revision';
                break;

            default:
                return 'design';
                break;
        }
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
        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        if ($context == 'com_jpdesigns.revisionform' || $context == 'com_jpdesigns.revision') {
            $item_id = $table->parent_id;
        }
        else {
            $item_id = $table->id;
        }

        $query->select('a.user_id')
              ->from('#__jp_ref_observer AS a')
              ->where('a.item_type = ' . $db->quote('com_jpdesigns.design'))
              ->where('a.item_id = ' . $db->quote((int) $item_id));

        $db->setQuery($query);
        $users = (array) $db->loadColumn();

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
    public static function getDesignSubject($lang, $receiver, $user, $after, $before, $is_new)
    {
        $txt_prefix = self::$prefix . '_' . ($is_new ? 'NEW' : 'UPD');

        if (isset($after->approved)) {
            if ($after->approved) {
                $format = $lang->_('COM_JOOMPROJECT_DESIGN_EMAIL_APPROVED_SUBJECT');
            }
            else {
                $format = $lang->_('COM_JOOMPROJECT_DESIGN_EMAIL_DECLINED_SUBJECT');
            }
        }
        else {
            $format = $lang->_($txt_prefix . '_SUBJECT');
        }

        $project = JPnotificationsHelper::translateValue('project_id', $after->project_id);
        $txt     = sprintf($format, $project, $user->name, $after->title);

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
    public static function getDesignMessage($lang, $receiver, $user, $after, $before, $is_new)
    {
        if (isset($after->approved)) {
            $changes = array(
                'album_id'  => self::translateValue('album_id', $after->album_id),
                'title'     => $after->title,
                'file_name' => $after->file_name,
                'file_size' => $after->file_size . 'kb',
                'created_by'=> $after->created_by,
                'description' => $after->description
            );

            $format = $lang->_('COM_JOOMPROJECT_DESIGN_EMAIL_' . ($after->approved ? 'APPROVED' : 'DECLINED') . '_MESSAGE');
        }
        else {
            // Get the changed fields
            $props = array(
                'created_by', 'access', 'album_id', 'file_name', 'description'
            );

            $changes = array();

            if (is_object($before) && is_object($after)) {
                $changes = JPObjectHelper::getDiff($before, $after, $props);
            }

            if (!count($changes)) {
                return false;
            }

            if (array_key_exists('album_id', $changes)) {
                $changes['album_id'] = self::translateValue('album_id', $changes['album_id']);
            }

            $txt_prefix = self::$prefix . '_' . ($is_new ? 'NEW' : 'UPD');
            $format     = $lang->_($txt_prefix . '_MESSAGE');
        }

        $changes = JPnotificationsHelper::formatChanges($lang, $changes);
        $footer  = sprintf($lang->_('COM_JOOMPROJECT_EMAIL_FOOTER'), Uri::root());
        $link    = Route::_(Uri::root() . JPdesignsHelperRoute::getDesignRoute($after->id, $after->project_id, $after->album_id, '0'));
        $txt     = sprintf($format, $receiver->name, $user->name, $changes, $link);
        $txt     = str_replace('\n', "\n", $txt . "\n\n" . $footer);

        return $txt;
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
    public static function getRevisionSubject($lang, $receiver, $user, $after, $before, $is_new)
    {
        if (!$is_new) {
            if (isset($after->approved)) {
                $format = $lang->_('COM_JOOMPROJECT_DESIGN_REV_EMAIL_' . ($after->approved ? 'APPROVED' : 'DECLINED') . '_SUBJECT');
            }
            else {
                return false;
            }
        }
        else {
            $txt_prefix = 'COM_JOOMPROJECT_DESIGN_REV_EMAIL_' . ($is_new ? 'NEW' : 'UPD');
            $format     = $lang->_($txt_prefix . '_SUBJECT');
        }

        $project = JPnotificationsHelper::translateValue('project_id', $after->project_id);
        $parent  = self::translateValue('parent_id', $after->parent_id);
        $txt     = sprintf($format, $project, $user->name, $parent);

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
    public static function getRevisionMessage($lang, $receiver, $user, $after, $before, $is_new)
    {
        if (!$is_new) {
            if (isset($after->approved)) {
                $fields = array(
                    'title'       => $after->title,
                    'file_name'   => $after->file_name,
                    'file_size'   => $after->file_size . 'kb',
                    'created_by'  => $after->created_by,
                    'description' => $after->description
                );

                $format = $lang->_('COM_JOOMPROJECT_DESIGN_REV_EMAIL_' . ($after->approved ? 'APPROVED' : 'DECLINED') . '_MESSAGE');
            }
            else {
                return false;
            }
        }
        else {
            // Get the changed fields
            $fields = array(
                'title'       => $after->title,
                'file_name'   => $after->file_name,
                'file_size'   => $after->file_size . 'kb',
                'description' => $after->description
            );

            $txt_prefix = 'COM_JOOMPROJECT_DESIGN_REV_EMAIL_' . ($is_new ? 'NEW' : 'UPD');
            $format     = $lang->_($txt_prefix . '_MESSAGE');
        }

        $album   = self::getAlbumId($after->parent_id);
        $changes = JPnotificationsHelper::formatChanges($lang, $fields);
        $footer  = sprintf($lang->_('COM_JOOMPROJECT_EMAIL_FOOTER'), Uri::root());
        $link    = Uri::base(false) . Route::_(JPdesignsHelperRoute::getDesignRoute($after->parent_id, $after->project_id, $album, $after->id));
        $txt     = sprintf($format, $receiver->name, $user->name, $changes, $link);
        $txt     = str_replace('\n', "\n", $txt . "\n\n" . $footer);

        return $txt;
    }


    public static function getAlbumId($design)
    {
        static $ids = array();

        if (array_key_exists($design, $ids)) {
            return $ids[$design];
        }

        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select('album_id')
              ->from('#__jp_designs')
              ->where('id = ' . $db->quote((int) $design));

        $db->setQuery($query);
        $id = (int) $db->loadResult();

        $ids[$design] = $id;

        return $id;
    }


    public static function translateValue($field, $value)
    {
        static $album_titles  = array();
        static $design_titles = array();

        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        switch ($field)
        {
            case 'album_id':
                if (array_key_exists($value, $album_titles)) {
                    $data = $album_titles[$value];
                }
                else {
                    if ($value > 0) {
                        $query->clear();
                        $query->select('title')
                              ->from('#__jp_design_albums')
                              ->where('id = ' . $db->quote((int) $value));

                        $db->setQuery($query);
                        $title = $db->loadResult();
                    }
                    else {
                        $title = '-';
                    }

                    $album_titles[$value] = $title;
                    $data = $album_titles[$value];
                }
                break;

            case 'parent_id':
                if (array_key_exists($value, $design_titles)) {
                    $data = $design_titles[$value];
                }
                else {
                    if ($value > 0) {
                        $query->clear();
                        $query->select('title')
                              ->from('#__jp_designs')
                              ->where('id = ' . $db->quote((int) $value));

                        $db->setQuery($query);
                        $title = $db->loadResult();
                    }
                    else {
                        $title = '-';
                    }

                    $design_titles[$value] = $title;
                    $data = $design_titles[$value];
                }
                break;

            default:
                $data = $value;
                break;
        }

        return $data;
    }
}
