<?php

/**
 * @package      Joomproject
 * @subpackage   Projects
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');


class Client extends JoomprojectSmartTag
{
    /**
     * Returns the type of the device of the user. 
     * 
     * @return  string  Possible values: desktop, mobile, tablet
     */
    public function getDevice()
    {
        return JoomprojectWebClient::getDeviceType();
    }

    /**
     * Returns the operating system of the user. 
     * 
     * @return  string  Possible values: windows, windows phone, iphone, ipad, ipod, mac, blackberry, android, android tablet, linux
     */
    public function getOS()
    {
        return JoomprojectWebClient::getOS();
    }

    /**
     * Returns the name of the browser of the user.
     * 
     * @return  string  Possible values: ie, firefox, chrome, safari, opera, edge
     */
    public function getBrowser()
    {
        return JoomprojectWebClient::getBrowser()['name'];
    }
    
    /**
     * Returns the user agent string of the user.
     * 
     * @return  string
     */
    public function getUserAgent()
    {
        return JoomprojectWebClient::getClient()->userAgent;
    }

    /**
     * Returns the 8-character hexadecimal ID representing the visitor's unique ID as stored in the nrid cookie in the visitor’s browser.
     *
     * @return string  Example: 03bc431d0d605ce4
     */
    public function getID()
    {
        return JoomprojectVisitorToken::getInstance()->get();
    }
}