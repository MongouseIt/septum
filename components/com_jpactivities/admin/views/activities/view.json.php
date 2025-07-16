<?php
/**
 * @package      pkg_jpactivities
 * @subpackage   com_jpactivities
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */


defined('_JEXEC') or die();

use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView;



class JPactivitiesViewActivities extends HtmlView
{
    /**
     * Displays the view.
     *
     */
    public function display($tpl = null)
    {
        // Get data from model
        $items = $this->get('Items');

        // Check for errors
        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
            jexit(500);
        }

        echo json_encode($items);
        jexit(201);
    }
}
