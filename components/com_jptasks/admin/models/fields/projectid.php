<?php
/**
 * @package      Joomproject
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\Field\ListField;




/**
 * Form Field class for selecting a project.
 *
 */
class JFormFieldProjectid extends ListField
{
    /**
     * The form field type.
     *
     * @var    string
     */
    public $type = 'Projectid';


    public function getInput() {
        echo HTMLHelper::_('jphtml.project.filter');
    }
}
