<?php
/**
 * @package      Joomproject
 * @subpackage   Comments
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2006-2012 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Object\CMSObject;
use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;


// Base this model on the backend version.
JLoader::register('JPcommentsModelComment', JPATH_ADMINISTRATOR . '/components/com_jpcomments/models/comment.php');


/**
 * Joomproject Component Comment Form Model
 *
 */
class JPcommentsModelForm extends JPcommentsModelComment
{
    /**
     * Constructor.
     *
     * @param    array          $config    An optional associative array of configuration settings.
     *
     * @see      jcontroller
     */
    public function __construct($config = array())
    {
       // Call parent constructor
       parent::__construct($config);
    }


    /**
     * Method to get item data.
     *
     * @param     integer    $id       The id of the item.
     * @return    mixed      $value    Item data object on success, false on failure.
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

        // Count parent replies
        $value->parent_replies = $this->countReplies($value->parent_id);

        // Count replies of this item
        $value->replies = $this->countReplies($value->id);

        // Compute selected asset permissions.
        $uid    = Factory::getApplication()->getIdentity()->get('id');
        $access = JPcommentsHelper::getActions($value->id);

        // Check general edit permission first.
        if ($access->get('core.edit')) {
            $value->params->set('access-edit', true);
        }
        // Now check if edit.own is available.
        elseif (!empty($uid) && $access->get('core.edit.own')) {
            // Check for a valid user and that they are the owner.
            if ($uid == $value->created_by) {
                $value->params->set('access-edit', true);
            }
        }

        // Check edit state permission.
        if ($id) {
            // Existing item
            $value->params->set('access-change', $access->get('core.edit.state'));
        }
        else {
            // New item
            $access = JPcommentsHelper::getActions();
            $value->params->set('access-change', $access->get('core.edit.state'));
        }

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
        // Load state from the request.
        $pk = \Joomla\CMS\Factory::getApplication()->input->getInt('id');
        $this->setState($this->getName() . '.id', $pk);

        $return = \Joomla\CMS\Factory::getApplication()->input->get('return', null, 'base64');
        $this->setState('return_page', base64_decode($return));

        // Load the parameters.
        $params = Factory::getApplication()->getParams();
        $this->setState('params', $params);

        $this->setState('layout', \Joomla\CMS\Factory::getApplication()->input->getCmd('layout'));
    }
}
