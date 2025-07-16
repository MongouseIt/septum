<?php
/**
 * @package      Joomproject
 * @subpackage   Users
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2006-2012 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */
defined('_JEXEC') or die();

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Controller\BaseController;

jimport('joomla.application.component.controller');


/**
 * Projects main controller
 *
 * @see    JController
 */
class JPusersController extends BaseController
{
    /**
     * Displays the current view
     *
     * @param     boolean    $cachable    If true, the view output will be cached  (Not Used!)
     * @param     array      $urlparams   An array of safe url parameters and their variable types (Not Used!)
     *
     * @return    JController             A JController object to support chaining.
     */
    public function display($cachable = false, $urlparams = false)
    {
        // Load CSS and JS assets
        HTMLHelper::_('jphtml.style.bootstrap');
        HTMLHelper::_('jphtml.style.joomproject');

        HTMLHelper::_('jphtml.script.jQuery');
        HTMLHelper::_('jphtml.script.bootstrap');
        HTMLHelper::_('jphtml.script.joomproject');

        HTMLHelper::_('bootstrap.tooltip');

        // Override method arguments
        $urlparams = array('id'               => 'INT',
                           'cid'              => 'ARRAY',
                           'limit'            => 'INT',
                           'limitstart'       => 'INT',
                           'showall'          => 'INT',
                           'return'           => 'BASE64',
                           'filter'           => 'STRING',
                           'filter_order'     => 'CMD',
                           'filter_order_Dir' => 'CMD',
                           'filter_search'    => 'STRING'
                           );


        // Display the view
        parent::display($cachable, $urlparams);

        // Return own instance for chaining
        return $this;
    }
}