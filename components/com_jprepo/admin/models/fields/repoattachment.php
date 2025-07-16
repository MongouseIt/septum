<?php
/**
 * @package      Joomproject
 * @subpackage   Repository
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Form\FormField;


JLoader::register('JoomprojectHelperFrontend', JPATH_ROOT . '/components/com_joomproject/helpers/joomproject.php');


/**
 * Form Field class for selecting or uploading an attachment.
 *
 */
class JFormFieldRepoAttachment extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 */
	public $type = 'RepoAttachment';


	/**
	 * Method to get the field input markup.
	 *
	 * @return    string    The html field markup
	 */
	protected function getInput()
	{
		// Load the modal behavior script
		//HTMLHelper::_('behavior.modal', 'a.modal_' . $this->id);

        if(!\Joomla\CMS\Component\ComponentHelper::isEnabled('com_jprepo'))
            return;

		// Add the script to the document head.
		$script = $this->getJavascript();
		Factory::getDocument()->addScriptDeclaration(implode("\n", $script));

		if (!is_array($this->value))
		{
			$this->value = array();
		}

		$project = (int) $this->form->getValue('project_id');
		$hidden  = '<input type="hidden" name="' . $this->name . '[]" value="" />';

		if (!$project)
		{
			$project = JPApplicationHelper::getActiveProjectId();
		}

		if (!$project)
		{
			return '<span class="readonly">' . Text::_('COM_JOOMPROJECT_FIELD_PROJECT_REQ') . '</span>' . $hidden;
		}

		$html = $this->getHTML($project);

		return implode("\n", $html);
	}


	/**
	 * Method to generate the input markup.
	 *
	 * @return    string              The html field markup
	 */
	protected function getHTML($project)
	{
		if (Factory::getApplication()->isClient('site') || version_compare(JVERSION, '3.0.0', 'ge'))
		{
			return $this->getSiteHTML($project);
		}

		return $this->getAdminHTML($project);
	}


	/**
	 * Method to generate the backend input markup.
	 *
	 * @return    array     $html     The html field markup
	 */
	protected function getAdminHTML($project)
	{
		$html = array();
		$link = 'index.php?option=com_jprepo&amp;view=repository'
			. '&amp;filter_project=' . (int) $project
			. '&amp;layout=modal&amp;tmpl=component'
			. '&amp;function=jpSelectAttachment_' . $this->id;

		$html[] = '<ul id="' . $this->id . '_list" class="unstyled list-group">';

		foreach ($this->value AS $item)
		{
			if (!isset($item->repo_data))
			{
				continue;
			}

			if (empty($item->repo_data))
			{
				continue;
			}

			list($asset, $id) = explode('.', $item->attachment, 2);

			$icon = '<i class="fas fa-file"></i> ';

			if ($asset == 'directory')
			{
				$icon = '<i class="fas fa-folder"></i> ';
			}

			if ($asset == 'note')
			{
				$icon = '<i class="fas fa-edit"></i> ';
			}

			$html[] = '<li class="list-group-item">';
			$html[] = '<div class="btn-group  float-start"><a class="btn btn-sm btn-sm" onclick="jpRemoveAttachment_' . $this->id . '(this);"><i class="icon-remove"></i> </a></div>';
			$html[] = '&nbsp;';
			$html[] = '<span class="badge p-2">' . $icon . htmlspecialchars($item->repo_data->title, ENT_COMPAT, 'UTF-8') . '</span>';
			$html[] = '<input type="hidden" name="' . $this->name . '[]" value="' . htmlspecialchars($item->attachment, ENT_COMPAT, 'UTF-8') . '"/>';
			$html[] = '<div class="clearfix clr"></div>';
			$html[] = '</li>';
		}

		$html[] = '</ul>';
		$html[] = '<input type="hidden" name="' . $this->name . '[]" value=""/>';

		// Create the select button.
		if ($this->element['readonly'] != 'true')
		{
			$html[] = '<a class="modal_' . $this->id . ' btn btn-primary" title="' . Text::_('COM_JOOMPROJECT_SELECT_ATTACHMENT') . '"'
				. ' href="' . Route::_($link) . '">';
			$html[] = Text::_('COM_JOOMPROJECT_SELECT_ATTACHMENT') . '</a>';
		}

		return $html;
	}


	/**
	 * Method to generate the frontend input markup.
	 *
	 * @return    array     $html     The html field markup
	 */
	protected function getSiteHTML($project)
	{
		$html = array();

		if (Factory::getApplication()->isClient('site'))
		{
			$link = JPrepoHelperRoute::getRepositoryRoute($project)
				. '&amp;layout=modal&amp;tmpl=component'
				. '&amp;function=jpSelectAttachment_' . $this->id;
		}
		else
		{
			$link = 'index.php?option=com_jprepo&amp;view=repository'
				. '&amp;filter_project=' . (int) $project
				. '&amp;layout=modal&amp;tmpl=component'
				. '&amp;function=jpSelectAttachment_' . $this->id;
		}


		$html[] = '<ul id="' . $this->id . '_list" class="list-unstyled list-group">';
		if(!empty($this->value)){

			foreach ($this->value AS $item)
			{

				if (!isset($item->repo_data))
				{
					continue;
				}

				if (empty($item->repo_data))
				{
					continue;
				}

				list($asset, $id) = explode('.', $item->attachment, 2);


				if ($asset == 'directory')
				{
					$icon = '<i class="text-muted fas fa-folder me-2"></i> ';
				}

				if ($asset == 'note')
				{
					$icon = '<i class="text-muted fas fa-edit me-2"></i> ';
				}

				if($asset == 'file'){
					$icon_class = JoomprojectHelperFrontend::extToIcon(strtolower($item->repo_data->file_extension));
					$icon = '<i class="me-2 text-muted fas fa-'.$icon_class.'"></i> ';
				}

				$html[] = '<li class="list-group-item d-flex justify-content-between align-items-center">';
				$html[] = '<span>'.$icon . htmlspecialchars($item->repo_data->title, ENT_COMPAT, 'UTF-8').'</span>';
				$html[] = '<input type="hidden" name="' . $this->name . '[]" value="' . htmlspecialchars($item->attachment, ENT_COMPAT, 'UTF-8') . '"/>';
				$html[] = '<a class="btn btn-sm btn-danger text-white" onclick="jpRemoveAttachment_' . $this->id . '(this);"><i class="fas fa-times"></i> </a>';
				$html[] = '</li>';
			}

		}
		$html[] = '</ul>';

		$html[] = '<input type="hidden" name="' . $this->name . '[]" value=""/>';

		// Create the select button.
		if ($this->element['readonly'] != 'true')
		{
			$html[] = '<div class="mt-3">';
			$html[] = '<a class="modal_' . $this->id . ' btn btn-izimodal btn-primary"   title="' . Text::_('COM_JOOMPROJECT_SELECT_ATTACHMENT') . '"'
				. ' href="' . Route::_($link).'" ';
			$html[] = 'data-original-link="'. Route::_($link).'" ';
			$html[] = ' >';
			$html[] = '<i class="fas fa-plus"></i>';
			$html[] = Text::_('COM_JOOMPROJECT_SELECT_ATTACHMENT') . '</a>';
			$html[] = '</div>';
		}

		return $html;
	}


	/**
	 * Generates the javascript needed for this field
	 *
	 * @param boolean $submit Whether to submit the form or not
	 * @param string  $view   The name of the view
	 *
	 * @return    array      $script    The generated javascript
	 */
	protected function getJavascript()
	{
		$script   = array();
		$onchange = $this->element['onchange'] ? $this->element['onchange'] : '';

		$script[] = 'function jpSelectAttachment_' . $this->id . '(id, title, atype)';
		$script[] = '{';
 		$script[] = '    var l = jQuery("#' . $this->id . '_list");';
		$script[] = '    var i = "<i class=\"text-muted fas fa-file\"></i> "';
		$script[] = '    ';
		$script[] = '    if (atype == "directory") i = "<i class=\"text-muted fas fa-folder\"></i> ";';
		$script[] = '    if (atype == "note")      i = "<i class=\"text-muted fas fa-edit\"></i> ";';
		$script[] = '    ';
		$script[] = '    var c = "<li class=\"list-group-item  d-flex justify-content-between align-items-center\">"';
		$script[] = '          + "<span>"';
		$script[] = '          + i + title';
		$script[] = '          + "</span>"';
		$script[] = '          + "<input type=\"hidden\" name=\"' . $this->name . '[]\" value=\"" + atype + "." + id + "\"/>"';
		$script[] = '          + "<a class=\"btn btn-sm btn-danger text-white\" onclick=\"jpRemoveAttachment_' . $this->id . '(this);\"><i class=\"fas fa-times\"></i> </a>"';
		$script[] = '          + "</li>"';
		$script[] = '    ';
		$script[] = '    l.append(c);';
		//$script[] = '    SqueezeBox.close();';
		$script[] = '    ' . $onchange;
		$script[] = '}';
		$script[] = 'function jpRemoveAttachment_' . $this->id . '(el)';
		$script[] = '{';
		$script[] = '    jQuery(el).parent().remove();';
		$script[] = '}';

		return $script;
	}


	/**
	 * Method to get the title of the currently selected project
	 *
	 * @return    string    The project title
	 */
	protected function getAttachmentTitle()
	{
		$default = Text::_('COM_JOOMPROJECT_SELECT_A_PROJECT');

		if (empty($this->value))
		{
			return $default;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('title')
			->from('#__jp_projects')
			->where('id = ' . $db->quote($this->value));

		$db->setQuery((string) $query);
		$title = $db->loadResult();

		if (empty($title))
		{
			return $default;
		}

		return $title;
	}
}
