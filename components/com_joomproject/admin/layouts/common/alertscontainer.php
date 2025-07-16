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

Factory::getDocument()->addScriptDeclaration("

jQuery(document).ready(function($){	
	$('.alertsContainer').appendTo('body');
})
	
");

?>
<div
	style="position:fixed;top: 15px; right: 15px; z-index: 9999999999"
	id="joomproject"
	class="alertsContainer"
>

</div>