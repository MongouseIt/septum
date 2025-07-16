<?php

/**
 * @package      Joomproject
 * @subpackage   Projects
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

extract($displayData);
?>
<input type="hidden" name="<?php echo $name; ?>[unit]" class="tf-unit-control-unit-value" value="<?php echo $unit; ?>" />