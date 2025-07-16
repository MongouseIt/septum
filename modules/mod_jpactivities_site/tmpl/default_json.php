<?php
/**
 * @package      pkg_jpactivities
 * @subpackage   mod_jpactivities_site
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2013 JoomBoost.com. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Component\ComponentHelper;

?>
<?php
$items = array();
$com_params = ComponentHelper::getParams('com_jpactivities');
$date_rel = $params->get('date_relative', $com_params->get('date_relative', 1));
$date_format = $params->get('date_format');

if (!$date_format) $date_format = Text::_('DATE_FORMAT_LC1');


foreach ($data['items'] AS $item) {

    $items[] = LayoutHelper::render('event',['item' => $item,'params' => $params]);
}

echo json_encode(array('total' => $data['total'], 'items' => $items));
?>
