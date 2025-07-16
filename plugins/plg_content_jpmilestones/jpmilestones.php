<?php
/**
 * @package      pkg_joomproject
 * @subpackage   plg_content_jpmilestones
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
 * Joomproject Milestones Content Plugin Class
 *
 */
class plgContentJPmilestones extends CMSPlugin
{
    /**
     * Supported plugin contexts
     *
     * @var    array
     */
    protected $contexts = array(
        'com_jpprojects.project', 'com_jpprojects.form'
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
        if (!PluginHelper::isEnabled('content', 'jpmilestones')) return true;

        // Check if the context is supported
        if (!in_array($context, $this->contexts)) return true;

        // Do nothing if this is a new item
        if ($is_new) return true;

        // Update access
        $this->updateAccess($table->id, $table->access);

        // Update publishing state
        $this->updatePubState($table->id, $table->state);

        // Update parent asset
        $this->updateParentAsset($table->id);

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
        if (!PluginHelper::isEnabled('content', 'jpmilestones')) return true;

        // Check if the context is supported
        if (!in_array($context, $this->contexts)) return true;

        // Update publishing state
        foreach ($pks AS $id)
        {
            $this->updatePubState($id, $value);
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
        if (!PluginHelper::isEnabled('content', 'jpmilestones')) return true;

        // Check if the context is supported
        if (!in_array($context, $this->contexts)) return true;

        // Delete milestones
        $this->deleteFromProject($table->id);
        $this->deleteProjectAsset($table->id);

        return true;
    }


    /**
     * Method to delete all milestones from the given project
     *
     * @param     integer    $id    The project
     *
     * @return    void
     */
    protected function deleteFromProject($id)
    {
        static $imported = false;

        if (!$imported) {
            jimport('joomproject.library');
            JLoader::register('JPtableMilestone', JPATH_ADMINISTRATOR . '/components/com_jpmilestones/tables/milestone.php');

            $imported = true;
        }

        $table = Table::getInstance('Milestone', 'JPtable');
        if (!$table) return;

        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select('id')
              ->from('#__jp_milestones')
              ->where('project_id = ' . (int) $id);

        $db->setQuery($query);
        $pks = (array) $db->loadColumn();

        foreach ($pks AS $pk)
        {
            $table->delete((int) $pk);
        }
    }


    /**
     * Method to update the publishing state of all milestones
     * associated with the given project
     *
     * @param     integer    $project    The project
     * @param     integer    $state      The new publishing state
     *
     * @return    void
     */
    protected function updatePubState($project, $state)
    {
        // Do nothing on publish
        if ($state == '1') return;

        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->update('#__jp_milestones')
              ->set('state = ' . $state)
              ->where('project_id = ' . (int) $project);

        if ($state == 0) {
            $query->where('state NOT IN(-2,0,2)');
        }
        else {
            $query->where('state <> -2');
        }

        $db->setQuery($query);
        $db->execute();
    }


    /**
     * Method to update the access level of all milestones
     * associated with the given project
     *
     * @param     integer    $project    The project
     * @param     integer    $access     The access level
     *
     * @return    void
     */
    protected function updateAccess($project, $access)
    {
        jimport('joomproject.library');

        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        $allowed = JPAccessHelper::getAccessTree($access);

        $query->update('#__jp_milestones')
              ->set('access = ' . (int) $access)
              ->where('project_id = ' . (int) $project);

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
     * Method to change the hierarchy of all milestone assets
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
              ->where('name = ' . $db->quote('com_jpmilestones'));

        $db->setQuery($query);
        $com_asset = (int) $db->loadResult();


        if (!$com_asset) return false;

        // Find the project asset id
        $query->clear();
        $query->select('id')
              ->from('#__assets')
              ->where('name = ' . $db->quote('com_jpmilestones.project.' . $project));

        $db->setQuery($query);
        $project_asset = (int) $db->loadResult();

        if (!$project_asset) return false;

        // Find all assets that need to be updated
        $query->clear();
        $query->select('a.asset_id')
              ->from('#__jp_milestones AS a')
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
              ->where('name = ' . $db->quote('com_jpmilestones.project.' . (int) $project));

        $db->setQuery($query);

        if (!$db->execute()) return false;

        return true;
    }
}
