<?php

/**
 * @package      Joomproject
 * @subpackage   Tasks
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Layout\LayoutHelper;

$step = \Joomla\CMS\Factory::getApplication()->input->getInt('step', 1);

?>
<div class="row">
    <div id="j-sidebar-container" class="col-md-2">
        <?php echo $this->sidebar; ?>
    </div>
    <div id="j-main-container" class="col-md-10">

        <?php if (function_exists('iconv')): ?>
            <?php echo LayoutHelper::render('import.step'.$step, ['current' => $this], '', ['option' => 'com_jptasks', 'client' => 'site']); ?>
        <?php else: ?>
            <p class="alert alert-danger"><strong>Iconv PHP module</strong> not installed, please ask your web hosting
                to enable it to be able to use import feature.</p>
        <?php endif; ?>
    </div>
</div>
