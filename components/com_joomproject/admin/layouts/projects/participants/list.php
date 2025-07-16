<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_joomproject
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2019 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

# No Permission
defined( '_JEXEC' ) or die ;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Router\Route;
// init

$params     = $displayData['params'];
$data       = $displayData['data'];
$modId      =  $displayData['modid'];
$text = $params->get('userType',0) == 0 ? 'PARTICIPANTS' : 'ASSIGNED';

JLoader::register('JPusersHelperRoute',JPATH_ROOT.'/components/com_jpusers/helpers/route.php');

?>
<?php if($params->get('userType',0) == 0 && (!ComponentHelper::isInstalled('com_jpactivities') OR !ComponentHelper::isEnabled('com_jpactivities'))): ?>

    <div class="alert alert-warning">
        <?php echo Text::_('COM_JPPROJECT_COM_JPACTIVITIES_NOT_INSTALLED_OR_ENABLED'); ?>
    </div>
<?php else : ?>
<?php  if (count($data) > 0 ) : ?>
    <ul id="jpItems-<?php echo $modId; ?>" class="mb-4 list-group list-group-horizontal">
         <?php foreach ($data as $i => $participant) :
             $user = $participant->username;
             $link = "index.php?option=com_jpusers&view=user&id=$participant->id:$user";

             ?>
       <li class="list-group-item">
           <a style="background-color:<?php echo $participant->backgroundColor;?>"
              title="<?php echo $participant->name;?>" data-bs-toggle="tooltip" data-placement="top"
              class="img-users shadow-sm rounded-circle text-decoration-none text-center d-inline-block text-white"
              href="<?php echo Route::_(JPusersHelperRoute::getUserRoute($participant->link));?>">
               <?php echo $participant->img ?>
           </a>
           <span class="d-inline-block ms-2">
               <a href="<?php echo Route::_(JPusersHelperRoute::getUserRoute($participant->link));?>">
                   <?php echo $participant->name; ?>
               </a>
           </span>
       </li>
        <?php endforeach; ?>
    </ul>
<?php else : ?>
    <div class="alert alert-warning"><?php echo Text::_('MOD_JPPROJECT_NO_'.$text.'_MATCHING_RESULTS'); ?></div>
<?php endif; ?>
<?php endif; ?>
