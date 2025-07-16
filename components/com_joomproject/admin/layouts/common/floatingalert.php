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

?>
<div class="shadow-lg p-3 alert alert-<?php echo $type  ?> alert-item-<?php echo $id  ?>">
	<?php echo $message ?>
</div>