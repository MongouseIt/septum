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


use Joomla\CMS\Language\Text;
JLoader::register('JFormFieldJCNumber', JPATH_ADMINISTRATOR . '/components/com_jpprojects/models/fields/jcnumber.php');

class JFormFieldNRNumber extends JFormFieldJCNumber
{
	/**
	 *  Method to render the input field
	 *
	 *  @return  string
	 */
	function getInput()
	{

		$parent = parent::getInput();
		$addon  = (string) $this->element['addon'];

		if (empty($addon))
		{
			return $parent;
		}

		return '
            <div class="input-append input-group">
                ' . $parent . '
                <span class="add-on input-group-append">
                    <span class="input-group-text" style="font-size:inherit;">' . Text::_($addon) . '</span>
                </span>
            </div>
        ';
	}
}
