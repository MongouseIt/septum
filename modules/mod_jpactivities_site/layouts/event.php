<?php
/**
 * @package      mod_jp_taskcounter
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2015 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

extract($displayData);

$com_params = \Joomla\CMS\Component\ComponentHelper::getParams('com_joomproject');

$date_rel = $params->get('date_relative', $com_params->get('date_relative', 1));
$date_format = $params->get('date_format');

$date = HTMLHelper::_('date', $item->created, $date_format);


?>
<div class="list-group-item">
    <div class="w-100 d-flex justify-content-between align-items-center">
                        <span class="text-muted">
                            <?php if ($date_rel) : ?>
                                <span data-toggle="tooltip" title="<?php echo $date; ?>" style="cursor: help;">
                                    <?php echo JPactivitiesHelper::relativeDateTime($item->created); ?>
                                </span>
                            <?php
                            else :
                                ?>
                                <?php echo $date; ?>
                            <?php
                            endif;
                            ?>
                                </span>
        <span class="badge  bg-<?php echo $item->name; ?>"><?php echo isset($item->nameTranslated) ? $item->nameTranslated : $item->name; ?></span>
    </div>
    <p class="my-1"><?php echo $item->text; ?></p>

</div>
