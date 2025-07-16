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

$rsp = array('total' => $this->get('Total'), 'items' => array());

foreach ($this->items AS $item)
{
    $row = new stdClass();

    $row->id   = (int) $item->id;
    $row->text = $this->escape('[' . $item->username . '] ' . $item->name);

    $rsp['items'][] = $row;
}

echo json_encode($rsp);
jexit();