<?php
/**
 * @package      Joomproject
 * @subpackage   Dashboard
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
 * Joomproject main controller
 *
 * @see    JController
 */
class JoomprojectController extends BaseController
{
    /**
     * Constructor
     *
     * @param    array    $config    Optional config options
     */
    function __construct($config = array())
    {
        parent::__construct($config);
    }


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

        HTMLHelper::_('jphtml.script.jquery');
        HTMLHelper::_('jphtml.script.bootstrap');
        HTMLHelper::_('jphtml.script.joomproject');

        HTMLHelper::_('bootstrap.tooltip');

        // Override method arguments
        $urlparams = array('id'               => 'INT',
                           'filter_project'   => 'CMD'
                           );


        // Display the view
        parent::display($cachable, $urlparams);

        // Return own instance for chaining
        return $this;
    }
}