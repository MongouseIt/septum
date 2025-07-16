<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_joomproject
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2019 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\HTML\Helpers\Sidebar;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * View class for a list of reminders.
 *
 * @since  1.6
 */
class JPremindersViewReminders extends HtmlView
{
	/**
	 * The item authors
	 *
	 * @var  stdClass
	 *
	 * @deprecated  4.0  To be removed with Hathor
	 */
	protected $authors;

	/**
	 * An array of items
	 *
	 * @var  array
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var  JPagination
	 */
	protected $pagination;

	/**
	 * The model state
	 *
	 * @var  object
	 */
	protected $state;

	/**
	 * Form object for search filters
	 *
	 * @var  JForm
	 */
	public $filterForm;

	/**
	 * The active search filters
	 *
	 * @var  array
	 */
	public $activeFilters;

	/**
	 * The sidebar markup
	 *
	 * @var  string
	 */
	protected $sidebar;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{


		$this->items      = $this->get('Items');
		$this->filterForm = $this->get('FilterForm');
		$this->pagination = $this->get('Pagination');
		$this->state      = $this->get('State');


		//$this->authors       = $this->get('Authors');


		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}


		// We don't need toolbar in the modal window.
		if ($this->getLayout() !== 'modal')
		{
			JPremindersHelper::addSubmenu('reminders');
			$this->addToolbar();
			$this->sidebar = Sidebar::render();
		}


		// Get an instance of the generic Reminder model
		$modelReminder = BaseDatabaseModel::getInstance('Reminder', 'JPremindersModel', array('ignore_request' => true));

		// update state of reminders
		foreach ($this->items as $i => $item){

			$item->reminderDates = $modelReminder->reminderDate($item->id);

			if ($item->reminderDates->total == 0 && $item->state == 1)
			{
				$modelReminder->updateRstate($item->id);
			}


		}


		return parent::display($tpl);
	}



    protected function addSidebar()
    {
        Sidebar::setAction('index.php?option=com_jpreminders');
    }

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
    {



        $user = Factory::getApplication()->getIdentity();
        // Get the toolbar object instance
        $bar = Toolbar::getInstance('toolbar');
        ToolbarHelper::title(Text::_('COM_JPREMINDERS_TITLE_REMINDERS'), 'stack article');




        if ($user->authorise('core.edit', 'com_jpreminders') || $user->authorise('core.edit.own', 'com_jpreminders')) {
            ToolbarHelper::editList('reminder.edit');
        }


        if ($user->authorise('core.delete', 'com_jpreminders')) {
            ToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'reminders.delete','JTOOLBAR_DELETE');
            ToolbarHelper::divider();
        }




    }

    protected function getSortFields()
    {
        return array(
            'r.ordering'     => Text::_('JGRID_HEADING_ORDERING'),
            'r.state'        => Text::_('JSTATUS'),
            't.title'        => Text::_('COM_JPREMINDERS_TASK_NAME'),
            'r.created_by'   => Text::_('COM_JPREMINDERS_AUTHOR'),
            'r.created'      => Text::_('JDATE'),
            'r.id'           => Text::_('JGRID_HEADING_ID'),
        );
    }

}
