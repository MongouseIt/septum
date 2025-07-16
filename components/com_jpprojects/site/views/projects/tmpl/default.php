<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jpprojects
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2006-2016 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('bootstrap.dropdown');
HTMLHelper::_('bootstrap.collapse');
?>

<div id="joomproject" class="category-list<?php echo $this->pageclass_sfx; ?> view-projects PrintArea all">
<?php

// load internal navigation
echo JPhtmlNav::loadMain();


if ((int)$this->params->get('layoutType') == 0) {
	echo $this->loadTemplate('list');
}else{
	echo $this->loadTemplate('table');
}

?>
</div>
