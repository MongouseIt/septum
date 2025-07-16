<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_joomproject
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2019 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die;

/*
 * Tasks grouped by task list
 */

extract($displayData);
$tasksGroupedByList = JoomprojectHelperFrontend::arrayGroupBy($items, 'list_title'); // group by list name

$k = $x = 0;

// load color helper class
JLoader::register('JoomprojectHelperColor',JPATH_SITE.'/components/com_joomproject/helpers/color.php');


?>
<?php foreach ($tasksGroupedByList as $listName => $listItems): ?>

    <div class="cat-list-row<?php echo $k; ?> mb-3">

        <?php if (!empty($listName)) : ?>
            <div class="mb-3">
                <h5 class="m-0">
                    <a
                            href="<?php echo Route::_(JPtasksHelperRoute::getTasksRoute($listItems[0]->project_slug, $listItems[0]->milestone_slug, $listItems[0]->list_slug)); ?>">
                        <i class="fas fa-list"></i> <?php echo $listItems[0]->list_title; ?>
                    </a>
                    <small class="text-black-50"><?php echo htmlspecialchars_decode($listItems[0]->list_description); ?></small>
                </h5>
            </div>
        <?php endif; ?>


        <ul class="list-tasks list-group list-group-flush card rounded" id="tasklist_<?php echo $k; ?>">
            <?php foreach ($listItems as $i => $item): ?>
                <?php
                // Start task item


                // task bg color
                $item_css = JoomprojectHelperColor::getItemColor($item->params->get('task_color', ''));

                // list item class
                $class = ($item->complete ? 'task-complete' : 'task-incomplete');


                ?>
                <li id="list-item-<?php echo $x; ?>"
                    class="<?php echo $class; ?> list-group-item  <?php if ($item->complete) : echo "complete"; endif; ?> priority-<?php echo $item->priority; ?>" <?php echo $item_css ?>>

                    <?php echo LayoutHelper::render('task.taskInlineModule', ['item' => $item, 'params' => $params, 'i' => $i], '', ['client' => 'site', 'component' => 'com_jptasks']);

                    ?>

                </li>

                <?php $x++; ?>
            <?php endforeach; ?>

        </ul>

        <?php $k++; ?>
    </div>
<?php endforeach; ?>


