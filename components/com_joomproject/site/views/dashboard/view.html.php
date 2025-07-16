<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_joomproject
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





class JoomprojectViewDashboard extends HtmlView
{
    protected $params;
    protected $state;
    protected $modules;
    protected $item;
    protected $pageclass_sfx;
    protected $toolbar;


	function display($tpl = null)
	{
		$this->state   = $this->get('State');
        $this->item    = $this->get('Item');
        $this->params  = $this->state->params;
        $this->modules = Factory::getDocument()->loadRenderer('modules');
        $this->toolbar = $this->getToolbar();
        $dispatcher	   = \Joomla\CMS\Factory::getApplication();

        // Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx',''));

        // Check for errors.
		if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
		}

        // Process the content plugins.
        if (!empty($this->item)) {
            // Fake content item
            JPObjectHelper::toContentItem($this->item);

    		// Import plugins
    		PluginHelper::importPlugin('content');
            $context = 'com_jpprojects.project';

            // Trigger events
    		$results = $dispatcher->triggerEvent('onContentPrepare', array ($context, &$this->item, &$this->params, 0));

    		$this->item->event = new stdClass();
    		$results = $dispatcher->triggerEvent('onContentAfterTitle', array($context, &$this->item, &$this->params, 0));
    		$this->item->event->afterDisplayTitle = trim(implode("\n", $results));

    		$results = $dispatcher->triggerEvent('onContentBeforeDisplay', array($context, &$this->item, &$this->params, 0));
    		$this->item->event->beforeDisplayContent = trim(implode("\n", $results));

    		$results = $dispatcher->triggerEvent('onContentAfterDisplay', array($context, &$this->item, &$this->params, 0));
    		$this->item->event->afterDisplayContent = trim(implode("\n", $results));
        }

        // Prepare the document
        $this->prepareDocument();

        // Display
		parent::display($tpl);
	}


    /**
	 * Prepares the document
     *
	 */
	protected function prepareDocument()
	{
		$app	 = Factory::getApplication();
		$menu    = $app->getMenu()->getActive();
		$pathway = $app->getPathway();
		$title	 = null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		if ($menu) {
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else {
			$this->params->def('page_heading', Text::_('COM_JOOMPROJECT_DASHBOARD_TITLE'));
		}


        // Set the page title
		$title = $this->params->get('page_title', '');

		if (empty($title)) {
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
			$title = Text::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
			$title = Text::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}

		$this->document->setTitle($title);

        // Set crawler behavior info
		if ($this->params->get('robots')) {
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}

        // Set page description
        if($this->params->get('menu-meta_description')) {
            $this->document->setDescription($desc);
        }

        // Set page keywords
        if($this->params->get('menu-meta_keywords')) {
            $this->document->setMetadata('keywords', $keywords);
        }

		// Add feed links
		if ($this->params->get('show_feed_link', 1)) {
			// Add RSS link
            $link    = '&format=feed&limitstart=';
			$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');

			$this->document->addHeadLink(Route::_($link . '&type=rss'), 'alternate', 'rel', $attribs);

            // Add atom link
			$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');

			$this->document->addHeadLink(Route::_($link . '&type=atom'), 'alternate', 'rel', $attribs);
		}
	}


    /**
     * Generates the toolbar for the top of the view
     *
     * @return    string    Toolbar with buttons
     */
    protected function getToolbar()
    {
        $id = (empty($this->item) || empty($this->item->id) ? null : $this->item->id);

        $access = JPprojectsHelper::getActions($id);
        $uid    = Factory::getApplication()->getIdentity()->get('id');

        if (!empty($id)) {
            $slug   = $this->item->id . ':' . $this->item->alias;
            $return = base64_encode(JPprojectsHelperRoute::getDashboardRoute($slug));

            JPToolbar::button(
                'COM_JOOMPROJECT_ACTION_EDIT',
                '',
                false,
                array(
                    'access' => ($access->get('core.edit') || $access->get('core.edit.own') && $uid == $this->item->created_by),
                    'href' => Route::_(JPprojectsHelperRoute::getProjectsRoute() . '&task=form.edit&id=' . $slug . '&return=' . $return),
	                'icon'  => 'fas fa-edit'
                )
            );
        }

        return JPToolbar::render();
    }
}
