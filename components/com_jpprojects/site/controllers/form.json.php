<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jpprojects
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;


JLoader::register('JPprojectsControllerProject', JPATH_ADMINISTRATOR . '/components/com_jpprojects/controllers/project.json.php');


/**
 * Joomproject Project Form Controller
 *
 */
class JPprojectsControllerForm extends JPprojectsControllerProject
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
        return Factory::getApplication()->getIdentity()->authorise('core.create', 'com_jpprojects');
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
        $asset = 'com_jpprojects.project.' . $id;

        // Check if the user has viewing access when not a super admin
        if (!$user->authorise('core.admin')) {
            $db    = Factory::getDbo();
            $query = $db->getQuery(true);

            $query->select('access')
                  ->from('#__jp_projects')
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

        // Fall back on edit.own.
        // First test if the permission is available.
        if (!$user->authorise('core.edit.own', $asset)) {
            return false;
        }

        // Now test the owner is the user.
        $owner = (int) isset($data['created_by']) ? (int) $data['created_by'] : 0;

        if (!$owner && $id) {
            // Need to do a lookup from the model.
            $record = $this->getModel()->getItem($id);

            if (empty($record)) return false;

            $owner = $record->created_by;
        }

        // If the owner matches 'me' then do the test.
        return ($owner == $uid && $uid > 0);
    }
}
