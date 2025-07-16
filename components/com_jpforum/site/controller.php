<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jpforum
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Controller\BaseController;


jimport('joomla.application.component.controller');


/**
 * Component main controller
 *
 * @see    JController
 */
class JPforumController extends BaseController
{
    /**
     * The default view
     *
     * @var    string
     */
    protected $default_view = 'topics';


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

        $view      = \Joomla\CMS\Factory::getApplication()->input->getCmd('view');
        $id        = \Joomla\CMS\Factory::getApplication()->input->getUInt('id');
        $urlparams = array(
            'id'               => 'INT',
            'cid'              => 'ARRAY',
            'limit'            => 'INT',
            'limitstart'       => 'INT',
            'showall'          => 'INT',
            'return'           => 'BASE64',
            'filter'           => 'STRING',
            'filter_order'     => 'CMD',
            'filter_order_Dir' => 'CMD',
            'filter_search'    => 'STRING',
            'filter_published' => 'CMD',
            'filter_project'   => 'INT',
            'filter_topic'     => 'INT'
        );

        // Inject default view if not set
        if (empty($view)) {
            \Joomla\CMS\Factory::getApplication()->input->set('view', $this->default_view);
        }

        // todo: disabled temporary, note sure why needed
        // Check for topic edit form.
        /*if ($view == 'topicform' && !$this->checkEditId('com_jpforum.edit.topicform', $id)) {
            // Somehow the person just went to the form - we don't allow that.
            return \Joomla\CMS\Factory::getApplication()->enqueueMessage( $id,'error');
        }

        // Check for reply edit form.
        if ($view == 'replyform' && !$this->checkEditId('com_jpforum.edit.replyform', $id)) {
            // Somehow the person just went to the form - we don't allow that.
            return \Joomla\CMS\Factory::getApplication()->enqueueMessage( $id,'error');
        }*/

        // Display the view
        parent::display($cachable, $urlparams);

        // Return own instance for chaining
        return $this;
    }
}