<?php
/**
 * @package      Joomproject Tasks
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 **/

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Router\Route;


/**
 * Module helper class
 *
 */
abstract class modJPlatestCommentsHelper
{


    /**
     * Method to get a list of tasks
     *
     * @return    array    $items    The tasks
     */
    public static function getItems($params)
    {
        // we will use model later
        JLoader::register('JPcommentsModelComments', JPATH_SITE . '/components/com_jpcomments/models/comments.php');

        $model = BaseDatabaseModel::getInstance('Comments', 'JPcommentsModel', array('ignore_request' => true));

        // Set application parameters in model
        $app = Factory::getApplication();
        $appParams = $app->getParams();
        $model->setState('params', $appParams);

        // Set the filters based on the module params
        $model->setState('list.start', 0);
        $model->setState('list.limit', (int)$params->get('count', 10));
        $model->setState('filter.published', 1);

        // set level
        $model->setState('filter.level', 1);

        // Sort and order
        $model->setState('list.ordering', 'created');
        $model->setState('list.direction', 'DESC');

        $items = $model->getItems();

        // Some extra traitement after getting comments
        foreach ($items as $k => &$item) {


            // build comment item link and title
            switch ($item->context) {

                case 'com_jpprojects.project': // for project

                    $item->itemLink = Route::_(JoomprojectHelperRoute::getDashboardRoute($item->item_id));
                    $item->itemIcon = 'fas fa-briefcase';

                    break;
                case 'com_jpmilestones.milestone': // for milestone

                    // load modal
                    JLoader::register('JPmilestonesModelMilestone', JPATH_SITE . '/components/com_jpmilestones/models/milesone.php');
                    $milestoneModel      = BaseDatabaseModel::getInstance('Milestone', 'JPmilestonesModel', array('ignore_request' => true));

                    $milestoneItem = $milestoneModel->getItem($item->item_id);

                    if(!$milestoneItem){
                        unset($items[$k]);
                        break;
                    }


                    $item->itemLink =  Route::_(JPmilestonesHelperRoute::getMilestoneRoute($milestoneItem->slug, $milestoneItem->project_slug));

                    $item->itemIcon = 'fas fa-clipboard-list';

                    break;
                case 'com_jptasks.task': // for task

                    // load modal
                    JLoader::register('JPtasksModelTask', JPATH_SITE . '/components/com_jptasks/models/task.php');
                    $taskModel      = BaseDatabaseModel::getInstance('Task', 'JPtasksModel', array('ignore_request' => true));

                    $taskItem = $taskModel->getItem($item->item_id);

                    if(!$taskItem){
                        unset($items[$k]);
                        break;
                    }

                    $item->itemLink =  Route::_(JPtasksHelperRoute::getTaskRoute((string)$taskItem->slug, (string)$taskItem->project_slug, (string)$taskItem->milestone_slug, (string)$taskItem->list_slug));

                    $item->itemIcon = 'fas fa-tasks';

                    break;
                case 'com_jprepo.note': // for note

                    // load modal
                    JLoader::register('JPtasksModelTask', JPATH_SITE . '/components/com_jprepo/models/note.php');
                    $noteModel      = BaseDatabaseModel::getInstance('Note', 'JPrepoModel', array('ignore_request' => true));

                    $noteItem = $noteModel->getItem($item->item_id);

                    if(!$noteItem){
                        unset($items[$k]);
                        break;
                    }

                    $item->itemLink =   JPrepoHelperRoute::getNoteRoute($noteItem->slug, $noteItem->project_slug, $noteItem->dir_slug, $noteItem->path);

                    $item->title = Text::_('COM_JOOMPROJECT_NOTE');

                    $item->itemIcon = 'fas fa-file';

                    break;
                case 'com_jpdesigns.design': // for design

                    // load modal
                    JLoader::register('JPdesignsModelDesign', JPATH_SITE . '/components/com_jpdesigns/models/design.php');
                    $designModel      = BaseDatabaseModel::getInstance('Design', 'JPdesignsModel', array('ignore_request' => true));

                    $designItem = $designModel->getItem($item->item_id);

                    if(!$designItem){
                        unset($items[$k]);
                        break;
                    }

                    $item->itemLink =   JPdesignsHelperRoute::getDesignRoute($designItem->slug, $designItem->project_slug, $designItem->album_slug, '0:original');


                    $item->itemIcon = 'fas fa-image';

                    break;
            }


        }


        return $items;

    }
}
