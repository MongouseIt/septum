<?php
/**
 * @package      Joomproject
 * @subpackage   Tasks
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */


defined('_JEXEC') or die();

use Joomla\CMS\HTML\Helpers\Sidebar;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;


class JPtasksViewImport extends HtmlView
{
    public $type;
    public $column;
    public $heads;
    public $statistic;
    public $presets;
    public $item;
    public $user;
    public $name;
    public $owner_id;
    public $selected;
    public $list;
    public $fields;
    public $columns;
    public $sidebar;
    public $selectedFields;
    public $preset = false;
    public $headList = [];


    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        $this->user = Factory::getApplication()->getIdentity();

        $canImport = Factory::getUser()->authorise('can.import', 'com_jptasks');

        if(!$canImport){

            Factory::getApplication()->enqueueMessage('You are not allowed to import');
            Factory::getApplication()->redirect(Route::_('index.php?option=com_jptasks&view=tasks', false));

        }


        // import type task => 1
        Factory::getApplication()->input->set('import_type', 1);

        $this->type = 1;

        if ($this->getLayout() == 'params') {
            $this->_params($tpl);
            return;
        }


        switch ($app->input->get('step', 1)) {
            case 1:

                break;

            case 2:

                $this->presets = $this->get('presets');
                break;

            case 3:

                // setup params and fields association
                $this->getParams();


            case 4:

                // get import stats
                $this->statistic = new Registry(Factory::getSession()->get('importstat'));

                break;
        }

        if ($this->getLayout() !== 'modal') {

            $this->sidebar = Sidebar::render();
        }

        parent::display($tpl);
    }

    public function _params($tpl)
    {
        $app = Factory::getApplication();
        $type = 'tasks';
        $this->type = $type;
        $this->heads = Factory::getSession()->get('headers', array(), 'import');
        //$this->fields = BaseDatabaseModel::getInstance('Fields', 'JoomcrmModel')->getFormFields($type->id);
        $this->item = $this->get('Preset');

        //var_dump($this->item);

        parent::display($tpl);
        Factory::getApplication()->close();
    }


    public function getParams()
    {

        $model = \Joomla\CMS\MVC\Model\BaseDatabaseModel::getInstance('Import', 'JPtasksModel', array('ignore_request' => true));
        $jinput = Factory::getApplication()->input;

        $this->preset = $model->getPreset();



        $presetVal = $jinput->get('preset', '', 'string');

        // no preset selected return empty
        if (empty($presetVal))
            return '';


        $db = Factory::getDBO();
        $this->columns = [];


        // columns to import
        $this->fields = [
            'title' => ['required' => true],
            'description' => ['required' => false],
            'start_date' => ['required' => true],
            'end_date' => ['required' => false],
            'created' => ['required' => false],
            'complete' => ['required' => false],
            'completed' => ['required' => false],
            'rate' => ['required' => false],
            'estimate' => ['required' => false],
        ];


        // include table fields
        foreach ($this->fields as $fieldName => $fieldParams) {
            $columnInfo = new stdClass();
            $columnInfo->name = $fieldName;
            $columnInfo->label = 'COM_JPTASKS_IMPORT_COLUMN_NAME_' . strtoupper($fieldName);
            $columnInfo->required = $fieldParams['required'];
            $this->columns[] = $columnInfo;

        }

        if ($presetVal != 'new') {
            $this->owner_id = isset($this->preset->params->owner_id) ? $this->preset->params->owner_id : '';
            $this->selectedFields = ArrayHelper::fromObject($this->preset->params->get('field'));

            $this->preset->params = $this->preset->params->toArray();
            $this->preset->params['name'] = $this->preset->name;
        } else {
            $this->name = $this->owner_id = '';
            $this->selectedFields = false;
        }

        $this->heads = json_decode(Factory::getSession()->get('fileHeaders', '{}'));



    }

}
