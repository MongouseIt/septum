<?php
/**
* @package		JoomProject
* @copyright	2013-2019 JoomBoost, joomboost.com
* @license		GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
extract($displayData);

$title = isset($countTitle) ? $countTitle : $title;

$randId = rand(0, 1000);


?>
<style>

    #count-card-<?php echo $randId ?> i.countCardIcon {

        position: absolute;
        bottom: 0;
        right: -2px;
        z-index: 9;
        opacity: 0.4;
        transform: rotate(-10deg);

    }

</style>
<div id="count-card-<?php echo $randId ?>" class="col-6 col-sm-6 col-md-3 col-lg-2 mb-2 position-relative">
    <div class="text-white shadow-sm border-0 card p-3 overflow-hidden <?php echo $iconClass ?> h-100">

            <i class="countCardIcon text-white fas fa-<?php echo $iconName ?> fa-5x"></i>
            <div>
                <a class="text-decoration-none" href="<?php echo $link; ?>&filter_published=*" title="<?php echo Text::_($title) ?>">
                <div class="d-inline-block <?php echo $countClass ?>  w-100">
                    <h2 class="m-0 text-white d-inline"><?php echo $count; ?></h2>
                    <h4 class="m-0 text-white font-weight-light text-truncate d-inline"><?php echo Text::_($title) ?></h4>
                </div>
                </a>

                <?php if (isset($countByState)): ?>
                    <div class="mt-3" >
                        <a class="text-decoration-none" href="<?php echo $link; ?>&filter_published=1">
                            <span title="<?php echo Text::_('JPUBLISHED') ?>" class="d-inline-block text-success shadow-sm border-0 rounded-pill py-1 px-2 bg-white"><i class="fas fa-check"></i> <?php echo $countByState['published'] ?></span>
                        </a>
                        <a class="text-decoration-none" href="<?php echo $link; ?>&filter_published=0">
                            <span title="<?php echo Text::_('JUNPUBLISHED') ?>" class="d-inline-block text-danger shadow-sm border-0 rounded-pill py-1 px-2 bg-white"><i
                                        class="fas fa-eye-slash"></i> <?php echo $countByState['unpublished'] ?></span>
                        </a>
                        <a class="text-decoration-none" href="<?php echo $link; ?>&filter_published=2">
                            <span title="<?php echo Text::_('JARCHIVED') ?>" class="d-inline-block text-info shadow-sm border-0 rounded-pill py-1 px-2 bg-white"><i
                                        class="fas fa-archive"></i> <?php echo $countByState['archived'] ?></span>
                        </a>
                        <a class="text-decoration-none" href="<?php echo $link; ?>&filter_published=-2">
                            <span title="<?php echo Text::_('JTRASHED') ?>" class="d-inline-block text-dark shadow-sm border-0 rounded-pill py-1 px-2 bg-white"><i
                                        class="fas fa-trash"></i> <?php echo $countByState['trashed'] ?></span>
                        </a>
                    </div>
                <?php endif; ?>



            </div>

    </div>

</div>