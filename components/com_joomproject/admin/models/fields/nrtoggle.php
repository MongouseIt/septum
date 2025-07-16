<?php
/**
 * @package      Joomproject
 * @subpackage   Projects
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\CheckboxField;

class JFormFieldNRToggle extends CheckboxField
{

	protected $type = 'NRToggle';
	/**
	 * On state value
	 *
	 * @var int
	 */
	protected $on_value = 1;

	/**
	 * Off state value
	 *
	 * @var int
	 */
	protected $off_value = 0;

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string
	 */
	public function getInput()
	{

		\Joomla\CMS\HTML\HTMLHelper::stylesheet(\Joomla\CMS\Uri\Uri::root().'media/com_joomproject/css/toggle.css');

		$required = $this->required ? ' required aria-required="true"' : '';
		$checked  = $this->checked ? ' checked' : '';
		$class	  = !empty($this->class) ? ' ' . $this->class : '';

		// Fix bug inherited from the Checkbox field where the input remains checked even if save it unchecked.
		if ($this->checked && (string) $this->value == (string) $this->off_value)
		{
			$checked = '';
		}

		return '
			<span class="nrtoggle' . $class . '">
				<input type="hidden" name="' . $this->name . '" id="' . $this->id . '_" value="' . $this->off_value . '">
				<input type="checkbox" name="' . $this->name . '" id="' . $this->id . '" value="'
			. htmlspecialchars($this->on_value, ENT_COMPAT, 'UTF-8') . '"' . $checked . $required . ' />
				<label for="' . $this->id . '"></label>
			</span>
		';
	}
}