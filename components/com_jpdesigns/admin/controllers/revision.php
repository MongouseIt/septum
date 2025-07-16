<?php
/**
 * @package      Joomproject Pro
 * @subpackage   Designs
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
use Joomla\CMS\MVC\Controller\FormController;


jimport('joomla.application.component.controllerform');


/**
 * Design Revision form controller class.
 *
 */
class JPdesignsControllerRevision extends FormController
{
    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     */
    protected $text_prefix = "COM_JOOMPROJECT_DESIGN_REVISION";


    /**
     * Class constructor.
     *
     * @param    array    $config    A named array of configuration variables
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
    }


    /**
     * Method to get a model object, loading it if required.
     *
     * @param     string    $name      The model name. Optional.
     * @param     string    $prefix    The class prefix. Optional.
     * @param     array     $config    Configuration array for model. Optional.
     *
     * @return    object               The model.
     */
    public function getModel($name = 'Revision', $prefix = 'JPdesignsModel', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }


    /**
     * Gets the URL arguments to append to an item redirect.
     *
     * @param     integer    $id         The primary key id for the item.
     * @param     string     $url_var    The name of the URL variable for the id.
     *
     * @return    string                 The arguments to append to the redirect URL.
     */
    protected function getRedirectToItemAppend($id = null, $url_var = 'id')
    {
        $tmpl    = \Joomla\CMS\Factory::getApplication()->input->getCmd('tmpl');
        $layout  = \Joomla\CMS\Factory::getApplication()->input->getCmd('layout', 'edit');
        $project = \Joomla\CMS\Factory::getApplication()->input->getUint('filter_project', 0);
        $parent  = \Joomla\CMS\Factory::getApplication()->input->getUint('filter_parent_id', 0);
        $append  = '';

        // Setup redirect info.
        if ($tmpl) {
            $append .= '&tmpl=' . $tmpl;
        }

        if ($layout) {
            $append .= '&layout=' . $layout;
        }

        if ($id) {
            $append .= '&' . $url_var . '=' . $id;
        }

        if ($project) {
            $append .= '&filter_project=' . $project;
        }

        if ($parent) {
            $append .= '&filter_parent_id=' . $parent;
        }

        return $append;
    }


    /**
	 * Gets the URL arguments to append to a list redirect.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 */
	protected function getRedirectToListAppend()
	{
		$tmpl   = \Joomla\CMS\Factory::getApplication()->input->getCmd('tmpl');
        $parent = \Joomla\CMS\Factory::getApplication()->input->getUint('filter_parent_id', 0);
		$append = '';

		// Setup redirect info.
        if ($parent) {
            $append .= '&filter_parent_id=' . $parent;
        }

		if ($tmpl) {
			$append .= '&tmpl=' . $tmpl;
		}

		return $append;
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
        $parent = (isset($data['parent_id']) ? (int) $data['parent_id'] : \Joomla\CMS\Factory::getApplication()->input->getUInt('filter_parent_id'));
        $access = JPdesignsHelper::getActions($parent);

        if (!$parent) {

            \Joomla\CMS\Factory::getApplication()->enqueueMessage(Text::_('COM_JOOMPROJECT_WARNING_DESIGN_NOT_FOUND'),'warning');
            return false;
        }

        return $access->get('core.create');
    }


    /**
     * Method to save a record.
     *
     * @param     string     $key       The name of the primary key of the URL variable.
     * @param     string     $urlVar    The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
     *
     * @return    boolean               True if successful, false otherwise.
     */
    public function save($key = null, $urlVar = null)
    {
        // Check for request forgeries.
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        // Initialise variables.
        $app     = Factory::getApplication();
        $model   = $this->getModel();
        $table   = $model->getTable();
        $data    = \Joomla\CMS\Factory::getApplication()->input->post->get('jform',[],'array');
        $context = $this->option . ".edit." . $this->context;
        $layout  = \Joomla\CMS\Factory::getApplication()->input->get('layout');
        $files   = Factory::getApplication()->input->files->get('jform');

        // Determine the name of the primary key for the data.
		if (empty($key)) $key = $table->getKeyName();

		// To avoid data collisions the urlVar may be different from the primary key.
        if (empty($urlVar)) $urlVar = $key;

        $record_id = \Joomla\CMS\Factory::getApplication()->input->getUInt($urlVar);

        if (!$this->checkEditId($context, $record_id)) {
			// Somehow the person just went to the form and tried to save it. We don't allow that.
            \Joomla\CMS\Factory::getApplication()->enqueueMessage(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $record_id),'error');

			$this->setRedirect(
				Route::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_list
					. $this->getRedirectToListAppend(), false
				)
			);

			return false;
		}

        // Upload the file first
        if (isset($files['file']) && !empty($files['file']['tmp_name'])) {
            $result = $model->upload($files['file'], (isset($data['project_id']) ? $data['project_id'] : JPApplicationHelper::getActiveProjectId()));

            if (is_array($result)) {
                $data['file'] = $result;
            }
            else {

                \Joomla\CMS\Factory::getApplication()->enqueueMessage($model->getError(),'error');

                // Save the data in the session.
			    $app->setUserState($context . '.data', $data);

                $this->setRedirect(
                    Route::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($record_id), false)
                );

                return false;
            }
        }

        if (version_compare(JVERSION, '3.0.0', 'ge')) {
            $this->input->post->set('jform', $data);
        }
        else {
            \Joomla\CMS\Factory::getApplication()->input->set('jform', $data, 'post');
        }

        return parent::save($key, $urlVar);
    }
}
