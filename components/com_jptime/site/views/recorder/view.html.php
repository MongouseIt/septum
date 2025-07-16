<?php
/**
 * @package      Joomproject
 * @subpackage   Timetracking
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;





/**
 * Joomproject Time Recorder View Class
 *
 */
class JPtimeViewRecorder extends HtmlView
{
    /**
     * List of items to display
     *
     * @var    array
     */
    protected $items;

    /**
     * Timestamp of the last punch-in
     *
     * @var    integer
     */
    protected $time;


    /**
     * Display the view
     *
     */
    public function display($tpl = null)
    {
        if(!\JoomProject\Permission\GlobalAccess::check('com_jptime'))
            return;

        $app = Factory::getApplication();
        $items      = $app->getUserState('com_jptime.recorder.data');
        $this->time = (int) $app->getUserState('com_jptime.recorder.time');
        $last_time  = time() - $this->time;

        // Pause all items except the ones that just started if 70+ secs have past
        // since the last punch-in
        if ($last_time > 70 && $this->time > 0 && is_array($items) && count($items)) {
            foreach ($items AS &$item)
            {
                if ($item['time'] == 1) continue;

                if (!$item['pause']) $item['pause'] = time();
            }

            // Update session data
            $app->setUserState('com_jptime.recorder.data', $items);
        }



        // Get the items from the model
        $this->items = $this->get('Items');



        $this->prepareDocument();

        // Display the view
        parent::display($tpl);
    }


    /**
     * Prepares the document
     *
     * @return    void
     */
    protected function prepareDocument()
    {
        $this->document->setTitle(Text::_('COM_JOOMPROJECT_TIME_RECORDER_TITLE'));
    }
}
