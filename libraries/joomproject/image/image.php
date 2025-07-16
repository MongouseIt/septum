<?php
/**
* @package      Joomproject.Library
* @subpackage   Image
*
* @author       JoomBoost
* @copyright    Copyright (C) 20012-2018 JoomBoost. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();


/**
 * Joomproject Image class
 *
 */
abstract class JPImage
{
    public static $valid_extension = array('jpg', 'jpeg', 'png', 'gif');

    public static function isValid($name, $path = NULL)
    {
        $ext = strtolower(\Joomla\CMS\Filesystem\File::getExt($name));

        if (!in_array($ext, self::$valid_extension)) {
            return false;
        }

        if (!empty($path)) {
            if (!\Joomla\CMS\Filesystem\File::exists($path)) {
                return false;
            }

            $dimensions = getimagesize($path);

            if (!is_array($dimensions)) {
                return false;
            }

            if ((int) $dimensions[0] <= 0 || (int) $dimensions[1] <= 0) {
                return false;
            }
        }

        return true;
    }
}