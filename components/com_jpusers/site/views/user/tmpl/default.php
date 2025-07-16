<?php
/**
 * @package      Joomproject
 * @subpackage   Users
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2006-2012 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Helper\ModuleHelper;

HTMLHelper::_('bootstrap.dropdown');
HTMLHelper::_('bootstrap.collapse');
$item    = &$this->item;
$user    = Factory::getApplication()->getIdentity();
$access  = JPusersHelper::getActions();
$params  = ComponentHelper::getParams('com_joomproject');
$cfg_img = $params->get('user_profile_avatar');

?>
<div id="joomproject" class="category-list<?php echo $this->pageclass_sfx; ?> view-user">

	<?php
	// load internal navigation
	echo JPhtmlNav::loadMain();
	?>

    <?php
    // load header
    echo JPhtmlNav::loadHeader($this->params);
    ?>

	<?php
	// load project internal navigation
	echo JPhtmlNav::loadProject();
	?>

    <div class="cat-items">

        <form id="item-form" name="adminForm" method="post"
              action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>" enctype="multipart/form-data">




            <div class="mb-4">
				<?php echo HTMLHelper::_('jphtml.project.filter'); ?>
				<?php if ($item) echo $item->event->afterDisplayTitle; ?>
            </div>

            <div class="clearfix"></div>

			<?php if ($item) echo $item->event->beforeDisplayContent; ?>
            <ul class="list-unstyled">
                <li class="media">
                    <div class="float-start">
						<?php if (($user->id == $item->id || $access->get('core.admin')) && empty($cfg_img) && !defined('JPDEMO')) : ?>
                            <img alt="<?php echo $this->escape($this->item->name); ?>"
                                 src="<?php echo HTMLHelper::_('joomproject.avatar.path', $item->id); ?>"
                                 class="d-block thumbnail me-3"
                                 style="cursor: pointer;"
                                 width="110"
                                 onclick="jQuery('#avatar-file').click();"
                            />
                            <button class="button btn btn-sm btn-danger mt-2"
                                    onclick="Joomla.submitform('user.deleteAvatar', document.getElementById('item-form'))">
                                <i class="fas fa-times"></i> <?php echo Text::_('JACTION_DELETE_IMAGE'); ?>
                            </button>

                            <div style="display: none;">
                                <input type="file" name="avatar" id="avatar-file" class="mt-4"
                                       onchange="Joomla.submitform('user.avatar', document.getElementById('item-form'))"/>
                            </div>
						<?php else : ?>
                            <img alt="<?php echo $this->escape($this->item->name); ?>"
                                 src="<?php echo HTMLHelper::_('joomproject.avatar.path', $item->id); ?>"
                                 class="thumbnail me-3"
                                 width="110"
                            />
						<?php endif; ?>
                    </div>
                    <div class="media-body">
                        <h5 class="mt-0 mb-3"><?php echo $this->escape($this->item->name); ?></h5>
                        <ul class="list-group list-group-flush">
                            <li class="username-item list-group-item">
                                <i class="fas fa-user" data-bs-toggle="tooltip"
                                   title="<?php echo Text::_('COM_JOOMPROJECT_USER_USERNAME'); ?>"></i> <?php echo $this->escape($this->item->username); ?>
                            </li>
							<?php if ($this->item->registerDate != Factory::getDBO()->getNullDate()): ?>
                                <li class="regdate-item list-group-item">
                                    <i class="fas fa-calendar" data-bs-toggle="tooltip" data-placement="top"
                                       title="<?php echo Text::_('COM_JOOMPROJECT_USER_REG_DATE'); ?>"></i> <?php echo HTMLHelper::_('date', $this->item->registerDate, $this->escape($this->params->get('date_format', Text::_('DATE_FORMAT_LC1')))); ?>
                                </li>
							<?php endif; ?>
							<?php if ($this->item->lastvisitDate != Factory::getDBO()->getNullDate()): ?>
                                <li class="visitdate-item list-group-item">
                                    <i class="fas fa-calendar" data-bs-toggle="tooltip" data-placement="top"
                                       title="<?php echo Text::_('COM_JOOMPROJECT_USER_VISIT_DATE'); ?>"></i> <?php echo HTMLHelper::_('date', $this->item->lastvisitDate, $this->escape($this->params->get('date_format', Text::_('DATE_FORMAT_LC1')))); ?>
                                </li>
							<?php endif; ?>
                        </ul>
                    </div>
                </li>


                <input type="hidden" name="task" value=""/>
                <input type="hidden" name="id" value="<?php echo (int) $item->id; ?>"/>
                <input type="hidden" name="view"
                       value="<?php echo htmlspecialchars($this->get('Name'), ENT_COMPAT, 'UTF-8'); ?>"/>
				<?php echo HTMLHelper::_('form.token'); ?>
        </form>

        <!-- Begin Dashboard Modules -->
		<?php if (count(ModuleHelper::getModules('jp-user-top'))) : ?>
            <div class="row-fluid">
                <div class="span12">
					<?php echo $this->modules->render('jp-user-top', array('style' => 'xhtml'), null); ?>
                </div>
            </div>
		<?php endif; ?>
		<?php if (count(ModuleHelper::getModules('jp-user-left')) || count(ModuleHelper::getModules('jp-user-right'))) : ?>
            <div class="row-fluid">
                <div class="span6">
					<?php echo $this->modules->render('jp-user-left', array('style' => 'xhtml'), null); ?>
                </div>
                <div class="span6">
					<?php echo $this->modules->render('jp-user-right', array('style' => 'xhtml'), null); ?>
                </div>
            </div>
		<?php endif; ?>
		<?php if (count(ModuleHelper::getModules('jp-user-bottom'))) : ?>
            <div class="row-fluid">
                <div class="span12">
					<?php echo $this->modules->render('jp-user-bottom', array('style' => 'xhtml'), null); ?>
                </div>
            </div>
		<?php endif; ?>
        <!-- End Dashboard Modules -->

		<?php if ($item) echo $item->event->afterDisplayContent; ?>

    </div>
</div>