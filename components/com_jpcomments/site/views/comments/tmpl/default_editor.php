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

$user = Factory::getApplication()->getIdentity();

?>
<div class="card mt-4 comment-editor" id="comment-editor">
    <div class="card-header"><h4 class="m-0 p-0"><?php echo Text::_('COM_JOOMPROJECT_WRITE_COMMENT'); ?></h4></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-1">
                <a href="#">
                    <img class="rounded-circle" width="90" src="<?php echo HTMLHelper::_('joomproject.avatar.path', $user->id);?>" alt="" />
                </a>
            </div>
            <div class="col-md-11">
                <div class="form-group">
                    <textarea id="jform_description" class="form-control" name="jform[description]"></textarea>
                </div>
                <div class="comment-form-actions">
                    <a id="btn_comment_save" class="btn btn-sm btn-info" href="javascript:void(0);">
                        <i class="fas fa-check "></i> <?php echo Text::_('COM_JOOMPROJECT_ACTION_POST_COMMENT'); ?>
                    </a>
                    <a id="btn_comment_cancel" class="btn btn-sm btn-secondary" href="javascript:void(0);">
                        <i class="fas fa-times"></i> <?php echo Text::_('COM_JOOMPROJECT_ACTION_CANCEL'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
