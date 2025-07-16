<?php
/**
 * @package      Joomproject
 * @subpackage   Repository
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2006-2012 JoomBoost. All rights reserved.
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
     * Constructor.
     *
     * @param    array          $config    An optional associative array of configuration settings
     * @see      jcontroller
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
    }


    /**
     * Proxy for getModel.
     *
     * @param     string    $name      The name of the model.
     * @param     string    $prefix    The prefix for the PHP class name.
     * @return    jmodel
     */
    public function getModel($name = 'Repository', $prefix = 'JPrepoModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
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

        $parent_id = (int) Factory::getApplication()->input->getUInt('filter_parent_id', 0);

        // Get items to remove from the request.
        $did = (array) Factory::getApplication()->input->get('did', array(), '', 'array');
        $nid = (array) Factory::getApplication()->input->get('nid', array(), '', 'array');
        $fid = (array) Factory::getApplication()->input->get('fid', array(), '', 'array');


        if ((!is_array($did) && !is_array($nid) && !is_array($fid)) || (count($did) < 1 && count($nid) < 1 && count($fid) < 1)) {
            Factory::getApplication()->enqueueMessage( Text::_($this->text_prefix . '_NO_ITEM_SELECTED'),'warning');
        }
        else {
            jimport('joomla.utilities.arrayhelper');
            $app = Factory::getApplication();

            // Delete directories
            if (is_array($did) && count($did) > 0) {
                $model = $this->getModel('DirectoryForm');

                ArrayHelper::toInteger($did);

                if ($model->delete($did)) {
                    $app->enqueueMessage(Text::plural('COM_JOOMPROJECT_DIRECTORIES_N_ITEMS_DELETED', count($did)));
                }
                else {
                    $app->enqueueMessage($model->getError(), 'error');
                }
            }

            // Delete notes
            if (is_array($nid) && count($nid) > 0) {
                $model = $this->getModel('NoteForm');

                ArrayHelper::toInteger($nid);

                if ($model->delete($nid)) {
                    $app->enqueueMessage(Text::plural('COM_JOOMPROJECT_NOTES_N_ITEMS_DELETED', count($nid)));
                }
                else {
                    $app->enqueueMessage($model->getError(), 'error');
                }
            }

            // Delete files
            if (is_array($fid) && count($fid) > 0) {
                $model = $this->getModel('FileForm');

                ArrayHelper::toInteger($fid);

                if ($model->delete($fid)) {
                    $app->enqueueMessage(Text::plural('COM_JOOMPROJECT_FILES_N_ITEMS_DELETED', count($fid)));
                }
                else {
                    $app->enqueueMessage($model->getError(), 'error');
                }
            }
        }

        $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list . ($parent_id > 1 ? '&filter_parent_id=' . $parent_id : ''), false));
    }


    /**
	 * Check in of one or more records.
	 *
	 * @return  boolean  True on success
	 */
	public function checkin()
	{
		// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$parent_id = (int) Factory::getApplication()->input->getUInt('filter_parent_id', 0);

        // Get items to remove from the request.
        $did = (array) Factory::getApplication()->input->get('did', array(), '', 'array');
        $nid = (array) Factory::getApplication()->input->get('nid', array(), '', 'array');
        $fid = (array) Factory::getApplication()->input->get('fid', array(), '', 'array');

        if ((!is_array($did) && !is_array($nid) && !is_array($fid)) || (count($did) < 1 && count($nid) < 1 && count($fid) < 1)) {
            Factory::getApplication()->enqueueMessage( Text::_($this->text_prefix . '_NO_ITEM_SELECTED'),'warning');
        }
        else {
            jimport('joomla.utilities.arrayhelper');
            $app = Factory::getApplication();

            // Check-in directories
            if (is_array($did) && count($did) > 0) {
                $model = $this->getModel('DirectoryForm');

                ArrayHelper::toInteger($did);

                if ($model->checkin($did) === false) {
                    $app->enqueueMessage($model->getError(), 'error');
                }
            }

            // Check-in notes
            if (is_array($nid) && count($nid) > 0) {
                $model = $this->getModel('NoteForm');

                ArrayHelper::toInteger($nid);

                if ($model->checkin($nid) === false) {
                    $app->enqueueMessage($model->getError(), 'error');
                }
            }

            // Check-in files
            if (is_array($fid) && count($fid) > 0) {
                $model = $this->getModel('FileForm');

                ArrayHelper::toInteger($fid);

                if ($model->checkin($fid) === false) {
                    $app->enqueueMessage($model->getError(), 'error');
                }
            }
        }

        $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list . ($parent_id > 1 ? '&filter_parent_id=' . $parent_id : ''), false));
	}


    /**
     * Method to run batch operations.
     *
     * @return    void
     */
    public function batch()
    {
        // Check for request forgeries
        Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

        $parent_id = (int) Factory::getApplication()->input->getUInt('filter_parent_id', 0);

        $vars = (array) Factory::getApplication()->input->get('batch', array(), '', 'array');
        $did  = (array) Factory::getApplication()->input->get('did', array(), '', 'array');
        $nid  = (array) Factory::getApplication()->input->get('nid', array(), '', 'array');
        $fid  = (array) Factory::getApplication()->input->get('fid', array(), '', 'array');

        if ((!is_array($did) && !is_array($nid) && !is_array($fid)) || (count($did) < 1 && count($nid) < 1 && count($fid) < 1)) {
            Factory::getApplication()->enqueueMessage( Text::_($this->text_prefix . '_NO_ITEM_SELECTED'),'warning');
        }
        else {
            jimport('joomla.utilities.arrayhelper');
            $app = Factory::getApplication();

            // Batch directories
            if (is_array($did) && count($did) > 0) {
                $model = $this->getModel('DirectoryForm');

               ArrayHelper::toInteger($did);

                if ($model->batch($vars, $did)) {
                    $app->enqueueMessage(Text::_('COM_JOOMPROJECT_SUCCESS_BATCH_DIRECTORIES'));
                }
                else {
                    $app->enqueueMessage($model->getError(), 'error');
                }
            }

            // Batch notes
            if (is_array($nid) && count($nid) > 0) {
                $model = $this->getModel('NoteForm');

                ArrayHelper::toInteger($nid);

                if ($model->batch($vars, $nid)) {
                    $app->enqueueMessage(Text::_('COM_JOOMPROJECT_SUCCESS_BATCH_NOTES'));
                }
                else {
                    $app->enqueueMessage($model->getError(), 'error');
                }
            }

            // Batch files
            if (is_array($fid) && count($fid) > 0) {
                $model = $this->getModel('FileForm');

               ArrayHelper::toInteger($fid);

                if ($model->batch($vars, $fid)) {
                    $app->enqueueMessage(Text::_('COM_JOOMPROJECT_SUCCESS_BATCH_FILES'));
                }
                else {
                    $app->enqueueMessage($model->getError(), 'error');
                }
            }
        }

        $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list . ($parent_id > 1 ? '&filter_parent_id=' . $parent_id : ''), false));
    }
}
