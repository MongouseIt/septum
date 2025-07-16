<?php
/**
 * @package      mod_jp_gantt
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 **/

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

$count = count($items);
$limit = (int) $params->get('limit', 25);

if ($count < $limit || $limit == 0) {
    $limit = $count;
}

// Bail out if we have nothing to display
if (!$count) {
    ?>
    <div class="alert"><?php echo Text::_('MOD_JP_GANTT_EMPTY'); ?></div>
    <?php
    return;
}

$months = array(
    Text::_('JANUARY'), Text::_('FEBRUARY'), Text::_('MARCH'),
    Text::_('APRIL'), Text::_('MAY'), Text::_('JUNE'),
    Text::_('JULY'), Text::_('AUGUST'), Text::_('SEPTEMBER'),
    Text::_('OCTOBER'), Text::_('NOVEMBER'), Text::_('DECEMBER')
);


modJPganttHelper::loadMedia();


$css = array();
$css[] = '#mod_jp_gantt_' . $module->id . ' .fn-gantt .leftPanel';
$css[] = '{';
$css[] = '    width: ' . (int) $params->get('column_width', 225) . 'px;';
$css[] = '}';
$css[] = '#mod_jp_gantt_' . $module->id . ' .fn-gantt .leftPanel .name';
$css[] = '{';
$css[] = '    width: ' . ((int) $params->get('column_width', 225) - 50) . 'px;';
$css[] = '}';
$css[] = '#mod_jp_gantt_' . $module->id . ' .fn-gantt .leftPanel .name .fn-label';
$css[] = '{';
$css[] = '    width: ' . ((int) $params->get('column_width', 225) - 50) . 'px;';
$css[] = '}';
$css[] = '#mod_jp_gantt_' . $module->id . ' .fn-gantt .navigate .nav-slider-content';
$css[] = '{';
$css[] = '    width: ' . (int) $params->get('slider_width', 300) . 'px;';
$css[] = '}';
$css[] = '#mod_jp_gantt_' . $module->id . ' .fn-gantt .navigate .nav-slider-bar';
$css[] = '{';
$css[] = '    width: ' . ((int) $params->get('slider_width', 300) - 5) . 'px;';
$css[] = '}';

if (in_array($params->get('task_dependencies'), array('0', '2'))) {
    $css[] = '.fn-gantt .dep';
    $css[] = '{';
    $css[] = '    display: none;';
    $css[] = '}';
}

$js = array();
$js[] = 'var ganttFT' . $module->id . ' = false;';
$js[] = 'jQuery(document).ready(function()';
$js[] = '{';
$js[] = ' jQuery("#mod_jp_gantt_' . $module->id . '").gantt(';
$js[] = '    {';
$js[] = '        source: ' . json_encode($items) . ',';
$js[] = '        scale: "days",';
$js[] = '        minScale: "days",';
$js[] = '        maxScale: "weeks",';
$js[] = '        months: ' . json_encode($months) . ',';
$js[] = '        itemsPerPage: ' . $limit . ', ';
$js[] = '        navigate: "scroll",';
$js[] = '        scrollOnDrag: true,';
$js[] = '        scrollOnWheel: true,';
$js[] = '        scrollToToday: true,';
$js[] = '        depHover: ' . ($params->get('task_dependencies') == '2' ? 'true' : 'false') . ',';
$js[] = '        onRender: function(element, core)';
$js[] = '        {';
$js[] = ' $( "#mod_jp_gantt_' . $module->id . ' > .fn-gantt > .row" ).removeClass("row").addClass("fn-content");';
$js[] = '            jQuery(".gantt-bs-tt").tooltip();';
$js[] = '            jQuery(".fn-popover").popover({placement: "top"});';
$js[] = '            if (!ganttFT' . $module->id . ') {';
$js[] = '                core.navigateTo(element, "now");';
$js[] = '                setTimeout(function() {core.synchronizeScroller(element);}, 500);';
$js[] = '                ganttFT' . $module->id . ' = true;';
$js[] = '            }';
$js[] = '        }';
$js[] = '    })';
$js[] = '});';

$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->addInlineScript(implode("\n", $js), ['name' => 'my.gantt.asset']);


//Factory::getDocument()->addScriptDeclaration(implode("\n", $js));
Factory::getDocument()->addStyleDeclaration(implode("\n", $css));
?>
<div id="joomproject">
<div id="mod_jp_gantt_<?php echo $module->id; ?>"></div>
</div>
