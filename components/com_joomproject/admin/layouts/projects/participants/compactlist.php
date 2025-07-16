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
extract($displayData);

$text_type =  !$params->get('userType',0) ? 'PARTICIPANTS' : 'ASSIGNED';


$userCount = $params->get('numberUser',10);
$displayCount     = count($data) - $userCount;

$activitiesEnabled = (ComponentHelper::isInstalled('com_jpactivities') && ComponentHelper::isEnabled('com_jpactivities')) ? true : false;

JLoader::register('JPusersHelperRoute',JPATH_ROOT.'/components/com_jpusers/helpers/route.php');

?>
 <?php if(!$params->get('userType',0) && !$activitiesEnabled): ?>
    <div class="alert alert-warning">
        <?php echo Text::_('COM_JPPROJECT_COM_JPACTIVITIES_NOT_INSTALLED_OR_ENABLED'); ?>
    </div>
<?php else : ?>
        <?php  if (count($data) > 0 ) : ?>
            <ul id="jpItems-<?php echo $modId; ?>" class="list-group list-group-horizontal">
                <?php
                foreach ($data as $i => $participant) : ?>

                <?php $bg = !$participant->hasImage ? 'background-color: '.$participant->backgroundColor : '' ?>

                    <li class="list-group-item border-0 text-center px-0 bg-transparent">
                        <a style="<?php echo $bg ?>"
                           title="<?php echo $participant->name;?>" data-bs-toggle="tooltip" data-placement="top"
                           class="img-users shadow-sm rounded-circle text-decoration-none  d-inline-block text-white"
                           href="<?php echo Route::_(JPusersHelperRoute::getUserRoute($participant->link));?>">
                            <?php echo $participant->img ?>
                        </a>
                    </li>

                    <?php
                    if($i == $userCount - 1)
                        break;
                endforeach; ?>
                <?php if( $displayCount > 0): ?>
                    <li class="list-group-item border-0 text-center px-0 bg-transparent d-flex align-items-center">
                        <a
                                href="#"
                                data-ua-open="izimodal<?php echo $modId ?>"
                                class="btn-user-izimodal p-2"
                                data-title-user="<?php echo Text::_("MOD_JPPROJECT_{$text_type}_USERS") ?>"
                                style="cursor: pointer">
                            <i class="fas fa-plus"></i> <?php  echo $displayCount ?>
                            <?php echo Text::_('COM_JPPROJECTS_MORE');?>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        <?php else : ?>
            <small class="d-block text-muted"><?php echo Text::_('MOD_JPPROJECT_NO_'.$text_type.'_MATCHING_RESULTS'); ?></small>
        <?php endif; ?>
<?php endif; ?>

<?php if(!empty($data)): ?>
    <div id="izimodal<?php echo $modId?>" style="display: none">
        <div id="joomproject">
            <table class="table table-striped modaluser-assigned">
                <thead>
                <tr>
                    <th><?php echo Text::_('JGRID_HEADING_TITRE') ?></th>
                    <th><?php echo Text::_('COM_JOOMPROJECT_FIELD_EMAIL_LABEL') ?></th>
                </tr>
                </thead>
                <tbody>
				<?php foreach ($data as  $i => $participant) :?>
                    <tr>
                        <td>
                            <a style="background: <?php echo $participant->backgroundColor;?>"
                               title="<?php echo $participant->name;?>"
                               data-bs-toggle="tooltip"
                               data-placement="top"
                               class="img-users text-center text-white shadow-sm rounded-circle text-decoration-none d-inline-block"
                               href="<?php echo Route::_(JPusersHelperRoute::getUserRoute($participant->link));?>">
								<?php echo $participant->img ?>
                            </a>
                            <span style="margin-left: 20px"><?php echo $participant->name;?></span>
                        </td>
                        <td>
							<?php echo $participant->email;?>
                        </td>
                    </tr>
				<?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>



