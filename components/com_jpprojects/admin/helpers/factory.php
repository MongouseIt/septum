<?php

/**
 * @package      Joomproject
 * @subpackage   Projects
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */



use Joomla\CMS\Factory as JoomlaFactory;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die;

/**
 *  Framework Factory Class
 *
 *  Used to decouple the framework from it's dependencies and make unit testing easier.
 *
 *  @todo Rename class to Container and make all methods static.
 */
class JoomprojectFactory
{
	public function isFrontend()
	{
		return $this->getApplication()->isClient('site');
	}

	public static function getCondition($name)
	{
		return \NRFramework\Conditions\ConditionsHelper::getInstance()->getCondition($name);
	}

	public function getDbo()
	{
		return JoomlaFactory::getDbo();
	}

	public function getApplication()
	{
		$app = JoomlaFactory::getApplication();

		// The 'forward_context' parameter is used to forward data from one page to another. This is rather useful in XHR requests.
		// This is mainly used in Convert Forms to make the {article} Smart Tag work after form submission.
		if ($context = $app->input->get('forward_context', '', 'raw'))
		{
			if (is_string($context))
			{
				try
				{
					$context = json_decode($context, true);
					$app->input->set('forward_context', $context);
				} catch (\Throwable $th)
				{
				}
			}
		}

		return $app;
	}

	public function getCookie($cookie_name)
	{
		return JoomlaFactory::getApplication()->input->cookie->get($cookie_name, null, 'string');
	}

	public function getDocument()
	{
		return JoomlaFactory::getDocument();
	}

	public function getUser($id = null)
	{
		return \Joomla\CMS\Factory::getUser($id);
	}

	public function getCache()
	{
		return JoomprojectCacheManager::getInstance(JoomlaFactory::getCache('tassos', ''));
	}

	public function getDate($date = 'now', $tz = null)
	{
		return JoomlaFactory::getDate($date, $tz);
	}

	public function getURI()
	{
		return Uri::getInstance();
	}

	public function getURL()
	{
		return Uri::getInstance()->toString();
	}

	public function getLanguage()
	{
		return JoomlaFactory::getLanguage();
	}

	public function getSession()
	{
		return JoomlaFactory::getSession();
	}

	public function getDevice()
	{
		return JoomprojectWebClient::getDeviceType();
	}

	public function getBrowser()
	{
		return JoomprojectWebClient::getBrowser();
	}

	public function getExecuter($php_code)
	{
		return new \NRFramework\Executer($php_code);
	}
}