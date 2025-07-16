<?php
/**
 * @package      Joomproject.Library
 * @subpackage   Toolbar
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 20012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;


abstract class JPToolbar
{
    protected static $html = array();

    protected static $group_open = false;


    public static function render()
    {
        return implode("\n", self::$html);
    }

    public static function clear()
    {
        self::$html = array();
        self::$group_open = false;
    }

    public static function group()
    {
        if (self::$group_open) {
            self::$html[] = '</div>
</span>';
            self::$group_open = false;
        } else {
            self::$html[] = '
<span class="btnContainer">
<div class="btn-group d-inline">';
            self::$group_open = true;
        }
    }

    public static function button($text, $task = '', $list = false, $options = array())
    {
        self::$html[] = self::renderButton($text, $task, $list, $options);
    }

    // Add this new method after the button() method
    public static function batchButton($options = array())
    {
        $class = isset($options['class']) ? $options['class'] : 'btn-info';
        $icon = isset($options['icon']) ? $options['icon'] : 'fas fa-layer-group';
        $modalId = isset($options['modal-id']) ? $options['modal-id'] : 'collapseModal';
        $id = 'toolbar-batch'; // Fixed ID for consistency

        // Get the document object
        $doc = Factory::getApplication()->getDocument();

        // Add the JavaScript for batch button functionality
        $js = <<<JS
    document.addEventListener("DOMContentLoaded", function() {
        const batchBtn = document.getElementById("$id");
        const checkboxes = document.querySelectorAll("input[name='cid[]']");
        
        const toggleBatchButton = () => {
            const checkedBoxes = document.querySelectorAll("input[name='cid[]']:checked");
            if (batchBtn) {
                if (checkedBoxes.length > 0) {
                    batchBtn.classList.remove("disabled");
                } else {
                    batchBtn.classList.add("disabled");
                }
            }
        };

        // Add listeners to all checkboxes
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener("change", toggleBatchButton);
        });

        // Add listener to toggle all checkbox
        const checkAll = document.getElementById("checkall-toggle");
        if (checkAll) {
            checkAll.addEventListener("change", toggleBatchButton);
        }

        // Initial state
        toggleBatchButton();
    });
    JS;

        $doc->addScriptDeclaration($js);

        // Build the HTML for the button
        $html = array();
        $html[] = '<button type="button"';
        $html[] = ' id="' . $id . '"';
        $html[] = ' class="btn btn-sm ' . $class . ' disabled"'; // Start disabled
        $html[] = ' data-bs-toggle="modal"';
        $html[] = ' data-bs-target="#' . $modalId . '">';
        $html[] = '<i class="' . $icon . '"></i> ';
        $html[] = addslashes(Text::_('COM_JOOMPROJECT_BATCH_BUTTON'));
        $html[] = '</button>';

        self::$html[] = implode("", $html);
    }

    public static function listButton($items, $options = array(), $text = 'JACTION_EDITSTATE')
    {
        $list = array();
        $html = array();
        $class = (isset($options['class']) ? $options['class'] : 'btn-dark');
        $icon = (isset($options['icon']) ? $options['icon'] : 'fas fa-edit');

        foreach ($items as $item) {
            if (isset($item['options'])) {
                if (array_key_exists('access', $item['options'])) {
                    if ($item['options']['access'] == false) {
                        continue;
                    }
                }
            }

            $list[] = $item;
        }

        $count = count($list);

        if ($count == 0) {
            return;
        }

        $html[] = '<div class="btn-group mb-0">';
        $html[] = '<button class="btn btn-sm ' . $class . ' dropdown-toggle disabled" type="button" id="btn-bulk" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
        $html[] = '<i class="' . $icon . '"></i> ';
        $html[] = Text::_($text);
        $html[] = '</button>';
        $html[] = '  <ul class="dropdown-menu" aria-labelledby="btn-bulk">';

        foreach ($list as $i => $item) {
            $text = $item['text'];
            $task = (isset($item['task']) ? $item['task'] : '');
            $lst = (isset($item['list']) ? $item['list'] : true);
            $opts = (isset($item['options']) ? $item['options'] : array());

            $html[] = self::renderListItem($text, $task, $lst, $opts);
        }

        $html[] = '</ul>';
        $html[] = '</div>';

        self::$html[] = implode("", $html);

    }

    public static function filterButton($isset = false, $target = '#filters')
    {
        $class = ($isset ? ' active' : '');

        $html = array();
        $html[] = '<div class="mb-0 float-end">';
        $html[] = '<a data-bs-toggle="collapse" href="' . $target . '" role="button" aria-expanded="false" aria-controls="' . str_replace("#", '', $target) . '" class="btn btn-light btn-sm' . $class . '">';
        $html[] = '<span aria-hidden="true" class="fas fa-sort"></span> ' . Text::_('JSEARCH_FILTER');
        $html[] = '</a>';
        $html[] = '</div>';

        self::$html[] = implode("", $html);
    }


    protected static function renderButton($text, $task = '', $list = false, $options = array())
    {
        $html = array();
        $class = (isset($options['class']) ? $options['class'] : 'btn-info');
        $href = (isset($options['href']) ? $options['href'] : 'javascript:void(0);');
        $icon = (isset($options['icon']) ? $options['icon'] : 'fas fa-plus');
        $id = (isset($options['id']) ? ' id="' . $options['id'] . '"' : '');

        if (array_key_exists('access', $options)) {
            if ($options['access'] !== true) {
                return '';
            }
        }

        $html[] = '<a class="btn btn-sm ' . $class . '" href="' . $href . '"';

        if ($task) {
            $html[] = 'onclick="';

            if ($list) {
                $message = addslashes(Text::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'));
                $html[] = "if (document.adminForm.boxchecked.value==0){alert('$message');}else{Joomla.submitbutton('$task')}";
            } else {
                $html[] = "Joomla.submitbutton('$task');";
            }

            $html[] = '" ';
        }

        $html[] = $id;
        $html[] = '>';
        $html[] = '<i class="' . $icon . '"></i> ';
        $html[] = addslashes(Text::_($text));
        $html[] = '</a>';

        return implode("", $html);
    }


    public static function dropdownButton($items, $options = array())
    {
        $list = array();
        $html = array();
        $class = (isset($options['class']) ? $options['class'] : 'btn-info');
        $icon = (isset($options['icon']) ? $options['icon'] : 'fas fa-plus');

        foreach ($items as $item) {
            if (isset($item['options'])) {
                if (array_key_exists('access', $item['options'])) {
                    if ($item['options']['access'] == false) {
                        continue;
                    }
                }
            }

            $list[] = $item;
        }

        $count = count($list);

        if ($count == 0) {
            return;
        }

        if ($count == 1) {
            $text = $list[0]['text'];
            $task = (isset($list[0]['task']) ? $list[0]['task'] : '');
            $lst = (isset($list[0]['list']) ? $list[0]['list'] : false);
            $opts = (isset($list[0]['options']) ? $list[0]['options'] : array());

            if (!isset($opts['class']) && isset($options['class'])) {
                $opts['class'] = $options['class'];
            }

            if (!isset($opts['icon']) && isset($options['icon'])) {
                $opts['icon'] = $options['icon'];
            }

            self::button($text, $task, $lst, $opts);
        } else {
            $reverse = array_reverse($list);
            $first = array_pop($reverse);

            $text = $first['text'];
            $task = (isset($first['task']) ? $first['task'] : '');
            $lst = (isset($first['list']) ? $first['list'] : false);
            $opts = (isset($first['options']) ? $first['options'] : array());

            if (!isset($opts['class']) && isset($options['class'])) {
                $opts['class'] = $options['class'];
            }

            if (!isset($opts['icon']) && isset($options['icon'])) {
                $opts['icon'] = $options['icon'];
            }

            if (!isset($opts['id']) && isset($options['id'])) {
                $opts['id'] = $options['id'];
            }


            $html[] = '<div class="btn-group me-2 mb-0">';
            $html[] = self::renderButton($text, $task, $lst, $opts);
            $html[] = '<button class="btn btn-sm dropdown-toggle dropdown-toggle-split ' . $class . ' dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
            $html[] = '<span class=""></span>';
            $html[] = '</button>';
            $html[] = '<ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">';

            foreach ($list as $i => $item) {
                if ($i == 0) continue;

                $text = $item['text'];
                $task = (isset($item['task']) ? $item['task'] : '');
                $lst = (isset($item['list']) ? $item['list'] : false);
                $opts = (isset($item['options']) ? $item['options'] : array());

                $html[] = self::renderListItem($text, $task, $lst, $opts);
            }

            $html[] = '</ul>';
            $html[] = '</div>';
            self::$html[] = implode("", $html);
        }
    }


    protected static function renderListItem($text, $task = '', $list = false, $options = array())
    {
        $html = array();
        $href = (isset($options['href']) ? $options['href'] : 'javascript:void(0);');
        $icon = (isset($options['icon']) ? $options['icon'] : '');

        if (isset($options['access'])) {
            if ($options['access'] == false) {
                return '';
            }
        }

        if ($text == 'divider') {
            $html[] = '<li class="divider"></li>';
            return implode("", $html);
        }

        $html[] = '<li>';
        $html[] = '<a  class="dropdown-item" href="' . $href . '"';

        if ($task) {
            $html[] = 'onclick="';

            if ($list) {
                $message = addslashes(Text::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'));
                $html[] = "if (document.adminForm.boxchecked.value==0){alert('$message');}else{Joomla.submitbutton('$task')}";
            } else {
                $html[] = "Joomla.submitbutton('$task');";
            }

            $html[] = '" ';
        }

        $html[] = '>';

        if ($icon) {
            $html[] = '<i class="' . $icon . '"></i> ';
        }

        $html[] = addslashes(Text::_($text));
        $html[] = '</a>';
        $html[] = '</li>';

        return implode("", $html);
    }
}
