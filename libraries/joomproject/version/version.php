<?php
/**
 * @package      pkg_joomproject
 * @subpackage   lib_joomproject
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


/**
 * Version information class for the Joomproject package.
 *
 */
final class JPVersion
{
    /** @var  string  Product name. */
    public $PRODUCT = 'Joomproject';

    /** @var  string  Release version. */
    public $RELEASE = '1.0';

    /** @var  string  Maintenance version. */
    public $DEV_LEVEL = '0';

    /** @var  string  Development status. */
    public $DEV_STATUS = 'Stable';

    /** @var  string  Build number. */
    public $BUILD = '0';

    /** @var  string  Code name. */
    public $CODENAME = 'J4';

    /** @var  string  Release date. */
    public $RELDATE = '02-12-2018';

    /** @var  string  Release time. */
    public $RELTIME = '16:00';

    /** @var  string  Release timezone. */
    public $RELTZ = 'CET';

    /** @var  string  Copyright Notice. */
    public $COPYRIGHT = 'Copyright (C) 2012 - 2018 JoomBoost. All rights reserved.';

    /** @var  string  Link text. */
    public $URL = '<a href="https://www.joomboost.com">JoomBoost</a>';


    /**
     * Compares two a "PHP standardized" version number against the current Joomproject version.
     *
     * @param     string    $minimum    The minimum version of Joomproject which is compatible.
     *
     * @return    bool                  True if the version is compatible.
     */
    public function isCompatible($minimum)
    {
        return version_compare(JPVERSION, $minimum, 'ge');
    }


    /**
     * Gets a "PHP standardized" version string for the current Joomproject.
     *
     * @return    string    Version string.
     */
    public function getShortVersion()
    {
        return $this->RELEASE . '.' . $this->DEV_LEVEL;
    }


    /**
     * Gets a version string for the current Joomproject with all release information.
     *
     * @return    string    Complete version string.
     */
    public function getLongVersion()
    {
        return $this->PRODUCT . ' ' . $this->RELEASE . '.' . $this->DEV_LEVEL . ' '
                . $this->DEV_STATUS . ' [ ' . $this->CODENAME . ' ] ' . $this->RELDATE . ' '
                . $this->RELTIME . ' ' . $this->RELTZ;
    }
}
