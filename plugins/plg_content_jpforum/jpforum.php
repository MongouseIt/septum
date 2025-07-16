<?php
/**
 * @package      pkg_joomproject
 * @subpackage   plg_content_jpforum
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;


/**
 * Joomproject Forum Content Plugin Class
 *
 */
class plgContentJPforum extends CMSPlugin
{
    /**
     * Supported plugin contexts
     *
     * @var    array
     */
    protected $contexts = array(
        'com_jpprojects.project', 'com_jpprojects.form',
        'com_jpforum.topic', 'com_jpforum.topicform'
    );


    /**
     * "onContentAfterSave" event handler
     *
     * @param     string     $context    The item context
     * @param     object     $table      The item table object
     * @param     boolean    $is_new     New item indicator (True is new, False is update)
     *
     * @return    boolean                True
     */
    public function onContentAfterSave($context, $table, $is_new = false)
    {
        // Do nothing if the plugin is disabled
        if (!PluginHelper::isEnabled('content', 'jpforum')) return true;

        // Check if the context is supported
        if (!in_array($context, $this->contexts)) return true;

        // Do nothing if this is a new item
        if ($is_new) return true;

        $context = $this->unalias($context);

        // Update access
        $this->updateAccess($context, $table->id, $table->access);

        // Update publishing state
        $this->updatePubState($context, $table->id, $table->state);

        if ($context == 'com_jpprojects.project') {
            // Update parent asset
            $this->updateParentAsset($table->id);
        }

        return true;
    }


    /**
     * "onContentChangeState" event handler
     *
     * @param     string     $context    The item context
     * @param     array      $pks        The item id's whose state was changed
     * @param     integer    $value      New state to which the items were changed
     *
     * @return    boolean                True
     */
    public function onContentChangeState($context, $pks, $value)
    {
        // Do nothing if the plugin is disabled
        if (!PluginHelper::isEnabled('content', 'jpforum')) return true;

        // Check if the context is supported
        if (!in_array($context, $this->contexts)) return true;

        $context = $this->unalias($context);

        // Update publishing state
        foreach ($pks AS $id)
        {
            $this->updatePubState($context, $id, $value);
        }

        return true;
    }


    /**
     * "onContentAfterDelete" event handler
     *
     * @param     string     $context    The item context
     * @param     object     $table      The item table object
     *
     * @return    boolean                True
     */
    public function onContentAfterDelete($context, $table)
    {
        // Do nothing if the plugin is disabled
        if (!PluginHelper::isEnabled('content', 'jpforum')) return true;

        // Check if the context is supported
        if (!in_array($context, $this->contexts)) return true;

        $context = $this->unalias($context);

        $this->deleteFromContext($context, $table->id);

        if ($context == 'com_jpprojects.project') {
            $this->deleteProjectAsset($table->id);
        }

        return true;
    }


    /**
     * Method to unalias the context
     *
     * @param     string    $context    The context alias
     *
     * @return    string    $context    The actual context
     */
    protected function unalias($context)
    {
        switch ($context)
        {
            case 'com_jpprojects.project':
            case 'com_jpprojects.form':
                return 'com_jpprojects.project';
                break;

            case 'com_jpforum.topic':
            case 'com_jpforum.topicform':
                return 'com_jpforum.topic';
                break;
        }

        return $context;
    }


    /**
     * Method to delete all topics and replies from the given context
     *
     * @param     string     $context    The context
     * @param     integer    $id         The context id
     *
     * @return    void
     */
    protected function deleteFromContext($context, $id)
    {
        static $imported = false;

        if (!$imported) {
            jimport('joomproject.library');
            JLoader::register('JPtableTopic', JPATH_ADMINISTRATOR . '/components/com_jpforum/tables/topic.php');
            JLoader::register('JPtableReply', JPATH_ADMINISTRATOR . '/components/com_jpforum/tables/reply.php');

            $imported = true;
        }

        $topic_table = Table::getInstance('Topic', 'JPtable');
        $reply_table = Table::getInstance('Reply', 'JPtable');

        if (!$topic_table || !$reply_table) return;

        $db      = Factory::getDbo();
        $query   = $db->getQuery(true);
        $topics  = array();
        $replies = array();

        $fields = array(
            'com_jpprojects.project' => 'project_id',
            'com_jpforum.topic'      => 'topic_id'
        );

        // Get all replies
        $query->select('id')
              ->from('#__jp_replies')
              ->where($fields[$context] . ' = ' . (int) $id);

        $db->setQuery($query);
        $replies = (array) $db->loadColumn();

        // Get all topics
        if ($context != 'com_jpforum.topic') {
            $query->clear();
            $query->select('id')
                  ->from('#__jp_topics')
                  ->where($fields[$context] . ' = ' . (int) $id);

            $db->setQuery($query);
            $topics = (array) $db->loadColumn();
        }

        // Delete replies
        foreach ($replies AS $pk)
        {
            $reply_table->delete((int) $pk);
        }

        // Delete topics
        foreach ($topics AS $pk)
        {
            $topic_table->delete((int) $pk);
        }
    }


    /**
     * Method to update the publishing state of all topics and replies
     * associated with the given context
     *
     * @param     string     $context    The context name
     * @param     integer    $id         The context item id
     * @param     integer    $state      The new publishing state
     *
     * @return    void
     */
    protected function updatePubState($context, $id, $state)
    {
        // Do nothing on publish
        if ($state == '1') return;

        $fields = array(
            'com_jpprojects.project' => 'project_id',
            'com_jpforum.topic'      => 'topic_id'
        );

        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        // Update replies
        $query->update('#__jp_replies')
              ->set('state = ' . $state)
              ->where($fields[$context] . ' = ' . (int) $id)
              ->where(($state == 0 ? 'state NOT IN(-2,0,2)' : 'state <> -2'));

        $db->setQuery($query);
        $db->execute();

        // Update topics
        if ($context != 'com_jpforum.topic') {
            $query->clear();
            $query->update('#__jp_topics')
                  ->set('state = ' . $state)
                  ->where($fields[$context] . ' = ' . (int) $id)
                  ->where(($state == 0 ? 'state NOT IN(-2,0,2)' : 'state <> -2'));

            $db->setQuery($query);
            $db->execute();
        }
    }


    /**
     * Method to update the access level of topics and replies
     * associated with the given context
     *
     * @param     string     $context    The context name
     * @param     integer    $id         The context id
     * @param     integer    $access     The access level
     *
     * @return    void
     */
    protected function updateAccess($context, $id, $access)
    {
        jimport('joomproject.library');

        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        $allowed = JPAccessHelper::getAccessTree($access);

        $fields = array(
            'com_jpprojects.project' => 'project_id',
            'com_jpforum.topic'      => 'topic_id'
        );

        // Update replies
        $query->update('#__jp_replies')
              ->set('access = ' . (int) $access)
              ->where($fields[$context] . ' = ' . (int) $id);

        if (count($allowed) == 1) {
            $query->where('access <> ' . (int) $allowed[0]);
        }
        elseif (count($allowed) > 1) {
            $query->where('access NOT IN( ' . implode(', ', $allowed) . ')');
        }

        $db->setQuery($query);
        $db->execute();


        if ($context == 'com_jpforum.topic') return;

        // Update topics
        $query->clear();
        $query->update('#__jp_topics')
              ->set('access = ' . (int) $access)
              ->where($fields[$context] . ' = ' . (int) $id);

        if (count($allowed) == 1) {
            $query->where('access <> ' . (int) $allowed[0]);
        }
        elseif (count($allowed) > 1) {
            $query->where('access NOT IN( ' . implode(', ', $allowed) . ')');
        }

        $db->setQuery($query);
        $db->execute();
    }


    /**
     * Method to change the hierarchy of all topic assets
     *
     * @param     integer    $project    The project id
     *
     * @return    boolean                True on success
     */
    protected function updateParentAsset($project)
    {
        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        // Find the component asset id
        $query->select('id')
              ->from('#__assets')
              ->where('name = ' . $db->quote('com_jpforum'));

        $db->setQuery($query);
        $com_asset = (int) $db->loadResult();

        if (!$com_asset) return false;

        // Find the project asset id
        $query->clear();
        $query->select('id')
              ->from('#__assets')
              ->where('name = ' . $db->quote('com_jpforum.project.' . $project));

        $db->setQuery($query);
        $project_asset = (int) $db->loadResult();

        if (!$project_asset) return false;

        // Find all assets that need to be updated
        $query->clear();
        $query->select('a.asset_id')
              ->from('#__jp_topics AS a')
              ->join('INNER', '#__assets AS s ON s.id = a.asset_id')
              ->where('a.project_id = ' . (int) $project)
              ->where('s.parent_id != ' . (int) $project_asset)
              ->order('a.id ASC');

        $db->setQuery($query);
        $pks = $db->loadColumn();

        if (empty($pks) || !is_array($pks)) return true;

        // Update each asset
        foreach ($pks AS $pk)
        {
            $asset = Table::getInstance('Asset', 'JTable', array('dbo' => $db));

            $asset->load($pk);

            $asset->setLocation($project_asset, 'last-child');
            $asset->parent_id = $project_asset;

            if (!$asset->check() || !$asset->store(false)) {
                return false;
            }
        }

        return true;
    }


    /**
     * Method to delete a component project asset
     *
     * @param     integer    $project    The project id
     *
     * @return    boolean                True on success
     */
    protected function deleteProjectAsset($project)
    {
        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->delete('#__assets')
              ->where('name = ' . $db->quote('com_jpforum.project.' . (int) $project));

        $db->setQuery($query);

        if (!$db->execute()) return false;

        return true;
    }
}
