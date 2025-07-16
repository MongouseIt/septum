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
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Filesystem\Path;

if (!class_exists('JoomprojectAvatar'))
{
    /**
     * Abstract class for User Avatar
     *
     */
    abstract class JoomprojectAvatar
    {
        public static function image($id, $name = null)
        {
            $img_url = self::lookup($id);
            $attr    = '';

            if ($name) $attr .= ' title="' . htmlspecialchars($name, ENT_COMPAT, 'UTF-8') . '"';

            $html = array();
            $html[] = '<img src="' . $img_url . '" ' . $attr . '/>';

            return implode('', $html);
        }


        public static function path($id)
        {
            return self::lookup($id);
        }


        protected static function lookup($id)
        {
            static $cache  = array();
            static $vendor = null;

            $key = (int) $id;

            if (isset($cache[$key])) {
                return $cache[$key];
            }

            if (is_null($vendor)) {
                $params = ComponentHelper::getParams('com_joomproject');
                $vendor = $params->get('user_profile_avatar');
            }

            $img_path = null;

            switch ($vendor)
            {
                case 'cb':
                    $img_path = self::lookupCB($id);
                    break;

                case 'js':
                    $img_path = self::lookupJS($id);
                    break;

                case 'kunena':
                    $img_path = self::lookupKunena($id);
                    break;

                case 'gravatar':
                    $img_path = self::lookupGravatar($id);
                    break;

                case 'mosets':
                    $img_path = self::lookupMosets($id);
                    break;
	            case 'osmembership':
		            $img_path = self::lookupOsmembership($id);
		            break;
            }

            if (!empty($img_path)) {
                return $img_path;
            }

            // Default - Joomproject avatar
            $base_path = JPATH_ROOT . '/media/com_joomproject/repo/0/avatar';
            $base_url  = Uri::root(true) . '/media/com_joomproject/repo/0/avatar';
            $img_path  = NULL;

            if (\Joomla\CMS\Filesystem\File::exists($base_path . '/' . $key . '.jpg')) {
                $img_path = $base_url . '/' . $key . '.jpg';
            }
            elseif (\Joomla\CMS\Filesystem\File::exists($base_path . '/' . $key . '.jpeg')) {
                $img_path = $base_url . '/' . $key . '.jpeg';
            }
            elseif (\Joomla\CMS\Filesystem\File::exists($base_path . '/' . $key . '.png')) {
                $img_path = $base_url . '/' . $key . '.png';
            }
            elseif (\Joomla\CMS\Filesystem\File::exists($base_path . '/' . $key . '.gif')) {
                $img_path = $base_url . '/' . $key . '.gif';
            }
            else {
                $img_path = Uri::root(true) . '/media/com_joomproject/joomproject/images/icons/avatar.jpg';
            }

            $cache[$key] = $img_path;

            return $cache[$key];
        }


        /**
         * Method to lookup a Community Builder user profile image path
         *
         * @param     integer    $id    The user slug
         *
         * @return    string            The profile url
         */
        protected static function lookupCB($id)
        {
            static $db;
            static $query;

            if (!$db) {
                $db    = Factory::getDbo();
                $query = $db->getQuery(true);
            }

            // Check CB 2
            if (is_dir(JPATH_LIBRARIES . '/CBLib')) {
                $default = Uri::root(true) . '/components/com_comprofiler/plugin/templates/default/images/avatar/tnnophoto_n.png';
            }
            else {
                // Fall back to CB 1
                $default = Uri::root(true) . '/components/com_comprofiler/images/english/tnnophoto.jpg';
            }

            $query->clear()
                  ->select('avatar')
                  ->from('#__comprofiler')
                  ->where('user_id = ' . (int) $id)
                  ->where('avatarapproved = 1');

            $db->setQuery($query);
            $img = $db->loadResult();

            if (empty($img)) return $default;

            $path = Path::clean(JPATH_ROOT . '/images/comprofiler/' . $img);
            if (!file_exists($path)) return $default;

            return Uri::root(true) . '/images/comprofiler/' . $img;
        }


        /**
         * Method to lookup a JomSocial user profile image path
         *
         * @param     integer    $id    The user slug
         *
         * @return    string            The profile url
         */
        protected static function lookupJS($id)
        {
            static $exists = null;

            if (is_null($exists)) {
                $path = JPATH_ROOT . '/components/com_community/libraries/core.php';

                $exists = file_exists($path);

                if ($exists) require_once $path;
            }

            $default = Uri::root(true) . '/media/com_joomproject/joomproject/images/icons/avatar.jpg';


            if (!$exists) return $default;

            return CFactory::getUser($id)->getAvatar();
        }


        /**
         * Method to lookup a Kunena user profile image path
         *
         * @param     integer    $id    The user slug
         *
         * @return    string            The profile url
         */
        protected static function lookupKunena($id)
        {
            static $installed = null;

            if (is_null($installed)) {
                // Initialize Kunena (if Kunena System Plugin isn't enabled).
                $api = JPATH_ADMINISTRATOR . '/components/com_kunena/api.php';
                $installed = file_exists($api);

                if ($installed) require_once $api;
            }

            if (!$installed) return null;

            $user = KunenaFactory::getUser((int) $id);

            return $user->getAvatarURL(200, 200);
        }


        /**
         * Method to lookup a Gravatar user profile image path
         *
         * @param     integer    $id    The user slug
         *
         * @return    string            The profile url
         */
        protected static function lookupGravatar($id)
        {
            static $db;
            static $query;
            static $ssl;

            if (!$db) {
                $db    = Factory::getDbo();
                $query = $db->getQuery(true);
                $ssl   = Uri::getInstance()->isSSL();
            }

            $query->clear()
                  ->select('email')
                  ->from('#__users')
                  ->where('id = ' . (int) $id);

            $db->setQuery($query);
            $email = $db->loadResult();

            $default = Uri::root() . 'media/com_joomproject/joomproject/images/icons/avatar.jpg';

            if (empty($email)) return $default;

            $path = 'http' . ($ssl ? 's' : '') . '://www.gravatar.com/avatar/'
                  . md5(strtolower(trim($email)))
                  . '?d=' . urlencode($default)
                  . '&s=200';

            return $path;
        }


        /**
         * Method to lookup a Mosets Profile Picture plugin image path
         *
         * @param     integer    $id    The user slug
         *
         * @return    string            The profile url
         */
        protected static function lookupMosets($id)
        {
            static $exists = null;

            if (is_null($exists)) {
                $lib    = JPATH_LIBRARIES . '/mosets/profilepicture/profilepicture.php';
                $exists = file_exists($lib);

                if ($exists) require_once $lib;
            }

            $default = Uri::root(true) . '/media/com_joomproject/joomproject/images/icons/avatar.jpg';

            if (!$exists) return $default;

            $pic = new ProfilePicture((int) $id);

            if (!$pic->exists()) return $default;

            $url = str_replace('\\', '/', $pic->getURL());

            return $url;
        }


	    /**
	     * Method to lookup a Mosets Profile Picture plugin image path
	     *
	     * @param     integer    $id    The user slug
	     *
	     * @return    string            The profile url
	     */
	    protected static function lookupOsmembership($id)
	    {
		    static $exists = null;

		    // check if helper file exist and require it
		    if (is_null($exists)) {

			    $lib    = JPATH_SITE . '/components/com_osmembership/helper/subscription.php';
			    $exists = file_exists($lib);

			    if ($exists) require_once $lib;
		    }

		    $default = Uri::root(true) . '/media/com_joomproject/joomproject/images/icons/avatar.jpg';

		    if (!$exists) return $default;

		    // get user profile from database
		    $userProfile = OSMembershipHelperSubscription::getMembershipProfile($id);

		    // user not found in subscribed users
		    if(is_null($userProfile)) return $default;

		    // if user profile image is empty return default
		    if(empty($userProfile->avatar)) return $default;

			// build profile image path
		    $url =  Uri::base(true) . '/media/com_osmembership/avatars/' . $userProfile->avatar;

		    return $url;
	    }


    }


}
