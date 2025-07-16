<?php
/**
 * @package		JoomProject
 * @copyright	2013-2019 JoomBoost, joomboost.com
 * @license		GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Form\Field\CalendarField;
FormHelper::loadFieldClass('calendar');



class JFormFieldJPCalendar extends CalendarField
{
    protected $type = 'JPCalendar';

    /**
     * Get the renderer
     *
     * @param   string  $layoutId  Id to load
     *
     * @return  FileLayout
     *
     * @since   3.5
     */
    protected function getRenderer($layoutId = 'default')
    {

        $renderer = new FileLayout($layoutId, null,
            [
                "client" => 1,
                'component' => 'com_joomproject'
            ]
        );

        return $renderer;
    }
}
