<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jpprojects
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2006-2015 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;

// Include css
HTMLHelper::_('stylesheet', 'com_joomproject/joomproject/print.css');


$db       = Factory::getDbo();
$query    = $db->getQuery(true);
$nulldate = $db->getNullDate();

$params = ComponentHelper::getParams('com_joomproject');
$date_format = $params->get('date_format');

if (!$date_format) {
    $date_format = Text::_('DATE_FORMAT_LC4');
}

$doc = Factory::getDocument();
$doc->addScriptDeclaration('
jQuery(document).ready(function()
{
    window.focus();
    window.print();
});
');
?>
<div id="joomproject" class="category-list<?php echo $this->pageclass_sfx;?> view-milestones-print">
    <table class="table table-striped cat-items">
        <thead>
            <tr>
                <th><?php echo Text::_('JGLOBAL_TITLE'); ?></th>
                <th class="nowrap center" style="width: 10%"><?php echo Text::_('COM_JOOMPROJECT_MILESTONES'); ?></th>
                <th class="nowrap center" style="width: 10%"><?php echo Text::_('COM_JOOMPROJECT_TASKS'); ?></th>
                <th class="nowrap" style="width: 10%"><?php echo Text::_('COM_JOOMPROJECT_FIELD_START_DATE_LABEL'); ?></th>
                <th class="nowrap" style="width: 10%"><?php echo Text::_('COM_JOOMPROJECT_FIELD_DEADLINE_LABEL'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach($this->items AS $i => $item) :
                ?>
                <tr>
                    <td><?php echo $this->escape($item->title);?></td>
                    <td class="center"><?php echo $this->escape($item->milestones);?></td>
                    <td class="center"><?php echo $this->escape($item->tasks);?></td>
                    <td>
                        <?php
                        if ($item->start_date != $nulldate) {
                            echo HTMLHelper::_('date', $item->start_date, $date_format);
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                        if ($item->end_date != $nulldate) {
                            echo HTMLHelper::_('date', $item->end_date, $date_format);
                        }
                        ?>
                    </td>
                </tr>
                <?php
            endforeach;
            ?>
        </tbody>
    </table>
</div>
