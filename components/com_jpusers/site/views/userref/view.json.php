<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jpusers
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */
defined('_JEXEC') or die();

use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;



jimport('joomproject.library');

/**
 * User Reference view class.
 *
 */
class JPusersViewUserRef extends HtmlView
{
    protected $items;


    /**
     * Generates a list of JSON items.
     *
     * @return    void
     */
    public function display($tpl = null)
    {
        $user   = Factory::getApplication()->getIdentity();
        $access = Factory::getApplication()->input->getUInt('filter_access');

        // No access if not logged in
        if ($user->id == 0) {
            Factory::getApplication()->enqueueMessage( Text::_('JERROR_ALERTNOAUTHOR'),'error');
            return false;
        }

        // Check Access for non-admins
        if (!$user->authorise('core.admin')) {
            $allowed = JPAccessHelper::getGroupsByAccessLevel($access, true);
            $groups  = $user->getAuthorisedGroups();

            $can_access = false;

            foreach ($groups AS $group)
            {
                if (in_array($group, $allowed)) {
                    $can_access = true;
                    break;
                }
            }

            if (!$can_access) {
                Factory::getApplication()->enqueueMessage( Text::_('JERROR_ALERTNOAUTHOR'),'error');
                return false;
            }
        }

        $this->items = $this->get('Items');

        // Check for errors.
		if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
		}

        parent::display($tpl);
    }
}
