<?php
/**
 * @package      Joomproject
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Form\FormField;


jimport('joomla.form.helper');


/**
 * Field to select a user id from a modal list.
 *
 */
class JFormFieldRolepermissions extends FormField
{

    public $type = 'Rolepermissions';
    public $enabledExtensions = [];
    public $extensionsPermissions = [];

    public function __construct($form = null)
    {

        $this->loadExtensions();
        $this->loadPermissions();

        parent::__construct($form);
    }


    /**
     * @return string
     */
    public function getInput(): string
    {

        return LayoutHelper::render('fields.permissions',['extensionsPermissions' => $this->extensionsPermissions,'value' => $this->value]);
    }


    public function loadExtensions(){


        $extensions = JPapplicationHelper::getComponents('com_jpreminders');

        foreach ($extensions as $extension){

            if($extension->enabled)
                $this->enabledExtensions[] = $extension->element;

        }

        return $this->enabledExtensions;

    }


    public function loadPermissions(){

        foreach($this->enabledExtensions as $extension){

            if(file_exists($permissionsFile = JPATH_ADMINISTRATOR.'/components/'.$extension.'/permissions.json'))
                $this->extensionsPermissions[$extension] = json_decode(file_get_contents($permissionsFile));

        }

    }




}