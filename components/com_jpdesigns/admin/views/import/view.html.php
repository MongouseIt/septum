<?php
/**
 * @package      Joomproject Pro
 * @subpackage   Designs
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */


defined('_JEXEC') or die();

use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\HTML\Helpers\Sidebar;
use Joomla\CMS\Toolbar\ToolbarHelper;





class JPdesignsViewImport extends HtmlView
{
    protected $items;
    protected $state;
    protected $albums;
    protected $designs;


    /**
     * Displays the view.
     *
     */
    public function display($tpl = null)
    {
        // Get data from model
        $this->items = $this->get('Items');
        $this->state = $this->get('State');

        $this->albums  = $this->get('Albums');
        $this->designs = $this->get('Designs');

        if (JPApplicationHelper::getActiveProjectId() <= 0) {
            $text = Text::_('COM_JOOMPROJECT_DESIGNS_WARNING_IMPORT_SELECT_PROJECT');
            Factory::getApplication()->enqueueMessage($text);
        }

        // Check for errors
        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
            return false;
        }

        if ($this->getLayout() !== 'modal') {
            $this->addToolbar();

            if (version_compare(JVERSION, '3', 'ge')) {
                JPdesignsHelper::addSubmenu('import');

                $this->sidebar = Sidebar::render();
            }
        }

        parent::display($tpl);
    }


    /**
     * Adds the page title and toolbar.
     *
     */
    protected function addToolbar()
    {
        ToolbarHelper::title(Text::_('COM_JOOMPROJECT_DESIGNS_IMPORT_TITLE'), 'article.png');

        ToolbarHelper::publish('import.import', 'JTOOLBAR_IMPORT', true);
    }
}
