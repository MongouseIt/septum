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
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Component\ComponentHelper;
use JoomProject\Permission\GlobalAccess;

$input = Factory::getApplication()->input;
$gparams = ComponentHelper::getParams('com_joomproject');

if ($gparams->get('fullscreen_mode',0))
{
    $fullscreenCSS = <<<css
body{
    padding: 20px !important;
    margin: 0px !important;
}

joomla-alert{
margin-bottom: 10px !important;
}


css;

    \Joomla\CMS\Factory::getDocument()->addStyleDeclaration($fullscreenCSS);
}

$view = $input->get('view', '', 'word');
$com  = $input->get('option', '', 'string');

$userLink = JPusersHelperRoute::getUserRoute(Factory::getApplication()->getIdentity()->id);

// get list of allowed components list can user access and see
$components = GlobalAccess::allowedMainComponents();


// load module language file of dash buttons
$language = Factory::getLanguage();
$language->load('mod_jp_dash_buttons',JPATH_BASE,$language->getTag(),true,true);

$buttons = JPhtmlNav::getButtons();

// clear remove components without buttons
foreach ($buttons as $bkey => $button){

    if(count($button) == 0)
        unset($buttons[$bkey]);

}

?>
<div class="jp-internal-nav mb-3">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm border rounded px-2 align-items-start justify-content-between">

        <div>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown"
                    aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>




            <?php if(count($components) > 0): ?>
                <div class="collapse navbar-collapse order-4 order-sm-4 order-md-0" id="navbarNavDropdown">
                    <ul class="navbar-nav m-0">
                        <?php foreach ($components as $component): ?>

                            <?php
                            // reset to all projects
                            $url = Route::_($component['link'].'&filter_project=0');

                            ?>

                                <li class="nav-item <?php echo $view == $component['view'] && $com == $component['com'] ? 'active' : '' ?>">
                                    <a class="nav-link" href="<?php echo $url ?>">
                                        <i class="<?php echo $component['icon'] ?>"></i> <?php echo Text::_($component['title']) ?>
                                    </a>
                                </li>


                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>


        <div class="d-flex order-0 order-sm-0 order-md-1">

            <?php if(count($buttons) > 0): ?>
                <div class="dropdown me-2 d-inline-block">
                    <button class="btn btn-success btn-sm dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <?php echo Text::_('COM_JOOMPROJECT_ACTION_NEW') ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownMenuLink">

                        <?php foreach($buttons AS $component => $btns) : ?>

                            <?php if(count($btns) > 1): ?>
                                <div class="dropdown-divider"></div>
                            <?php endif; ?>

                            <?php foreach ($btns AS $button) : ?>
                                <li><a class="dropdown-item" href="<?php echo $button['link'] ?>">
                                        <i class="<?php echo $button['iconClass'] ?>"></i> <?php echo Text::_($button['title']) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>

                            <?php if(count($btns) > 1): ?>
                                <div class="dropdown-divider"></div>
                            <?php endif; ?>


                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="inline-block">
                <a href="<?php echo $userLink ?>" title="<?php echo Factory::getApplication()->getIdentity()->name ?>">
                    <img title="<?php echo Factory::getApplication()->getIdentity()->name; ?>"
                         src="<?php echo HTMLHelper::_('joomproject.avatar.path', Factory::getApplication()->getIdentity()->id); ?>"
                         class="rounded"
                         style="min-width: 30px; width: 30px;"
                         data-bs-toggle="tooltip" data-placement="top"
                    />
                </a>
            </div>
        </div>

    </nav>
</div>