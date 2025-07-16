<?php
/**
 * @package      Joomproject
 * @subpackage   Forum
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2006-2012 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\Controller\AdminController;


jimport('joomla.application.component.controlleradmin');


/**
 * Joomproject Replies List Controller
 *
 */
class JPforumControllerReplies extends AdminController
{
    /**
     * The default view
     *
     * @var    string
     */
    protected $view_list = 'replies';

    /**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 */
    protected $text_prefix = "COM_JOOMPROJECT_REPLIES";


    /**
     * Method to get a model object, loading it if required.
     *
     * @param     string    $name      The model name. Optional.
     * @param     string    $prefix    The class prefix. Optional.
     * @param     array     $config    Configuration array for model. Optional.
     *
     * @return    object               The model.
     */
    public function &getModel($name = 'ReplyForm', $prefix = 'JPforumModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }


    /**
	 * Removes an item.
	 *
	 * @return  void
	 */
	public function delete()
	{
	   parent::delete();

       $project = \Joomla\CMS\Factory::getApplication()->input->getUInt('filter_project');
       $topic   = \Joomla\CMS\Factory::getApplication()->input->getUInt('filter_topic');

       $link = 'index.php?option=' . $this->option . '&view=' . $this->view_list
             . '&filter_project=' . $project
             . '&filter_topic=' . $topic;

       $this->setRedirect(Route::_($link, false));
    }


    /**
	 * Method to publish a list of items
	 *
	 * @return  void
	 */
	public function publish()
	{
	   parent::publish();

       $project = \Joomla\CMS\Factory::getApplication()->input->getUInt('filter_project');
       $topic   = \Joomla\CMS\Factory::getApplication()->input->getUInt('filter_topic');

       $link = 'index.php?option=' . $this->option . '&view=' . $this->view_list
             . '&filter_project=' . $project
             . '&filter_topic=' . $topic;

       $this->setRedirect(Route::_($link, false));
    }


    /**
     * Gets the URL arguments to append to an item redirect.
     *
     * @param     int       $id         The primary key id for the item.
     * @param     string    $url_var    The name of the URL variable for the id.
     *
     * @return    string                The arguments to append to the redirect URL.
     */
    protected function getRedirectToItemAppend($id = null, $url_var = 'id')
    {
        // Need to override the parent method completely.
        $tmpl    = \Joomla\CMS\Factory::getApplication()->input->getCmd('tmpl');
        $layout  = \Joomla\CMS\Factory::getApplication()->input->getCmd('layout');
        $item_id = \Joomla\CMS\Factory::getApplication()->input->getInt('Itemid');
        $return  = $this->getReturnPage();
        $append  = '';

        // Setup redirect info.
        if ($tmpl)    $append .= '&tmpl=' . $tmpl;
        if ($layout)  $append .= '&layout=' . $layout;
        if ($id)      $append .= '&' . $id.'=' . $id;
        if ($item_id) $append .= '&Itemid=' . $item_id;
        if ($return)  $append .= '&return='.base64_encode($return);

        return $append;
    }


    /**
     * Get the return URL.
     * If a "return" variable has been passed in the request
     *
     * @return    string    The return URL.
     */
    protected function getReturnPage()
    {
        $return = \Joomla\CMS\Factory::getApplication()->input->get('return', null, 'base64');

        if (empty($return) || !Uri::isInternal(base64_decode($return))) {
            return Uri::base();
        }

        return base64_decode($return);
    }
}
