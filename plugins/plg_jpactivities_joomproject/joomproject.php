<?php
/**
 * @package      plg_jpactivities_joomproject
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Plugin\CMSPlugin;


/**
 * Activity log plugin.
 *
 */
class plgJPactivitiesJoomproject extends CMSPlugin
{
    protected $supported;

    protected $aliases;

    protected $config;
    protected $_subject;

    protected $is_installed;

    /**
     * Constructor
     *
     * @param    object    $subject    The object to observe
     * @param    array     $config     An optional associative array of configuration settings.
     */
    public function __construct(&$subject, $config = array())
    {
       parent::__construct($subject, $config);

       $this->config = $config;

       $this->supported = array(
            'com_jpprojects.project',
            'com_jpmilestones.milestone',
            'com_jptasks.tasklist',
            'com_jptasks.task',
            'com_jprepo.directory',
            'com_jprepo.file',
            'com_jprepo.note',
            'com_jptime.time',
            'com_jpforum.topic',
            'com_jpforum.reply',
            'com_jpdesigns.album',
            'com_jpdesigns.design',
            'com_jpdesigns.revision'
            // 'com_jpcomments.comment'
       );

       $this->aliases = array(
            'com_jpprojects.form'        => 'com_jpprojects.project',
            'com_jpmilestones.form'      => 'com_jpmilestones.milestone',
            'com_jptasks.tasklistform'   => 'com_jptasks.tasklist',
            'com_jptasks.taskform'       => 'com_jptasks.task',
            'com_jprepo.directoryform'   => 'com_jprepo.directory',
            'com_jprepo.noteform'        => 'com_jprepo.note',
            'com_jprepo.fileform'        => 'com_jprepo.file',
            'com_jptime.form'            => 'com_jptime.time',
            'com_jpforum.topicform'      => 'com_jpforum.topic',
            'com_jpforum.replyform'      => 'com_jpforum.reply',
            'com_jpdesigns.albumform'    => 'com_jpdesigns.album',
            'com_jpdesigns.designform'   => 'com_jpdesigns.design',
            'com_jpdesigns.revisionform' => 'com_jpdesigns.revision'
            // 'com_jpcomments.form'        => 'com_jpcomments.comment'
       );
    }


    public function onJPactivitiesAfterSave($context, $table, $is_new)
    {
        $this->unalias($context);

        if (!in_array($context, $this->supported)) return true;
        if (!$plugin = $this->getPlugin($context)) return true;

        return $plugin->onJPactivitiesAfterSave($context, $table, $is_new);
    }


    public function onJPactivitiesAfterDelete($context, $table)
    {
        $this->unalias($context);

        if (!in_array($context, $this->supported)) return true;
        if (!$plugin = $this->getPlugin($context)) return true;

        return $plugin->onJPactivitiesAfterDelete($context, $table);
    }


    public function onJPactivitiesChangeState($context, $pks, $value)
    {
        $this->unalias($context);

        if (!in_array($context, $this->supported)) return true;
        if (!$plugin = $this->getPlugin($context)) return true;

        return $plugin->onJPactivitiesChangeState($context, $pks, $value);
    }


    protected function getPlugin($context)
    {
        static $cache = array();

        if (array_key_exists($context, $cache)) {
            return $cache[$context];
        }

        list($extension, $item) = explode('.', $context, 2);

        $file   = null;
        $class  = null;

        switch ($extension)
        {
            case 'com_jpprojects':
                $file = dirname(__FILE__) . '/plugins/jpprojects.php';
                $class = 'plgJPactivitiesJPprojects';
                break;

            case 'com_jpmilestones':
                $file = dirname(__FILE__) . '/plugins/jpmilestones.php';
                $class = 'plgJPactivitiesJPmilestones';
                break;

            case 'com_jptasks':
                $file = dirname(__FILE__) . '/plugins/jptasks.php';
                $class = 'plgJPactivitiesJPtasks';
                break;

            case 'com_jprepo':
                $file = dirname(__FILE__) . '/plugins/jprepo.php';
                $class = 'plgJPactivitiesJPrepo';
                break;

            case 'com_jptime':
                $file = dirname(__FILE__) . '/plugins/jptime.php';
                $class = 'plgJPactivitiesJPtime';
                break;

            case 'com_jpforum':
                $file = dirname(__FILE__) . '/plugins/jpforum.php';
                $class = 'plgJPactivitiesJPforum';
                break;

            case 'com_jpcomments':
                $file = dirname(__FILE__) . '/plugins/jpcomments.php';
                $class = 'plgJPactivitiesJPcomments';
                break;

            case 'com_jpdesigns':
                $file = dirname(__FILE__) . '/plugins/jpdesigns.php';
                $class = 'plgJPactivitiesJPdesigns';
                break;
        }


        if (is_null($file) || !file_exists($file)) return $helper;

        require_once $file;

        if (!class_exists($class)) return false;


        $cache[$context] = new $class($this->_subject, $this->config);

        return $cache[$context];
    }


    protected function unalias(&$context)
    {
        if(isset($this->aliases[$context])) {
            $context = $this->aliases[$context];
        }
    }
}
