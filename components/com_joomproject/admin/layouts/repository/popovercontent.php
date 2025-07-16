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

$current = $displayData['this'];
$item = $displayData['item'];

?>
<?php if(!empty($item->description)): ?>
    <p><?php echo $current->escape($item->description) ?></p>
<?php endif; ?>
<?php if (isset($item->jcfields) && count($item->jcfields) > 0) :?>
    <table class="table table-bordered m-0">
    <?php foreach ($item->jcfields as $field) : ?>
        <?php if(!empty($field->value)):?>
        <tr>
            <td><strong><?php echo  $field->label ?></strong></td>
            <td><?php echo $field->value ?></td>
        </tr>
        <?php endif; ?>
    <?php endforeach ?>
    </table>
<?php endif; ?>