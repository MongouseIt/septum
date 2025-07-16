<?php
/**
 * @package      Joomproject Pro
 * @subpackage   Designs
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

/**
 * Album List JSON Controller
 *
 */
class JPdesignsControllerAlbums extends JPControllerAdminJson
{
    /**
     * The default view
     *
     */
    protected $view_list = 'albums';


    /**
     * Method to get a model object, loading it if required.
     *
     * @param     string    $name      The model name. Optional.
     * @param     string    $prefix    The class prefix. Optional.
     * @param     array     $config    Configuration array for model. Optional.
     *
     * @return    object               The model.
     */
    public function &getModel($name = 'AlbumForm', $prefix = 'JPdesignsModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }
}
