<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_joomproject
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2019 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */
defined('_JEXEC') or die();



?>
<!-- Start List -->
<?php  echo $LayoutCarousel->render([
    'data' => $data,
    'modid' => $module->id,
    'params' => $params
])
?>
