<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jpprojects
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\MVC\View\HtmlView;





/**
 * Project JSON list view class.
 *
 */
class JPprojectsViewProjects extends HtmlView
{
    /**
     * Generates a list of JSON items.
     *
     * @return    void
     */
    function display($tpl = null)
    {
	    Session::checkToken( 'get' ) or die( 'Invalid Token' );

	    $ta   = (int) Factory::getApplication()->input->getUInt('typeahead');
        $s2   = (int) Factory::getApplication()->input->getUInt('select2');
        $resp = array();

        // Get model data
        $rows = $this->get('Items');

        if ($ta) {
            $tmp_rows = array();

            foreach ($rows AS &$row)
            {
                $id = (int) $row->id;

                $tmp_rows[$id] = $this->escape($row->title);
            }

            $rows = $tmp_rows;
        }
        elseif ($s2) {
            $tmp_rows = array();

            foreach ($rows AS &$row)
            {
                $id = (int) $row->id;

                $item = new stdClass();
                $item->id = $id;
                $item->text = $this->escape($row->title);

                $tmp_rows[] = $item;
            }

            $rows  = $tmp_rows;
            $total = (int) $this->get('Total');
            $rows  = array('total' => $total, 'items' => $rows);
        }

        // Set the MIME type for JSON output.
        Factory::getDocument()->setMimeEncoding('application/json');

        // Change the suggested filename.
        Factory::getApplication()->setHeader('Content-Disposition', 'attachment;filename="' . $this->getName() . '.json"');

        // Output the JSON data.
        echo json_encode($rows);

        jexit();
    }
}
