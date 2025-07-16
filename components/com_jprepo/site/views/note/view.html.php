<?php
/**
 * @package      Joomproject
 * @subpackage   Repository
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





/**
 * HTML Note View class for the Joomproject component
 *
 */
class JPrepoViewNote extends HtmlView
{
	protected $item;
	protected $params;
	protected $print;
	protected $state;
	protected $user;
    protected $rev;


	function display($tpl = null)
	{
        if(!\JoomProject\Permission\GlobalAccess::check('com_jprepo'))
            return;

		// Initialise variables.
		$app		= Factory::getApplication();
		$user		= Factory::getApplication()->getIdentity();
		$userId		= $user->get('id');
		$dispatcher	= Factory::getApplication();

		$this->item	 = $this->get('Item');
		$this->print = Factory::getApplication()->input->getBool('print');
		$this->state = $this->get('State');
        $this->rev   = $this->state->get($this->getName() . '.rev');
		$this->user  = $user;

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);

			return false;
		}

		// Merge item params.
		$this->params = $this->state->get('params');
		$active	      = $app->getMenu()->getActive();
		$temp	      = clone ($this->params);

		// Check to see which parameters should take priority
		if ($active) {
			$currentLink = $active->link;

			if (strpos($currentLink, 'view=note') && (strpos($currentLink, '&id='.(string) $this->item->id))) {
				$this->item->params->merge($temp);
				// Load layout from active query (in case it is an alternative menu item)
				if (isset($active->query['layout'])) $this->setLayout($active->query['layout']);
			}
			else {
				// Merge the menu item params with the milestone params so that the milestone params take priority
				$temp->merge($this->item->params);
				$this->item->params = $temp;

				// Check for alternative layouts (since we are not in a menu item)
				if ($layout = $this->item->params->get('note_layout')) $this->setLayout($layout);
			}
		}
		else {
			// Merge so that item params take priority
			$temp->merge($this->item->params);
			$this->item->params = $temp;

			// Check for alternative layouts (since we are not in a menu item)
			if ($layout = $this->item->params->get('note_layout')) $this->setLayout($layout);
		}

		$offset = $this->state->get('list.offset');

		// Check the view access to the milestone (the model has already computed the values).
		if ($this->item->params->get('access-view') != true && (($this->item->params->get('show_noauth') != true &&  $user->get('guest') ))) {
		    Factory::getApplication()->enqueueMessage( Text::_('JERROR_ALERTNOAUTHOR'),'warning');
			return;
		}

        // Fake some content item properties to avoid plugin issues
        $this->item->introtext = '';
        $this->item->fulltext  = '';

		// Process the content plugins.
		PluginHelper::importPlugin('content');
		$results = $dispatcher->triggerEvent('onContentPrepare', array ('com_jprepo.note', &$this->item, &$this->params, $offset));

		$this->item->event = new stdClass();
		$results = $dispatcher->triggerEvent('onContentAfterTitle', array('com_jprepo.note', &$this->item, &$this->params, $offset));
		$this->item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->triggerEvent('onContentBeforeDisplay', array('com_jprepo.note', &$this->item, &$this->params, $offset));
		$this->item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->triggerEvent('onContentAfterDisplay', array('com_jprepo.note', &$this->item, &$this->params, $offset));
		$this->item->event->afterDisplayContent = trim(implode("\n", $results));


		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($this->item->params->get('pageclass_sfx',''));

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
			$this->params->def('page_heading', Text::_('COM_JOOMPROJECT_NOTE'));
		}

		$title = $this->params->get('page_title', '');

		$id = (int) @$menu->query['id'];

		// If the menu item does not concern this item
		if($menu && ($menu->query['option'] != 'com_jprepo' || $menu->query['view'] != 'note' || $id != $this->item->id)) {
			// If this is not a single milestone menu item, set the page title to the milestone title
			if($this->item->title) $title = $this->item->title;

            $pid    = $this->item->project_id;
            $palias = $this->item->project_alias;

			$path   = array(array('title' => $this->item->title, 'link' => ''));
            $projectDashboard = Route::_(JoomprojectHelperRoute::getDashboardRoute("$pid:$palias"));
            $path[] = array('title' => $this->item->project_title, 'link' => $projectDashboard);

			$path = array_reverse($path);

			foreach($path as $item)
			{
				$pathway->addItem($item['title'], $item['link']);
			}
		}

		// Check for empty title and add site name if param is set
		if (empty($title)) {
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
			$title = Text::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
			$title = Text::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}
		if (empty($title)) {
			$title = $this->item->title;
		}

		$this->document->setTitle($title);


		if ($this->params->get('robots'))      $this->document->setMetadata('robots', $this->params->get('robots'));
		if ($app->getCfg('MetaAuthor') == '1') $this->document->setMetaData('author', $this->item->author);
		if ($this->print)                      $this->document->setMetaData('robots', 'noindex, nofollow');
	}
}
