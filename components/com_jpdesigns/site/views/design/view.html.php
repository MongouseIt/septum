<?php
/**
 * @package      Joomproject Pro
 * @subpackage   Designs
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
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;





/**
 * HTML Design View class for the Joomproject Design component
 *
 */
class JPdesignsViewDesign extends HtmlView
{
	protected $item;
    protected $revision;
    protected $revisions;
    protected $model_revisions;
	protected $params;
	protected $state;
	protected $user;
    protected $toolbar;
    protected $toolbar_rev;


	function display($tpl = null)
	{
        // check global access
        if(!\JoomProject\Permission\GlobalAccess::check('com_jpdesigns'))
            return;


		// Initialise variables.
		$app		= Factory::getApplication();
		$user		= Factory::getApplication()->getIdentity();
		$userId		= $user->get('id');
		$dispatcher	= Factory::getApplication();

		$this->item	 = $this->get('Item');
		$this->state = $this->get('State');
		$this->user  = $user;



        // Check for errors.
        if (count($errors = $this->get('Errors')))
        {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        if(is_null($this->item)){
            throw new GenericDataException('Design not found', 404);
        }

        $this->toolbar = $this->getToolbar();

        if ($this->item->revision) {
            // If we're looking at a specific revision
            $this->revision = &$this->item->revision;
        }

        // Get all revisions
        $this->model_revisions = BaseDatabaseModel::getInstance('Revisions', 'JPdesignsModel');

        // Need to override the revisions state filter if not set
        $rev_state  = (int) $this->model_revisions->getState('filter.state');
        $rev_filter = $this->model_revisions->getState('filter.isset');

        if (!$rev_state) {
            $rev_state = 1;
        }

        if (($this->item->state != $rev_state) && !$rev_filter) {
            $this->model_revisions->setState('filter.state', $this->item->state);
            $this->model_revisions->setState('filter.isset', true);
        }

        // Get all revisions
        $this->revisions = (array) $this->model_revisions->getItems();

        // Get the revisions toolbar
        $this->toolbar_rev = $this->getRevisionsToolbar();

		// Merge item params.
		$this->params = $this->state->get('params');
		$active	      = $app->getMenu()->getActive();
		$temp	      = clone ($this->params);

		// Check to see which parameters should take priority
		if ($active) {
			$currentLink = $active->link;

			if (strpos($currentLink, 'view=design') && (strpos($currentLink, '&id='.(string) $this->item->id))) {
				$this->item->params->merge($temp);
				// Load layout from active query (in case it is an alternative menu item)
				if (isset($active->query['layout'])) $this->setLayout($active->query['layout']);
			}
			else {
				// Merge the menu item params with the milestone params so that the milestone params take priority
				$temp->merge($this->item->params);
				$this->item->params = $temp;

				// Check for alternative layouts (since we are not in a menu item)
				if ($layout = $this->item->params->get('design_layout')) $this->setLayout($layout);
			}
		}
		else {
			// Merge so that item params take priority
			$temp->merge($this->item->params);
			$this->item->params = $temp;

			// Check for alternative layouts (since we are not in a menu item)
			if ($layout = $this->item->params->get('design_layout')) $this->setLayout($layout);
		}

        if ($this->revision) {
            // Check the view access to the item (the model has already computed the values).
    		if ($this->revision->params->get('access-view') != true && (($this->revision->params->get('show_noauth') != true &&  $user->get('guest') ))) {
    		    \Joomla\CMS\Factory::getApplication()->enqueueMessage( Text::_('JERROR_ALERTNOAUTHOR'),'warning');
    			return;
    		}

            // Fake some article content item
            JPObjectHelper::toContentItem($this->revision);
            $this->revision->event = new stdClass();

    		// Process the content plugins.
    		PluginHelper::importPlugin('content');

            $offset  = $this->state->get('list.offset');
    		$results = $dispatcher->triggerEvent('onContentPrepare', array ('com_jpdesigns.revision', &$this->revision, &$this->params, $offset));

    		$results = $dispatcher->triggerEvent('onContentAfterTitle', array('com_jpdesigns.revision', &$this->revision, &$this->params, $offset));
    		$this->revision->event->afterDisplayTitle = trim(implode("\n", $results));

    		$results = $dispatcher->triggerEvent('onContentBeforeDisplay', array('com_jpdesigns.revision', &$this->revision, &$this->params, $offset));
    		$this->revision->event->beforeDisplayContent = trim(implode("\n", $results));

    		$results = $dispatcher->triggerEvent('onContentAfterDisplay', array('com_jpdesigns.revision', &$this->revision, &$this->params, $offset));
    		$this->revision->event->afterDisplayContent = trim(implode("\n", $results));
        }
        else {
            // Check the view access to the item (the model has already computed the values).
    		if ($this->item->params->get('access-view') != true && (($this->item->params->get('show_noauth') != true &&  $user->get('guest') ))) {
    		    \Joomla\CMS\Factory::getApplication()->enqueueMessage( Text::_('JERROR_ALERTNOAUTHOR'),'warning');
    			return;
    		}

            // Fake some article content item
            JPObjectHelper::toContentItem($this->item);
            $this->item->event = new stdClass();

    		// Process the content plugins.
    		PluginHelper::importPlugin('content');

            $offset  = $this->state->get('list.offset');
    		$results = $dispatcher->triggerEvent('onContentPrepare', array ('com_jpdesigns.design', &$this->item, &$this->params, $offset));

    		$results = $dispatcher->triggerEvent('onContentAfterTitle', array('com_jpdesigns.design', &$this->item, &$this->params, $offset));
    		$this->item->event->afterDisplayTitle = trim(implode("\n", $results));

    		$results = $dispatcher->triggerEvent('onContentBeforeDisplay', array('com_jpdesigns.design', &$this->item, &$this->params, $offset));
    		$this->item->event->beforeDisplayContent = trim(implode("\n", $results));

    		$results = $dispatcher->triggerEvent('onContentAfterDisplay', array('com_jpdesigns.design', &$this->item, &$this->params, $offset));
    		$this->item->event->afterDisplayContent = trim(implode("\n", $results));
        }

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
			$this->params->def('page_heading', Text::_('COM_JOOMPROJECT_DESIGN'));
		}

		$title = $this->params->get('page_title', '');

		$id = (int) @$menu->query['id'];

		// If the menu item does not concern this item
		if($menu && ($menu->query['option'] != 'com_jpdesigns' || $menu->query['view'] != 'design' || $id != $this->item->id)) {
			// If this is not a single design menu item, set the page title to the design title
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
		if ($app->getCfg('MetaAuthor') == '1') $this->document->setMetaData('author', $this->item->author_name);
	}


    /**
     * Generates the toolbar for the top of the view
     *
     * @return    string    Toolbar with buttons
     */
    protected function getToolbar()
    {

        $config = ComponentHelper::getParams('com_jpdesigns', true);
        $uid    = Factory::getApplication()->getIdentity()->get('id');
        $slug   = $this->item->id . ':' . $this->item->alias;
        $return = base64_encode(Uri::getInstance()->toString());
        $rev    = $this->item->revision;

        $access  = JPdesignsHelper::getActions($this->item->id);
        $access2 = ($rev ? JPdesignsHelper::getRevisionActions($rev->id) : null);

        // Get the permissions
        $is_owner = ($uid == $this->item->created_by);
        $can_add  = $access->get('core.create');
        $can_edit = ($access->get('core.edit') || $access->get('core.edit.own') && $is_owner);
        $can_dl   = $access->get('core.download');
        $can_zip  = class_exists('ZipArchive');

        $can_edit_state = $access->get('core.edit.state');
        $can_delete     = $access->get  ('core.delete');
        $can_approve    = $access->get('core.approve');

        $this->item->approved = is_array($this->item->approved) ? $this->item->approved : [];

        $has_approved = array_key_exists($uid, $this->item->approved);
        $has_declined = array_key_exists($uid, $this->item->declined);
        $list_view    = 'designs';

        // Overwrite permissions when looking at a revision
        if ($rev) {
            $is_owner = ($uid == $rev->created_by);
            $can_edit = ($access2->get('core.edit') || $access2->get('core.edit.own') && $is_owner);
            $can_dl   = $access2->get('core.download');

            $can_edit_state = $access2->get('core.edit.state');
            $can_delete     = $access2->get('core.delete');
            $can_approve    = $access2->get('core.approve');

            $has_approved = array_key_exists($uid, $rev->approved);
            $has_declined = array_key_exists($uid, $rev->declined);
            $list_view    = 'revisions';
        }

        $options = array();

        if ($access->get('core.create')) {
            $options[] = array(
                'text'   => 'JACTION_ADD',
                'task'   => 'revisionform.add',
                'access' => $access->get('core.create'));
        }

        if ($can_edit) {
            $options[] = array(
                'text'   => 'COM_JOOMPROJECT_ACTION_EDIT',
                'task'   => ($rev ? 'revisionform.edit' : 'designform.edit'),
                'access' => $can_edit);
        }

        JPToolbar::dropdownButton($options, array('icon' => 'fas fa-plus'));

        // Download button
        if ($can_dl) {

            $link = JPdesignsHelperRoute::getDesignRoute(
                $this->item->slug,
                $this->item->project_slug,
                $this->item->album_slug,
                ($rev ? $rev->slug : '0:original')
            );

            JPToolbar::button('JACTION_DOWNLOAD',
                null,
                false,
                array(
                    'access' => true,
                    'icon'   => 'fas fa-download',
                    'href'   => $link . '&tmpl=component&layout=download&format=raw'
                )
            );
        }

        // Approve and Decline buttons
        if ($can_approve) {

            $behavior = $config->get('approval_behavior', 'changeable');
            $final    = ($behavior == 'final');

            JPToolbar::group();

            if (($final && !$has_declined) || !$final) {
                JPToolbar::button(($has_approved ? 'COM_JPDESIGNS_ACTION_APPROVED' : 'COM_JPDESIGNS_ACTION_APPROVE'),
                    (($has_approved || $final) ? '' : ($rev ? 'revisionform.approve' : 'designform.approve')),
                    false,
                    array(
                        'access' => true,
                        'icon'   => 'fas fa-thumbs-up',
                        'class'  => 'btn' . ($has_approved ? ' btn-success active' . ($final ? ' disabled' : '') : ' btn-light'),
                        'href'   => ((!$final || $has_approved) ? null : "javascript:confirmApprove('approve-design');"),
                        'id'     => 'approve-design'
                    )
                );
            }

            if (!$has_declined && !$has_approved) {
                JPToolbar::button('COM_JPDESIGNS_UNDECIDED',
                    '',
                    false,
                    array(
                        'access' => true,
                        'href'   => '#',
                        'icon'   => 'far fa-frown',
                        'class'  => 'btn btn-light active'
                    )
                );
            }

            if (($final && !$has_approved) || !$final) {
                JPToolbar::button(($has_declined ? 'COM_JPDESIGNS_ACTION_DECLINED' : 'COM_JPDESIGNS_ACTION_DECLINE'),
                    (($has_declined || $final) ? '' : ($rev ? 'revisionform.decline' : 'designform.decline')),
                    false,
                    array(
                        'access' => true,
                        'icon'   => 'fas fa-thumbs-down',
                        'class'  => 'btn' . ($has_declined ? ' btn-danger active' . ($final ? ' disabled' : '') : ' btn-light'),
                        'href'   => ((!$final || $has_declined) ? null : "javascript:confirmDecline('decline-design');"),
                        'id'     => 'decline-design'
                    )
                );
            }

            JPToolbar::group();
        }

        return JPToolbar::render();
    }


    protected function getRevisionsToolbar()
    {
        $access  = JPdesignsHelper::getActions($this->item->id);
        $options = array();

        if ($access->get('core.edit.state')) {
            $options[] = array('text' => 'COM_JOOMPROJECT_ACTION_PUBLISH',   'task' => 'revisions.publish');
            $options[] = array('text' => 'COM_JOOMPROJECT_ACTION_UNPUBLISH', 'task' => 'revisions.unpublish');
            $options[] = array('text' => 'COM_JOOMPROJECT_ACTION_ARCHIVE',   'task' => 'revisions.archive');
            $options[] = array('text' => 'COM_JOOMPROJECT_ACTION_CHECKIN',   'task' => 'revisions.checkin');
        }

        if ($this->state->get('filter.published') == -2 && $access->get('core.delete')) {
            $options[] = array('text' => 'COM_JOOMPROJECT_ACTION_DELETE', 'task' => 'revisions.delete');
        }
        elseif ($access->get('core.edit.state')) {
            $options[] = array('text' => 'COM_JOOMPROJECT_ACTION_TRASH', 'task' => 'revisions.trash');
        }

        JPToolbar::clear();

        if (count($options)) {
            JPToolbar::listButton($options);
        }

        JPToolbar::filterButton($this->model_revisions->getState('filter.isset'));

        return JPToolbar::render();
    }
}
