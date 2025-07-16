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

use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\View\HtmlView;





class JPusersViewUser extends HtmlView
{
	function display($tpl = null)
	{
	    $state   = $this->get('State');
        $item    = $this->get('Item');
        $params	 = $state->params;

        $modules    = Factory::getDocument()->loadRenderer('modules');
        $dispatcher	=Factory::getApplication();

        // Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx',''));

        // Check for errors.
		if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
			return false;
		}

        if (!$state->get('user.id')) {
           Factory::getApplication()->enqueueMessage( Text::_('COM_JOOMPROJECT_ERROR_USER_NOT_FOUND'),'error');
			return false;
        }

        // Process the content plugins.
        if ($item) {
            $item->title = $item->username;
            $item->text  = '';

    		// Import comment plugin only
    		PluginHelper::importPlugin('content', 'jpcomments');

            // Trigger events
    		$results = $dispatcher->triggerEvent('onContentPrepare', array ('com_jpusers.user', &$item, &$params, 0));

    		$item->event = new stdClass();
    		$results = $dispatcher->triggerEvent('onContentAfterTitle', array('com_jpusers.user', &$item, &$params, 0));
    		$item->event->afterDisplayTitle = trim(implode("\n", $results));

    		$results = $dispatcher->triggerEvent('onContentBeforeDisplay', array('com_jpusers.user', &$item, &$params, 0));
    		$item->event->beforeDisplayContent = trim(implode("\n", $results));

    		$results = $dispatcher->triggerEvent('onContentAfterDisplay', array('com_jpusers.user', &$item, &$params, 0));
    		$item->event->afterDisplayContent = trim(implode("\n", $results));
        }


        // Assign references
        $this->params = $params;
        $this->state = $state;
        $this->modules = $modules;
        $this->item = $item;


        // Prepare the document
        $this->prepareDocument();


		parent::display($tpl);
	}


    /**
	 * Prepares the document
     *
	 */
	protected function prepareDocument()
	{
		$app	 = Factory::getApplication();
		$menus	 = $app->getMenu();
		$pathway = $app->getPathway();
		$title	 = null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();

		if ($menu) {
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else {
			$this->params->def('page_heading', Text::_('COM_JOOMPROJECT_USER_DASHBOARD'));
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
			$link = '&format=feed&limitstart=';
			$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
			$this->document->addHeadLink(Route::_($link . '&type=rss'), 'alternate', 'rel', $attribs);
			$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
			$this->document->addHeadLink(Route::_($link . '&type=atom'), 'alternate', 'rel', $attribs);
		}
	}
}
