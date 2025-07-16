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

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\Controller\AdminController;


jimport('joomla.application.component.controlleradmin');


/**
 * Design Revisions List Controller
 *
 */
class JPdesignsControllerRevisions extends AdminController
{
    /**
     * The default list view
     *
     * @var    string
     */
    protected $view_list = 'design';

    /**
     * The default item view
     *
     * @var    string
     */
    protected $view_item = 'design';

    /**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 */
    protected $text_prefix = "COM_JOOMPROJECT_DESIGNS_REVISION";


    /**
     * Method to get a model object, loading it if required.
     *
     * @param     string    $name      The model name. Optional.
     * @param     string    $prefix    The class prefix. Optional.
     * @param     array     $config    Configuration array for model. Optional.
     *
     * @return    object               The model.
     */
    public function &getModel($name = 'RevisionForm', $prefix = 'JPdesignsModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }


    public function checkin()
    {
        parent::checkin();

        if (!$this->getError()) {
            $app  = Factory::getApplication();
            $link = 'index.php?option=' . $this->option . '&view=' . $this->view_list
                  . '&filter_project=' . (int) $app->input->get('filter_project')
                  . '&filter_album=' . (int) $app->input->get('filter_album')
                  . '&id=' . (int) $app->input->get('id')
                  . '&revision=' . (int) $app->input->get('revision');

            $this->setRedirect(Route::_($link, false));
            return true;
        }

        return false;
    }

    public function delete()
    {
        parent::delete();

        if (!$this->getError()) {
            $app  = Factory::getApplication();
            $link = 'index.php?option=' . $this->option . '&view=' . $this->view_list
                  . '&filter_project=' . (int) $app->input->get('filter_project')
                  . '&filter_album=' . (int) $app->input->get('filter_album')
                  . '&id=' . (int) $app->input->get('id')
                  . '&revision=' . (int) $app->input->get('revision');

            $this->setRedirect(Route::_($link, false));
            return true;
        }

        return false;
    }


    public function publish()
    {
        parent::publish();

        if (!$this->getError()) {
            $app  = Factory::getApplication();
            $link = 'index.php?option=' . $this->option . '&view=' . $this->view_list
                  . '&filter_project=' . (int) $app->input->get('filter_project')
                  . '&filter_album=' . (int) $app->input->get('filter_album')
                  . '&id=' . (int) $app->input->get('id')
                  . '&revision=' . (int) $app->input->get('revision');

            $this->setRedirect(Route::_($link, false));
            return true;
        }

        return false;
    }
}
