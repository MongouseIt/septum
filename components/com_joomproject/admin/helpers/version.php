<?php
/**
 * @package        JoomProject
 * @copyright      2013-2019 JoomBoost, joomboost.com
 * @license        GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;


class JoomprojectHelperVersion
{

	protected static function compareVersion($newv, $oldv)
	{
		if (version_compare($oldv, $newv, '<'))
		{
			return true;
		}

		return false;
	}

	public static function getNewVersion()
	{

		$nversion = '?.?.?';
		$cache    = Factory::getCache('com_joomproject', 'output');
		$cache->setCaching(1);
		$nvcached = $cache->get('joomproject_nversion', 'com_joomproject');

		if (!$nvcached)
		{

			$jburl = 'https://www.joomboost.com/index.php?option=com_mediastore&task=pluginTask&plugin=joomla&subtask=server&id=138&j=4';
			$data  = JoomprojectHelperVersion::getRemoteData($jburl);


            if (!strstr($data, '<?xml version="1.0"?>'))
            {
                return $nversion;
            }

            $manifest = simplexml_load_string($data, 'SimpleXMLElement');

            if (is_null($manifest))
            {
                return $nversion;
            }

            // check updates
            if(count($manifest->update) > 1){

                foreach ($manifest->update as $update){

                    $plateformversion = (string) $update->targetplatform->attributes()['version'];

                    if(strpos($plateformversion,'4.') !== false){
                        $nversion = (string) $update->version;
                        break;
                    }

                }

            }else{
                $nversion = (string) $manifest->update->version;
            }

			$cache->store($nversion, 'joomproject_nversion', 'com_joomproject');

		}
		else
		{

			$nversion = $nvcached;

		}

		return $nversion;

	}

	public static function getCurrentVersion() {

		$xml = JPATH_ADMINISTRATOR .'/components/com_joomproject/joomproject.xml';
        $manifest_details = \Joomla\CMS\Installer\Installer::parseXMLInstallFile($xml);
        return $manifest_details['version'];


	}

	// get remote data (used to get new jb version)
	public static function getRemoteData($url) {
		$user_agent = "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.0.3705; .NET CLR 1.1.4322; Media Center PC 4.0)";
		$data       = false;

		# cURL
		if (extension_loaded('curl')) {
			$process = @curl_init($url);

			@curl_setopt($process, CURLOPT_HEADER, false);
			@curl_setopt($process, CURLOPT_USERAGENT, $user_agent);
			@curl_setopt($process, CURLOPT_RETURNTRANSFER, true);
			@curl_setopt($process, CURLOPT_SSL_VERIFYPEER, false);
			@curl_setopt($process, CURLOPT_AUTOREFERER, true);
			@curl_setopt($process, CURLOPT_FAILONERROR, true);
			@curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
			@curl_setopt($process, CURLOPT_TIMEOUT, 10);
			@curl_setopt($process, CURLOPT_CONNECTTIMEOUT, 10);
			@curl_setopt($process, CURLOPT_MAXREDIRS, 20);

			$data = @curl_exec($process);

			@curl_close($process);

			return $data;
		}

		# fsockopen
		if (function_exists('fsockopen')) {
			$errno  = 0;
			$errstr = '';

			$url_info = parse_url($url);
			if ($url_info['host'] == 'localhost') {
				$url_info['host'] = '127.0.0.1';
			}

			# Open socket connection
			$fsock = @fsockopen($url_info['scheme'].'://'.$url_info['host'], 80, $errno, $errstr, 5);

			if ($fsock) {
				@fputs($fsock, 'GET '.$url_info['path'].(!empty($url_info['query']) ? '?'.$url_info['query'] : '').' HTTP/1.1'."\r\n");
				@fputs($fsock, 'HOST: '.$url_info['host']."\r\n");
				@fputs($fsock, "User-Agent: ".$user_agent."\n");
				@fputs($fsock, 'Connection: close'."\r\n\r\n");

				# Set timeout
				@stream_set_blocking($fsock, 1);
				@stream_set_timeout($fsock, 5);

				$data          = '';
				$passed_header = false;
				while (!@feof($fsock)) {
					if ($passed_header) {
						$data .= @fread($fsock, 1024);
					}
					else {
						if (@fgets($fsock, 1024) == "\r\n") {
							$passed_header = true;
						}
					}
				}

				#  Clean up
				@fclose($fsock);

				# Return data
				return $data;
			}
		}

		# fopen
		if (function_exists('fopen') && ini_get('allow_url_fopen')) {
			# Set timeout
			if (ini_get('default_socket_timeout') < 5) {
				ini_set('default_socket_timeout', 5);
			}

			@stream_set_blocking($handle, 1);
			@stream_set_timeout($handle, 5);
			@ini_set('user_agent', $user_agent);

			$url = str_replace('://localhost', '://127.0.0.1', $url);

			$handle = @fopen($url, 'r');

			if ($handle) {
				$data = '';
				while (!feof($handle)) {
					$data .= @fread($handle, 8192);
				}

				# Clean up
				@fclose($handle);

				# Return data
				return $data;
			}
		}

		# file_get_contents
		if (function_exists('file_get_contents') && ini_get('allow_url_fopen')) {
			$url = str_replace('://localhost', '://127.0.0.1', $url);
			@ini_set('user_agent', $user_agent);
			$data = @file_get_contents($url);

			# Return data
			return $data;
		}

		return $data;
	}


	public static function getVersionInfo()
	{

		$info = new stdClass();

		//version data
		$info->newVersion      = self::getNewVersion();
		$info->currentVersion  = self::getCurrentVersion();
		$info->comparedVersion = self::compareVersion($info->newVersion, $info->currentVersion);
		$info->downloadId      = ComponentHelper::getParams('com_joomproject')->get('downloadid', '');

		return $info;


	}

}