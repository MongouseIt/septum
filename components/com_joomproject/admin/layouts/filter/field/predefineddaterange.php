<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_joomproject
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2019 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */
defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;

extract($displayData);

?>
<select
        name="filter_date_range_type"
        id="predefinedDateRange"
        class="predefinedDateRange form-control"

        onChange="this.form.submit()"
>
    <option <?php echo $default == 7 ? 'selected' : '' ?> value="7"><?php echo Text::_('COM_JOOMPROJECT_OPTION_SELECT_DATE_RANGE') ?></option>
    <option <?php echo $default == 1 ? 'selected' : '' ?> value="1"><?php echo Text::_('COM_JOOMPROJECT_OPTION_THIS_WEEK') ?></option>
    <option <?php echo $default == 2 ? 'selected' : '' ?> value="2"><?php echo Text::_('COM_JOOMPROJECT_OPTION_THIS_MONTH') ?></option>
    <option <?php echo $default == 3 ? 'selected' : '' ?> value="3"><?php echo Text::_('COM_JOOMPROJECT_OPTION_THIS_YEAR') ?></option>
    <option <?php echo $default == 4 ? 'selected' : '' ?> value="4"><?php echo Text::_('COM_JOOMPROJECT_OPTION_PREVIOUS_WEEK') ?></option>
    <option <?php echo $default == 5 ? 'selected' : '' ?> value="5"><?php echo Text::_('COM_JOOMPROJECT_OPTION_PREVIOUS_MONTH') ?></option>
    <option <?php echo $default == 6 ? 'selected' : '' ?> value="6"><?php echo Text::_('COM_JOOMPROJECT_OPTION_PREVIOUS_YEAR') ?></option>
</select>