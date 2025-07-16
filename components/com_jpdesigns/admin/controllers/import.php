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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\Utilities\ArrayHelper;

jimport('joomla.application.component.controlleradmin');


/**
 * Design import list controller class.
 *
 */
class JPdesignsControllerImport extends AdminController
{
    /**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 */
    protected $text_prefix = "COM_JOOMPROJECT_DESIGNS_IMPORT";


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
    public function getModel($name = 'Import', $prefix = 'JPdesignsModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }


    public function import()
    {
        // Check for request forgeries
		Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

		// Get items to import from the request.
		$cid  = \Joomla\CMS\Factory::getApplication()->input->get('cid', array(), '', 'array');
		$data = \Joomla\CMS\Factory::getApplication()->input->get('import', array(), '', 'array');

		if (empty($cid)) {
			\Joomla\CMS\Factory::getApplication()->enqueueMessage( Text::_($this->text_prefix . '_NO_ITEM_SELECTED'),'warning');
		}
		else {
			// Get the model.
			$model = $this->getModel();

			// Make sure the item ids are integers
			\Joomla\Utilities\ArrayHelper::toInteger($cid);

			// Import the items.
			if (!$model->import($cid, $data)) {
				\Joomla\CMS\Factory::getApplication()->enqueueMessage( $model->getError(),'warning');
			}
			else {
                $ntext = $this->text_prefix . '_N_ITEMS_IMPORTED';

				$this->setMessage(Text::plural($ntext, count($cid)));
			}
		}

		$extension    = \Joomla\CMS\Factory::getApplication()->input->getCmd('extension');
		$extensionURL = ($extension) ? '&extension=' . \Joomla\CMS\Factory::getApplication()->input->getCmd('extension') : '';

		$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $extensionURL, false));
    }
}
