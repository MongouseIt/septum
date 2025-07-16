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
defined('_JEXEC') or die;
extract($displayData);

?>

<?php if ($params->get('show_page_heading', 1)) : ?>
	<div class="page-header mb-4">
		<h1><?php echo $params->get('page_heading'); ?></h1>
	</div>
<?php endif; ?>
