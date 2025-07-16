<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jprepo
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\Controller\BaseController;


jimport('joomla.application.component.controller');


/**
 * Repository Main Controller Class
 *
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
     * Method to display a view.
     *
     * @param     boolean        If true, the view output will be cached
     * @param     array          An array of safe url parameters
     *
     * @return    jcontroller    This object to support chaining.
     */
    public function display($cachable = false, $urlparams = false)
    {
        $view    = Factory::getApplication()->input->getCmd('view', $this->default_view);
        $layout  = Factory::getApplication()->input->getCmd('layout');
        $id      = Factory::getApplication()->input->getUint('id');

        // Inject default view if not set
        if (empty($view)) {
            Factory::getApplication()->input->set('view', $this->default_view);
            $view = $this->default_view;
        }

        if ($view == $this->default_view) {
            $parent_id = \Joomla\CMS\Factory::getApplication()->input->getUInt('filter_parent_id');
            $project   = JPApplicationHelper::getActiveProjectId('filter_project');

            if ($parent_id && $project === "") {
                $this->setRedirect('index.php?option=com_jprepo&view=' . $this->default_view);
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
                        $this->setRedirect('index.php?option=com_jprepo&view=' . $this->default_view . '&filter_project=' . $project . '&filter_parent_id=' . $dir->id);
                        return $this;
                    }
                }
            }
        }

        // Check form edit access
        if ($layout == 'edit' && !$this->checkEditId('com_jprepo.edit.' . $view, $id)) {

            \Joomla\CMS\Factory::getApplication()->enqueueMessage(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id),'error');

            $this->setRedirect(Route::_('index.php?option=com_jprepo&view=' . $this->default_view, false));

            return false;
        }

        // Add the sub-menu
        JPrepoHelper::addSubmenu($view);

        // Display the view
        parent::display($cachable, $urlparams);

        return $this;
    }
}
