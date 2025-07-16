<?php

/**
 * @package      Joomproject
 * @subpackage   Projects
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */



defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Crypt\Crypt;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

abstract class JoomprojectSmartTag
{
	/**
	 * Factory Class
	 *
	 * @var object
	 */
    protected $factory;

    /**
	 * Joomla Application object
	 *
	 * @var object
     */
    protected $app;

    /**
	 * Joomla Document
	 *
	 * @var object
     */
    protected $doc;

    /**
     * Useful data used by a Smart Tag
     * 
     * @var  array
     */
    protected $data;

    /**
     * Parsed Options
     * 
     * @var  array
     */
    protected $parsedOptions;

    /**
     * Smart Tags Configuration Options
     * 
     * @var  array
     */
    protected $options;

    /**
     * Indicates whether this Smart Tag is a Pro-only feature
     *
     * @var boolean
     */
    public $proOnly = false;

    public function __construct($factory = null, $options = null)
    {
        if (!$factory)
        {
            $factory = new JoomprojectFactory();
        }
        $this->factory = $factory;
        
		$this->app = $this->factory->getApplication();
        $this->doc = $this->factory->getDocument();

        $this->parsedOptions = isset($options['options']) ? $options['options'] : new Registry();

        $this->options = $options;

    }


    /**
     * Set the data
     * 
     * @param   array  $data
     * 
     * @return  void
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * This method runs before replacements and determines whether the class can be executed and do replacements or not.
     * 
     * THE PROBLEM: 
     * 
     * Let's say we have a bunch of Smart Tags in a namespaced folder and we register them using the register() method. 
     * The Smart Tags include, Foo and Bar. Let's say our replacement subject is: 'lorem {foo.x} ipsum {foo.y} lorem ipsum {bar.x}' 
     * and we'd like to replace {foo.x} and {foo.y} and leave {bar.x} untouched. Right now this is not possible. 
     * All 3 Smart Tags will be replaced in the subject because all classes are already registered.
     * 
     * This problem occurs also in Convert Forms during form rendering. When a form is using Calculations, it's very likely 
     * a calculation formula in the form {field.XXX} + {field.YYY} is included in the form's HTML layout. 
     * In Convert Forms, Smart Tag replacements run during page load. Since we have a Smart Tag for Fields {field.XXX} already registered, 
     * the Smart Tags found in the Calculations formula will be replaced by empty space (there's no submitted data yet) breaking Calculation. 
     * 
     * We need a way to determine during runtime whether a Smart Tag can run or not.
     * 
     * We could write a new method so 3rd party extension can register individual classes conditionally but this would add more work on the extension's side.
     * 
     * @return boolean 
     */
    public function canRun()
    {
        return true;
    }

    /**
     * This helps us easily call a Smart Tag inside another Smart Tag. 
     * The best example is the Article Smart Tag where we want to retrieve information about the author. 
     * Instead of re-writing the logic to read author's data, we call the User Smart Tag.
     *
     * @param   string  $smartTagName   The Smart Tag name. eg: User
     * @param   string  $ke             The property we want to get. Eg: 'firstname'
     * @param   array   $options        The options to construct the class
     *
     * @return  mixed   String on success, null on failure
     */
    public function redirect($smartTagName, $key, $options)
    {
		$smartTagClass = 'JoomprojectSmartTags';
		
		$class = new $smartTagClass($this->factory, $options);

        if (method_exists($class, 'get' . $key))
        {
            return $class->{'get' . $key}();
        }

        if (method_exists($class, 'fetchValue'))
        {
            return $class->fetchValue($key);
        }
    }


	/**
	 * Returns the complete URL of the page, including the query string. Example: https://www.site.com/blog/?category=123
	 *
	 * @return  string
	 */
	public function getURL()
	{
		return $this->factory->getURI()->toString();
	}

	/**
	 * It returns the complete URL of the page, including the query string, but encoded. For instance, if the current URL is https://www.site.com/blog/?category=123 the Smart Tag will return https%3A%2F%2Fwww.site.com%2Fblog%2F%3Fcategory%3D123. This is useful when you want to pass the URL as a parameter in another URL.
	 *
	 * @return  string
	 */
	public function getEncoded()
	{
		return urlencode($this->factory->getURI()->toString());
	}

	/**
	 * Returns the URL of the page without the query string. Example: https://www.site.com/blog/
	 *
	 * @return  string
	 */
	public function getPath()
	{
		$url = $this->factory->getURI();
		return $url::current();
	}
	public function getTime()
	{
		return \Joomla\CMS\Factory::getDate()->format('H:i', true);
	}
	/**
	 * Returns the site email
	 *
	 * @return  string
	 */
	public function getEmail()
	{
		return $this->app->get('mailfrom');
	}

	/**
	 * Returns the site name
	 *
	 * @return  string
	 */
	public function getName()
	{
		return $this->app->get('sitename');
	}

	public function getReferrer()
	{
		return $this->app->input->server->get('HTTP_REFERER', '', 'RAW');
	}
	public function getRandomID()
	{
		return bin2hex(Crypt::genRandomBytes(8));
	}
	/**
	 * Returns the value of a URL query string parameter as found in the $_GET superglobal array. For example, if the page URL is http://example.com/page.php?key1=red&key2=blue, the {querystring.key2} Smart Tag will return blue.
	 *
	 * @param   string  $key
	 *
	 * @return  string
	 */
	public function fetchValue($key)
	{
		$query = $this->factory->getURI()->getQuery(true);

		if (empty($query))
		{
			return;
		}

		// Convert array keys to lowercase
		$query = array_change_key_case($query);

		// Convert array to registry object so we can access any level with dot notation.
		$queryReg = new Registry($query);

		return InputFilter::getInstance()->clean($queryReg->get(strtolower($key)));
	}
	public function PostfetchValue($key)
	{
		$filter = $this->parsedOptions->get('filter', 'STRING');
		$default_value = $this->parsedOptions->get('default', '');

		return $this->app->input->post->get($key, $default_value, $filter);
	}
	/**
	 * It returns the title of the page. If the page is behind a Menu Item, its Browser Page Title will be returned if not empty; otherwise, it falls back to the title of the Menu Item. If you want to display the title of a Joomla Article, use the {article.title} Smart Tag instead.
	 *
	 * @return  string
	 */
	public function getTitle()
	{
		return $this->doc->getTitle();
	}

	/**
	 * It returns the page’s meta description. If the page is a Joomla Article and has a meta description set, it will be returned. Otherwise, it falls back to the menu item’s page meta description.
	 *
	 * @return  string
	 */
	public function getDesc()
	{
		return $this->doc->getMetaData('description');
	}

	/**
	 * Returns the page keywords
	 *
	 * @return  string
	 *
	 * @deprecated Joomla 4 stopped offering the Meta Keywords option in the Menu Item. Use {article.keywords} instead. Reference: https://github.com/joomla/joomla-cms/issues/36639
	 */
	public function getKeywords()
	{
		return $this->doc->getMetaData('keywords');
	}

	/**
	 * It returns the language code of the page. For example, if the page’s language is English or Greek, expect "en-gb" and "el-GR" as the returned value, respectively.
	 *
	 * @return  string
	 */
	public function getLang()
	{
		return $this->doc->getLanguage();
	}

	/**
	 * It returns the first part of the language code of the page. For example, if the page’s language is English or Greek, expect "en" and "el" as the returned value, respectively.
	 *
	 * @return  string
	 */
	public function getLangURL()
	{
		return explode('-',  $this->doc->getLanguage())[0];
	}

	/**
	 * Returns the value of the generator meta tag.
	 *
	 * @return  string
	 */
	public function getGenerator()
	{
		return $this->doc->getGenerator();
	}

	/**
	 * Returns the menu item’s Browser Page Title option even if it is empty.
	 *
	 * @return  string
	 */
	public function getBrowserTitle()
	{
		if (!$menu = $this->app->getMenu()->getActive())
		{
			return '';
		}

		return $menu->getParams()->get('page_title');
	}
	public function getMonth()
	{
		return $this->date->format('n');
	}
	/**
	 * Read a property from the current active menu item
	 *
	 * @param   string  $key   The name of the property to return
	 *
	 * @return  mixed   Null if property is not found, mixed if property is found
	 */
	public function MenufetchValue($key)
	{
		$menu = new Registry($this->app->getMenu()->getActive());

		return $menu->get($key);
	}

	/**
	 * Returns the text of a language string. Replace CONSTANT with the language constant you want to return its text. For instance, to return the text of the language string COM_CONTACT_DETAILS, use {language.COM_CONTACT_DETAILS}.
	 *
	 * @param   string  $key
	 *
	 * @return  string
	 */
	public function LanguagefetchValue($key)
	{
		$key = strtolower($key);
		$key_parts = explode('_', $key);

		$lang = $this->factory->getLanguage();

		// Load language overrides: On front-end load administrator's override and vice versa.
		$overridePath = $this->factory->isFrontend() ? JPATH_ADMINISTRATOR : JPATH_SITE;
		$lang->load($lang->getTag() . '.override', $overridePath, 'overrides');

		switch ($key_parts[0])
		{
			case 'com':
				if (isset($key_parts[1]) && !empty($key_parts[1]))
				{
					$extension = 'com_' . $key_parts[1];
				}

				$lang->load($extension, JPATH_ADMINISTRATOR);
				$lang->load($extension, JPATH_SITE);
				break;

			case 'plg':
				if (isset($key_parts[1]) && !empty($key_parts[1]) && isset($key_parts[2]) && !empty($key_parts[2]))
				{
					$extension = implode('_', ['plg', $key_parts[1], $key_parts[2]]);
				}

				$path = implode(DIRECTORY_SEPARATOR, [JPATH_PLUGINS, $key_parts[1], $key_parts[2]]);

				$lang->load($extension, $path);
				break;
		}

		return Text::_($key);
	}
	/**
	 * Returns the IP address of the visitor.
	 *
	 * @return  string
	 */
	public function getIP()
	{
		return User::getIP();
	}



	public function DomCrawlefetchValue($key)
	{
		// Sanity check.
		if (!$css_selector = $this->parsedOptions->get('selector'))
		{
			return;
		}

		$crawler = new JoomprojectDomCrawler();
		$crawler->filter($css_selector);

		$fallback = $this->parsedOptions->get('fallback');

		switch ($key)
		{
			case 'html':
				return $crawler->html($fallback, $this->parsedOptions->get('innerhtml', false));

			case 'attr':
				return $crawler->attr($this->parsedOptions->get('attr'), $fallback);

			case 'count':
				return $crawler->count($fallback);

			// text
			default:
				return $crawler->text($fallback);
		}
	}
	public function CookiefetchValue($key)
	{
		return $this->factory->getCookie($key);
	}

}