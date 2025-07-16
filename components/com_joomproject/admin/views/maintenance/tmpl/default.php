<?php
/**
 * @package      Joomproject
 * @subpackage   Dashboard
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 **/

defined('_JEXEC') or die();

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('bootstrap.tooltip');

$modules = &$this->modules;


?>
<form action="<?php echo Route::_('index.php?option=com_joomproject&view=maintenance'); ?>" method="post"
      name="adminForm" id="adminForm">
    <div class="row">
        <div id="j-sidebar-container" class="col-md-2">
            <?php echo $this->sidebar; ?>
        </div>
        <div id="j-main-container" class="col-md-10"></div>
    </div>
    <!-- for quick add buttons in toolbar -->

    <input type="hidden" name="task" value=""/>
</form>