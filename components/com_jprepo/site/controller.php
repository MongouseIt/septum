<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jprepo
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\Controller\BaseController;


jimport('joomla.application.component.controller');


/**
 * Component main controller
 *
 * @see    JController
 */
class JPrepoController extends BaseController
{
    /**
     * The default view
     *
     * @var    string
     */
    protected $default_view = 'repository';


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
            'filter_search'    => 'STRING',
            'filter_published' => 'CMD'
        );

        // Inject default view if not set
        if (empty($view)) {
            \Joomla\CMS\Factory::getApplication()->input->set('view', $this->default_view);
            $view = $this->default_view;
        }


        if ($view == $this->default_view) {
            $parent_id = \Joomla\CMS\Factory::getApplication()->input->getUInt('filter_parent_id');
            $project   = JPApplicationHelper::getActiveProjectId('filter_project');

            if ($parent_id && $project == "") {
                $this->setRedirect(Route::_(JPrepoHelperRoute::getRepositoryRoute()));
                return $this;
            }
            elseif ($parent_id > 1 && $project > 0) {
                // Check if the folder belongs to the project
                $db    = Factory::getDbo();
                $query = $db->getQuery(true);

                $query->select('project_id')
                      ->from('#__jp_repo_dirs')
                      ->where('id = ' . (int) $parent_id);

                $db->setQuery($query);
                $pid = $db->loadResult();

                if ($pid != $project) {
                    // No match, redirect to the project root dir
                    $query->clear();
                    $query->select('id, path')
                          ->from('#__jp_repo_dirs')
                          ->where('parent_id = 1')
                          ->where('project_id = ' . (int) $project);

                    $db->setQuery($query, 0, 1);
                    $dir = $db->loadObject();

                    if ($dir) {
                        $this->setRedirect(Route::_(JPrepoHelperRoute::getRepositoryRoute($project, $dir->id, $dir->path)));
                        return $this;
                    }
                }
            }
        }

        // todo: disabled temporary, note sure why needed
        // Check for directory edit form.
        /*if ($view == 'directoryform' && !$this->checkEditId('com_jprepo.edit.directoryform', $id)) {
            // Somehow the person just went to the form - we don't allow that.
            return \Joomla\CMS\Factory::getApplication()->enqueueMessage( $id,'error');
        }

        // Check for note edit form.
        if ($view == 'noteform' && !$this->checkEditId('com_jprepo.edit.noteform', $id)) {
            // Somehow the person just went to the form - we don't allow that.
            return \Joomla\CMS\Factory::getApplication()->enqueueMessage( $id,'error');
        }

        // Check for file edit form.
        if ($view == 'fileform' && !$this->checkEditId('com_jprepo.edit.fileform', $id)) {
            // Somehow the person just went to the form - we don't allow that.
            return \Joomla\CMS\Factory::getApplication()->enqueueMessage( $id,'error');
        }*/

        // Display the view
        parent::display($cachable, $urlparams);

        // Return own instance for chaining
        return $this;
    }
}