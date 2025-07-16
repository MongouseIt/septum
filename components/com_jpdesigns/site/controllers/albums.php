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

use Joomla\CMS\MVC\Controller\AdminController;


jimport('joomla.application.component.controlleradmin');


/**
 * Design Album List Controller
 *
 */
class JPdesignsControllerAlbums extends AdminController
{
    /**
     * The default list view
     *
     * @var    string
     */
    protected $view_list = 'albums';

    /**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 */
    protected $text_prefix = "COM_JOOMPROJECT_DESIGNS_ALBUMS";


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
