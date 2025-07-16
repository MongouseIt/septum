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

use Joomla\CMS\Form\FormHelper;

if (empty($html))
{
    return;
}

$styles = array_filter([
    'max-width' => $width
]);
$styles = array_map(function($k, $v) {
    return $k . ':' . $v . ';';
}, array_keys($styles), $styles);

?>
<div class="nr-responsive-control<?php echo $class; ?>"<?php echo $styles ? ' style="' . implode('', $styles) . '"' : ''; ?>>
    <div class="nr-responsive-control--item <?php echo $breakpoint; ?>">
        <?php echo $html; ?>
    </div>
</div>