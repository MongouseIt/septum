<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jpusers
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Access\Access;


if (count($this->item->children) > 5) {
    $this->item->children = array_slice($this->item->children, 0, 5);
    $this->item->children[] = Text::_('COM_JOOMPROJECT_AND_MORE') . '...';
}
?>
<div class="p-2 mb-4 bg-light" id="alcm-group-<?php echo (int) $this->item->id; ?>">
    <div class="d-flex justify-content-between">
        <a href="javascript:void(0);" onclick="JPform.alcmToggleActions(<?php echo $this->item->id; ?>)" style="font-weight: bold;">
            <?php echo $this->escape($this->item->title); ?>
        </a>
        <button type="button" class="btn btn-small  btn-sm btn-danger" onclick="JPform.alcmRemoveGroup(<?php echo $this->item->id; ?>)">
            <i class="fas fa-times"></i> <?php echo Text::_('COM_JOOMPROJECT_REMOVE'); ?>
        </button>

    </div>

    <?php if (count($this->item->children)) : ?>
        <p class="small">
            <?php echo Text::_('COM_JOOMPROJECT_INCLUDING') . ': '; ?>
            <?php echo implode(', ', $this->item->children); ?>
        </p>
    <?php endif; ?>

    <div id="alcm-actions-<?php echo (int) $this->item->id; ?>" style="display: none;">
        <hr />
        <table class="group-rules table table-striped table-condensed">
            <thead class="thead-dark">
                <tr>
                    <th width="20%"><?php echo Text::_('JLIB_RULES_ACTION'); ?></th>
                    <th width="30%"><?php echo Text::_('JLIB_RULES_SELECT_SETTING'); ?></th>
                    <?php if ($this->item->parent_id > 0) : ?>
                        <th><?php echo Text::_('JLIB_RULES_CALCULATED_SETTING'); ?></th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach($this->item->actions AS $action)
                {
                    $sid   = 'jform_rules_' . $action->name . '_' . $this->item->id;
                    $title = Text::_($action->title);
                    $desc  = htmlspecialchars($title . '::' . Text::_($action->description), ENT_COMPAT, 'UTF-8');
                    $rule  = $this->rules->allow($action->name, $this->item->id);

                    $calculated = Access::checkGroup($this->item->id, $action->name, $this->asset_id);

                    if ($rule === true) {
                        $selected = '1';
                    }
                    elseif ($rule === false) {
                        $selected = '0';
                    }
                    else {
                        $selected = '';
                    }
                    ?>
                    <tr>
                        <td>
                            <label data-bs-toggle="tooltip" for="<?php echo $sid; ?>" title="<?php echo $desc; ?>">
                                <?php echo Text::_($action->title); ?>
                            </label>
                        </td>
                        <td><?php echo $this->getActionHTML($action, $this->component, $selected); ?></td>
                        <?php if ($this->item->parent_id > 0) : ?>
                            <td>
                                <?php echo $this->getCalculated($action, $calculated, $rule); ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>
    <input type="hidden" name="jform[rules][]" value="<?php echo (int) $this->item->id; ?>" />
</div>