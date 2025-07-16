<?php
/**
* @package      pkg_joomproject
* @subpackage   com_joomproject
*
 * @author       JoomBoost (eaxs)
* @copyright    Copyright (C) 2012-2019 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Layout\FileLayout;


// load joomproject library
require_once JPATH_ADMINISTRATOR.'/components/com_joomproject/libraries/vendor/autoload.php';

// Include the Joomproject library
if (!defined('JP_LIBRARY')) {
    $lib = JPATH_SITE . '/libraries/joomproject/library.php';

    if (!file_exists($lib)) return '';

    require_once $lib;
}

require_once JPATH_ADMINISTRATOR . '/components/com_jpprojects/helpers/jpprojects.php';

$doc = Factory::getDocument();
$headData = $doc->getHeadData();
$styleSheets = $headData['styleSheets'];
$scripts = $headData['scripts'];
//scripts to remove, customise as required
/*  if(!in_array('/media/com_joomproject/bootstrap/js/bootstrap.js',$scripts)){

  } */



//Text limits params
$titlelimit            = $params->get('recipe_title_max_length', 20);
$desclimit             = $params->get('recipe_desc_max_length',200);


// Swiper params
$effect                = $params->get('effect','slide');
$loop                  = $params->get('loop','true');
$grabcursor            = $params->get('grabcursor','true');
$loopblank             = $params->get('loopblank','true');
$bar                   = $params->get('bar','pagination');
$navbuttons            = $params->get('navbuttons','1');
$item480               = $params->get('item480',1);
$item768               = $params->get('item768',2);
$item1024              = $params->get('item1024',3);



$layout = $str = preg_replace('/[^A-Za-z0-9\. -]/', '', $params->get('layout','default'));
$LayoutCarousel = new FileLayout('projects.participants.carousel',JPATH_ROOT.'/administrator/components/com_joomproject/layouts',array('component' => 'com_joomproject'));
$LayoutList = new FileLayout('projects.participants.list',JPATH_ROOT.'/administrator/components/com_joomproject/layouts',array('component' => 'com_joomproject'));
$LayoutCard = new FileLayout('projects.participants.card',JPATH_ROOT.'/administrator/components/com_joomproject/layouts',array('component' => 'com_joomproject'));


// Include the helper class.
jimport('joomla.application.module.helper');



$data  = JPprojectsHelper::getAssignedUsers($params->get('mode',0),$params->get('userType',0),$params->get('list_limit',10));


// Render the module
require ModuleHelper::getLayoutPath('mod_jp_participants', $params->get('layout', 'default'));
