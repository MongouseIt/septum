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

JLoader::register('JoomprojectHelperColor',JPATH_SITE.'/components/com_joomproject/helpers/color.php');

/*
 * Tasks grouped by task list
 */

extract($displayData);

?>
<ul class="list-tasks list-group list-group-flush">
    <?php foreach ($items as $i => $item): ?>
        <?php
        // Start task item


        // task bg color
        $item_css = JoomprojectHelperColor::getItemColor($item->params->get('task_color', ''));

        // list item class
        $class = ($item->complete ? 'task-complete' : 'task-incomplete');


        ?>
        <li id="list-item-<?php echo $i; ?>"
            class="<?php echo $class; ?> list-group-item  <?php if ($item->complete) : echo "complete"; endif; ?> priority-<?php echo $item->priority; ?>" <?php echo $item_css ?>>

            <?php echo LayoutHelper::render('task.taskInlineModule', ['item' => $item, 'params' => $params, 'i' => $i], '', ['client' => 'site', 'component' => 'com_jptasks']);

            ?>

        </li>

    <?php endforeach; ?>

</ul>


