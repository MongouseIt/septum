<?php
/**
 * JoomProject
 * @author     JoomBoost <support@joomboost.com>
 * @copyright  Copyright (C) 2018 Joomboost.com All Rights Reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Website: https://www.joomboost.com
 */
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseModel;
use Joomla\Registry\Registry;
use Joomla\CMS\Factory;

class JPtasksModelImport extends BaseModel
{
    protected $db = null;
    protected $query = null;


    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->db = Factory::getDbo();

    }

    public function getPresets()
    {
        $this->db->setQuery("SELECT id as value, name as text FROM #__jp_import WHERE type_id = 1 AND user_id = " . (int)Factory::getApplication()->getIdentity()->get('id', 0));

        return $this->db->loadObjectList();
    }

    public function getPreset()
    {
        $this->db->setQuery("SELECT * FROM #__jp_import WHERE type_id = 1 and id = " . (int)Factory::getApplication()->input->get('preset'));
        $preset = $this->db->loadObject();

        if(!@$preset)
        {
            return NULL;
        }

        @$preset->params = new Registry(@$preset->params);

        return $preset;
    }
    public function deletePreset(){

        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $conditions = array(
            $db->quoteName('id') . ' =' .(int)Factory::getApplication()->input->get('preset')

        );

        $query->delete($db->quoteName('#__jp_import'));
        $query->where($conditions);
        $db->setQuery($query);
        $db->execute();
    }

}