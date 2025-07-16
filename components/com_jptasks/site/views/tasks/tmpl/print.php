<?php
/**
 * @package      Joomproject
 * @subpackage   Tasks
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

$current_list = '';
$list_open    = false;

$priorities = array(
    '1' => Text::_('COM_JOOMPROJECT_PRIORITY_VERY_LOW'),
    '2' => Text::_('COM_JOOMPROJECT_PRIORITY_LOW'),
    '3' => Text::_('COM_JOOMPROJECT_PRIORITY_MEDIUM'),
    '4' => Text::_('COM_JOOMPROJECT_PRIORITY_HIGH'),
    '5' => Text::_('COM_JOOMPROJECT_PRIORITY_VERY_HIGH')
);

$assigned_users = array();

$db = Factory::getDbo();
$query = $db->getQuery(true);
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
<div id="joomproject" class="category-list<?php echo $this->pageclass_sfx;?> view-tasks-print">

    <?php if ($this->state->get('filter.project')) : ?>
        <h2>
            <?php echo $this->escape(JPApplicationHelper::getActiveProjectTitle()); ?>
            <?php
            if ($this->state->get('filter.milestone')) {
                $query->clear()
                      ->select('title')
                      ->from('#__jp_milestones')
                      ->where('id = ' . (int) $this->state->get('filter.milestone'));

                $db->setQuery($query);
                $title = $db->loadResult();

                echo ' / ' . $this->escape($title);
            }

            if ($this->state->get('filter.tasklist')) {
                $query->clear()
                      ->select('title')
                      ->from('#__jp_task_lists')
                      ->where('id = ' . (int) $this->state->get('filter.tasklist'));

                $db->setQuery($query);
                $title = $db->loadResult();

                echo ' / ' . $this->escape($title);
            }
            ?>
        </h2>
    <?php endif; ?>

    <table class="table table-striped cat-items">
        <thead>
            <tr>
                <th><?php echo Text::_('JGLOBAL_TITLE'); ?></th>
                <th class="nowrap" style="width: 8%"><?php echo Text::_('COM_JOOMPROJECT_FIELD_PRIORITY_LABEL'); ?></th>
                <th class="nowrap" style="width: 25%"><?php echo Text::_('COM_JOOMPROJECT_FIELDSET_ASSIGNED_USERS'); ?></th>
                <th class="nowrap" style="width: 8%"><?php echo Text::_('COM_JOOMPROJECT_FIELD_DEADLINE_LABEL'); ?></th>
                <th class="nowrap center" style="width: 1%"><?php echo Text::_('COM_JOOMPROJECT_FIELD_COMPLETE_LABEL'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach($this->items AS $i => $item) :
                // Start Task list heading
                if ($current_list !== $item->list_title) :
                    if ($item->list_title != '') :
                        ?>
                        <tr>
                            <td colspan="5">
                                <h3><?php echo $this->escape($item->list_title);?></h3>
                                <div><?php echo htmlspecialchars_decode($this->escape($item->list_description));?></div>
                            </td>
                        </tr>
                        <?php
                    endif;

                    $current_list = $item->list_title;
                endif;
                // End Task list heading
                // Start Task item
                $assigned_users = array();
                ?>
                <tr>
                    <td><?php echo $this->escape($item->title);?></td>
                    <td><?php echo (array_key_exists($item->priority, $priorities) ? $priorities[$item->priority] : ''); ?></td>
                    <td>
                    <?php
                        foreach ($item->users AS $user)
                        {
                            $assigned_users[] = $this->escape($user->name);
                        }

                        echo implode(', ', $assigned_users);
                    ?>
                    </td>
                    <td>
                        <?php
                        if ($item->end_date != $nulldate) {
                            echo HTMLHelper::_('date', $item->end_date, $date_format);
                        }
                        ?>
                    </td>
                    <td class="nowrap text-center"><?php if ($item->complete) : ?><span aria-hidden="true" class="fas fa-check"></span><?php endif; ?></td>
                </tr>
                <?php
            endforeach;
            ?>
        </tbody>
    </table>
</div>
