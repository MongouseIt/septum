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

use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;


class JFormRuleJPamount extends FormRule{

    public function test(SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null)
    {

        $jinput =  Factory::getApplication()->input;
        $formData = $jinput->get('jform','','ARRAY');
        $types = $formData['types'];

        switch ($types) {
            case 1:
                $type  = ' hour';
                break;
            case 2:
                $type  = ' day';
                break;
            case 3:
                $type  = ' week';
                break;
            case 4:
                $type  = ' month';
                break;
            default:
                $type  = ' hour';
        }

        if ($value >= 0) {
            return true;
        }else{
            $element->addAttribute('message', "The field every  $value  $type  is not valid because it is less than 1");
            return false;
        }
    }

}
