<?php
/**
 * @package      Joomproject Pro
 * @subpackage   Designs
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;


/**
 * Design Form JSON Controller
 *
 */
class JPdesignsControllerAlbumForm extends JPControllerFormJson
{
    /**
     * Method to check if you can add a new record.
     *
     * @param     array      $data    An array of input data.
     *
     * @return    boolean
     */
    protected function allowAdd($data = array())
    {
        // Get form input
        $project = isset($data['project_id']) ? (int) $data['project_id'] : JPApplicationHelper::getActiveProjectId();

        $user   = Factory::getApplication()->getIdentity();
        $asset  = 'com_jpdesigns';
        $access = true;

        // Check if the user has access to the project
        if ($project) {
            // Check if in allowed projects when not a super admin
            if (!$user->authorise('core.admin')) {
                $db    = Factory::getDbo();
                $query = $db->getQuery(true);

                $query->select('access')
                      ->from('#__jp_projects')
                      ->where('id = ' . (int) $project);

                $db->setQuery($query);

                $access = in_array($db->loadResult(), $user->getAuthorisedViewLevels());
            }

            // Change the asset name
            if (version_compare(JPVERSION, '4.2', 'ge')) {
                $asset  .= '.project.' . $project;
            }
        }

        return ($user->authorise('core.create', $asset) && $access);
    }


    /**
     * Method override to check if you can edit an existing record.
     *
     * @param     array      $data    An array of input data.
     * @param     string     $key     The name of the key for the primary key.
     *
     * @return    boolean
     */
    protected function allowEdit($data = array(), $key = 'id')
    {
        // Get form input
        $id = (int) isset($data[$key]) ? $data[$key] : 0;

        $user  = Factory::getApplication()->getIdentity();
        $uid   = $user->get('id');
        $asset = 'com_jpdesigns.album.' . $id;

        // Check if the user has viewing access when not a super admin
        if (!$user->authorise('core.admin')) {
            $db    = Factory::getDbo();
            $query = $db->getQuery(true);

            $query->select('access')
                  ->from('#__jp_design_albums')
                  ->where('id = ' . $id);

            $db->setQuery($query);
            $lvl = $db->loadResult();

            if (!in_array($lvl, $user->getAuthorisedViewLevels())) {
                return false;
            }
        }

        // Check edit permission first
        if ($user->authorise('core.edit', $asset)) {
            return true;
        }

        // Fallback on edit.own.
        // First test if the permission is available.
        if (!$user->authorise('core.edit.own', $asset)) {
            return false;
        }

        // Load the item
        $record = $this->getModel()->getItem($id);

        // Abort if not found
        if (empty($record)) return false;

        // Now test the owner is the user.
        $owner = (int) isset($data['created_by']) ? (int) $data['created_by'] : $record->created_by;

        // If the owner matches 'me' then do the test.
        return ($owner == $uid && $uid > 0);
    }
}
