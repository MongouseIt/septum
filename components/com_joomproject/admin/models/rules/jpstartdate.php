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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;

class JFormRuleJPstartdate extends FormRule{

    public function test(SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null)
    {

        $jinput =  Factory::getApplication()->input;

        $reminderId = $jinput->get('id',0,'int');
        $dateNow = Factory::getDate('now')->format('Y-m-d H');
        $value  = Factory::getDate($value)->format('Y-m-d H');
        $formData = $jinput->get('jform','','ARRAY');
        $amount = $formData['amount'];
        $types = $formData['types'];
        $repeats = $formData['repeats'];

		if($dateNow > $value && $reminderId == 0){
			$element->addAttribute('message',Text::_('COM_JOOMPROJECT_REMINDERS_EXPIRED_START_DATE'));
			return false;
		}

		return true;

    }

}
