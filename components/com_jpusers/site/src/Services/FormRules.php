<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\JPusers\Site\Service;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Component\Router\Rules\RulesInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\Database\DatabaseDriver;
use Joomla\CMS\Application\ApplicationHelper;
use function Symfony\Component\String\s;



/**
 * Rule to identify the right Itemid for a view in a component
 *
 * @since  3.4
 */
class FormRules implements RulesInterface
{
    /**
     * Router this rule belongs to
     *
     * @var   RouterView
     * @since 3.4
     */
    protected $router;

    /**
     * Lookup array of the menu items
     *
     * @var   array
     * @since 3.4
     */
    protected $lookup = array();
    /**
     * Class constructor.
     *
     * @param RouterView $router Router this rule belongs to
     *
     * @since   3.4
     */
    public function __construct(RouterView $router)
    {
        $this->router = $router;
        $this->buildLookup();

    }

    /**
     * Finds the right Itemid for this query
     *
     * @param array  &$query The query array to process
     *
     * @return  void
     *
     * @since   3.4
     */
    public function preprocess(&$query)
    {
        $active = $this->router->menu->getActive();

        /**
         * If the active item id is not the same as the supplied item id or we have a supplied item id and no active
         * menu item then we just use the supplied menu item and continue
         */
        if (isset($query['Itemid']) && ($active === null || $query['Itemid'] != $active->id)) {
            return;
        }

        // Get query language
        $language = isset($query['lang']) ? $query['lang'] : '*';

        // Set the language to the current one when multilang is enabled and item is tagged to ALL
        if (Multilanguage::isEnabled() && $language === '*') {
            $language = $this->router->app->get('language');
        }

        if (!isset($this->lookup[$language])) {
            $this->buildLookup($language);
        }

        // Check if the active menu item matches the requested query
        if ($active !== null && isset($query['Itemid'])) {
            // Check if active->query and supplied query are the same
            $match = true;

            foreach ($active->query as $k => $v) {
                if (isset($query[$k]) && $v !== $query[$k]) {
                    // Compare again without alias
                    if (\is_string($v) && $v == current(explode(':', $query[$k], 2))) {
                        continue;
                    }

                    $match = false;
                    break;
                }
            }

            if ($match) {
                // Just use the supplied menu item
                return;
            }
        }

        $needles = $this->router->getPath($query);

        $layout = isset($query['layout']) && $query['layout'] !== 'default' ? ':' . $query['layout'] : '';

        if ($needles) {
            foreach ($needles as $view => $ids) {
                $viewLayout = $view . $layout;

                if ($layout && isset($this->lookup[$language][$viewLayout])) {
                    if (\is_bool($ids)) {
                        $query['Itemid'] = $this->lookup[$language][$viewLayout];

                        return;
                    }

                    foreach ($ids as $id => $segment) {
                        if (isset($this->lookup[$language][$viewLayout][(int)$id])) {
                            $query['Itemid'] = $this->lookup[$language][$viewLayout][(int)$id];

                            return;
                        }
                    }
                }

                if (isset($this->lookup[$language][$view])) {
                    if (\is_bool($ids)) {
                        $query['Itemid'] = $this->lookup[$language][$view];

                        return;
                    }

                    foreach ($ids as $id => $segment) {
                        if (isset($this->lookup[$language][$view][(int)$id])) {
                            $query['Itemid'] = $this->lookup[$language][$view][(int)$id];

                            return;
                        }
                    }
                }
            }
        }

        // Check if the active menuitem matches the requested language
        if ($active && $active->component === 'com_' . $this->router->getName()
            && ($language === '*' || \in_array($active->language, array('*', $language)) || !Multilanguage::isEnabled())) {
            $query['Itemid'] = $active->id;

            return;
        }

        // If not found, return language specific home link
        $default = $this->router->menu->getDefault($language);

        if (!empty($default->id)) {
            $query['Itemid'] = $default->id;
        }
    }

    /**
     * Method to build the lookup array
     *
     * @param string $language The language that the lookup should be built up for
     *
     * @return  void
     *
     * @since   3.4
     */
    protected function buildLookup($language = '*')
    {
        // Prepare the reverse lookup array.
        if (!isset($this->lookup[$language])) {
            $this->lookup[$language] = array();

            $component = ComponentHelper::getComponent('com_' . $this->router->getName());
            $views = $this->router->getViews();

            $attributes = array('component_id');
            $values = array((int)$component->id);

            $attributes[] = 'language';
            $values[] = array($language, '*');

            $items = $this->router->menu->getItems($attributes, $values);

            foreach ($items as $item) {
                if (isset($item->query['view'], $views[$item->query['view']])) {
                    $view = $item->query['view'];

                    $layout = '';

                    if (isset($item->query['layout'])) {
                        $layout = ':' . $item->query['layout'];
                    }

                    if ($views[$view]->key) {
                        if (!isset($this->lookup[$language][$view . $layout])) {
                            $this->lookup[$language][$view . $layout] = array();
                        }

                        if (!isset($this->lookup[$language][$view])) {
                            $this->lookup[$language][$view] = array();
                        }

                        // If menuitem has no key set, we assume 0.
                        if (!isset($item->query[$views[$view]->key])) {
                            $item->query[$views[$view]->key] = 0;
                        }

                        /**
                         * Here it will become a bit tricky
                         * language != * can override existing entries
                         * language == * cannot override existing entries
                         */
                        if (!isset($this->lookup[$language][$view . $layout][$item->query[$views[$view]->key]]) || $item->language !== '*') {
                            $this->lookup[$language][$view . $layout][$item->query[$views[$view]->key]] = $item->id;
                            $this->lookup[$language][$view][$item->query[$views[$view]->key]] = $item->id;
                        }
                    } else {
                        /**
                         * Here it will become a bit tricky
                         * language != * can override existing entries
                         * language == * cannot override existing entries
                         */
                        if (!isset($this->lookup[$language][$view . $layout]) || $item->language !== '*') {
                            $this->lookup[$language][$view . $layout] = $item->id;
                        }
                    }
                }
            }
        }
    }

    /**
     * Dummy method to fulfil the interface requirements
     *
     * @param array  &$query The vars that should be converted
     * @param array  &$segments The URL segments to create
     *
     * @return  void
     *
     * @since   3.4
     * @codeCoverageIgnore
     */

    /**
     * Dummy method to fulfil the interface requirements
     *
     * @param array  &$query The vars that should be converted
     * @param array  &$segments The URL segments to create
     *
     * @return  void
     *
     * @since   3.4
     * @codeCoverageIgnore
     */
    public function build(&$query, &$segments)
    {
        $segments = self::JPusersBuildRoute($query);

    }

    /**
     * Dummy method to fulfil the interface requirements
     *
     * @param array  &$segments The URL segments to parse
     * @param array  &$vars The vars that result from the segments
     *
     * @return  void
     *
     * @since   3.4
     * @codeCoverageIgnore
     */
    public function parse(&$segments, &$vars)
    {
        $vars = self::JPusersParseRoute($segments);
        // empty segments is required for router to work
        $segments = [];

    }

    /**
     * Build the route for the com_jpusers component
     *
     * @param     array    $query    An array of URL arguments
     *
     * @return    array              The URL arguments to use to assemble the subsequent URL.
     */
    function JPusersBuildRoute(&$query)
    {
        $menu = \Joomla\CMS\Factory::getContainer()->get(SiteApplication::class)->getMenu();

        // if menu item exist and it's view same as $query view
        if (
            !empty($query['Itemid']) && // Itemid not empty
            isset($query['view']) && // view exist
            // view exist in itemid query
            $menu->getItem($query['Itemid'])->query['view'] == $query['view'] &&
            !isset($query['task']) // if has task query skip it
        ) {

            unset($query['view']);
            unset($query['id']);

            return [];
        }


        // We need to have a view in the query or it is an invalid URL
        if (!isset($query['view'])) {
            return array();
        }

        // Setup vars
        $segments = array();
        $view     = $query['view'];

        // We need a menu item.  Either the one specified in the query, or the current active one if none specified
        if (empty($query['Itemid'])) {
            $menu_item_given = false;
        }
        else {
            $menu_item_given = true;
        }

        // Handle users query
        if($view == 'users') {
            if (!$menu_item_given) $segments[] = $view;
            unset($query['view']);

            // Get project filter
            if (isset($query['filter_project'])) {
                if (strpos($query['filter_project'], ':') === false) {
                    $query['filter_project'] = self::JPusersMakeSlug($query['filter_project'], '#__jp_projects');
                }
            }
            else {
                $query['filter_project'] = self::JPusersMakeSlug('0', '#__jp_projects');
            }

            $segments[] = $query['filter_project'];
            unset($query['filter_project']);

            return $segments;
        }


        // Handle users query
        if($view == 'user') {
            if (!$menu_item_given) $segments[] = $view;
            unset($query['view']);

            // Get user id
            if (isset($query['id'])) {
                if (strpos($query['id'], ':') === false) {
                    $query['id'] = self::JPusersMakeSlug($query['id'], '#__users', 'username', 'username');
                }
            }
            else {
                $query['id'] = self::JPusersMakeSlug('0', '#__users', 'username', 'username');
            }

            $segments[] = 'profile';
            $segments[] = $query['id'];
            unset($query['id']);


            return $segments;
        }

        // Handle the layout
        if (isset($query['layout'])) {
            if ($menu_item_given && isset($menuItem->query['layout'])) {
                if ($query['layout'] == $menuItem->query['layout']) {
                    unset($query['layout']);
                }
            }
            else {
                if ($query['layout'] == 'default') {
                    unset($query['layout']);
                }
            }
        }

        return $segments;
    }
    /**
     * Parse the segments of a URL.
     *
     * @param     array    The segments of the URL to parse.
     *
     * @return    array    The URL attributes to be used by the application.
     */
    function JPusersParseRoute($segments)
    {
        // Setup vars
        $vars  = array();
        $count = count($segments);
        $menu  = \Joomla\CMS\Factory::getContainer()->get(SiteApplication::class)->getMenu();
        $item  = $menu->getActive();

        // Standard routing.  If we don't pick up an Itemid then we get the view from the segments
        // the first segment is the view and the last segment is the id of the item.
        if (!isset($item)) {
            $vars['view'] = $segments[0];
            $vars['id']   = $segments[$count - 1];

            return $vars;
        }

        // Set the view var
        $vars['view'] = $item->query['view'];

        // Handle Users
        if ($vars['view'] == 'users') {
            if ($count == 1) {
                $vars['filter_project'] = self::JPusersParseSlug($segments[0]);
            }
            if ($count > 1) {
                $vars['view'] = 'user';
                $vars['id']   = self::JPusersParseSlug($segments[1]);
            }

            return $vars;
        }


        // Handle User
        if ($vars['view'] == 'user') {
            if ($count == 1) {
                $vars['id'] = self::JPusersParseSlug($segments[0]);
            }

            return $vars;
        }

        return $vars;
    }


    /**
     * Parses a slug segment and extracts the ID of the item
     *
     * @param     string    $segment    The slug segment
     *
     * @return    int                   The item id
     */
    function JPusersParseSlug($segment)
    {
        if (strpos($segment, ':') === false) {
            return (int) $segment;
        }
        else {
            list($id, $alias) = explode(':', $segment, 2);
            return (int) $id;
        }
    }


    /**
     * Creates a slug segment
     *
     * @param     int       $id       The item id
     * @param     string    $table    The item table
     * @param     string    $alt      Alternative alias if the id is 0
     * @param     string    $field    The field to query
     *
     * @return    string              The slug
     */
    function JPusersMakeSlug($id, $table, $alt = 'all', $field = 'alias')
    {
        if ($id == '' || $id == '0') {
            if ($table == '#__jp_projects') {
                $app   = Factory::getApplication();
                $id    = (int) $app->getUserState('com_joomproject.project.active.id', 0);
                $alias = $app->getUserState('com_joomproject.project.active.title', 'all-projects');
                $alias = ApplicationHelper::stringURLSafe($alias);

                return $id . ':' . $alias;
            }
            else {
                return '0:' . $alt;
            }
        }

        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select($db->quoteName($field))
            ->from($db->quoteName($table))
            ->where('id = ' . (int) $id);

        $db->setQuery($query->__toString());

        $alias = $db->loadResult();
        $slug  = $id . ':' . $alias;

        return $slug;
    }
}
