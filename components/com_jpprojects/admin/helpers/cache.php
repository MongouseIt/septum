<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jpprojects
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


use \Joomla\CMS\Factory;


/**
 *  Caching mechanism
 */
class JoomprojectCache
{
	/**
	 *  Check if has alrady exists in memory
	 *
	 *  @param   string   $hash  The hash string
	 *
	 *  @return  boolean
	 */
	static public function has($hash)
	{
		$cache = JoomprojectCacheManager::getInstance(Factory::getCache('tassos', ''));
		return $cache->has($hash);
	}

	/**
	 *  Returns hash value
	 *
	 *  @param   string  $hash  The hash string
	 *  @param   string  $clone Why the hell we clone objects here?
	 *
	 *  @return  mixed          False on error, Object on success
	 */
	static public function get($hash, $clone = true)
	{
		$cache = JoomprojectCacheManager::getInstance(Factory::getCache('tassos', ''));
		return $cache->get($hash, $clone);
	}

	/**
	 *  Sets on memory the hash value
	 *
	 *  @param  string  $hash  The hash string
	 *  @param  mixed   $data  Can be string or object
	 *
	 *  @return mixed
	 */
	static public function set($hash, $data)
	{
		$cache = JoomprojectCacheManager::getInstance(Factory::getCache('tassos', ''));
		return $cache->set($hash, $data);
	}

	/**
	 *  Reads hash value from memory or file
	 *
	 *  @param   string   $hash   The hash string
	 *  @param   boolean  $force  If true, the filesystem will be used as well on the /cache/ folder
	 *
	 *  @return  mixed            The hash object valuw
	 */
	static public function read($hash, $force = false)
	{
		$cache = JoomprojectCacheManager::getInstance(Factory::getCache('tassos', ''));
		return $cache->read($hash, $force);
	}

	/**
	 *  Writes hash value in cache folder
	 *
	 *  @param   string   $hash  The hash string
	 *  @param   mixed    $data  Can be string or object
	 *  @param   integer  $ttl   Expiration duration in milliseconds
	 *
	 *  @return  mixed           The hash object value
	 */
	static public function write($hash, $data, $ttl = 0)
	{
		$cache = JoomprojectCacheManager::getInstance(Factory::getCache('tassos', ''));
		return $cache->write($hash, $data, $ttl);
	}

	/**
	 * Memoize a function to run once per runtime
	 *
	 * @param  string	$key		The key to store the result of the callback
	 * @param  callback $callback	The callable anonymous function to call
	 *
	 * @return mixed
	 */
	static public function memo($key, callable $callback)
	{
		$hash = md5($key);

		if (JoomprojectCache::has($hash))
		{
			return JoomprojectCache::get($hash);
		}

		return JoomprojectCache::set($hash, $callback());
	}
}