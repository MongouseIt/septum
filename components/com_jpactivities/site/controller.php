<?php
/**
 * @package      pkg_jpactivities
 * @subpackage   com_jpactivities
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2013 JoomBoost.com. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\MVC\Controller\BaseController;


/**
 * User Activity Component Controller
 *
 */
class JPactivitiesController extends BaseController
{
    /**
     * Method to display a view.
     *
     * @param     boolean        If true, the view output will be cached
     * @param     array          An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
     *
     * @return    jcontroller    This object to support chaining.
     */
    public function display($cachable = false, $urlparams = false)
    {
        $app = \Joomla\CMS\Factory::getApplication();
        if (!$app->input->getCmd('view')) {
            $app->input->set('view', 'activities');
        }

        $safeurlparams = array(
            'limit'            => 'UINT',
            'limitstart'       => 'UINT',
            'showall'          => 'INT',
            'return'           => 'BASE64',
            'filter_search'    => 'STRING',
            'filter_extension' => 'STRING',
            'filter_event_id'  => 'CMD',
            'filter_order'     => 'CMD',
            'filter_order_Dir' => 'CMD',
            'lang'             => 'CMD',
            'Itemid'           => 'INT'
        );

        parent::display($cachable, $safeurlparams);

        return $this;
    }
}
