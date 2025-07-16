<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jpusers
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;





/**
 * Group users view class.
 *
 */
class JPusersViewGroupUsers extends HtmlView
{
    protected $items;


    /**
     * Generates a list of JSON items.
     *
     * @return    void
     */
    public function display($tpl = null)
    {

        $this->items = $this->get('Items');

        $user = Factory::getApplication()->getIdentity();

        if (!$user->authorise('core.admin', 'com_jpprojects') && !$user->authorise('core.manage', 'com_jpprojects')) {
            \Joomla\CMS\Factory::getApplication()->enqueueMessage( Text::_('JERROR_ALERTNOAUTHOR'),'error');
            return false;
        }

        // Check for errors.
		if (count($errors = $this->get('Errors'))) {
			\Joomla\CMS\Factory::getApplication()->enqueueMessage( $errors,'warning');
			return false;
		}

        parent::display($tpl);
    }
}
