<?php
/**
 * @package      Joomproject Notifications
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Date\Date;



/**
 * Joomproject Notifications plugin.
 *
 */
class plgContentJpreminders extends CMSPlugin
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


    /**
     * Constructor
     *
     * @access      protected
     * @param       object  $subject The object to observe
     * @param       array   $config  An array that holds the plugin configuration
     * @since       1.6
     */
    public function __construct(& $subject, $config)
    {

        parent::__construct($subject, $config);
        $this->loadLanguage();

    }


    public function onContentBeforeDelete($context,$table){

        if($context == 'com_jpreminders.reminder'){
            $model = BaseDatabaseModel::getInstance('Reminder', 'JPremindersModel', array('ignore_request' => true));
            $model->deleteDates($table->id);
        }
    }


    public function onBeforeRender()
    {


    	//todo: cli (/cli/reminders.php) has same code, we should removed duplication in future

        $input = Factory::getApplication()->input;

    	// Check if the plugin is disabled. Return true if it is not.
        if (!PluginHelper::isEnabled('content', 'jpreminders') && !ComponentHelper::isEnabled('com_jpreminders')) {
            return true;
        }

        // if context is com_jptasks.task no need to continue, cuz force frontend task model
        if($input->get('option','') == 'com_jptasks' && $input->get('view','','word') == 'task')
            return true;

	    if($this->params->get('send_method',0))
	    	return true;

        BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_jptasks/models');
        JLoader::register('JPtasksModelTask',JPATH_ADMINISTRATOR.'/components/com_jptasks/models/task.php');
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
        $tasksConfig = ComponentHelper::getParams('com_jptasks');
        $sitename = $jConfig->get('sitename');
        $mailFrom = $jConfig->get('mailfrom');
        $fromName = $jConfig->get('fromname');
        $curent = new Date('now');
        $curentDate = $curent->format('Y-m-d H');
        $useCache = $this->params->get('use_joomla_cache',1);

        if($useCache){
            $cache = Factory::getCache('com_joomproject', 'output');
            $cache->setCaching(1);
            $reminderStatusCached = $cache->get('joomprject_reminder_status', 'com_joomproject');

            if($reminderStatusCached)
                return true;
            else
                // create cache if expired
                $cache->store(1, 'joomprject_reminder_status', 'com_joomproject');

        }


        // send reminders
        foreach ($dates as $reminder)
        {

            // skip dates without task id
            if($reminder->task_id == 0) continue;

            $reminderDate = Factory::getDate($reminder->date)->format('Y-m-d H');



            if($reminderDate >= $curentDate){


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

                    $email_subject =  $tasksConfig->get('reminder_email_subject','Reminder of task : [task-name]');




                    $email_body    =  $tasksConfig->get('reminder_email_body',$email_body_default);




                    foreach ($substitutions as $k => $v)
                    {
                        $v = (string) $v;

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
                    $mailer->Send();
                    $reminderModel->deleteDates($reminder->reminder_id,true);
                    $reminderModel->expiredDate();

                }
            }

        }

        return true;


    }
}