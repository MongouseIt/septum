<?php
/**
 * @package      Joomproject
 * @subpackage   Dashboard
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die;
use Joomla\CMS\MVC\Controller\AdminController;


class JPusersControllerTeams extends AdminController
{
    protected $text_prefix = 'COM_JOOMPROJECT';

    /**
     * Proxy for getModel.
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  The array of possible config values. Optional.
     *
     * @return  JModelLegacy
     *
     * @since   1.6
     */
    public function getModel($name = 'Team', $prefix = 'JPusersModel', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }




}
