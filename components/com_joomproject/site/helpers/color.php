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
abstract class JoomprojectHelperColor
{

	public static function getItemColor($color){

		$style = "";

		// check if color has sign
		$sign = (strpos($color,'#') === false) ? '#' : '';

		// append sign to color
		$color = $sign.$color;

		// generate css code
		if(!empty($color) AND $color != "#ffffff"){
			$style = "style='border-right: 10px solid {$color}90;background:{$color}25'";
		}

		return $style;

	}

}
