<?php
/**
* @package      Joomproject Latest comments module
*
* @author       JoomBoost
* @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Helper\ModuleHelper;


// load joomproject library
require_once JPATH_ADMINISTRATOR.'/components/com_joomproject/libraries/vendor/autoload.php';

if (!jimport('joomproject.framework')) {
    echo 'JoomProject lib not installed';
    return;
}

if (!JPApplicationHelper::exists('com_joomproject')) {
    echo 'JoomProject not installed';
    return;
}

require_once dirname(__FILE__) . '/helper.php';

// don't display on edit form
$layout = \Joomla\CMS\Factory::getApplication()->input->get('layout', '');
if($layout == 'edit')
    return;

$items = modJPlatestCommentsHelper::getItems($params);

if (empty($items)) return;



// Include layout
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx',''));
require \Joomla\CMS\Helper\ModuleHelper::getLayoutPath('mod_jp_latest_comments', $params->get('layout', 'default'));
