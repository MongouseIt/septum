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

class JFormRuleJPremindersusers extends FormRule{

    public function test(SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null)
    {
        if (empty($value)) {
            return false;
        }else{
            return true;
        }
    }

}
