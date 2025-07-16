<?php
/**
 * @package      Joomproject
 * @subpackage   Tasks
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Form\Field\ListField;

FormHelper::loadFieldClass('list');


/**
 * Form Field class for selecting a task list.
 *
 */
class JFormFieldAlbums extends ListField
{
    /**
     * The form field type.
     *
     * @var    string
     */
    public $type = 'Albums';

    protected $project;

    protected $milestone;




    /**
     * Method to get the field list options markup.
     *
     * @return    array      $options      The list options markup.
     */
    public function getOptions() {


        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        // Construct the query
        $query->select('c.id AS value, c.title AS text')
            ->from('#__jp_design_albums AS c')
            ->join('INNER', '#__jp_designs AS a ON a.album_id = c.id')
            ->group('c.id')
            ->order('c.title');

        // Return the result
        $db->setQuery((string) $query);
        $rows =  (array) $db->loadObjectList();

        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $rows);
        return $options;

    }
}
