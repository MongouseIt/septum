<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jpusers
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\ItemModel;

jimport('joomla.application.component.modelitem');
jimport('joomproject.application.helper');

/**
 * Methods supporting an user group including rules.
 *
 */
class JPusersModelGroupRules extends ItemModel
{
    /**
     * Model context string.
     *
     * @var    string
     */
    protected $_context = 'com_jpusers.accessmanagergroup';


    /**
     * Method to get item data.
     *
     * @param     integer    The id of the item.
     *
     * @return    mixed      Record object on success, false on failure.
     */
    public function getItem($pk = null)
    {
        // Initialise variables.
        $pk = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');

        if ($this->_item === null) $this->_item = array();

        if (isset($this->_item[$pk])) return $this->_item[$pk];

        $cfg = ComponentHelper::getParams('com_jpprojects');

        $create_group   = (int) $cfg->get('create_group');
        $group_location = (int) $cfg->get('group_location');
        $inherit        = $this->getState($this->getName() . '.inherit');

        if (!$group_location) $group_location = 1;

        try
        {
            $db    = $this->getDbo();
            $query = $db->getQuery(true);

            if (!$inherit && !$pk && $create_group) {
                $item = new stdClass();

                $item->id = 0;
                $item->parent_id = $group_location;
                $item->lft   = 0;
                $item->rgt   = 0;
                $item->title = Text::_('COM_JPUSERS_DEFAULT_GROUP');
            }
            else {
                $query->select('id, parent_id, lft, rgt, title')
                      ->from('#__usergroups')
                      ->where('id = ' . (int) $pk);

                $db->setQuery($query);


                try
                {
                    $item = $db->loadObject();
                }
                catch (RuntimeException $e)
                {
                    throw new Exception($e->getMessage());

                }
            }




            $this->_item[$pk] = (empty($item) ? false : $item);

            if ($this->_item[$pk]) {
                if (!$inherit) {
                    $component = $this->getState($this->getName() . '.component');
                    $section   = $this->getState($this->getName() . '.section');

                    $this->_item[$pk]->actions = array();
                    $this->_item[$pk]->actions[$component] = JPAccessHelper::getActions($component, $section);

                    $components = JPApplicationHelper::getComponents('com_jpreminders');

                    foreach ($components AS $name => $item)
                    {
                        if ($name == $component) continue;

                        $use_asset = JPApplicationHelper::usesProjectAsset($name);
                        $enabled   = JPApplicationHelper::enabled($name);

                        if (!$use_asset || !$enabled) continue;

                        $this->_item[$pk]->actions[$name] = JPAccessHelper::getActions($name, $section);
                    }

                    // Get child group names
                    if ($this->_item[$pk]->lft && $this->_item[$pk]->rgt) {
                        $this->_item[$pk]->children = $this->getChildGroups($this->_item[$pk]->lft, $this->_item[$pk]->rgt);
                    }
                    else {
                        $this->_item[$pk]->children = array();
                    }
                }
                else {
                    $component = $this->getState($this->getName() . '.component');
                    $section   = $this->getState($this->getName() . '.section');

                    $this->_item[$pk]->actions  = JPAccessHelper::getActions($component, $section);

                    // Get child group names
                    if ($this->_item[$pk]->lft && $this->_item[$pk]->rgt) {
                        $this->_item[$pk]->children = $this->getChildGroups($this->_item[$pk]->lft, $this->_item[$pk]->rgt);
                    }
                    else {
                        $this->_item[$pk]->children = array();
                    }
                }
            }
        }
        catch (RuntimeException $e)
        {
            if ($e->getCode() == 404) {
                // Need to go thru the error handler to allow Redirect to work.
                \Joomla\CMS\Factory::getApplication()->enqueueMessage( $e->getMessage(),'error');
            }
            else {
                $this->setError($e);
                $this->_item[$pk] = false;
            }
        }

        return $this->_item[$pk];
    }


    protected function getChildGroups($lft, $rgt)
    {
        $query = $this->_db->getQuery(true);

        $query->select('title')
              ->from('#__usergroups')
              ->where('lft > ' . $lft)
              ->where('rgt < ' . $rgt)
              ->order('lft ASC');

        $this->_db->setQuery($query);
        $result = $this->_db->loadColumn();

        if (!is_array($result)) $result = array();

        return $result;
    }


    /**
     * Method to auto-populate the model state.
     *
     * @return    void
     */
    protected function populateState()
    {
        // Load state from the request.
        $pk = \Joomla\CMS\Factory::getApplication()->input->getInt('id');
        $this->setState($this->getName() . '.id', $pk);

        $inherit = \Joomla\CMS\Factory::getApplication()->input->get('inherit', false);
        $this->setState($this->getName() . '.inherit', (boolean) $inherit);

        $component = \Joomla\CMS\Factory::getApplication()->input->get('component');
        $this->setState($this->getName() . '.component', $component);

        $section = \Joomla\CMS\Factory::getApplication()->input->get('section');
        $this->setState($this->getName() . '.section', $section);

        $asset_id = (int) \Joomla\CMS\Factory::getApplication()->input->get('asset_id');
        $this->setState($this->getName() . '.asset_id', $asset_id);

        $project_id = (int) \Joomla\CMS\Factory::getApplication()->input->get('project_id');
        $this->setState($this->getName() . '.project_id', $project_id);
    }
}
