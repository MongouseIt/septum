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

require_once JPATH_ADMINISTRATOR . '/components/com_jpprojects/helpers/field.php';

class JFormFieldNRRangeSlider extends NRFormField
{
	/**
	 *  Method to render the input field
	 *
	 *  @return  string
	 */
	protected function getInput()
	{
		$min = isset($this->element['min']) ? (float) $this->element['min'] : null;
		$max = isset($this->element['max']) ? (float) $this->element['max'] : null;
		$step = isset($this->element['step']) ? (float) $this->element['step'] : null;

		$payload = [
			'name' => $this->name,
			'value' => (float) $this->value
		];

		if ($min)
		{
			$payload['min'] = $min;
		}
		if ($max)
		{
			$payload['max'] = $max;
		}
		if ($step)
		{
			$payload['step'] = $step;
		}

		if ($this->class)
		{
			$payload['css_class'] = $this->class;
		}

		$slider = \Joomla\CMS\Layout\LayoutHelper::render('rangeslider.default', $payload,JPATH_ADMINISTRATOR . '/components/com_jpprojects/layouts/');

		return $slider;
	}
}