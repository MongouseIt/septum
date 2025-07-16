<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jptasks
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;

jimport('joomla.application.component.controller');


/**
 * Component main controller
 *
 * @see    JController
 */
class JPtasksController extends BaseController
{
    /**
     * The default view
     *
     * @var    string
     */
    protected $default_view = 'tasks';


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

        $view      = Factory::getApplication()->input->getCmd('view');
        $layout = $this->input->get('layout', 'default');
        $id        = Factory::getApplication()->input->getUInt('id');
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
            'filter_project'   => 'CMD',
            'filter_milestone' => 'CMD',
            'filter_tasklist'  => 'CMD',
            'filter_search'    => 'STRING',
            'filter_published' => 'CMD'
        );

        // Inject default view if not set
        if (empty($view)) {
            Factory::getApplication()->input->set('view', $this->default_view);
        }

        // auto-checking feature for tasks
        if($view == 'taskform' && $layout === 'edit' && \Joomla\CMS\Component\ComponentHelper::getParams('com_jptasks')->get('task_autochecking',0)){
            JoomprojectHelperFrontend::autoCheckin('jp_tasks',Factory::getApplication()->input->getInt('id'));

        }

        // auto-checking feature for task lists
        if($view == 'tasklistform' && $layout === 'edit' && \Joomla\CMS\Component\ComponentHelper::getParams('com_jptasks')->get('tasklist_autochecking',0)){
            JoomprojectHelperFrontend::autoCheckin('jp_task_lists',Factory::getApplication()->input->getInt('id'));

        }

        // todo: disabled temporary, note sure why needed
        // Check for task edit form.
		/*if ($view == 'taskform' && $layout === 'edit'  && !$this->checkEditId('com_jptasks.edit.taskform', $id)) {
			// Somehow the person just went to the form - we don't allow that.
			return Factory::getApplication()->enqueueMessage( $id,'error');
		}

        // Check for task list edit form.
		if ($view == 'tasklistform' && !$this->checkEditId('com_jptasks.edit.tasklistform', $id)) {
			// Somehow the person just went to the form - we don't allow that.
			return Factory::getApplication()->enqueueMessage( $id,'error');
		}*/

        // Display the view
        parent::display($cachable, $urlparams);
    }
}