<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jprepo
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

JLoader::register('JoomprojectHelperFrontend', JPATH_ROOT . '/components/com_joomproject/helpers/joomproject.php');


/**
 * Repository HTML helper class
 *
 */
abstract class JHtmlJPrepo
{
    /**
     * Returns a label for an item attachment count
     *
     * @param     integer    $count    The attachment count
     *
     * @return    string
     */
    public static function attachmentsLabel($count = 0)
    {
        if (!$count) return '';

        return ' <span class="badge"><i class="fas fa-flag text-white"></i> ' . (int) $count . '</span> ';
    }


    /**
     * Returns a list with attachments
     *
     * @param     array     $items    The attachment objects
     *
     * @return    string
     */
    public static function attachments($items = array())
    {
        if (!is_array($items) || !count($items)) return '';

        $user   = Factory::getApplication()->getIdentity();
        $levels = $user->getAuthorisedViewLevels();
        $admin  = $user->authorise('core.admin', 'com_jprepo');
        $html[] = '<ul class="list-group list-group-flush">';

        foreach($items AS $item)
        {
            if (!isset($item->repo_data) || empty($item->repo_data)) {
                continue;
            }

            $data = &$item->repo_data;

            if (!$admin && !in_array($data->access, $levels)) {
                continue;
            }

            list($asset, $id) = explode('.', $item->attachment, 2);

            $link = '#';

            if ($asset == 'directory') {
                $icon = '<i class="text-muted far fa-folder me-2"></i> ';
                $link = JPrepoHelperRoute::getRepositoryRoute($data->project_id . ':' . $data->project_alias, $data->id . ':' . $data->alias, $data->path);
            }

            if ($asset == 'note') {
                $icon = '<i class="text-muted far fa-edit me-2"></i> ';
                $link = JPrepoHelperRoute::getNoteRoute($data->id . ':' . $data->alias, $data->project_id . ':' . $data->project_alias, $data->dir_id . ':' . $data->dir_alias, $data->path);
            }

            if ($asset == 'file') {
	            $icon_class = JoomprojectHelperFrontend::extToIcon(strtolower($data->file_extension));
	            $icon = '<i class="text-muted  me-2 far fa-'.$icon_class.'"></i> ';
                $link = JPrepoHelperRoute::getFileRoute($data->id . ':' . $data->alias, $data->project_id . ':' . $data->project_alias, $data->dir_id . ':' . $data->dir_alias, $data->path);
            }

            $html[] = '<li class="list-group-item d-flex justify-content-between align-items-center">';
	        $html[] = '<span>';
            $html[] = $icon;
            $html[] = '<a href="' . Route::_($link) . '">';
            $html[] = htmlspecialchars($data->title, ENT_COMPAT, 'UTF-8');
            $html[] = '</a>';
	        $html[] = '</span>';
            $html[] = '</li>';
        }

        $html[] = '</ul>';

        return implode('', $html);
    }


    /**
     * Displays a batch widget for moving or copying items.
     *
     * @param     string    $project    The project id
     * @param     string    $dir        The current browsing directory
     *
     * @return    string                The necessary HTML for the widget.
     */
    public static function batchItem($project, $dir)
    {
        // Create the copy/move options.
        $options = array(
            HTMLHelper::_('select.option', 'c', Text::_('JLIB_HTML_BATCH_COPY')),
            HTMLHelper::_('select.option', 'm', Text::_('JLIB_HTML_BATCH_MOVE'))
        );

        $paths = self::pathOptions($project);

        // Create the batch selector to change select the category by which to move or copy.
        $lines = array(
            '<label id="batch-choose-action-lbl" for="batch-choose-action">',
            Text::_('COM_JOOMPROJECT_REPO_BATCH_MENU_LABEL'),
            '</label>',
            '<fieldset id="batch-choose-action" class="combo">',
            '<select name="batch[parent_id]" class="inputbox" id="batch-parent-id">',
            '<option value="">' . Text::_('JSELECT') . '</option>',
            HTMLHelper::_('select.options', $paths, 'value', 'text', (int) $dir),
            '</select>',
            HTMLHelper::_('select.radiolist', $options, 'batch[move_copy]', '', 'value', 'text', 'm'),
            '</fieldset>'
        );

        return implode("\n", $lines);
    }


    /**
     * Build a list of directory paths
     *
     * @param     string     $project    The project id
     * @param     integer    $exclude    The directory id to exclude
     *
     * @return    array                  The path array
     */
    public static function pathOptions($project, $exclude = null)
    {
        $user  = Factory::getApplication()->getIdentity();
        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        if ((int) $project == 0) {
            return array();
        }

        // Construct the query
        $query->select('a.id AS value, a.path AS text')
              ->from('#__jp_repo_dirs AS a')
              ->where('a.project_id = ' . $project);

        // Implement View Level Access
        if (!$user->authorise('core.admin')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('a.access IN (' . $groups . ')');
        }

        if (is_numeric($exclude)) {
            $query->where('a.id != ' . $db->quote((int) $exclude));
        }

        if (is_array($exclude)) {
            \Joomla\Utilities\ArrayHelper::toInteger($exclude);

            $query->where('a.id NOT IN(' . implode(', ', $exclude) . ')');
        }

        $query->order('a.path');

        $db->setQuery($query);

        $list    = (array) $db->loadObjectList();
        $options = array();

        foreach($list AS $item)
        {
            $options[] = HTMLHelper::_('select.option',
                (int) $item->value, htmlspecialchars($item->text, ENT_COMPAT, 'UTF-8')
            );
        }

        return $options;
    }
}
