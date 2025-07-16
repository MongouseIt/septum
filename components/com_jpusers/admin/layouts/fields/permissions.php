<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_joomproject
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2019 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

extract($displayData);

$tabOptions = array(
    'useCookie' => true,
    'active' => 'permissions_tab_0'
);

$i = 0;

?>
<div class="tabbable tabs-left">
    <?php echo HTMLHelper::_('bootstrap.startTabSet', 'permissionsList', $tabOptions); ?>
    <?php foreach ($extensionsPermissions as $extension => $permissions): ?>


        <?php

        $extensionName = Text::_('COM_JOOMPROJECT_PERMISSION_EXTENSION_'.strtoupper(str_replace('com_jp','',$extension)))


        ?>

        <?php echo HTMLHelper::_('bootstrap.addTab', 'permissionsList', 'permissions_tab_'.$i, $extensionName); ?>

            <?php foreach ($permissions as $type => $list): ?>
                <fieldset>
                    <legend><?php echo Text::_('COM_JOOMPROJECT_PERMISSION_TYPE_'.strtoupper($type)) ?></legend>

                    <?php foreach ($list as $permission): ?>

                        <?php


                        // is checked
                        $isChecked = '';
                        $check =  "$extension.$type.$permission";
                        if(in_array($check,$value))
                            $isChecked = 'checked';


                        ?>


                        <label  class="checkbox" for="<?php echo $extension.'_'.$type.'_'.$permission ?>">
                            <input <?php echo $isChecked ?> id="<?php echo $extension.'_'.$type.'_'.$permission ?>" type="checkbox" name="jform[permissions][<?php echo $extension ?>][<?php echo $type ?>][<?php echo $permission ?>]"> <?php echo Text::_('COM_JOOMPROJECT_PERMISSION_NAME_'.strtoupper($permission)) ?>
                        </label>
                    <?php endforeach; ?>

                </fieldset><br>
            <?php $i++; endforeach; ?>

        <?php echo HTMLHelper::_('bootstrap.endTab'); ?>
    <?php endforeach; ?>
    <?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>
</div>







