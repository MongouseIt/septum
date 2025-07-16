<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jprepo
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\Utilities\ArrayHelper;

jimport('joomla.application.component.controlleradmin');


/**
 * Repository controller class.
 *
 */
class JPrepoControllerRepository extends AdminController
{
    /**
     * Proxy for getModel.
     *
     * @param     string    $name      The name of the model.
     * @param     string    $prefix    The prefix for the PHP class name.
     *
     * @return    jmodel
     */
    public function getModel($name = 'Repository', $prefix = 'JPrepoModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }


    /**
     * Check in of one or more records.
     *
     * @return    boolean    True on success
     */
    public function checkin()
    {
        // Check for request forgeries.
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        $parent_id = (int) \Joomla\CMS\Factory::getApplication()->input->getUInt('filter_parent_id', 0);

        $link = 'index.php?option=' . $this->option . '&view=' . $this->view_list
              . ($parent_id > 1 ? '&filter_parent_id=' . $parent_id : '');

        // Get items to check in from the request.
        $did = \Joomla\CMS\Factory::getApplication()->input->post->get('did',[],'array');
        $nid = \Joomla\CMS\Factory::getApplication()->input->post->get('nid',[],'array');
        $fid = \Joomla\CMS\Factory::getApplication()->input->post->get('fid',[],'array');

        // Check-in directories
        if (count($did)) {
            $model = $this->getModel('Directory');

            if (!$model->checkin($did)) {
                $message = Text::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError());
                $this->setRedirect(Route::_($link, false), $message, 'error');
                return false;
            }
        }

        // Check-in notes
        if (count($nid)) {
            $model = $this->getModel('Note');

            if (!$model->checkin($nid)) {
                $message = Text::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError());
                $this->setRedirect(Route::_($link, false), $message, 'error');
                return false;
            }
        }

        // Check-in files
        if (count($fid)) {
            $model = $this->getModel('File');

            if (!$model->checkin($fid)) {
                $message = Text::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError());
                $this->setRedirect(Route::_($link, false), $message, 'error');
                return false;
            }
        }

        $message = Text::plural($this->text_prefix . '_N_ITEMS_CHECKED_IN', (count($did) + count($nid) + count($fid)));
        $this->setRedirect(Route::_($link, false), $message);

        return true;
    }


    /**
     * Removes an item.
     *
     * @return    void
     */
    public function delete()
    {
        // Check for request forgeries
        Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

        $parent_id = (int) \Joomla\CMS\Factory::getApplication()->input->getUInt('filter_parent_id', 0);

        // Get items to remove from the request.
        $did = \Joomla\CMS\Factory::getApplication()->input->post->get('did',[],'array');
        $nid = \Joomla\CMS\Factory::getApplication()->input->post->get('nid',[],'array');
        $fid = \Joomla\CMS\Factory::getApplication()->input->post->get('fid',[],'array');

        if ((count($did) < 1 && count($nid) < 1 && count($fid) < 1)) {
            \Joomla\CMS\Factory::getApplication()->enqueueMessage( Text::_($this->text_prefix . '_NO_ITEM_SELECTED'),'warning');
        }
        else {
            jimport('joomla.utilities.arrayhelper');
            $app = Factory::getApplication();

            // Delete directories
            if (count($did)) {
                $model = $this->getModel('Directory');

                \Joomla\Utilities\ArrayHelper::toInteger($did);

                if ($model->delete($did)) {
                    $app->enqueueMessage(Text::plural('COM_JOOMPROJECT_DIRECTORIES_N_ITEMS_DELETED', count($did)));
                }
                else {
                    $app->enqueueMessage($model->getError(), 'error');
                }
            }

            // Delete notes
            if (count($nid)) {
                $model = $this->getModel('Note');

                \Joomla\Utilities\ArrayHelper::toInteger($nid);

                if ($model->delete($nid)) {
                    $app->enqueueMessage(Text::plural('COM_JOOMPROJECT_NOTES_N_ITEMS_DELETED', count($nid)));
                }
                else {
                    $app->enqueueMessage($model->getError(), 'error');
                }
            }

            // Delete files
            if (count($fid)) {
                $model = $this->getModel('File');

                \Joomla\Utilities\ArrayHelper::toInteger($fid);

                if ($model->delete($fid)) {
                    $app->enqueueMessage(Text::plural('COM_JOOMPROJECT_FILES_N_ITEMS_DELETED', count($fid)));
                }
                else {
                    $app->enqueueMessage($model->getError(), 'error');
                }
            }
        }

        $link = 'index.php?option=' . $this->option . '&view=' . $this->view_list
              . ($parent_id > 1 ? '&filter_parent_id=' . $parent_id : '');

        $this->setRedirect(Route::_($link, false));
    }


    /**
     * Method to run batch operations.
     *
     * @param     object     $model    The model of the component being processed.
     *
     * @return    boolean              True if successful, false otherwise and internal error is set.
     */
    public function batch($model = null)
    {
        // Check for request forgeries
        Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

        $parent_id = (int) \Joomla\CMS\Factory::getApplication()->input->getUInt('filter_parent_id', 0);
        $return    = true;

        $vars = \Joomla\CMS\Factory::getApplication()->input->post->get('batch',[],'array');
        $did  = \Joomla\CMS\Factory::getApplication()->input->post->get('did',[],'array');
        $nid  = \Joomla\CMS\Factory::getApplication()->input->post->get('nid',[],'array');
        $fid  = \Joomla\CMS\Factory::getApplication()->input->post->get('fid',[],'array');

        if (count($did) < 1 && count($nid) < 1 && count($fid) < 1) {
            \Joomla\CMS\Factory::getApplication()->enqueueMessage( Text::_($this->text_prefix . '_NO_ITEM_SELECTED'),'warning');
            $return = false;
        }
        else {
            jimport('joomla.utilities.arrayhelper');
            $app = Factory::getApplication();

            // Batch directories
            if (count($did) > 0) {
                $model    = $this->getModel('Directory');
                $contexts = array();

                \Joomla\Utilities\ArrayHelper::toInteger($did);

                // Build an array of item contexts to check
                foreach ($did as $id)
                {
                    $contexts[$id] = $this->option . '.directory.' . $id;
                }

                // Process
                if ($model->batch($vars, $did, $contexts)) {
                    $app->enqueueMessage(Text::_('COM_JOOMPROJECT_SUCCESS_BATCH_DIRECTORIES'));
                }
                else {
                    $app->enqueueMessage($model->getError(), 'error');
                    $return = false;
                }
            }

            // Batch notes
            if (count($nid) > 0) {
                $model    = $this->getModel('Note');
                $contexts = array();

                \Joomla\Utilities\ArrayHelper::toInteger($nid);

                // Build an array of item contexts to check
                foreach ($nid as $id)
                {
                    $contexts[$id] = $this->option . '.note.' . $id;
                }

                // Process
                if ($model->batch($vars, $nid, $contexts)) {
                    $app->enqueueMessage(Text::_('COM_JOOMPROJECT_SUCCESS_BATCH_NOTES'));
                }
                else {
                    $app->enqueueMessage($model->getError(), 'error');
                    $return = false;
                }
            }

            // Batch files
            if (count($fid) > 0) {
                $model    = $this->getModel('File');
                $contexts = array();

                \Joomla\Utilities\ArrayHelper::toInteger($fid);

                // Build an array of item contexts to check
                foreach ($fid as $id)
                {
                    $contexts[$id] = $this->option . '.file.' . $id;
                }

                // Process
                if ($model->batch($vars, $fid, $contexts)) {
                    $app->enqueueMessage(Text::_('COM_JOOMPROJECT_SUCCESS_BATCH_FILES'));
                }
                else {
                    $app->enqueueMessage($model->getError(), 'error');
                    $return = false;
                }
            }
        }

        $link = 'index.php?option=' . $this->option . '&view=' . $this->view_list
              . ($parent_id > 1 ? '&filter_parent_id=' . $parent_id : '');

        $this->setRedirect(Route::_($link, false));

        return $return;
    }
}
