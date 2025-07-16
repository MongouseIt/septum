<?php
/**
 * @package      com_jpdesigns
 * @subpackage   plg_content_jpdesigns
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
 * Joomproject Designs Content Plugin Class
 *
 */
class plgContentJPdesigns extends CMSPlugin
{
    /**
     * Supported plugin contexts
     *
     * @var    array    
     */
    protected $contexts = array(
        'com_jpprojects.project', 'com_jpprojects.form',
        'com_jpdesigns.album', 'com_jpdesigns.albumform',
        'com_jpdesigns.design', 'com_jpdesigns.designform'
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
        if (!PluginHelper::isEnabled('content', 'jpdesigns')) return true;

        // Check if the context is supported
        if (!in_array($context, $this->contexts)) return true;

        // Do nothing if this is a new item
        if ($is_new) return true;

        $context = $this->unalias($context);

        if (in_array($context, array('com_jpprojects.project', 'com_jpdesigns.album', 'com_jpdesigns.design'))) {
            // Update access
            $this->updateAccess($context, $table->id, $table->access);

            // Update publishing state
            $this->updatePubState($context, $table->id, $table->state);
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
        if (!PluginHelper::isEnabled('content', 'jpdesigns')) return true;

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
        if (!PluginHelper::isEnabled('content', 'jpdesigns')) return true;

        // Check if the context is supported
        if (!in_array($context, $this->contexts)) return true;

        $context = $this->unalias($context);

        $this->deleteFromContext($context, $table->id);

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

            case 'com_jpdesigns.album':
            case 'com_jpdesigns.albumform':
                return 'com_jpdesigns.album';
                break;

            case 'com_jpdesigns.design':
            case 'com_jpdesigns.designform':
                return 'com_jpdesigns.design';
                break;
        }

        return $context;
    }


    /**
     * Method to delete all albums, designs and revisions from the given context
     *
     * @param     string     $context    The context
     * @param     integer    $id         The project
     *
     * @return    void                   
     */
    protected function deleteFromContext($context, $id)
    {
        static $imported = false;

        if (!$imported) {
            jimport('joomproject.library');
            JLoader::register('JPtableAlbum', JPATH_ADMINISTRATOR . '/components/com_jpdesigns/tables/album.php');
            JLoader::register('JPtableDesign', JPATH_ADMINISTRATOR . '/components/com_jpdesigns/tables/design.php');
            JLoader::register('JPtableRevision', JPATH_ADMINISTRATOR . '/components/com_jpdesigns/tables/revision.php');

            $imported = true;
        }

        $album_table  = Table::getInstance('Album', 'JPtable');
        $design_table = Table::getInstance('Design', 'JPtable');
        $rev_table    = Table::getInstance('Revision', 'JPtable');

        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        $albums  = array();
        $designs = array();
        $revs    = array();

        $fields = array(
            'com_jpprojects.project' => 'project_id',
            'com_jpdesigns.album'    => 'album_id',
            'com_jpdesigns.design'   => 'parent_id'
        );

        // Get all albums
        if ($context == 'com_jpprojects.project') {
            $query->select('id')
                  ->from('#__jp_design_albums')
                  ->where($fields[$context] . ' = ' . (int) $id);

            $db->setQuery($query);
            $albums = (array) $db->loadColumn();
        }

        // Get all designs
        if (in_array($context, array('com_jpprojects.project', 'com_jpdesigns.album'))) {
            $query->clear();
            $query->select('id')
                  ->from('#__jp_designs')
                  ->where($fields[$context] . ' = ' . (int) $id);

            $db->setQuery($query);
            $designs = (array) $db->loadColumn();
        }

        // Get all revisions
        if (in_array($context, array('com_jpprojects.project', 'com_jpdesigns.design'))) {
            $query->clear();
            $query->select('id')
                  ->from('#__jp_design_revisions')
                  ->where($fields[$context] . ' = ' . (int) $id);

            $db->setQuery($query);
            $revs = (array) $db->loadColumn();
        }
        elseif ($context == 'com_jpdesigns.album' && count($designs)) {
            $query->clear();
            $query->select('id')
                  ->from('#__jp_design_revisions')
                  ->where('parent_id IN(' . implode(', ', $designs) . ')');

            $db->setQuery($query);
            $revs = (array) $db->loadColumn();
        }

        // Delete revisions
        foreach ($revs AS $pk)
        {
            $rev_table->deleteReferences($pk);

            $query->clear();
            $query->delete('#__jp_design_revisions')
                  ->where('id = ' . (int) $pk);

            $db->setQuery($query);
            $db->execute();
        }

        // Delete designs
        foreach ($designs AS $pk)
        {
            $design_table->delete((int) $pk);
        }

        // Delete albums
        foreach ($albums AS $pk)
        {
            $album_table->delete((int) $pk);
        }
    }


    /**
     * Method to update the publishing state of albums, designs and revisions
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
            'com_jpdesigns.album'    => 'album_id',
            'com_jpdesigns.design'   => 'parent_id'
        );

        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        // Update albums
        if ($context == 'com_jpprojects.project') {
            $query->clear();
            $query->update('#__jp_design_albums')
                  ->set('state = ' . $state)
                  ->where($fields[$context] . ' = ' . (int) $id)
                  ->where(($state == 0 ? 'state NOT IN(-2,0,2)' : 'state <> -2'));

            $db->setQuery($query);
            $db->execute();
        }

        // Update designs
        if (in_array($context, array('com_jpprojects.project', 'com_jpdesigns.album'))) {
            $query->clear();
            $query->update('#__jp_designs')
                  ->set('state = ' . $state)
                  ->where($fields[$context] . ' = ' . (int) $id)
                  ->where(($state == 0 ? 'state NOT IN(-2,0,2)' : 'state <> -2'));

            $db->setQuery($query);
            $db->execute();
        }

        // Update revisions
        if (in_array($context, array('com_jpprojects.project', 'com_jpdesigns.design'))) {
            $query->clear();
            $query->update('#__jp_design_revisions')
                  ->set('state = ' . $state)
                  ->where($fields[$context] . ' = ' . (int) $id)
                  ->where(($state == 0 ? 'state NOT IN(-2,0,2)' : 'state <> -2'));

            $db->setQuery($query);
            $db->execute();
        }
        elseif ($context == 'com_jpdesigns.album') {
            $query->clear();
            $query->select('id')
                  ->from('#__jp_designs')
                  ->where($fields[$context] . ' = ' . (int) $id);

            $db->setQuery($query);
            $designs = (array) $db->loadColumn();

            if (!count($designs)) return;

            foreach ($designs AS $design)
            {
                $query->clear();
                $query->update('#__jp_design_revisions')
                      ->set('state = ' . $state)
                      ->where('parent_id = ' . (int) $design)
                      ->where(($state == 0 ? 'state NOT IN(-2,0,2)' : 'state <> -2'));

                $db->setQuery($query);
                $db->execute();
            }
        }
    }


    /**
     * Method to update the access level of all albums, designs and revs
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
            'com_jpdesigns.album'    => 'album_id',
            'com_jpdesigns.design'   => 'parent_id'
        );

        // Update albums
        if ($context == 'com_jpprojects.project') {
            // Update tasks
            $query->update('#__jp_design_albums')
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

        // Update designs
        if (in_array($context, array('com_jpprojects.project', 'com_jpdesigns.album'))) {
            $query->clear();
            $query->update('#__jp_designs')
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

        // Update revisions
        if (in_array($context, array('com_jpprojects.project', 'com_jpdesigns.design'))) {
            $query->clear();
            $query->update('#__jp_design_revisions')
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
        elseif ($context == 'com_jpdesigns.album') {
            $query->clear();
            $query->select('id')
                  ->from('#__jp_designs')
                  ->where($fields[$context] . ' = ' . (int) $id);

            $db->setQuery($query);
            $designs = (array) $db->loadColumn();

            if (!count($designs)) return;

            $query->clear();
            $query->update('#__jp_design_revisions')
                  ->set('access = ' . (int) $access)
                  ->where('parent_id IN(' . implode(', ', $designs) . ')');

            if (count($allowed) == 1) {
                $query->where('access <> ' . (int) $allowed[0]);
            }
            elseif (count($allowed) > 1) {
                $query->where('access NOT IN( ' . implode(', ', $allowed) . ')');
            }

            $db->setQuery($query);
            $db->execute();
        }
    }
}
