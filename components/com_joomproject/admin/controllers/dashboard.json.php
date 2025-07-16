<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_joomproject
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\AdminController;


jimport('joomla.application.component.controlleradmin');


/**
 * Joomproject JSON Controller
 *
 */
class JoomprojectControllerDashboard extends AdminController
{
    /**
     * Proxy for getModel.
     *
     * @param     string    $name      The name of the model.
     * @param     string    $prefix    The prefix for the class name.
     * @param     array     $config    Configuration array for model. Optional.
     *
     * @return    object
     */
    public function getModel($name = 'Dashboard', $prefix = 'JoomprojectModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }


    /**
     * Returns the total amount of projects
     *
     * @return    void
     */
    public function countProjects()
    {
        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select('COUNT(*)')
              ->from('#__jp_projects');

        $db->setQuery($query);
        $count = (int) $db->loadResult();

        $rsp = array('total' => $count);

        // Set the MIME type for JSON output.
        Factory::getDocument()->setMimeEncoding('application/json');

        // Change the suggested filename.
        \Joomla\CMS\Factory::getApplication()->setHeader('Content-Disposition', 'attachment;filename="dashboard.json"');
        // Output the JSON data.
        echo json_encode($rsp);

        jexit();
    }


    public function checkAssets()
    {
        $limitstart = Factory::getApplication()->input->getUInt('chk_assets_limitstart');
        $model      = $this->getModel('CheckAsset');

        $model->setState('limitstart', $limitstart);

        $rsp = array('success' => $model->check());

        // Set the MIME type for JSON output.
        Factory::getDocument()->setMimeEncoding('application/json');

        // Change the suggested filename.
        Factory::getApplication()->setHeader('Content-Disposition', 'attachment;filename="dashboard.json"');
        // Output the JSON data.
        echo json_encode($rsp);

        jexit();
    }
}
