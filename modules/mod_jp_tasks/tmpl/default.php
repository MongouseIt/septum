<?php
/**
 * @package      pkg_joomproject
 * @subpackage   mod_jp_tasks
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2006-2013 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 **/

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$groupByList = $params->get('group_by_list',0);
$taskslayout = $groupByList ? 'groupedByListModule' : 'listModule';

?>
<div id="joomproject">
    <div id="JPTasks<?php echo $module->id; ?>" class="JPTasks <?php echo $groupByList ? 'p-4' : '' ?>">
        <?php echo LayoutHelper::render('tasks.'.$taskslayout,['items' => $items,'params' => $params],'',['client' => 'site','component' => 'com_jptasks']); ?>
    </div>
</div>
