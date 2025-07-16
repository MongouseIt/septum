<?php
/**
 * @package      Joomproject Notifications
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Date\Date;
use Joomla\CMS\Language\Language;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Plugin\CMSPlugin;

require_once dirname(__FILE__) . '/helper.php';


/**
 * Joomproject Notifications plugin.
 *
 */
class plgContentJpnotifications extends CMSPlugin
{
    /**
     * The item table before it is saved/updated
     *
     * @var    object
     */
    protected $table_before;

    /**
     * The item table after it is saved/updated
     *
     * @var    object
     */
    protected $table_after;


    public function onContentBeforeSave($context, $table, $is_new = false)
    {
        // Check if the plugin is disabled. Return true if it is not.
        if (!PluginHelper::isEnabled('content', 'jpnotifications')) {
            return true;
        }

        // Component name must start with com_jp
        if (substr($context, 0 , 6) != 'com_jp') {
            return;
        }

        // Check config if sending is enabled for this type of event (new/update)
        $send_type = (int) $this->params->get('send_type');

        if (($is_new && $send_type == 2) || (!$is_new && $send_type == 1)) {
            return;
        }

        // Import JP library, just to be sure
        jimport('joomproject.library');

        // Make sure the item is supported
        if (!JPnotificationsHelper::isSupported($context, $this->params)) {
            return;
        }

        list($component, $item) = explode('.', $context, 2);

        $class_name = 'JP' . str_replace('com_jp', '', $component) . 'NotificationsHelper';
        $methods    = get_class_methods($class_name);

        if (in_array('getItemName', $methods)) {
            $item = call_user_func(array($class_name, 'getItemName'), $context);
        }

        if ($is_new) {
            $this->table_before = null;
        }
        else {
            $this->table_before = Table::getInstance(ucfirst($item), 'JPtable');

            if ($this->table_before) {
                $this->table_before->load($table->id);
            }
            else {
                $this->table_before = null;
            }
        }
    }


    public function onContentAfterSave($context, $table, $is_new = false)
    {
        // Check if the plugin is disabled. Return true if it is not.
        if (!PluginHelper::isEnabled('content', 'jpnotifications')) {
            return true;
        }

        // Component name must start with com_jp
        if (substr($context, 0 , 6) != 'com_jp') {
            return true;
        }

        // Check config if sending is enabled for this type of event (new/update)
        $send_type = (int) $this->params->get('send_type');

        if (($is_new && $send_type == 2) || (!$is_new && $send_type == 1)) {
            return;
        }

        // Import JP library, just to be sure
        jimport('joomproject.library');

        // Make sure the item is supported
        if (!JPnotificationsHelper::isSupported($context, $this->params)) {
            return true;
        }

        list($component, $item) = explode('.', $context, 2);

        $class_name = 'JP' . str_replace('com_jp', '', $component) . 'NotificationsHelper';
        $methods    = get_class_methods($class_name);

        if (in_array('getItemName', $methods)) {
            $item = call_user_func(array($class_name, 'getItemName'), $context);
        }

        $subject_method = 'get' . ucfirst($item) . 'Subject';
        $message_method = 'get' . ucfirst($item) . 'Message';
        $users_method   = 'getObservers';

        // Check if the item is active or not
        if (isset($table->state)) {
            if (intval($table->state) !== 1) {
                return true;
            }
        }

        // Check if the methods are available
        if (!in_array($subject_method, $methods) || !in_array($message_method, $methods) ||
            !in_array($users_method, $methods))
        {
            return true;
        }

        $users = call_user_func_array(array($class_name, $users_method), array($context, $table, $is_new));
        $dupe  = array();

        if (count($users) == 0) {
            return true;
        }

        // Load user objects and perform access check
        if (isset($table->access)) {
            foreach ($users AS $i => $u)
            {
                if (in_array($u, $dupe)) {
                    unset($users[$i]);
                    continue;
                }

                $dupe[] = $u;

                $user = Factory::getUser((int) $u);

                if (!$user->authorise('core.admin', $component)) {
                    $allowed = $user->getAuthorisedViewLevels();

                    if (!in_array($table->access, $allowed)) {
                        unset($users[$i]);
                        continue;
                    }
                }

                $users[$i] = $user;
            }
        }
        else {
            foreach ($users AS $i => $u)
            {
                if (in_array($u, $dupe)) {
                    unset($users[$i]);
                    continue;
                }

                $dupe[] = $u;

                $users[$i] = Factory::getUser((int) $u);
            }
        }


        if (count($users) == 0) {
            return true;
        }

        $def_lang = ComponentHelper::getParams('com_languages')->get('administrator');
		$debug    = Factory::getConfig()->get('debug_lang');
		$mailfrom = Factory::getConfig()->get('mailfrom');
		$fromname = Factory::getConfig()->get('fromname');
        $user     = Factory::getApplication()->getIdentity();
        $is_site  = Factory::getApplication()->isClient('site');
        $date     = new Date();
        $now      = $date->toSql();
        $store    = $this->params->get('send_method');

        $db = Factory::getDbo();

        $this->table_after = $table;

        foreach ($users as $receiver)
		{
		    // Make sure we have a user object and an email address
            if (!is_object($receiver) || empty($receiver->email)) {
		        continue;
		    }

		    if ($receiver->id == $user->id) {
		        // Don't mail own actions to self
                continue;
		    }

            // Load the default language of the component
            $lang = Language::getInstance($receiver->getParam('language', $receiver->getParam('site_language', $def_lang)), $debug);
		    $lang->load("com_joomproject", JPATH_SITE);
            $lang->load($component, JPATH_SITE);

            if ($is_site) {
                $lang->load("com_joomproject", JPATH_ADMINISTRATOR);
                $lang->load($component, JPATH_ADMINISTRATOR);
            }

            // Generate the subject and body
            $subject = call_user_func_array(array($class_name, $subject_method), array($lang, $receiver, $user, $this->table_after, $this->table_before, $is_new));
            $message = call_user_func_array(array($class_name, $message_method), array($lang, $receiver, $user, $this->table_after, $this->table_before, $is_new));

            if ($subject === false || $message === false) {
                // Abort if the subject or message is False
                break;
            }

            if (!$store) {
                // Send directly
                $mailer = Factory::getMailer();
                $mailer->sendMail($mailfrom, $fromname, $receiver->email, $subject, $message);
            }
            else {
                // Store in db
                $data = new stdClass();

                $data->id      = null;
                $data->email   = $receiver->email;
                $data->subject = $subject;
                $data->message = $message;
                $data->created = $now;

                $db->insertObject('#__jp_emailqueue', $data);
            }
		}

        return true;
    }


    public function onJoomprojectComplete($context, $pks)
    {
        // Check if the plugin is disabled. Return true if it is not.
        if (!PluginHelper::isEnabled('content', 'jpnotifications')) {
            return true;
        }

        // Component name must start with com_jp
        if (substr($context, 0 , 6) != 'com_jp') {
            return true;
        }

        // Check config if sending is enabled for this type of event (new/update)
        $send_type = (int) $this->params->get('send_type');

        if ($send_type == 1) {
            return true;
        }

        // Import JP library, just to be sure
        jimport('joomproject.library');

        // Make sure the item is supported
        if (!JPnotificationsHelper::isSupported($context, $this->params)) {
            return true;
        }

        list($component, $item) = explode('.', $context, 2);

        $class_name = 'JP' . str_replace('com_jp', '', $component) . 'NotificationsHelper';
        $methods    = get_class_methods($class_name);

        if (in_array('getItemName', $methods)) {
            $item = call_user_func(array($class_name, 'getItemName'), $context);
        }

        $subject_method = 'get' . ucfirst($item) . 'CompletedSubject';
        $message_method = 'get' . ucfirst($item) . 'CompletedMessage';
        $table_method   = 'get' . ucfirst($item) . 'Table';
        $users_method   = 'getObservers';

        // Check if the item is active or not
        if (isset($table->state)) {
            if (intval($table->state) != 1) {
                return true;
            }
        }

        // Check if the methods are available
        if (!in_array($subject_method, $methods) || !in_array($message_method, $methods) || !in_array($users_method, $methods) || !in_array($table_method, $methods)) {
            return true;
        }

        $table = call_user_func_array(array($class_name, $table_method), array());

        foreach ($pks AS $id)
        {
            $table->load($id);

            $users = call_user_func_array(array($class_name, $users_method), array($context, $table, false));
            $dupe  = array();

            if (count($users) == 0) {
                continue;
            }

            // Load user objects and perform access check
            if (isset($table->access)) {
                foreach ($users AS $i => $u)
                {
                    if (in_array($u, $dupe)) {
                        unset($users[$i]);
                        continue;
                    }

                    $dupe[] = $u;

                    $user = Factory::getUser((int) $u);

                    if (!$user->authorise('core.admin', $component)) {
                        $allowed = $user->getAuthorisedViewLevels();

                        if (!in_array($table->access, $allowed)) {
                            unset($users[$i]);
                            continue;
                        }
                    }

                    $users[$i] = $user;
                }
            }
            else {
                foreach ($users AS $i => $u)
                {
                    if (in_array($u, $dupe)) {
                        unset($users[$i]);
                        continue;
                    }

                    $dupe[] = $u;

                    $users[$i] = Factory::getUser((int) $u);
                }
            }

            if (count($users) == 0) {
                continue;
            }

            $def_lang = ComponentHelper::getParams('com_languages')->get('administrator');
    		$debug    = Factory::getConfig()->get('debug_lang');
    		$mailfrom = Factory::getConfig()->get('mailfrom');
    		$fromname = Factory::getConfig()->get('fromname');
            $user     = Factory::getApplication()->getIdentity();
            $is_site  = Factory::getApplication()->isClient('site');
            $date     = new Date();
            $now      = $date->toSql();
            $store    = $this->params->get('send_method');

            $db = Factory::getDbo();

            $this->table_after = $table;

            foreach ($users as $receiver)
    		{
    		    // Make sure we have a user object and an email address
                if (!is_object($receiver) || empty($receiver->email)) {
    		        continue;
    		    }

    		    if ($receiver->id == $user->id) {
    		        // Don't mail own actions to self
                    continue;
    		    }

                // Load the default language of the component
                $lang = Language::getInstance($receiver->getParam('language', $receiver->getParam('site_language', $def_lang)), $debug);
    		    $lang->load("com_joomproject", JPATH_SITE);
                $lang->load($component, JPATH_SITE);

                if ($is_site) {
                    $lang->load("com_joomproject", JPATH_ADMINISTRATOR);
                    $lang->load($component, JPATH_ADMINISTRATOR);
                }

                // Generate the subject and body
                $subject = call_user_func_array(array($class_name, $subject_method), array($lang, $receiver, $user, $table));
                $message = call_user_func_array(array($class_name, $message_method), array($lang, $receiver, $user, $table));

                if ($subject === false || $message === false) {
                    // Abort if the subject or message is False
                    break;
                }

                if (!$store) {
                    // Send directly
                    $mailer = Factory::getMailer();
                    $mailer->sendMail($mailfrom, $fromname, $receiver->email, $subject, $message);
                }
                else {
                    // Store in db
                    $data = new stdClass();

                    $data->id      = null;
                    $data->email   = $receiver->email;
                    $data->subject = $subject;
                    $data->message = $message;
                    $data->created = $now;

                    $db->insertObject('#__jp_emailqueue', $data);
                }
    		}
        }

        return true;
    }
}