<?php
/**
 * JoomProject
 *
 * @author     JoomBoost <support@joomboost.com>
 * @copyright  Copyright (C) 2018 Joomboost.com All Rights Reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Website: https://www.joomboost.com
 */

defined('_JEXEC') or die();

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\Archive\Archive;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;use Joomla\Utilities\ArrayHelper;
use JoomProject\Import\Upload;

class JPtasksControllerImport extends BaseController
{

    public $heads = [];
    public $key = null;
    public $json;
    public $db;
    public $dir;

    public function __construct($config = array())
    {
        parent::__construct($config);

        if (!$this->input)
        {
            $this->input = Factory::getApplication()->input;

        }
        Factory::getApplication()->input->set('view', 'import');
        Factory::getApplication()->input->set('layout',  Factory::getApplication()->input->get('layout', 'default'));
    }


    public function import()
    {

        // inits
        $user    = Factory::getApplication()->getIdentity();
        $app     = Factory::getApplication();
        $imports = Table::getInstance('Import', 'JoomprojectTable');

        $db = Factory::getDbo();
        $insertType = Factory::getApplication()->input->get('InsertType', 0, 'INT'); // how to import if items existing skip => 0, update => 1, duplicate 2 ..

        // get preset params from request
        $params  = $this->input->get('jform', array(), 'array');

        // get preset name
        $name = $params['name'];

        // remove non needed params
        unset($params['elements']); // cuz used to reload fields on step 3
        unset($params['name']); // no need to store name on params cuz already retrieved will be stored on name column

        // save preset data
        if ($this->input->get('preset') == 'new') // when new preset
        {
            $save = array(
                'user_id'  => $user->get('id'), // current user id
                'type_id'  => 1, // type id always 1 cuz task type means 1 when importing, 2 for importing milestones list ....
                'params'   => json_encode($params), // form params to be saved, including project,
                'name'     => $name,
                'crossids' => '[]'
            );
            $imports->save($save);
        }
        else // existing preset
        {
            $imports->load($this->input->get('preset'));
            $imports->params = json_encode($params);
            $imports->name   = $name;
            $imports->store();
        }

        // start importing data
        try
        {
            // remove exection limits
            if (function_exists('set_time_limit'))
            {
                set_time_limit(0);
            }
            if (function_exists('ini_set'))
            {
                ini_set('max_execution_time', 0);
            }


            $params     = new Registry($imports->params);
            $import_key = Factory::getSession()->get('key', null);
            $stat = ['new' => 0, 'update' => 0, 'exist' => 0];


            $db->setQuery("SELECT * FROM #__jp_import_rows WHERE `import` = '{$import_key}'");
            $list = $db->loadObjectList();

            if (!$list)
            {
                $this->setRedirect('index.php?option=com_jptasks&view=import',\Joomla\CMS\Language\Text::_('COM_JOOMPROJECT_IMPORTROWSARENOTFOUND'),'warning');
                $this->redirect();
                return;
            }

            // set preset to session
            $selectedPreset = [];
            $selectedPreset['id'] = $imports->id;
            $selectedPreset['type_id'] = 1; // always 1 whcih means we import tasks, for others for example if 2 means milestones ...
            Factory::getSession()->set('selectedPreset', $selectedPreset);

            // add insert type to session
            Factory::getSession()->set('messageInsertType', $insertType);

            // set stats info
            $stat['total'] = count($list); // total tasks to import
            $stat['skipped'] = 0;


            // todo: complete errors log if item not saved
            $errorsLog = [];



            // start importing
            foreach ($list as $record)
            {

                // get tasks model
                $modelStore = BaseDatabaseModel::getInstance('Task', 'JPtasksModel', array('ignore_request' => true));

                $row       = (array) json_decode($record->text); // row imported from csv
                $keys = array_keys($row); // get keys

                $insertRow = new stdClass(); // row to be inserted

                // get row from mapped fields
                foreach ($params->get('field') as $column => $mappedColumn)
                {

                    if(!isset($keys[$mappedColumn-1]))
                        continue;

                    if (!empty($row[$keys[$mappedColumn-1]]))
                    {
                        $insertRow->$column = $row[$keys[$mappedColumn-1]];
                    }
                }

                // convert to array
                $insertRow = (array) $insertRow;

                // skip empty row
                if (empty($insertRow)){

                    $stat['skipped'] += 1;
                    continue;
                }

                // check if item to insert is duplicate
                $duplicatedItem = $this->getDuplicatedItem($insertRow,$params);

                $isDuplicated = false;

                if(is_object($duplicatedItem))
                    $isDuplicated = true;


                //set owner id
                $insertRow['created_by'] = Factory::getUser()->id;

                //set import (used in models to detect importing mode
                $insertRow['import'] = $import_key;

                // add project id
                $insertRow['project_id'] = $params->get('project_id');

                // add milestone id
                $insertRow['milestone_id'] = $params->get('milestone_id');

                // add list id
                $insertRow['list_id'] = $params->get('list_id');


                // start adding

                if ($insertType == 0 && $isDuplicated) // item to insert already exist, so we skip it.
                { // skip item

                    $stat['exist'] += 1;
                    continue;

                }

                if($insertType == 0 && !$isDuplicated){

                    $stat['new'] += 1;

                    if(!$modelStore->save($insertRow)){

                        $errorsLog[] = [
                            'recordTitle' => $insertRow['title'],
                            'recordError' => $modelStore->getError()
                        ];

                    }

                    continue;
                }

                if ($insertType == 1) // item to insert already exist, so we update it.
                { //  Update item


                    if($isDuplicated){ // mean update
                        $insertRow['id'] = $duplicatedItem->id;
                        $stat['update'] += 1;
                    }
                    else{ // mean new one

                        $stat['new'] += 1;
                    }

                    if(!$modelStore->save($insertRow)){

                        $errorsLog[] = [
                            'recordTitle' => $insertRow['title'],
                            'recordError' => $modelStore->getError()
                        ];

                    }

                    continue;

                }

                if ($insertType == 2) // item to insert already exists so we duplicate it
                { // duplicate item

                    $currentDate = Factory::getDate()->format('Y-m-d H:i:s');
                    $insertRow['title'] = $insertRow['title'] . "[ Duplicated at $currentDate]";

                    $insertRow['import'] = true;

                    if(!$modelStore->save($insertRow)){

                        $errorsLog[] = [
                            'recordTitle' => $insertRow['title'],
                            'recordError' => $modelStore->getError()
                        ];

                    }

                    $stat['new'] += 1;
                }



            }


            Factory::getSession()->set('messageState', Text::_('COM_JOOMPROJECT_IMPORT_WAS_SUCCESSFUL'));

        }
        catch (Exception $e)
        {
            echo $e->getMessage();
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
        }

        Factory::getSession()->set('importstat', $stat);

        Factory::getApplication()->redirect(Route::_(
            'index.php?option=com_jptasks&view=import&step=4&import_type=1', false)
        );
    }

    /*
     * get duplicated item by title
     */
    public function getDuplicatedItem($dataToInsert,$params){

        $params = $params->toArray();

        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from('#__jp_tasks');
        $query->where('title = '.$db->quote($dataToInsert['title']));
        $query->where('project_id = '.$params['project_id']);

        if(isset($params['milestone_id']) && $params['milestone_id'] > 0){
            $query->where('milestone_id = '.$db->quote($params['milestone_id']));
        }

        if(isset($params['list_id']) && $params['list_id'] > 0){
            $query->where('list_id = '.$db->quote($params['list_id']));
        }


        $db->setQuery($query);
        return $db->loadObject();
    }


    public function analize()
    {

        $this->key = $this->input->get('json');
        $file      = $this->input->getString('file');

        $ext        = strtolower(File::getExt($file));
        $this->json = JPATH_ROOT . '/tmp/' . $this->key . '.json';

        $upload = JPATH_ROOT . '/tmp/import_uploads/' . urldecode($file);

        if (!File::exists($upload))
        {
            $this->_error('COM_JOOMPROJECT_IMPORTCANNOTFINDFILE');
        }

        if (!in_array($ext,
            array(
                'zip',
                'csv',
                'json'
            ))
        )
        {
            $this->_error('COM_JOOMPROJECT_IMPORTWRONGEXT');
        }

        if ($ext == 'zip')
        {

            $this->_msg(10, 'COM_JOOMPROJECT_IMPORTEXTRCT');

            $dir       = JPATH_ROOT . '/tmp/import_extract/' . $this->key;

            $this->dir = $dir;

            if (!Folder::exists($dir))
            {
                Folder::create($dir);
            }

            $jarchive = new \Joomla\Archive\Archive();

            if (!$jarchive->extract($upload, $dir))
            {
                $this->_error('COM_JOOMPROJECT_IMPORTCANNOTEXTRACT');
            }

            $files = \Joomla\CMS\Filesystem\Folder::files($dir, '\.(csv|json)$', true, true);

            if (count($files) == 0)
            {
                $this->_error('COM_JOOMPROJECT_IMPORTNOFOUND');
            }

            if (count($files) > 1)
            {
                $this->_error('COM_JOOMPROJECT_IMPORTMORETHANONE');
            }

            $upload = $files[0];
        }

        $this->_msg(20, 'COM_JOOMPROJECT_IMPORTPARCE');

        if (!\Joomla\CMS\Filesystem\File::exists($upload))
        {

            $this->_error('COM_JOOMPROJECT_IMPORTCANNOTFINDFILE');
        }



        $this->db = Factory::getDbo();

        $this->heads = array();

        $this->db->setQuery("DELETE FROM `#__jp_import_rows` WHERE `ctime` < NOW() - INTERVAL 1 DAY OR `import` = {$this->key}");
        $this->db->execute();

        $ext = strtolower(\Joomla\CMS\Filesystem\File::getExt($upload));

        if ($ext == 'csv')
        {
            $this->_load_csv($upload, $this->input->get('delimiter', ','));
        }
        elseif ($ext == 'json')
        {
            $this->_load_json($upload);
        }


        Factory::getSession()->set('fileHeaders', json_encode($this->heads));
        Factory::getSession()->set('key', $this->key);

        $this->_msg(100, 'COM_JOOMPROJECT_IMPORTPARCE');

        echo json_encode(['success' => true]);


        die;

    }

    /*
     * Return json file status
     */
    public function getJsonStatus(){

        // json file name
        $name = Factory::getApplication()->input->get('name',0,'int');

        // get file content
        $content = file_get_contents(JPATH_ROOT.'/tmp/'.$name.'.json');

        header('Content-Type: application/json');

        echo $content;

        die;
    }

    private function _error($msg)
    {
        if (!empty($this->dir))
        {
            Folder::delete($this->dir);
        }
        File::write($this->json, json_encode(array(
            'error' => Text::_($msg)
        )));


        die;
    }

    private function _msg($stat, $msg)
    {
        File::write($this->json, json_encode(array(
            'status' => $stat,
            'msg'    => Text::_($msg)
        )));
    }

    private function _load_csv($filename = '', $delimiter = ',')
    {

        $header = null;

        $csv = new ParseCsv\Csv();
        $csv->encoding('UTF-8', 'UTF-8');


        //$csv->delimiter = $delimiter;
        $csv->auto($filename);

        $data = [];

        foreach ($csv->data as $row){

            foreach ($csv->titles as $csvColumn){
                $data[$csvColumn] = isset($row[$csvColumn]) ? $row[$csvColumn] : '';
            }

            $this->_row($data);
        }

    }

    private function _row($data)
    {

        $sql = "INSERT INTO #__jp_import_rows (id, `import`,`text`,`ctime`) VALUES (null, %d, '%s', NOW())";

        $sql = sprintf($sql, $this->key, $this->db->escape(json_encode($data)));
        $this->db->setQuery($sql);
        $this->db->execute();

        $columns     = array_keys($data);
        $this->heads = array_unique(array_merge($this->heads, $columns));


    }

    private function _load_json($file)
    {
        $body = json_decode(file_get_contents($file));
        foreach ($body as $row)
        {
            $this->_row($row);
        }
    }



    public function upload()
    {

        $options = array(
            'accept_file_types' => '/\.(zip|json|csv)$/i',
            'upload_dir'        => JPATH_ROOT . '/tmp/import_uploads/'
        );

        Factory::getSession()->set('importprocess', 0);

        if (!Folder::exists($options['upload_dir']))
        {
            Folder::create($options['upload_dir']);
        }

        $upload = new Upload($options);

        die;
    }

    public function map_table($n, $m)
    {
        return (array($n => $m));
    }

    public function deletePreset()
    {
        $model = $this->getModel('import');

        $model->deletePreset();

        $this->setRedirect('index.php?option=com_jptasks&view=import&step=2','Preset successfully deleted','success');
    }

    public function cancel(){

        $this->setRedirect('index.php?option=com_jptasks&view=import');
    }

}