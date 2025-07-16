<?php
/**
 * @package      Joomproject
 * @subpackage   Users
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2006-2012 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\Registry\Registry;
use Joomla\CMS\Factory;


// Base this on the backend users model

use \Joomla\Component\Users\Administrator\Model\UsersModel;
/**
 * This models supports retrieving lists of users.
 * Extends on the backend version of com_users
 *
 */
class JPusersModelUsers extends UsersModel
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
        parent::__construct($config);
    }


    /**
     * Method to auto-populate the model state.
     * Note. Calling getState in this method will result in recursion.
     *
     * @return    void
     */
    protected function populateState($ordering = null, $direction = null)
    {
        parent::populateState();

        $app    = Factory::getApplication();
        $user   = Factory::getApplication()->getIdentity();
        $model  = $this->getInstance('Form', 'JPprojectsModel', array('ignore_request' => true));
        $groups = array();

        $params  = $app->getParams();
        $itemid  = $app->input->get('Itemid', 0, 'int');
        $menu    = $app->getMenu()->getActive();

        // Merge app params with menu item params
        if ($menu) {
            $menu_params = new Registry();

            $menu_params->loadString((string)$menu->getParams());
            $clone_params = clone $menu_params;
            $clone_params->merge($params);

            if (!$itemid) {
                $itemid = (int) $menu->id;
            }
        }

        // Filter - State
        $this->setState('filter.state', 0);

        // Filter - Project
        $pid = JPApplicationHelper::getActiveProjectId('filter_project');
        $this->setState('filter.project', $pid);




        // Override group filter by active project
        if ($pid) {
            $tmp_groups = $model->getUserGroups($pid);

            // Get group ids
            if (is_array($tmp_groups)) {
                foreach($tmp_groups AS $group)
                {
                    $groups[] = (int) $group;
                }
            }
        }
        else {
            // No active project. Filter by all accessible projects

            // get list params
            $defaultListType = $params->get('default_list_type',0);

            if (!$user->authorise('core.admin') && $defaultListType == 0) { // accessible by all projects
                $umodel   = $this->getInstance('User', 'JPusersModel');
                $projects = $umodel->getProjects();

                foreach($projects AS $project)
                {
                    $tmp_groups = $model->getUserGroups($project);

                    if ($tmp_groups !== false) {
                        // Get group ids
                        if (is_array($tmp_groups)) {
                            foreach($tmp_groups AS $group)
                            {
                                $groups[] = (int) $group;
                            }
                        }
                    }
                }
            }elseif($defaultListType == 1){ // selected groups

                $groups = (array) $params->get('include_selected_groups','');

            }

        }

        if (count($groups)) {
            $this->setState('filter.groups', $groups);
        }
        else {
            if (!$user->authorise('core.admin')) {
                $this->setState('filter.groups', array('1'));
            }
        }
    }
}
