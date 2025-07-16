<?php
/**
 * @package      Joomproject Pro
 * @subpackage   Designs
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;



class JPdesignsController extends BaseController
{
    /**
     * The default view
     *
     * @var    string
     */
    protected $default_view = 'designs';


    public function display($cachable = false, $urlparams = false)
    {
        if (version_compare(JVERSION, '3', 'lt')) {
            JPdesignsHelper::addSubmenu(Factory::getApplication()->input->get('view', $this->default_view));
        }

        parent::display();

        return $this;
    }
}
