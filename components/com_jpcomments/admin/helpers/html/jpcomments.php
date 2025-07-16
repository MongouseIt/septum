<?php
/**
 * @package      Joomproject
 * @subpackage   Comments
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


abstract class JHtmlJPcomments
{
    public static function label($count = 0,$complete = 0)
    {
        // if no comments no need to continue;
        if (!$count) {
            return '';
        }

        // if task is complete change classes
        if($complete) {

            $class = "bg-success text-white";

        }else{
            $class = "bg-dark text-white";
        }

        return '<span class="badge p-1 '.$class.'"><i class="fas fa-comment"></i> ' . intval($count) . '</span>';
    }
}