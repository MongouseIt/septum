<?php
/**
 * @package      Joomproject
 * @subpackage   Comments
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2006-2012 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

extract($displayData);

$access = JPcommentsHelper::getActions();
$user = Factory::getApplication()->getIdentity();
$uid = $user->get('id');


$can_create = $access->get('core.create');
$can_trash = ($access->get('core.edit.state') || ($access->get('core.edit.own') && $item->created_by == $uid));

$isChild = $item->level > 1 ? true : false;

$showActions = isset($actions) ? $actions : true;

?>
<div class="overflow-hidden <?php echo $isChild ? 'p-4' : 'bg-light shadow-sm p-3 border-bottom'  ?>"
    id="comment-item-<?php echo $item->id; ?>"
>
    <div class="d-flex bg-light p-2 rounded">
        <a class="<?php echo $isChild ? 'childComment' : ''  ?> position-relative" href="#"><img
                class="rounded  me-3" width="70"
                src="<?php echo HTMLHelper::_('joomproject.avatar.path', $item->created_by); ?>" alt=""/></a>
        <div class="comment-content  w-100 ms-2">
            <h5 class="mb-1"><?php echo $item->author_name; ?></h5>
            <div class="text-body mb-4">
                <?php
                echo nl2br($item->description);
                ?>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-between align-items-center">
        <small class="text-muted "><?php echo HTMLHelper::date($item->created); ?></small>

        <?php if(isset($item->itemLink)): ?>
            <a class="float-end" href="<?php echo $item->itemLink ?>#comments"><i class="<?php echo $item->itemIcon ?>"></i> <?php echo $item->title ?></a>
        <?php endif; ?>
        <?php if($showActions): ?>
        <div class="comment-item-actions">
            <?php if ($can_create) : ?>
                <a class="btn-add-reply btn btn-link" data-comment-id="<?php echo $item->id; ?>" href="javascript:void(0)">
                    <small>
                        <i class="fas fa-reply"></i> <?php echo Text::_('COM_JOOMPROJECT_ACTION_REPLY'); ?>
                    </small>
                </a>
            <?php endif; ?>
            <?php if ($can_trash) : ?>
                <a class="btn btn-sm  btn-danger btn-trash-reply px-1 py-0"   data-comment-id="<?php echo $item->id; ?>" href="javascript:void(0);">
                    <small>
                        <i class="fas fa-times"></i> <?php echo Text::_('COM_JOOMPROJECT_ACTION_DELETE'); ?>
                    </small>
                </a>
                <div style="display: none !important;">
                    <?php echo HTMLHelper::_('grid.id', $item->id, $item->id); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
