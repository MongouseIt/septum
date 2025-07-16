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


?>
<div id="jp-latest-comments-<?php echo $module->id; ?>">

    <?php foreach ($items as $item) :?>

    <?php echo LayoutHelper::render(
            'comment',
            ['item' => $item,'actions' => false],
            '',
            ['component' => 'com_jpcomments','client' => 'site']
        ); ?>

    <?php endforeach; ?>

</div>
