<?php
/**
 * @package      Joomproject
 * @subpackage   Repository
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

$function = Factory::getApplication()->input->getCmd('function', 'jpSelectAttachment');
$user     = Factory::getApplication()->getIdentity();
$uid      = $user->get('id');
$this_dir = $this->items['directory'];
$j3000    = version_compare(JVERSION, '3.0.0', 'ge');

$link_append = '&layout=modal&tmpl=component&function=' . $function;

$selectedItems = json_decode(Factory::getApplication()->input->get('selectedItems','','string'));

if ($this_dir->parent_id > 1) : ?>
    <tr class="row1">
        <td class="center"></td>
        <td colspan="6">
            <a href="<?php echo Route::_('index.php?option=com_jprepo&view=repository&filter_parent_id=' . $this_dir->parent_id . $link_append);?>">
                ..
            </a>
        </td>
    </tr>
<?php endif; ?>
<?php
foreach ($this->items['directories'] as $i => $item) :
    $link = 'index.php?option=com_jprepo&view=repository&filter_parent_id=' . $item->id . $link_append;

    $js = 'if (window.parent) window.parent.'
        . $this->escape($function)
        . '(\'' . $item->id . '\', \''
        . $this->escape(addslashes($item->title))
        . '\', \'directory\''
        . ');';

    $selectedItems = is_array($selectedItems) ? in_array('note.'.$item->id,$selectedItems) : false;
    ?>
    <tr  data-repo-item-id="<?php echo $item->id ?>"  style="<?php echo $selectedItems ? 'display:none' : ''  ?>" class="row<?php echo $i % 2; ?>">
        <td>

                <a  data-repo-item-id="<?php echo $item->id ?>" class="add-repo-item btn" href="javascript:void(0);" onclick="<?php echo $js; ?>">
                    <i class="icon-ok"></i>
                </a>

        </td>
        <td>
            <a href="<?php echo $link;?>">
                <?php echo $this->escape($item->title); ?>
            </a>
        </td>
        <td>
            <?php echo $this->escape($item->description); ?>
        </td>
    </tr>
<?php endforeach; ?>
