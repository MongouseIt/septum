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
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Access\Access;


if (count($this->item->children) > 5)
{
    $this->item->children   = array_slice($this->item->children, 0, 5);
    $this->item->children[] = Text::_('COM_JOOMPROJECT_AND_MORE') . '...';
}

$backend = Factory::getApplication()->isClient('administrator');

$rule_group_id = $this->item->id;
if (!$rule_group_id) $rule_group_id = $this->item->parent_id;
?>
<style>

    #joomproject .nav-tabs .nav-link{
        display: block !important;
    }

    .select2-choices{
        margin: 0px !important;
        border: none !important;
    }


</style>
<div class="p-3 mb-4 card" id="alcm-group-<?php echo (int) $this->item->id; ?>">
    <h3 class="d-flex justify-content-between align-items-center mb-0">
        <a href="javascript:void(0);" onclick="JPform.alcmToggleActions(<?php echo $this->item->id; ?>,this)"
           style="font-weight: bold;">
            <i class="fas fa-caret-square-up"></i> <?php echo $this->escape($this->item->title); ?>

        </a>
        <?php if ($this->item->id > 0) : ?>
            <button type="button" class="btn btn-sm btn-danger"
                    onclick="JPform.alcmRemoveGroup(<?php echo $this->item->id; ?>)">
                <i class="fas fa-times"></i> <?php echo Text::_('COM_JOOMPROJECT_REMOVE'); ?>
            </button>
        <?php endif; ?>
    </h3>

    <?php if (count($this->item->children)) : ?>
        <p class="d-block text-muted mt-1 mb-1">
            <?php echo Text::_('COM_JOOMPROJECT_INCLUDING') . ': '; ?>
            <?php echo implode(', ', $this->item->children); ?>
        </p>
    <?php endif; ?>

    <div id="alcm-actions-<?php echo (int) $this->item->id; ?>" style="display: none;">
        <hr/>
        <?php if (!in_array($this->item->id, $this->public_groups)) : ?>

            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="m-0"><?php echo Text::_('COM_JOOMPROJECT_MANAGE_GROUP_MEMBERS'); ?></h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">

                            <div class="form-group">
                                <label for="add_user_group_<?php echo (int) $this->item->id; ?>"><?php echo Text::_('COM_JOOMPROJECT_ADD_USERS'); ?></label>
                                <input type="hidden"
                                       id="add_user_group_<?php echo (int) $this->item->id; ?>"
                                       class="form-control form-control-sm p-0"
                                       size="80"
                                       name="jform[add_groupuser][<?php echo (int) $this->item->id; ?>]"

                                />
                            </div>

                        </div>
                        <?php if ($this->item->id > 0) : ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="rm_user_group_<?php echo (int) $this->item->id; ?>"><?php echo Text::_('COM_JOOMPROJECT_REMOVE_USERS'); ?></label>

                                    <input
                                            type="hidden"
                                            id="rm_user_group_<?php echo (int) $this->item->id; ?>"
                                            class="form-control form-control-sm p-0"
                                            size="80"
                                            name="jform[rm_groupuser][<?php echo (int) $this->item->id; ?>]"
                                    />
                                </div>

                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>



        <?php endif; ?>
        <div class="card">
            <div class="card-header">
                <h5 class="m-0">
                    <?php echo Text::_('COM_JOOMPROJECT_MANAGE_GROUP_PERMISSIONS'); ?>
                </h5>
            </div>

            <div class="card-body">
                <?php echo HTMLHelper::_('uitab.startTabSet', 'nav-tab-'.$this->item->id, array('active' => 'com_jpprojects-1')); ?>

                        <?php foreach ($this->item->actions as $com => $actions): ?>
                            <?php


                            $com_title = str_replace('JoomProject','',Text::_($com));

                            if (strpos($com_title, '-') !== false)
                            {
                                $tmp       = explode('-', $com_title);
                                $com_title = trim($tmp[1]);
                            }



                            ?>
                            <?php echo HTMLHelper::_('uitab.addTab', 'nav-tab-'.$this->item->id, $com.'-'.$this->item->id, $com_title); ?>
                        <?php
                            $com_title = Text::_($com);


                            if (strpos($com_title, '-') !== false)
                            {
                            $tmp       = explode('-', $com_title);
                            $com_title = trim($tmp[1]);
                            }

                            $asset_id = $this->getComponentProjectAssetId($com, $this->project_id);
                            $rules    = $this->getAssetRules($com, $asset_id);
                            ?>
                            <table class="group-rules table table-striped table-borderless">
                                <thead>
                                <thead>
                                <tr>
                                    <th width="20%"><?php echo Text::_('JLIB_RULES_ACTION'); ?></th>
                                    <th width="30%"><?php echo Text::_('JLIB_RULES_SELECT_SETTING'); ?></th>
                                    <?php if ($this->item->parent_id > 0) : ?>
                                        <th class="d-none d-sm-table-cell"><?php echo Text::_('JLIB_RULES_CALCULATED_SETTING'); ?></th>
                                    <?php endif; ?>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($actions as $action)
                                {
                                    $sid   = 'jform_rules_' . $action->name . '_' . $this->item->id;
                                    $title = Text::_($action->title);
                                    $desc  = htmlspecialchars($title . '::' . Text::_($action->description), ENT_COMPAT, 'UTF-8');

                                    $rule       = $rules->allow($action->name, $rule_group_id);

                                    $calculated = Access::checkGroup($rule_group_id, $action->name, $asset_id);

                                    /*
									// get parent id of rule group (example: get "registered" group id if rule group is "author")
                                    $parentRuleGroupId =  $this->getParentGroupId($rule_group_id);

                                    // recheck parent of rule group
                                    if(is_null($calculated) && $parentRuleGroupId > 0){
	                                    $calculated = Access::checkGroup($parentRuleGroupId, $action->name, $com);
                                    }
                                    */

                                    if ($rule === true)
                                    {
                                        $selected = '1'; // allowed
                                    }
                                    elseif ($rule === false)
                                    {
                                        $selected = '0'; // not allowed
                                    }
                                    else
                                    {
                                        $selected = ''; // inherit
                                        // if action is view, get inherit automatically
                                        if($action->name == 'core.view') $selected = '1';

                                    }

                                    ?>
                                    <tr>
                                        <td>
                                            <label data-bs-toggle="tooltip" for="<?php echo $sid; ?>"
                                                   title="<?php echo $desc; ?>">
                                                <?php echo Text::_($action->title); ?>
                                            </label>
                                        </td>
                                        <td>
                                            <?php echo $this->getActionHTML($action, $com, $selected); ?>
                                        </td>
                                        <?php if ($this->item->parent_id > 0) : ?>
                                            <td class="d-none d-sm-table-cell">
                                                <?php if($action->name != 'core.view'): ?>
                                                    <?php echo $this->getCalculated($action, $calculated, $rule); ?>
                                                <?php endif; ?>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                    <?php
                                }
                                ?>
                                </tbody>
                            </table>
                            <?php echo HTMLHelper::_('uitab.endTab'); ?>


                        <?php endforeach; ?>

                <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
            </div>


        </div>
    </div>
    <input type="hidden" name="jform[rules][<?php echo $this->component; ?>][]"
           value="<?php echo (int) $this->item->id; ?>"/>
    <?php if (!in_array($this->item->id, $this->public_groups)) : ?>
        <script type="text/javascript">
            // add users

            jQuery('#add_user_group_<?php echo (int) $this->item->id; ?>').select2(
                {
                    theme: "bootstrap",
                    dropdownCssClass: ':all:',
                    allowClear: true,
                    minimumInputLength: 0,
                    multiple: true,
                    ajax:
                        {
                            url: <?php echo Uri::base() ?>'index.php?option=com_jpusers&view=groupusers&id=<?php echo (int) $this->item->id; ?>&filter_type=exclude&tmpl=component&layout=select2&format=json',
                            dataType: 'json',
                            quietMillis: 200,
                            data: function (term, page) {
                                return {filter_search: term, limit: 10, limitstart: ((page - 1) * 10)};
                            },
                            results: function (data, page) {
                                var more = (page * 10) < data.total;
                                return {results: data.items, more: more};
                            }
                        }
                });

            <?php if ($this->item->id > 0) : ?>
            // remove users
            jQuery('#rm_user_group_<?php echo (int) $this->item->id; ?>').select2(
                {
                    theme: "bootstrap",
                    allowClear: true,
                    minimumInputLength: 0,
                    multiple: true,
                    ajax:
                        {
                            url: <?php echo Uri::base() ?>'index.php?option=com_jpusers&view=groupusers&id=<?php echo (int) $this->item->id; ?>&filter_type=include&tmpl=component&layout=select2&format=json',
                            dataType: 'json',
                            quietMillis: 200,
                            data: function (term, page) {
                                return {filter_search: term, limit: 10, limitstart: ((page - 1) * 10)};
                            },
                            results: function (data, page) {
                                var more = (page * 10) < data.total;
                                return {results: data.items, more: more};
                            }
                        }
                });
            <?php endif; ?>
        </script>
    <?php endif; ?>
</div>