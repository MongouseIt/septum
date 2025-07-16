<?php
/**
 * @package      Joomproject
 * @subpackage   Tasks
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
$params  = $item->params;
$canEdit = $item->params->get('access-edit');
$user    = Factory::getApplication()->getIdentity();
$uid     = $user->get('id');

$nulldate = Factory::getDBO()->getNullDate();

$asset_name = 'com_jptasks.task.' . $this->item->id;
$canEdit    = ($user->authorise('core.edit', $asset_name));
$canEditOwn = ($user->authorise('core.edit.own', $asset_name) && $this->item->created_by == $uid);


// task bg color
$task_color = $item->params->get('task_color','');
$taskColor = JoomprojectHelperColor::getItemColor($task_color);


Factory::getDocument()->addScriptDeclaration("
window.onbeforeunload = function() {}
");

$showTaskId = \Joomla\CMS\Component\ComponentHelper::getParams('com_jptasks')->get('show_task_id',0);


?>
<div id="joomproject" class="item-page view-task">
    <div id="jb_template">

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

        <div class="btn-toolbar btn-toolbar-top d-block mb-3">
			<?php echo $this->toolbar; ?>
        </div>

        <div class="page-header">
            <h2 class="<?php echo $item->complete ? 'text-success' : '' ?>">
                <?php if($item->complete): ?>
                <i class="fas fa-check-square text-success"></i>
                <?php endif; ?>
                <?php if(in_array($showTaskId,[1,3]) ): ?>
                    <span class="text-muted">[#<?php echo $item->id ?>]</span>
                <?php endif;?>
                <?php echo $this->escape($item->title); ?>
            </h2>
        </div>

	    <?php echo $item->event->beforeDisplayContent; ?>

       <div class="card p-0 mb-3 overflow-hidden">
           <div class="p-3" <?php echo $taskColor ?>>
               <ul class="shadow-sm article-info list-group float-md-end m-md-2">
                   <li class="list-group-item d-flex justify-content-between align-items-center">
                       <span class="project-title"> <?php echo Text::_('JGRID_HEADING_PROJECT'); ?>:</span>
                       <span class="project-data  ms-1">
                    <a href="<?php echo Route::_(JPprojectsHelperRoute::getDashboardRoute($item->project_slug)); ?>"><?php echo $item->project_title; ?></a>
                </span>
                   </li>

		           <?php if ($item->milestone_id) : ?>
                       <li class="list-group-item d-flex justify-content-between align-items-center">
                           <span class="milestone-title"><?php echo Text::_('JGRID_HEADING_MILESTONE'); ?>:</span>
                           <span class="milestone-data  ms-1">
        			<a href="<?php echo Route::_(JPmilestonesHelperRoute::getMilestoneRoute($item->project_slug, $item->milestone_slug)); ?>"><?php echo $item->milestone_title; ?></a>
        		</span>
                       </li>
		           <?php endif; ?>
		           <?php if ($item->list_id) : ?>
                       <li class="list-group-item d-flex justify-content-between align-items-center">
                           <span class="list-title"><?php echo Text::_('JGRID_HEADING_TASKLIST'); ?>:</span>
                           <span class="list-data ms-1">
        			<a href="<?php echo Route::_(JPtasksHelperRoute::getTasksRoute($item->project_slug, $item->milestone_slug, $item->list_slug)); ?>">
                        <?php echo $item->list_title; ?>
                    </a>
        		</span>
                       </li>
		           <?php endif; ?>
		           <?php if ($item->start_date != $nulldate): ?>
                       <li class="list-group-item d-flex justify-content-between align-items-center">
                           <span class="start-title"><?php echo Text::_('JGRID_HEADING_START_DATE'); ?>:</span>
                           <span class="start-data  ms-1">
        		  <?php echo HTMLHelper::_('jphtml.label.datetime', $item->start_date); ?>
        		</span>
                       </li>

		           <?php endif; ?>
		           <?php if ($item->end_date != $nulldate): ?>
                       <li class="list-group-item d-flex justify-content-between align-items-center">
        		<span class="due-title">
        			<?php echo Text::_('JGRID_HEADING_DEADLINE'); ?>:
        		</span>
                           <span class="due-data  ms-1">
        			<?php echo HTMLHelper::_('jphtml.label.datetime', $item->end_date); ?>
        		</span>
                       </li>
		           <?php endif; ?>
                   <li class="list-group-item d-flex justify-content-between align-items-center">
    		<span class="owner-title">
    			<?php echo Text::_('JGRID_HEADING_CREATED_BY'); ?>:
    		</span>
                       <span class="owner-data ms-1">
    			 <?php echo HTMLHelper::_('jphtml.label.author', $item->author, $item->created); ?>
    		</span>
                   </li>
		           <?php if ($item->users) : ?>
                       <li class="list-group-item d-flex justify-content-between align-items-center">
            <span class="assigned-title">
    			<?php echo Text::_('COM_JOOMPROJECT_FIELDSET_ASSIGNED_USERS'); ?>:
    		</span>
                           <span class="assigned-data ms-1">
    			 <?php echo HTMLHelper::_('jptasks.assignedLabel', $item->id, $item->id, $item->users); ?>
    		</span>
                       </li>
		           <?php endif; ?>

		           <?php if ($item->labels) : ?>
                       <li class="list-group-item d-flex justify-content-between align-items-center">
				<span class="labels-title">
					<?php echo Text::_('COM_JOOMPROJECT_FIELDSET_PROJECT_LABELS'); ?>:
				</span>
                           <span class="labels-data ms-1">
					<?php echo HTMLHelper::_('jphtml.label.labels', $item->labels); ?>
				</span>
                       </li>
		           <?php endif; ?>
               </ul>
	           <div class="item-description mt-2 mt-sm-2 mt-md-0">
		           <?php echo $item->text; ?>
               </div>

               <div class="clearfix"></div>
           </div>
       </div>
	    <?php if (JPApplicationHelper::enabled('com_jprepo') && count($item->attachments)) : ?>
            <div class="card mb-3">
                <div class="card-header"><h4 class="m-0"><?php echo Text::_('COM_JOOMPROJECT_FIELDSET_ATTACHMENTS'); ?></h4></div>
			    <?php echo HTMLHelper::_('jprepo.attachments', $item->attachments); ?>
            </div>
	    <?php endif; ?>

		<?php echo $item->event->afterDisplayContent; ?>
    </div>
</div>