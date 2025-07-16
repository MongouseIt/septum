<?php

/**
 * @package      Joomproject
 * @subpackage   Projects
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Layout\FileLayout;

extract($displayData);
?>
<div class="tf-unit-control form-control<?php echo isset($wrapper_class) && !empty($wrapper_class) ? ' ' . $wrapper_class : ''; ?>" data-hint="<?php echo $hint; ?>">
	<?php
	echo $input;
	if (count($units) > 0)
	{
		$layout = new FileLayout('selector', JPATH_ADMINISTRATOR . '/components/com_jpprojects/layouts/controls/unit');
		echo $layout->render($displayData);
	}
	?>
</div>