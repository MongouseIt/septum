<?php
/**
 * @package      Joomproject
 * @subpackage   Timetracking
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\Controller\AdminController;


jimport('joomla.application.component.controlleradmin');


/**
 * Joomproject Time Recorder List Controller
 *
 */
class JPtimeControllerRecorder extends AdminController
{
    /**
     * The default view
     *
     * @var    string
     */
    protected $view_list = 'recorder';

    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     */
    protected $text_prefix = 'COM_JOOMPROJECT_TIME_REC';


    /**
     * Method to get a model object, loading it if required.
     *
     * @param     string    $name      The model name. Optional.
     * @param     string    $prefix    The class prefix. Optional.
     * @param     array     $config    Configuration array for model. Optional.
     *
     * @return    object               The model.
     */
    public function &getModel($name = 'Recorder', $prefix = 'JPtimeModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }


    /**
     * Method to add one or more tasks to the recorder
     *
     * @return    boolean    True on success, False on error
     */
    public function add()
    {
        $user  = Factory::getApplication()->getIdentity();
		$input = Factory::getApplication()->input;
		$cid   = $input->get('cid', array(), 'array');





        // Check general access
        if (!$user->authorise('core.create', $this->option)) {
            Factory::getApplication()->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'),'error');

            $this->setRedirect(Uri::root(). "index.php?option=com_jptime&view=recorder&tmpl=component");
            return false;
        }

        // Check if empty list
        if (empty($cid)) {
			Factory::getApplication()->enqueueMessage( Text::_($this->text_prefix . '_NO_ITEM_SELECTED'),'warning');
            $this->setRedirect(Uri::root(). "index.php?option=com_jptime&view=recorder&tmpl=component");
            return false;
		}

        $model = $this->getModel();


        // Add the items to the recorder
		if (!$model->addItems($cid)) {

			Factory::getApplication()->enqueueMessage( $model->getError(),'warning');
            $this->setRedirect(Uri::root(). "index.php?option=com_jptime&view=recorder&tmpl=component");
            return false;
		}

        $this->setRedirect(Uri::root(). "index.php?option=com_jptime&view=recorder&tmpl=component");
        return true;
    }
}
