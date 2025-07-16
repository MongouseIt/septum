<?php
/**
 * @package      Joomproject
 * @subpackage   Tasks
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

namespace JoomProject\Patterns;

defined('_JEXEC') or die();


trait Singleton
{

    private static $instance;

    public static function getInstance($params = null)
    {


        if (!isset(self::$instance) && !(self::$instance instanceof self)) {

            self::$instance = new self($params);

        }

        return self::$instance;

    }

}