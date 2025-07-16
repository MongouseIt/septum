<?php
/**
 * @package      pkg_jpactivities
 * @subpackage   com_jpactivities
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2013 JoomBoost.com. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Session\Session;
use Joomla\CMS\MVC\View\HtmlView;



class JPactivitiesViewModule extends HtmlView
{
    /**
     * Displays the module.
     *
     */
    public function display($tpl = null)
    {
        // Check token
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        $user = Factory::getApplication()->getIdentity();

        // Get the module record
        $module = $this->get('Item');

        // Check for errors
        if (count($errors = $this->get('Errors'))) {
			throw new GenericDataException( implode("\n", $errors),   500 );
            jexit(500);
        }

        // Check if enabled
        if ($module->published != '1') {
            Factory::getApplication()->enqueueMessage(Text::_('COM_JPACTIVITIES_ERROR_MODULE_ACCESS_DENIED'),'ERROR');
            jexit(500);
        }

        // Check access
        if (!$user->authorise('core.admin')) {
            $access = (int) $module->access;

            if (!in_array($access, $user->getAuthorisedViewLevels())) {
	            Factory::getApplication()->enqueueMessage( Text::_('COM_JPACTIVITIES_ERROR_MODULE_ACCESS_DENIED'),'error');
                jexit(500);
            }
        }

        // Show only site modules here
        if ($module->client_id != '0') {
	        Factory::getApplication()->enqueueMessage(Text::_('COM_JPACTIVITIES_ERROR_MODULE_ACCESS_DENIED'),'error');
            jexit(500);
        }

        $name = $module->module;
        $file = JPATH_SITE . '/modules/' . $name . '/' . $name . '.php';

        if (!file_exists($file)) {

	        Factory::getApplication()->enqueueMessage( Text::_('COM_JPACTIVITIES_ERROR_MODULE_FILE_NOT_FOUND'),'error');
            jexit(500);
        }

        // Set the module params
        $this->setParamsFromRequest($module->params);
        $params = &$module->params;

        // Include the module
        require_once $file;
        jexit(201);
    }


    protected function setParamsFromRequest(&$params)
    {
        $app    = Factory::getApplication();
        $layout = $params->get('layout', 'default') . '_json';
        $start  = $app->input->post->get('limitstart', 0);

        $filter_search = $app->input->post->get('filter_search', '');
        $filter_ext    = $app->input->post->get('filter_extension', '');
        $filter_event  = $app->input->post->get('filter_event_id', '');

        // Layout
        $params->set('layout', $layout);

        // List start
        $params->set('list_start', (int) $start);

        // Filter - Search
        if (!empty($filter_search) && $params->get('show_filter_search')) {
            $params->set('filter_search', $filter_search);
        }

        // Filter Extension
        if (!empty($filter_ext) && $params->get('show_filter_extension')) {
            $params->set('filter_extension', $filter_ext);
        }

        // Filter - Event
        if (is_numeric($filter_event) && $params->get('show_filter_event')) {
            $params->set('filter_event_id', $filter_event);
        }
    }
}
