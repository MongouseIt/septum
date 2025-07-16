<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jpmilestones
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */


defined('_JEXEC') or die();

use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\View\HtmlView;





/**
 * HTML Milestone View class for the Joomproject component
 *
 */
class JPmilestonesViewMilestone extends HtmlView
{
	protected $item;
	protected $params;
	protected $print;
	protected $state;
	protected $user;
    protected $toolbar;


	function display($tpl = null)
	{

        // check global access
        if(!\JoomProject\Permission\GlobalAccess::check('com_jpmilestones'))
            return;

		// Initialise variables.
		$app		= Factory::getApplication();
        $dispatcher	= $app;
		$user		= Factory::getApplication()->getIdentity();

		$uid   = $user->get('id');
		$item  = $this->get('Item');
		$state = $this->get('State');
        $print = Factory::getApplication()->input->getBool('print');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
			return false;
		}

        // Check the view access.
		if (!$item->params->get('access-view')) {
		   $app->enqueueMessage( Text::_('JERROR_ALERTNOAUTHOR'),'warning');
			return false;
		}

        // Set active project
        if (!JPApplicationHelper::setActiveProject($item->project_id)) {
           $app->enqueueMessage( Text::_('JERROR_ALERTNOAUTHOR'),'warning');
            return false;
        }

		// Merge milestone params. If this is single-milestone view, menu params override milestone params
		// Otherwise, milestone params override menu item params
		$params = $state->get('params');
		$active	= $app->getMenu()->getActive();
		$temp	= clone ($params);

		// Check to see which parameters should take priority
		if ($active) {
			$current_link = $active->link;

			if (strpos($current_link, 'view=milestone') && (strpos($current_link, '&id='.(string) $item->id))) {
				$item->params->merge($temp);

				// Load layout from active query (in case it is an alternative menu item)
				if (isset($active->query['layout'])) $this->setLayout($active->query['layout']);
			}
			else {
				// Merge the menu item params with the milestone params so that the milestone params take priority
				$temp->merge($item->params);
				$item->params = $temp;

				// Check for alternative layouts (since we are not in a single-milestone menu item)
				if ($layout = $item->params->get('milestone_layout')) $this->setLayout($layout);
			}
		}
		else {
			// Merge so that milestone params take priority
			$temp->merge($item->params);
			$item->params = $temp;

			// Check for alternative layouts (since we are not in a single-milestone menu item)
			if ($layout = $item->params->get('milestone_layout')) $this->setLayout($layout);
		}

		$offset = $state->get('list.offset');

        // Fake some content item properties to avoid plugin issues
        JPObjectHelper::toContentItem($item);

		// Process the content plugins.
		PluginHelper::importPlugin('content');

		$results = $dispatcher->triggerEvent('onContentPrepare', array ('com_jpmilestones.milestone', &$item, &$params, $offset));

		$item->event = new stdClass();
		$results = $dispatcher->triggerEvent('onContentAfterTitle', array('com_jpmilestones.milestone', &$item, &$params, $offset));
		$item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->triggerEvent('onContentBeforeDisplay', array('com_jpmilestones.milestone', &$item, &$params, $offset));
		$item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->triggerEvent('onContentAfterDisplay', array('com_jpmilestones.milestone', &$item, &$params, $offset));
		$item->event->afterDisplayContent = trim(implode("\n", $results));

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($item->params->get('pageclass_sfx',''));

        // Assign references
        $this->params = $params;
        $this->state = $state;
        $this->user = $user;
        $this->item =   $item;
        $this->print =  $print;

        $this->toolbar = $this->getToolbar();

		$this->_prepareDocument();

		parent::display($tpl);
	}


	/**
	 * Prepares the document
     *
	 */
	protected function _prepareDocument()
	{
		$app	 = Factory::getApplication();
		$menus	 = $app->getMenu();
        $menu    = $menus->getActive();
		$pathway = $app->getPathway();
		$title   = null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		if ($menu) {
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else {
			$this->params->def('page_heading', Text::_('COM_JOOMPROJECT_MILESTONE'));
		}

		$title = $this->params->get('page_title', '');
		$id    = (int) @$menu->query['id'];

		// If the menu item does not concern this item
		if($menu && ($menu->query['option'] != 'com_jpmilestones' || $menu->query['view'] != 'milestone' || $id != $this->item->id))
        {
			// If this is not a single milestone menu item, set the page title to the milestone title
			if ($this->item->title) $title = $this->item->title;

            $pid    = $this->item->project_id;
            $palias = $this->item->project_alias;

			$path   = array(array('title' => $this->item->title,
                                  'link'  => '')
                                 );

            $projectDashboard = Route::_(JoomprojectHelperRoute::getDashboardRoute("$pid:$palias"));

            $path[] = array('title' => $this->item->project_title,
                            'link'  => $projectDashboard
                           );

			$path = array_reverse($path);

			foreach ($path as $item)
			{
				$pathway->addItem($item['title'], $item['link']);
			}
		}

		// Check for empty title and add site name if param is set
		if (empty($title))
        {
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1)
        {
			$title = Text::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2)
        {
			$title = Text::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}

		if (empty($title))
        {
			$title = $this->item->title;
		}

		$this->document->setTitle($title);


		if ($this->params->get('robots'))      $this->document->setMetadata('robots', $this->params->get('robots'));
		if ($app->getCfg('MetaAuthor') == '1') $this->document->setMetaData('author', $this->item->author);
		if ($this->print)                      $this->document->setMetaData('robots', 'noindex, nofollow');
	}


    /**
     * Generates the toolbar for the top of the view
     *
     * @return    string    Toolbar with buttons
     */
    protected function getToolbar()
    {
        $access = JPmilestonesHelper::getActions($this->item->id);
        $uid    = Factory::getApplication()->getIdentity()->get('id');
        $app    = Factory::getApplication();

        if ($this->item->id) {
            $slug   = $this->item->id . ':' . $this->item->alias;
            $project_slug = $app->input->getCmd('filter_project');
            $return = base64_encode(JPmilestonesHelperRoute::getMilestoneRoute($slug, $project_slug));

            JPToolbar::button(
                'COM_JOOMPROJECT_ACTION_EDIT',
                '',
                false,
                array(
                    'access' => ($access->get('core.edit') || $access->get('core.edit.own') && $uid == $this->item->created_by),
                    'href' => Route::_(JPmilestonesHelperRoute::getMilestonesRoute() . '&task=form.edit&id=' . $slug . '&return=' . $return),
	                'icon' => 'fas fa-edit'
                )
            );
        }

        if (JPApplicationHelper::enabled('com_jptasks')) {
            JPToolbar::button(
                Text::sprintf('JGRID_HEADING_TASKLISTS_AND_TASKS', intval($this->item->lists), intval($this->item->tasks)),
                '',
                false,
                array(
                    'href' => Route::_(JPtasksHelperRoute::getTasksRoute($this->item->project_id, $this->item->id)),
                    'icon' => 'fas fa-chevron-right',
                    'class'=> 'btn-secondary'
                )
            );
            ;
        }


        return JPToolbar::render();
    }
}
