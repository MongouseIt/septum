<?php

/**
 * @package      Joomproject
 * @subpackage   Projects
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;

extract($displayData);
?>
<div class="tf-gallery-manager-edit-modal-content">
    <div class="tf-gallery-manager-edit-modal-content--preview">

    </div>
    <div class="tf-gallery-manager-edit-modal-content--form">
        <div class="tf-gallery-manager-edit-modal-content--form--item">
            <label class="tf-gallery-manager-edit-modal-content--form--item--label"><?php echo Text::_('COM_JOOMPROJECT_GALLERY_MANAGER_EDIT_ALT_FIELD_LABEL'); ?></label>
            <div class="tf-gallery-manager-edit-modal-content--form--item--content">
                <input type="text" name="alt" class="tf-gallery-manager-edit-modal-content--form--item--content--input form-control" value="" />
            </div>
            <div class="tf-gallery-manager-edit-modal-content--form--item--help"><?php echo Text::_('COM_JOOMPROJECT_GALLERY_MANAGER_EDIT_ALT_FIELD_DESC'); ?></div>
        </div>
        <div class="tf-gallery-manager-edit-modal-content--form--item">
            <label class="tf-gallery-manager-edit-modal-content--form--item--label"><?php echo Text::_('COM_JOOMPROJECT_GALLERY_MANAGER_EDIT_POPUP_DESC_FIELD_LABEL'); ?></label>
            <div class="tf-gallery-manager-edit-modal-content--form--item--content">
                <textarea name="caption" class="tf-gallery-manager-edit-modal-content--form--item--content--input form-control" rows="3"></textarea>
            </div>
            <div class="tf-gallery-manager-edit-modal-content--form--item--help"><?php echo Text::_('COM_JOOMPROJECT_GALLERY_MANAGER_EDIT_POPUP_DESC_FIELD_DESC'); ?></div>
        </div>
        <div class="tf-gallery-manager-edit-modal-content--form--item--help divider"><?php echo Text::_('COM_JOOMPROJECT_GALLERY_MANAGER_EDIT_SMART_TAGS_DESC'); ?></div>

        <input type="hidden" class="item_id" />
    </div>
</div>