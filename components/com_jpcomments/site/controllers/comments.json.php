<?php
/**
 * @package      Joomproject
 * @subpackage   Comments
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2006-2012 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomproject.controller.admin.json');


/**
 * Joomproject Comment List Controller
 *
 */
class JPcommentsControllerComments extends JPControllerAdminJson
{
    /**
     * The default view
     *
     * @var    string
     */
    protected $view_list = 'comments';


    /**
     * Method to get a model object, loading it if required.
     *
     * @param     string    $name      The model name. Optional.
     * @param     string    $prefix    The class prefix. Optional.
     * @param     array     $config    Configuration array for model. Optional.
     *
     * @return    object               The model.
     */
    public function &getModel($name = 'Form', $prefix = 'JPcommentsModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }
}
