<?php
/**
 * @package		JoomProject
 * @copyright	2013-2019 JoomBoost, joomboost.com
 * @license		GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');


extract($displayData);

/*
 * Pagination for module
 */

?>
<div class="mt-4">
	<nav class="d-flex pagination pagination-wrapper">
		<div class="me-auto">
			<?php echo $pagination->getPagesLinks(); ?>
		</div>
		<div class="text-muted">
			<?php echo $pagination->getPagesCounter(); ?>
		</div>
	</nav>
</div>
