<?php
/**
 * @package      com_joomproject
 * @subpackage   com_jprepo
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\Utilities\ArrayHelper;
jimport('joomla.application.component.controlleradmin');


/**
 * Joomproject Repo List Controller
 *
 */
class JPrepoControllerRepository extends JPControllerAdminJson
{
    /**
     * The default view
     *
     */
    protected $view_list = 'repository';


    /**
     * Method to get a model object, loading it if required.
     *
     * @param     string    $name      The model name. Optional.
     * @param     string    $prefix    The class prefix. Optional.
     * @param     array     $config    Configuration array for model. Optional.
     *
     * @return    object               The model.
     */
    public function &getModel($name = 'DirectoryForm', $prefix = 'JPrepoModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }


    public function watch()
    {
        $rdata = array();
        $rdata['success']  = "true";
        $rdata['messages'] = array();
        $rdata['data']     = array();

        // Check for request forgeries
        if (!Session::checkToken()) {
            $rdata['success']    = "false";
            $rdata['messages'][] = Text::_('JINVALID_TOKEN');

            $this->sendResponse($rdata);
        }

        // Make sure the user is logged in
        if (Factory::getApplication()->getIdentity()->get('id') == 0) {
            $rdata['success']    = "false";
            $rdata['messages'][] = Text::_('JERROR_ALERTNOAUTHOR');

            $this->sendResponse($rdata);
        }

        $cid   = \Joomla\CMS\Factory::getApplication()->input->get('did', array(), '', 'array');
        $task  = $this->getTask();
        $data  = array('watch' => 1, 'unwatch' => 0);
        $value = ArrayHelper::getValue($data, $task, 0, 'int');

        if (empty($cid)) {
            $rdata['success']    = "false";
            $rdata['messages'][] = Text::_($this->text_prefix . '_NO_ITEM_SELECTED');
        }
        else {
            // Get the model.
            $model = $this->getModel();

            // Make sure the item ids are integers
            ArrayHelper::toInteger($cid);

            // Publish the items.
            if (!$model->watch($cid, $value)) {
                 $rdata['success']    = "false";
                 $rdata['messages'][] = $model->getError();
            }
            else {
                if ($value == 1) {
                    $ntext = $this->text_prefix . '_N_ITEMS_WATCHED';
                }
                else {
                    $ntext = $this->text_prefix . '_N_ITEMS_UNWATCHED';
                }

                $rdata['success']    = "true";
                $rdata['messages'][] = Text::plural($ntext, count($cid));
            }
        }

        $this->sendResponse($rdata);
    }
}
