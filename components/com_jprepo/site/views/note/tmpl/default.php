<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jprepo
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
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
$params	 = $item->params;
$canEdit = $item->params->get('access-edit');
$user	 = Factory::getApplication()->getIdentity();
$uid	 = $user->get('id');

$asset_name = 'com_jprepo.note.'.$this->item->id;
$canEdit	= ($user->authorise('core.edit', $asset_name));
$canEditOwn	= ($user->authorise('core.edit.own', $asset_name) && $this->item->created_by == $uid);
?>
<div id="joomproject" class="item-page view-task">

	<?php
	// load internal navigation
	echo JPhtmlNav::loadMain();
	?>

	<?php
	// load header
	echo JPhtmlNav::loadHeader($this->params);
	?>

    <div class="page-header">
	    <h2><?php echo $this->escape($item->title); ?></h2>
	</div>

	<?php
	echo JPhtmlNav::loadProject();
	?>

    <div class="card mb-3">
        <div class="p-3">
            <ul class="shadow-sm list-group float-md-end m-md-2">
                <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="project-title">
		                    <?php echo Text::_('JGRID_HEADING_PROJECT');?>:
                        </span>
                    <span class="project-data ms-1">
                            <a href="<?php echo Route::_(JPprojectsHelperRoute::getDashboardRoute($item->project_slug));?>"><?php echo $item->project_title;?></a>
                        </span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="owner-title">
		                    <?php echo Text::_('JGRID_HEADING_CREATED_BY');?>:
                        </span>
                    <span class="owner-data">
		                    <?php echo $this->escape($this->item->author);?>
                        </span>
                </li>
            </ul>
            <div class="item-description mt-2 mt-sm-2 mt-md-0">
		        <?php echo $item->text; ?>
            </div>
        </div>
    </div>

	<div class="actions btn-toolbar">
		<div class="btn-group">
			<?php if(($canEdit || $canEditOwn) && !$this->rev) : ?>
			   <a class="btn" href="<?php echo Route::_('index.php?option=com_jprepo&task=noteform.edit&id='.intval($item->id).':'.$item->alias);?>">
			       <i class="fas fa-edit"></i> <?php echo Text::_('COM_JOOMPROJECT_ACTION_EDIT');?>
			   </a>
			<?php endif; ?>

            <?php echo $item->event->afterDisplayTitle;?>
		</div>
	</div>

    <?php echo $item->event->beforeDisplayContent;?>

	<div class="item-description">

	</div>
	<hr />

    <?php echo $item->event->afterDisplayContent;?>

</div>