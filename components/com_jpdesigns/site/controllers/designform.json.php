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


JLoader::register('JPdesignsControllerDesign', JPATH_ADMINISTRATOR . '/components/com_jpdesigns/controllers/design.json.php');


/**
 * Design Form JSON Controller
 *
 */
class JPdesignsControllerDesignForm extends JPdesignsControllerDesign
{
    /**
     * Method to check if you can approve a record.
     *
     * @param     array      $id    The design ID
     *
     * @return    boolean
     */
    protected function allowApprove($id = null)
    {
        if (!$id) {
            return false;
        }

        $user   = Factory::getApplication()->getIdentity();
        $db     = Factory::getDbo();
        $query  = $db->getQuery(true);
        $access = true;

        // Check if the user has access to the design
        if (!$user->authorise('core.admin')) {
            if ($project) {
                $query->select('access')
                      ->from('#__jp_designs')
                      ->where('id = ' . $db->quote((int) $id));

                $db->setQuery($query);
                $access = in_array((int) $db->loadResult(), $user->getAuthorisedViewLevels());
            }

            if ($access) {
                $access = $user->authorise('core.approve', 'com_jpdesigns.design.' . (int) $id);
            }
        }

        return $access;
    }


    /**
     * Method to check if you can add a new record.
     *
     * @param     array      $data    An array of input data.
     *
     * @return    boolean
     */
    protected function allowAdd($data = array())
    {
        $user  = Factory::getApplication()->getIdentity();
        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        $access  = true;
        $project = isset($data['project_id']) ? (int) $data['project_id'] : JPApplicationHelper::getActiveProjectId();
        $album   = isset($data['album_id'])   ? (int) $data['album_id'] : 0;
        $asset   = 'com_jpdesigns';

        // Check if the user has access to the album
        if (!$user->authorise('core.admin')) {
            if ($album && $access) {
                $query->clear();
                $query->select('access')
                      ->from('#__jp_design_albums')
                      ->where('id = ' . (int) $album);

                $db->setQuery($query);
                $access = in_array((int) $db->loadResult(), $levels);
            }
        }

        // Check if the user has access to the project
        if (!$user->authorise('core.admin')) {
            if ($project && $access) {
                $query->select('access')
                      ->from('#__jp_projects')
                      ->where('id = ' . (int) $project);

                $db->setQuery($query);
                $access = in_array((int) $db->loadResult(), $user->getAuthorisedViewLevels());

                // Change the asset name
                if (version_compare(JPVERSION, '4.2', 'ge')) {
                    $asset  .= '.project.' . $project;
                }
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
        // Initialise variables.
        $id     = (int) isset($data[$key]) ? $data[$key] : 0;
        $uid    = Factory::getApplication()->getIdentity()->get('id');
        $access = JPdesignsHelper::getActions($id);

        // Check general edit permission first.
        if ($access->get('core.edit')) {
            return true;
        }

        // Fallback on edit.own.
        // First test if the permission is available.
        if ($access->get('core.edit.own')) {
            // Now test the owner is the user.
            $owner = (int) isset($data['created_by']) ? $data['created_by'] : 0;

            if (empty($owner) && $id) {
                // Need to do a lookup from the model.
                $record = $this->getModel()->getItem($id);

                if (empty($record)) return false;

                $owner = $record->created_by;
            }

            // If the owner matches 'me' then do the test.
            if ($owner == $uid) return true;
        }

        // Since there is no asset tracking, revert to the component permissions.
        return parent::allowEdit($data, $key);
    }
}
