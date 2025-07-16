<?php
/**
 * @package      Joomproject
 * @subpackage   Dashboard
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2006-2012 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

JLoader::registerNamespace("Joomla\\Component\\JPprojects\\Site\\Service", JPATH_SITE . '/components/com_jpprojects/src/Services');

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Categories\CategoryFactoryInterface;
use Joomla\CMS\Categories\CategoryInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Component\Router\RouterViewConfiguration;
use Joomla\CMS\Component\Router\Rules\MenuRules;
use Joomla\CMS\Component\Router\Rules\NomenuRules;
use Joomla\CMS\Component\Router\Rules\StandardRules;
use Joomla\CMS\Menu\AbstractMenu;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;


/**
 * Routing class of com_joomfaqs
 *
 * @since  3.3
 */
class JPprojectsRouter extends RouterView
{
    /**
     * Flag to remove IDs
     *
     * @var    boolean
     */
    protected $noIDs = false;

    /**
     * The category factory
     *
     * @var CategoryFactoryInterface
     *
     * @since  4.0.0
     */
    private $categoryFactory;

    /**
     * The category cache
     *
     * @var  array
     *
     * @since  4.0.0
     */
    private $categoryCache = [];

    /**
     * The db
     *
     * @var DatabaseInterface
     *
     * @since  4.0.0
     */
    private $db;

    /**
     * Content Component router constructor
     *
     * @param SiteApplication $app The application object
     * @param AbstractMenu $menu The menu object to work with
     * @param CategoryFactoryInterface $categoryFactory The category object
     * @param DatabaseInterface $db The database object
     */
    public function __construct(SiteApplication $app, AbstractMenu $menu)
    {


        parent::__construct($app, $menu);
        $this->attachRule(new \Joomla\Component\JPprojects\Site\Service\FormRules($this));
        /*$this->attachRule(new MenuRules($this));
         $this->attachRule(new StandardRules($this));
         $this->attachRule(new NomenuRules($this));*/
    }



}


