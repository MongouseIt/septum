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
extract($displayData);

$class = isset($class) ? 'class="'.$class.'"' : '';

?>

<?php if(!JPApplicationHelper::getActiveProjectId()): // if no project selected display project name ?>
    <div <?php echo $class ?>>
        <a href="<?php echo JoomprojectHelperRoute::getDashboardRoute($item->project_id) ?>">
            <i class="fas fa-briefcase"></i> <?php echo $item->project_title ?>
        </a>
    </div>
<?php endif; ?>
