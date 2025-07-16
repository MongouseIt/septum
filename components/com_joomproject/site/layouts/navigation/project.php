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

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;
use JoomProject\Permission\GlobalAccess;

defined('_JEXEC') or die;

// inits
$input = Factory::getApplication()->input;
$project_id = JPApplicationHelper::getActiveProjectId();
$cParams = ComponentHelper::getParams('com_joomproject');
$components = $project_id > 0 ? GlobalAccess::allowedProjectComponents($project_id) : [];

// get input values
$view = $input->get('view', '', 'word');
$com = $input->get('option', '', 'string');

?>
<div id="projectSwitcherContainer">
    <form
            action="<?php echo htmlspecialchars(Route::_(Uri::getInstance()->toString())); ?>"
            method="post"
            name="projectSwitcherForm"
            id="formSwitcherForm"
    >
        <div class="my-3 p-3 rounded border clearfix">
            <?php if ($project_id > 0): // if project selected ?>
                <div class="projectSwitcher float-end">
                    <div class="mt-0 mb-1"><?php echo HTMLHelper::_('jphtml.project.filter', 0, true, true); ?></div>
                    <div class="text-muted">
                        <small>
                            <i class="fas fa-sync-alt"></i> <?php echo Text::_('COM_JOOMPROJECT_SWITCH_PROJECT') ?>
                        </small>
                    </div>
                </div>
                <div>
                    <h2 class="mt-0 mb-1 border-0 justify-content-between align-items-center d-inline-flex">
                        <small
                                class="hasTooltip me-2"
                                title="<?php echo Text::_('COM_JOOMPROJECT_ACTIVE_PROJECT') ?>"
                                data-bs-original-title="<?php echo Text::_('COM_JOOMPROJECT_ACTIVE_PROJECT') ?>"
                                data-bs-toggle="tooltip"><svg width="40" height="40" viewBox="0 0 40 40">
                                <circle cx="20" cy="20" r="6" fill="#4ade80">
                                    <animate attributeName="opacity"
                                             dur="2s"
                                             values="1;0"
                                             repeatCount="indefinite"/>
                                    <animate attributeName="r"
                                             dur="2s"
                                             values="6;18"
                                             repeatCount="indefinite"/>
                                </circle>
                                <circle cx="20" cy="20" r="6" fill="#4ade80"/>
                            </svg>
                        </small>
                        <?php echo JPApplicationHelper::getActiveProjectTitle(); ?>
                    </h2>
                    <div>
                        <?php $activeProjectInfo = JPApplicationHelper::getActiveProjectInfo(); ?>


                        <?php if ($cParams->get('internal_project_nav_show_startend_dates', 1)): ?>

                            <small>
                                <?php if (isset($activeProjectInfo['startDate'])): ?>
                                    <?php echo Text::_('JGRID_HEADING_START_DATE'); ?>: <?php echo HTMLHelper::_('jphtml.label.datetime', $activeProjectInfo['startDate']); ?>
                                <?php endif; ?>

                                <?php if (isset($activeProjectInfo['endDate'])): ?>
                                    <?php echo Text::_('JGRID_HEADING_DEADLINE'); ?>: <?php echo HTMLHelper::_('jphtml.label.datetime', $activeProjectInfo['endDate']); ?>
                                <?php endif; ?>
                            </small>

                        <?php endif; ?>
                    </div>
                </div>
            <?php else:  // for all projects?>
                <div class="projectSwitcher">
                    <div class="mt-0 mb-1">
                        <?php echo HTMLHelper::_('jphtml.project.filter', 0, true, true); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
    <?php if ($project_id > 0): // if project selected?>
        <ul class="nav nav-tabs mb-4 m-0">
            <?php foreach ($components as $component): ?>
                <?php if (ComponentHelper::isEnabled($component['com'])): ?>
                    <li class="nav-item">

                        <?php
                        if (
                            (is_array($component['view']) && in_array($view, $component['view']) && $com == $component['com']) ||
                            ($view == $component['view'] && $com == $component['com'] && $com == $component['com'])
                        ) {
                            $active = 'active';
                        } else {
                            $active = '';
                        }
                        ?>

                        <a class="nav-link <?php echo $active ?>"
                           href="<?php echo Route::_($component['link']) ?>">
                            <i class="<?php echo $component['icon'] ?>"></i> <?php echo Text::_($component['title']) ?>
                            <?php if (isset($component['count'])): ?>
                                <span class="badge bg-light border text-muted">
                                <?php echo $component['count'] ?>
                            </span>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>