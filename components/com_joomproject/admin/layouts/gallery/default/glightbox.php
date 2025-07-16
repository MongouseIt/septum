<?php

/**
 * @package      Joomproject
 * @subpackage   Projects
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;

extract($displayData);

    HTMLHelper::stylesheet('com_joomproject/glightbox.min.css', ['relative' => true, 'version' => 'auto']);
    HTMLHelper::script('com_joomproject/glightbox.min.js', ['relative' => true, 'version' => 'auto']);
