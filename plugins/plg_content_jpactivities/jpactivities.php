<?php
/**
 * @package      pkg_jpactivities
 * @subpackage   plg_content_jpactivities
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();
;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Profiler\Profiler;


// Register the component model and table
BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jpactivities/models', 'JPactivitiesModel');

// Register component table classes
JLoader::register('JPactivitiesTableItem', JPATH_ADMINISTRATOR . '/components/com_jpactivities/tables/item.php');
JLoader::register('JPTableJPactivities',    JPATH_ADMINISTRATOR . '/components/com_jpactivities/tables/jpactivities.php');
// Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jpactivities/tables');

// Register the activity plugin class
JLoader::register('plgJPactivities', dirname(__FILE__) . '/plugin.php');

// Include the user activity plugins
PluginHelper::importPlugin('jpactivities');


/**
 * User Activity Content Plugin.
 *
 */
class plgContentJPactivities extends CMSPlugin
{
    /**
     * Event dispatcher instance
     *
     * @var    object
     */
    protected $dispatcher;

    /**
     * List of unsupported contexts
     *
     * @var    array
     */
    protected $unsupported;


    /**
     * Constructor
     *
     * @param    object    $subject    The object to observe
     * @param    array     $config     An optional associative array of configuration settings.
     */
    public function __construct(&$subject, $config = array())
    {
        // Call parent contructor first
        parent::__construct($subject, $config);

        // Get the dispatcher
        $this->dispatcher = \Joomla\CMS\Factory::getApplication();

        // Set unsupported contexts
        $this->unsupported = array(
            'com_jpactivities.item',
            'com_jpactivities.event',
            'com_jpactivities.activity'
        );
    }


    /**
     * Triggers User Activity Plugins for the "onContentAfterSave" event
     *
     * @param     string     $context    The item context
     * @param     object     $table      The item table object
     * @param     boolean    $is_new     New item indicator (True is new, False is update)
     *
     * @return    boolean                True
     */
    public function onContentAfterSave($context, $table, $is_new)
    {
        if (!in_array($context, $this->unsupported)) {
            if (JDEBUG) {
                Profiler::getInstance()->mark('beforeJPactivities' . $context);
            }

            if (Factory::getApplication()->getIdentity()->authorise('core.create', 'com_jpactivities')) {
                $this->dispatcher->triggerEvent('onJPactivitiesAfterSave', array($context, $table, $is_new));
            }

            if (JDEBUG) {
                Factory::getApplication()->enqueueMessage(Profiler::getInstance()->mark('afterJPactivities' . $context), 'notice');
            }
        }

        return true;
    }


    /**
     * Triggers User Activity Plugins for the "onContentAfterDelete" event
     *
     * @param     string     $context    The item context
     * @param     object     $table      The item table object
     *
     * @return    boolean                True
     */
    public function onContentAfterDelete($context, $table)
    {
        if (!in_array($context, $this->unsupported)) {
            if (JDEBUG) {
                Profiler::getInstance()->mark('beforeJPactivities' . $context);
            }

            if (Factory::getApplication()->getIdentity()->authorise('core.create', 'com_jpactivities')) {
                $this->dispatcher->triggerEvent('onJPactivitiesAfterDelete', array($context, $table));
            }

            if (JDEBUG) {
                Factory::getApplication()->enqueueMessage(Profiler::getInstance()->mark('afterJPactivities' . $context), 'notice');
            }
        }

        return true;
    }


    /**
     * Triggers User Activity Plugins for the "onContentChangeState" event
     *
     * @param     string     $context    The item context
     * @param     array      $pks        The item id's whose state was changed
     * @param     integer    $value      New state to which the items were changed
     *
     * @return    boolean                True
     */
    public function onContentChangeState($context, $pks, $value)
    {
        if (!in_array($context, $this->unsupported)) {
            if (JDEBUG) {
                Profiler::getInstance()->mark('beforeJPactivities' . $context);
            }

            if (Factory::getApplication()->getIdentity()->authorise('core.create', 'com_jpactivities')) {
                $this->dispatcher->triggerEvent('onJPactivitiesChangeState', array($context, $pks, $value));
            }

            if (JDEBUG) {
                Factory::getApplication()->enqueueMessage(Profiler::getInstance()->mark('afterJPactivities' . $context), 'notice');
            }
        }

        return true;
    }
}
