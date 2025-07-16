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
use Joomla\CMS\HTML\HTMLHelper;


$function = \Joomla\CMS\Factory::getApplication()->input->getCmd('function', 'jpSelectAttachment');
$j3000    = version_compare(JVERSION, '3.0.0', 'ge');

$selectedItems = json_decode(Factory::getApplication()->input->get('selectedItems','','string'));

foreach ($this->items['notes'] as $i => $item) :
    $js = 'if (window.parent) window.parent.'
        . $this->escape($function)
        . '(\'' . $item->id . '\', \''
        . $this->escape(addslashes($item->title))
        . '\', \'note\''
        . ');';

    ?>
    <tr  data-repo-item-id="<?php echo $item->id ?>" style="<?php echo in_array('note.'.$item->id,(array)$selectedItems) ? 'display:none' : ''  ?>" class="row<?php echo $i % 2; ?>">
        <td>

                <a data-repo-item-id="<?php echo $item->id ?>" class="add-repo-item btn" onclick="<?php echo $js; ?>">
                    <i class="icon-ok"></i>
                </a>

        </td>
        <td>
            <?php echo $this->escape($item->title); ?>
        </td>
        <td>
            <?php echo HTMLHelper::_('jp.html.truncate', $item->description); ?>
        </td>
    </tr>
<?php endforeach; ?>
