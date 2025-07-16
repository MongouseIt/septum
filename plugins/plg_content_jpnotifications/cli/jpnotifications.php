<?php
/**
 * @package      Joomproject Notifications
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

// Initialize Joomla framework
defined('_JEXEC') or die();
use Joomla\CMS\Application\CliApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Object\CMSObject;
if(!defined('DS')) define ('DS',DIRECTORY_SEPARATOR);

if (strtolower(php_sapi_name()) != 'cli') {
    if (is_array($_SERVER)) {
        if (!isset($_SERVER['REMOTE_ADDR']) && !isset($_SERVER['SERVER_SOFTWARE'])) {
            echo str_repeat('#', 72), PHP_EOL, 'ERROR: ', 'You are currently using the ', php_sapi_name(), ' ', 'PHP interpreter to run JoomGrabber Pipes Execution.', PHP_EOL, 'ERROR: ', 'For security reasons, using the PHP *CLI* interpreter is ', 'required.', PHP_EOL, 'ERROR: ', 'If in doubt, ask your administrator for the path to the ', 'system\'s PHP *CLI* interpreter.', PHP_EOL, str_repeat('#', 72), PHP_EOL;
        }
    }
    die();
}

@error_reporting(-1);
@ini_set('display_errors', 1);

define('JPATH_BASE', dirname(__DIR__));

require_once(JPATH_BASE . '/includes/defines.php');

if (file_exists(JPATH_BASE . '/includes/framework.php')) {
    require_once(JPATH_BASE . '/includes/framework.php');
}
require_once((defined('JPATH_CONFIGURATION') ? JPATH_CONFIGURATION : JPATH_BASE) . '/configuration.php');
jimport('joomla.application.cli');
if (class_exists('JConfig')) {
    $jc = new JConfig();
    if (isset($jc->offset)) {
        date_default_timezone_set($jc->offset);
    }
    if (isset($jc->debug) && !defined('JDEBUG')) {
        define('JDEBUG', $jc->debug);
    }
} else {
    date_default_timezone_set('UTC');
}
$_SERVER['HTTP_HOST'] = '';


/**
 * Cron job to send Joomproject notifications
 *
 * @since      4.2
 */
class JPNotificationCron extends CliApplication
{


    public function __construct()
    {

        parent::__construct();
        Factory::$application = $this;
        Factory::$session = new CMSObject();

    }

    /**
     * Entry point for the script
     *
     */
    public function doExecute()
    {
        $mailfrom = Factory::getConfig()->get('mailfrom');
		$fromname = Factory::getConfig()->get('fromname');
        $db       = Factory::getDbo();

	    // Print a blank line.
	    $this->out('Notifications Cron Job');
	    $this->out('============================');

        // Get plugin params
        $query = $db->getQuery(true);

        $query->select('params')
              ->from('#__extensions')
              ->where('element = ' . $db->quote('jpnotifications'))
              ->where('type = ' . $db->quote('plugin'));

        $db->setQuery($query);
        $plg_params = $db->loadResult();

        $params = new Registry();
        $params->loadString($plg_params);

        $limit = (int) $params->get('cron_limit');


        // Get a list of emails to send
        $query->clear();

        $query->select('id, email, subject, message, created')
              ->from('#__jp_emailqueue')
              ->order('id ASC');

        $db->setQuery($query, 0, $limit);
        $items = $db->loadObjectList();

        if (!is_array($items)) $items = array();

        // Send and delete each email
        foreach ($items AS $item)
        {
            $mailer = Factory::getMailer();
            $mailer->sendMail($mailfrom, $fromname, $item->email, $item->subject, $item->message);

            $query->clear();
            $query->delete('#__jp_emailqueue')
                  ->where('id = ' . (int) $item->id);

            $db->setQuery($query);
            $db->execute();
        }
    }


    public function getMenu($name = null, $opt = array())
    {
        return null;
    }

    public function getName()
    {
        return 'JoomProject Notifications Execution';
    }

    public function getCfg($key, $dflt = null)
    {
        $jc = new JConfig();
        return (isset($jc->{$key}) ? $jc->{$key} : $dflt);
    }

    public function getTemplate()
    {
        return 'system';
    }

    public function isClient($identifier)
    {
        return ($identifier == 'cli');
    }


}

CliApplication::getInstance('JPNotificationCron')->execute();
