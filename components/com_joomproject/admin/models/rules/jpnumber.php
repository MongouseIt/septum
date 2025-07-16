<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_reminders
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\Registry\Registry;

class JFormRuleJPnumber extends FormRule{

    public function test(SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null)
    {



        if ($value >= 0) {
            return true;
        }else{
            $element->addAttribute('message', "The field Repeats $value is not valid because it is less than 0");
            return false;
        }
    }

}
