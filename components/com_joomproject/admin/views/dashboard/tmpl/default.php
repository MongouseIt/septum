<?php
use Joomla\CMS\Factory;
use Joomla\CMS\Document\JsonDocument;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

/**
* @package      Joomproject
* @subpackage   Dashboard
*
* @author       JoomBoost
* @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();

HTMLHelper::_('bootstrap.tooltip');

$modules = &$this->modules;


?>

<div class="row">
    <div class="col-md-2">
        <?php echo $this->sidebar; ?>
    </div>
    <div class="col-md-10">
        <div class="row">
            <?php echo $modules->render('jp-dashboard-before', array('style' => 'xhtml'), null); ?>
            <div id="joomproject">
                <?php echo JoomprojectHelperDashboard::getSkeleton(); ?>
            </div>
            <?php echo $modules->render('jp-dashboard-after', array('style' => 'xhtml'), null); ?>
        </div>
    </div>
</div>

<!-- for quick add buttons in toolbar -->
<form action="<?php echo Route::_('index.php?option=com_joomproject&view=dashboard'); ?>" method="post" name="adminForm" id="adminForm">
    <input type="hidden" name="task" value="" />
</form>