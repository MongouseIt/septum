<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_joomproject
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

use Joomla\CMS\Factory;

defined('_JEXEC') or die();

class JPCommentsHelperComments
{

    public static function getCountOfPublishedComments($item_id,$context = 'com_jpprojects.project'){
        // Inside your method/function
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        // Main query
        $query->select('COUNT(*) as valid_comments_count')
            ->from($db->quoteName('#__jp_comments', 'c1'))
            ->where('item_id = '. (int) $item_id)
            ->where($db->quoteName('c1.state') . ' = 1 AND c1.context = "'.$context.'"')
            ->where('NOT EXISTS (' .
                // Subquery to check for unpublished ancestors
                'SELECT 1 FROM ' . $db->quoteName('#__jp_comments', 'c2') .
                ' WHERE ' . $db->quoteName('c2.state') . ' = 0' .
                ' AND ' . $db->quoteName('c1.lft') . ' > ' . $db->quoteName('c2.lft') .
                ' AND ' . $db->quoteName('c1.lft') . ' < ' . $db->quoteName('c2.rgt') .
                ')'
            );

        // If you need to debug the query
        // echo $query->dump();

        // Execute the query
        $db->setQuery($query);
        $count = $db->loadResult();

        return is_null($count) ? 0 : $count;
    }

    public static function restructureComments($comments)
    {



        // add root element if not existing
        if (isset($comments[0]) && $comments[0]->level != 0) {

            $rootElement = new stdClass();
            $rootElement->id = 1;
            $rootElement->title = 'ROOT';
            $rootElement->level = 0;
            $rootElement->parent_id = 0;
            $rootElement->childs = [];


            array_unshift($comments, $rootElement);
        }



        $comments = self::reFixArraykeys($comments);

        $comments = array_reverse($comments, true);


        foreach ($comments as $id => $comment) {

            // skip for first level
            if ($comment->level == 0)
                continue;

            if (isset($comments[$comment->parent_id])) {
                $comments[$comment->parent_id]->childs[] = $comment;
            }

            unset($comments[$id]);


        }

        // return root element
        return isset($comments[1]) ? $comments[1] : [];

    }


    // use comment id in array key
    public static function reFixArraykeys($comments)
    {

        $newArray = [];

        foreach ($comments as $comment) {

            $comment->childs = [];

            $newArray[$comment->id] = $comment;

        }

        return $newArray;

    }


}