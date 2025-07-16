<?php
/**
 * @package      Joomproject
 * @subpackage   Milestones
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2006-2012 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('bootstrap.dropdown');
HTMLHelper::_('bootstrap.collapse');

// Create shortcuts to some parameters.
$item    = &$this->item;
$user	 = &$this->user;
$params	 = $item->params;
$canEdit = $item->params->get('access-edit');
$uid	 = $user->get('id');

$nulldate = Factory::getDBO()->getNullDate();

$asset_name = 'com_jpmilestones.milestone.' . $item->id;
$canEdit	= ($user->authorise('core.edit', $asset_name) || $user->authorise('core.edit', $asset_name));
$canEditOwn	= (($user->authorise('core.edit.own', $asset_name) || $user->authorise('core.edit.own', $asset_name)) && $item->created_by == $uid);

$item_css = JoomprojectHelperColor::getItemColor($item->params->get('milestone_color',''));

?>
<div id="joomproject" class="item-page<?php echo $this->pageclass_sfx?> view-milestone">

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

	<div class="page-header">
		<h2><?php echo $this->escape($item->title); ?></h2>
	</div>

    <div class="mb-4">
        <?php echo $this->toolbar;?>
    </div>

    <?php if($item) echo $item->event->afterDisplayTitle; ?>

    <?php echo $item->event->beforeDisplayContent;?>

    <div class="card mb-3">
        <div class="p-3" <?php echo $item_css ?>>
            <ul class="shadow-sm article-info list-group float-md-end m-md-2">
		        <?php if($item->start_date != $nulldate): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
    			<span class="start-title">
    				<?php echo Text::_('JGRID_HEADING_START_DATE');?>:
    			</span>
                        <span class="start-data">
                    <?php echo HTMLHelper::_('jphtml.label.datetime', $item->start_date); ?>
    			</span>
                    </li>
		        <?php endif; ?>
		        <?php if($item->end_date != $nulldate): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
    			<span class="due-title">
    				<?php echo Text::_('JGRID_HEADING_DEADLINE');?>:
    			</span>
                        <span class="due-data">
                    <?php echo HTMLHelper::_('jphtml.label.datetime', $item->end_date); ?>
    			</span>
                    </li>
		        <?php endif;?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
    		<span class="owner-title">
    			<?php echo Text::_('JGRID_HEADING_CREATED_BY');?>:
    		</span>
                    <span class="owner-data">
    			 <?php echo HTMLHelper::_('jphtml.label.author', $item->author, $item->created); ?>
    		</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
            <span class="project-title">
    			<?php echo Text::_('JGRID_HEADING_PROJECT');?>:
    		</span>

                    <span class="project-data">
    			<a href="<?php echo Route::_(JPprojectsHelperRoute::getDashboardRoute($item->project_slug));?>"><?php echo $item->project_title;?></a>
    		</span>
                </li>
		        <?php if ($item->labels) : ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
				<span class="labels-title">
					<?php echo Text::_('COM_JOOMPROJECT_FIELDSET_PROJECT_LABELS'); ?>:
				</span>
                        <span class="labels-data">
					<?php echo HTMLHelper::_('jphtml.label.labels', $item->labels); ?>
				</span>
                    </li>
		        <?php endif; ?>
            </ul>
            <div class="item-description mt-2 mt-sm-2 mt-md-0">
	            <?php echo $this->escape($item->text); ?>
            </div>
        </div>
    </div>

	<?php if (JPApplicationHelper::enabled('com_jprepo') && count($item->attachments)) : ?>
    <div class="card mb-3">
        <div class="card-header"><h4 class="m-0"><?php echo Text::_('COM_JOOMPROJECT_FIELDSET_ATTACHMENTS'); ?></h4></div>
	    <?php echo HTMLHelper::_('jprepo.attachments', $item->attachments); ?>
    </div>
	<?php endif; ?>

	<?php echo $item->event->afterDisplayContent;?>
</div>