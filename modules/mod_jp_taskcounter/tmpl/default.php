<?php
/**
 * @package      mod_jp_taskcounter
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2015 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die;

$data = '';

switch ($param_link)
{
    case "":
	    $data = $params->get('prepend_text').' '. $count .' '.$params->get('append_text');
        break;

    case "1":
        $class = $params->get('link_class', "");
	    $data = $params->get('prepend_text') . ' <a href="' . $link . '" class="' . $class . '">' . $count . '</a> ' . $params->get('append_text');
        break;

    case "2":
        $class = $params->get('link_class', "");
        $data = '<a href="' . $link . '" class="' . $class . '">' . $params->get('prepend_text').' '. $count .' '.$params->get('append_text') . '</a>';
        break;
}
?>
<div id="joomproject">
    <div id="JPTaskCounter<?php echo $module->id ?>" class="JPTaskCounter">
        <ul class="list-group mb-4">
          <li class="list-group-item">
              <?php echo $data ?>
          </li>
        </ul>
    </div>
</div>