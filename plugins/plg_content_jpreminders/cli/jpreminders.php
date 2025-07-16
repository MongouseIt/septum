<?php
/**
 * @package      Joomproject Notifications
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

// Initialize Joomla framework
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\PluginHelper;

defined('_JEXEC') or die();

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



class JPremindersCron extends JApplicationCli
{

    public $time;

    public function __construct()
    {

        Factory::getLanguage()->load('com_jpreminders', JPATH_ADMINISTRATOR);
        parent::__construct();
        Factory::$application = $this;
        Factory::$session = new CMSObject();

    }

    public function getMenu($name = null, $opt = array())
    {
        return null;
    }

    public function getName()
    {
        return 'JoomProject Reminders Execution';
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

    /**
     * Entry point for the script
     *
     */
    public function doExecute()
    {

	    // Print a blank line.
	    $this->out('Reminders Cron Job');
	    $this->out('============================');

	    // Initialize the time value.
	    $this->time = microtime(true);



	    // Remove the script time limit.
	    @set_time_limit(0);

	    // Fool the system into thinking we are running as JSite with Smart Search as the active component.
	    $_SERVER['HTTP_HOST'] = 'domain.com';
	    Factory::getApplication('site');

        if(!ComponentHelper::isEnabled('com_jpreminders')){
            die('Error: Component "JoomProject Reminders" not enabled');
        }


	    // Check if the plugin is disabled. Return true if it is not.
	    if (!PluginHelper::isEnabled('content', 'jpreminders')) {
		    die('Error: Plugin "JoomProject Reminders" not enabled');
	    }


	    $plugin = PluginHelper::getPlugin('content','jpreminders');




	    $plugin->params =   json_decode($plugin->params);

	    $limit = (int) $plugin->params->cron_limit;
	    $sendMethod    = (int) $plugin->params->send_method;

	    if(!$sendMethod){
	    	die('Error: Cron Job disabled in plugin, please edit plugin "JoomProject Reminders" and change send method option to "cron job"');
	    }

	    BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_jptasks/models');
	    BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_jpreminders/models');
	    BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_jpmilestones/models');
	    BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_jpprojects/models');
	    JLoader::register('JPtasksHelper',JPATH_ADMINISTRATOR.'/components/com_jptasks/helpers/jptasks.php');


	    $reminderModel = BaseDatabaseModel::getInstance( 'Reminder', 'JPremindersModel');
	    $dates = $reminderModel->getDates();
	    $taskModel = BaseDatabaseModel::getInstance( 'Task', 'JPtasksModel',array('ignore_request' => true));

	    $milestoneModel = BaseDatabaseModel::getInstance( 'Milestone', 'JPmilestonesModel',array('ignore_request' => true));
	    $projectsModel = BaseDatabaseModel::getInstance( 'Project', 'JPprojectsModel',array('ignore_request' => true));
	    $tasklistModel  = BaseDatabaseModel::getInstance( 'Tasklist', 'JPtasksModel',array('ignore_request' => true));


	    $jConfig  = Factory::getConfig();
	    $tasksComponent = ComponentHelper::getParams('com_jptasks');
	    $sitename = $jConfig->get('sitename');
	    $mailFrom = $jConfig->get('mailfrom');
	    $fromName = $jConfig->get('fromname');
	    $curent = new Date('now');
	    $curentDate = $curent->format('Y-m-d H');


	    $i = 1;
	    foreach ($dates as $reminder)
	    {
		    // skip dates without task id
		    if($reminder->task_id == 0) continue;

		    $reminderDate = Factory::getDate($reminder->date)->format('Y-m-d H');


		    if($reminderDate <= $curentDate ){

			    $task =  $taskModel->getItem((int)$reminder->task_id);
			    $milestone = (int)$task->milestone_id > 0 ? $milestoneModel->getItem((int)$task->milestone_id) : null;
			    $project = (int)$task->project_id > 0  ? $projectsModel->getItem((int)$task->project_id) : null;
			    $tasklist = (int)$task->list_id > 0 ? $tasklistModel->getItem((int)$task->list_id) : null;
			    $users_extract = implode(',',(array)json_decode($reminder->assigned_users, true));

			    if(is_null($users_extract) or empty($users_extract)){
				    $users = $taskModel->getUsers((int)$reminder->task_id);
				    $users[] = $task->created_by;
			    }else{
				    $users   = explode(',',$users_extract);
			    }

			    foreach ($users  as $user){

				    $reminder->name =   Factory::getUser($user)->name;
				    $reminder->email =  Factory::getUser($user)->email;

				    $substitutions = array(
					    '[task-name]'       => $task->title,
					    '[task-project]'    => !is_null($project) ? $project->title : '-',
					    '[task-milestone]'  => !is_null($milestone) ? $milestone->title : '-',
					    '[task-list]'       => !is_null($tasklist) ? $tasklist->title : '-',
					    '[task-startdate]'  => $task->start_date,
					    '[task-deadline]'   => $task->end_date,
					    '[task-priority]'   => JPtasksHelper::priority2string($task->priority),
					    '[estimated-time]'  => $task->estimate,
					    '[hourly-rate]'     => $task->rate,
					    '[reminder-description]' => $reminder->description,
					    '\\n'           => "\n",
				    );

				    $email_body_default = "<p>[reminder-description]</p>
<h3>Task Information :</h3>
<table>
<tbody>
<tr>
<td><strong>In Project</strong></td>
<td>[task-project]</td>
</tr>
<tr>
<td><strong>In Milestone</strong></td>
<td>[task-milestone]</td>
</tr>
<tr>
<td><strong>In Task List</strong></td>
<td>[task-list]</td>
</tr>
<tr>
<td><strong>Start date</strong></td>
<td>[task-startdate]</td>
</tr>
<tr>
<td><strong>Deadline</strong></td>
<td>[task-deadline]</td>
</tr>
<tr>
<td><strong>Priority</strong></td>
<td>[task-priority]</td>
</tr>
<tr>
<td><strong>Estimated Time</strong></td>
<td>[estimated-time]</td>
</tr>
<tr>
<td><strong>Hourly Rate</strong></td>
<td>[hourly-rate]</td>
</tr>
</tbody>
</table>";

				    $email_subject =  $tasksComponent->get('reminder_email_subject','Reminder of task : [task-name]');
				    $email_body    =  $tasksComponent->get('reminder_email_body',$email_body_default);

				    foreach ($substitutions as $k => $v)
				    {
					    $email_subject = str_replace($k, $v,$email_subject);
					    $email_body    = str_replace($k, $v,$email_body);
				    }


				    // Send directly
				    $mailer = Factory::getMailer();
				    $mailer->setSender(array($mailFrom, $fromName));
				    $mailer->addRecipient($reminder->email);
				    $mailer->isHtml(true);
				    $mailer->setSubject($email_subject);
				    $mailer->setBody($email_body);
				    $reminderModel->deleteDates($reminder->reminder_id,true);
				    $reminderModel->expiredDate();

				    if($mailer->Send()){
					    $i++;
				    }


			    }



		    }

		    if($limit == $i) break;
	    }
	    $i--;
	    $this->out($i.' reminder email was sent');



    }


}


JApplicationCli::getInstance('JPremindersCron')->execute();

