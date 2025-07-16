<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jpprojects
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2006-2016 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */


defined('_JEXEC') or die;

use Joomla\CMS\Factory;


/**
 * Projects Component Route Helper
 *
 * @static
 */
abstract class JPprojectsHelperProject
{

    public static function getInfo($id){

        $db = Factory::getDbo();

        $query = $db->getQuery(true);
        $query->select('*')->from('#__jp_projects')->where('id ='.$id);

        $db->setQuery($query);

        return $db->loadObject();


    }

}
