<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_joomproject
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2019 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */
# No Permission
defined( '_JEXEC' ) or die ;
use Joomla\CMS\Factory;
// init
$renderer   = Factory::getDocument()->loadRenderer('module');
$mod        = $displayData['mod'];

?>

<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title m-0"><?php echo $mod->title  ?></h3>
    </div>
    <div class="card-body <?php echo ($mod->module == 'mod_jp_gantt' or $mod->module == 'mod_jp_tasks') ? 'p-0': '';?>">
        <?php
        echo $renderer->render($mod,array('style' => 'raw'));
        ?>
    </div>
</div>