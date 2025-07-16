<?php
/**
 * @copyright	Copyright (c) 2013-2018 JoomBoost (https://www.joomboost.com). All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;

JLoader::register('JoomprojectHelperMaintenance', JPATH_ADMINISTRATOR . '/components/com_joomproject/helpers/maintenance.php');

/**
 * Maintenance Controller Class.
 *
 * @package		Joomla.Administrator
 * @subpakage	JoomBoost.JoomProject
 */
class JoomprojectControllerMaintenance extends AdminController {

    public function addMenu(){

        JoomprojectHelperMaintenance::addMenu();

    }

    public function executeSqlUpdateFile(){
        JoomprojectHelperMaintenance::executeSqlUpdateFile();
    }

}