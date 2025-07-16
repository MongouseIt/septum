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

use Joomla\CMS\Object\CMSObject;
use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;


JLoader::register('JPdesignsModelAlbum', JPATH_ADMINISTRATOR . '/components/com_jpdesigns/models/album.php');


/**
 * Design Album Form Model
 *
 */
class JPdesignsModelAlbumForm extends JPdesignsModelAlbum
{
    /**
     * Method to get item data.
     *
     * @param     integer    $id    The id of the item.
     * @return    mixed             Item data object on success, false on failure.
     */
    public function getItem($id = null)
    {
        // Initialise variables.
        $id = (int) (!empty($id)) ? $id : $this->getState($this->getName() . '.id');

        // Get a row instance.
        $table = $this->getTable();

        // Attempt to load the row.
        $return = $table->load($id);

        // Check for a table object error.
        if ($return === false && $table->getError()) {
            $this->setError($table->getError());
            return false;
        }

        $properties = $table->getProperties(1);
        $value = ArrayHelper::toObject($properties, CMSObject::class);

        // Convert attrib field to Registry.
        $value->params = new Registry;
        $value->params->loadString((string)$value->attribs);
        $value->attribs = $value->params->toArray();

        // Compute selected asset permissions.
        $uid = Factory::getApplication()->getIdentity()->get('id');

        if ($id) {
            $access = JPdesignsHelper::getAlbumActions($value->id);
        }
        else {
            $access = JPdesignsHelper::getAlbumActions();
        }


        // Check general edit permission first.
        if ($access->get('core.edit')) {
            $value->params->set('access-edit', true);
        }
        elseif (!empty($uid) &&  $access->get('core.edit.own')) {
            // Now check if edit.own is available.
            // Check for a valid user and that they are the owner.
            if ($uid == $value->created_by) {
                $value->params->set('access-edit', true);
            }
        }

        // Check edit state permission.
        $value->params->set('access-change', $access->get('core.edit.state'));

        return $value;
    }


    /**
     * Get the return URL.
     *
     * @return    string    The return URL.
     */
    public function getReturnPage()
    {
        return base64_encode($this->getState('return_page'));
    }


    /**
     * Method to auto-populate the model state.
     * Note. Calling getState in this method will result in recursion.
     *
     * @return    void
     */
    protected function populateState()
    {
        $app = Factory::getApplication();

        // Load state from the request.
        $pk = Factory::getApplication()->input->getInt('id');
        $this->setState($this->getName() . '.id', $pk);

        $return = Factory::getApplication()->input->get('return', null, 'base64');
        $this->setState('return_page', base64_decode($return));

        // Load the parameters.
        $params = $app->getParams();
        $this->setState('params', $params);

        $this->setState('layout', Factory::getApplication()->input->getCmd('layout'));
    }
}
