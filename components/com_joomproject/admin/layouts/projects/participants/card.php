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
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Router\Route;

// init
$params     = $displayData['params'];
$data       = $displayData['data'];
$modId      = $displayData['modid'];
$text       = $params->get('userType', 0) == 0 ? 'PARTICIPANTS' : 'ASSIGNED';
$xs_columns = $params->get('xs_columns', 12);
$sm_columns = $params->get('sm_columns', 6);
$md_columns = $params->get('md_columns', 4);
$lg_columns = $params->get('lg_columns', 3);
$xl_columns = $params->get('xl_columns', 3);
$col        = "mb-3 col-xs-$xs_columns col-sm-$sm_columns col-md-$md_columns col-lg-$lg_columns col-xl-$xl_columns";

JLoader::register('JPusersHelperRoute',JPATH_ROOT.'/components/com_jpusers/helpers/route.php');

?>

<?php if ($params->get('userType', 0) == 0 && (!ComponentHelper::isInstalled('com_jpactivities') OR !ComponentHelper::isEnabled('com_jpactivities'))): ?>

    <div class="alert alert-warning">
		<?php echo Text::_('COM_JPPROJECT_COM_JPACTIVITIES_NOT_INSTALLED_OR_ENABLED'); ?>
    </div>
<?php else : ?>
	<?php if (count($data) > 0) : ?>
        <div class="row">
			<?php foreach ($data as $i => $participant) :
				$user = $participant->username;
				$link = "index.php?option=com_jpusers&view=user&id=$participant->id:$user";
				?>
                <div class="<?php echo $col; ?>">
                    <div class="card text-center">
                        <img title="<?php echo $participant->name; ?>"
                             src="<?php echo HTMLHelper::_('joomproject.avatar.path', $participant->id); ?>"
                             class="w-100" data-bs-toggle="tooltip" data-placement="top"/>
                        <div class="card-body">
                            <div class="card-title">
                                <h6>
                                    <a href="<?php echo Route::_(JPusersHelperRoute::getUserRoute($participant->link)); ?>">
                                        <?php echo $participant->name; ?>
                                    </a>
                                </h6>
                            </div>
                        </div>
                    </div>
                </div>
			<?php endforeach; ?>
        </div>
	<?php else : ?>
        <div class="alert alert-warning"><?php echo Text::_('MOD_JPPROJECT_NO_' . $text . '_MATCHING_RESULTS'); ?></div>
	<?php endif; ?>
<?php endif; ?>
