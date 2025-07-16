<?php
/**
 * @package      Joomproject
 * @subpackage   Tasks
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

namespace JoomProject\Export;

use League\Csv\Writer;
use Joomla\CMS\Language\Text;
defined('_JEXEC') or die();

class Csv
{

    public $header = [];
    public $records = [];
    public $filename = '';
    public $context = '';
    public $csv;

    public function __construct($params)
    {

        // init
        $this->context = $params['context'];
        $this->filename = $params['context'].'_'. \Joomla\CMS\Factory::getDate()->format('y-m-d').'.csv';
        $this->header = $params['header'];
        $this->records = $params['records'];

        //load the CSV document from a string
        $this->csv = Writer::createFromString();

        // translate header
        $this->translateHeader();

        //insert the header
        $this->csv->insertOne($this->header);

        //insert all the records
        $this->csv->insertAll($this->records);

    }

    public function translateHeader(){

        foreach ($this->header as $ckey => $cvalue){
            $this->header[$ckey] = Text::_('COM_JOOMPROJECT_CSV_HEADER_'.$cvalue);
        }

    }

    public function export(){

        header('Content-Type: application/octet-stream');
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=\"" . $this->filename . "\"");

        echo $this->csv->toString(); //returns the CSV document as a string
        die;

    }

}