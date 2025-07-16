<?php
/**
 * @package      pkg_joomproject
 * @subpackage   plg_content_jpcommments
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */


defined('_JEXEC') or die();

use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Plugin\CMSPlugin;


/**
 * Joomproject Comments plugin.
 *
 */
class plgContentJPcomments extends CMSPlugin
{
    /**
     * Supported plugin contexts
     *
     * @var    array
     */
    protected $contexts = array(
        'com_jpprojects.project',
        'com_jpmilestones.milestone',
        'com_jptasks.tasklist',
        'com_jptasks.task',
        'com_jprepo.directory',
        'com_jprepo.note',
        'com_jpdesigns.design',
        'com_jpdesigns.revision'
    );

    /**
     * The current item context
     *
     * @var    string
     */
    protected $item_context;

    /**
     * The current item id
     *
     * @var    integer
     */
    protected $item_id;



    /**
     * Triggers the comment plugin after/below the item output.
     *
     * @param     string     $context       The current item context
     * @param     object     $item          The actual item data
     * @param     object     $params        The item parameters
     * @param     integer    $limitstart    Optional list limit (not being used though)
     *
     * @return    string                    The comment form and user comments HTML
     */
    public function onContentAfterDisplay($context, &$item, &$params, $ls = 0)
    {

        // List of valid contexts.
        // The context tells us which kind of item we're dealing with.
        $default_context_items = ['com_jpprojects.project',
                               'com_jpmilestones.milestone',
                               'com_jptasks.task',
                               'com_jpusers.user',
                               'com_jprepo.note',
                               'com_jpdesigns.design',
                               'com_jpdesigns.revision'];

        $context_items = ComponentHelper::getParams('com_jpcomments')->get('enable_in',$default_context_items);



        // Check if the context is supported. Return empty string if its not.
        if (!in_array($context, $context_items)) return '';

        // Check if the plugin is disabled. Return empty string if it is.
        if (!PluginHelper::isEnabled('content', 'jpcomments')) return '';

        // Dont show comments through the plugin if the output is not in HTML
        if (Factory::getDocument()->getType() != 'html') return '';

        // Set context
        $this->item_context = $context;
        $this->item_id      = (isset($item->id) ? intval($item->id) : 0);

        // Load comments JS
        HTMLHelper::_('jphtml.script.comments');

        return $this->display();
    }


    /**
     * "onContentAfterDelete" event handler
     *
     * @param     string     $context    The item context
     * @param     object     $table      The item table object
     *
     * @return    boolean                True
     */
    public function onContentAfterDelete($context, $table)
    {
        // Do nothing if the plugin is disabled
        if (!PluginHelper::isEnabled('content', 'jpcomments')) return true;

        $context = $this->unalias($context);

        // Check if the context is supported
        if (!in_array($context, $this->contexts)) return true;

        $this->deleteFromContext($context, $table->id);

        return true;
    }


    public function display()
    {

        // no need to display at the bottom if display type is as tab
        if(
            $this->item_context == 'com_jpprojects.project' && // view is project dashboard
            !ComponentHelper::getParams('com_jpcomments')->get('project_dash_comments_display',1) // and display as tab
        )
            return '';


        $doc  = Factory::getDocument();

        $url  = Uri::root().'index.php?option=com_jpcomments&view=comments';

        $js = <<<js
jQuery(document).ready(function(){
    jQuery.ajax({
        url: '$url',
        type: 'POST',
        data: {
            option: 'com_jpcomments',
                views: 'comments',
                filter_context: '{$this->item_context}',
                filter_item_id: '{$this->item_id}',
                tmpl: 'component'
                
        },
        cache: false,
        success: function(resp)
        {
        jQuery('#comments').append(resp);
        var comments = JPcomments.init();
         }
    });
});
js;


        $doc->addScriptDeclaration($js);

        return '<div class="items-more" id="comments"></div>';
    }


    /**
     * Method to unalias the context
     *
     * @param     string    $context    The context alias
     *
     * @return    string    $context    The actual context
     */
    protected function unalias($context)
    {
        switch ($context)
        {
            case 'com_jpprojects.form':
                return 'com_jpprojects.project';
                break;

            case 'com_jpmilestones.form':
                return 'com_jpmilestones.milestone';
                break;

            case 'com_jptasks.tasklistform':
                return 'com_jptasks.tasklist';
                break;

            case 'com_jptasks.taskform':
                return 'com_jptasks.task';
                break;

            case 'com_jprepo.directoryform':
                return 'com_jprepo.directory';
                break;

            case 'com_jprepo.noteform':
                return 'com_jprepo.note';
                break;

            case 'com_jpdesigns.designform':
                return 'com_jpdesigns.design';
                break;

            case 'com_jpdesigns.revisionform':
                return 'com_jpdesigns.revision';
                break;
        }

        return $context;
    }


    /**
     * Method to delete all comments from the given context
     *
     * @param     string     $context    The context
     * @param     integer    $id         The context id
     *
     * @return    void
     */
    protected function deleteFromContext($context, $id)
    {
        static $imported = false;

        if (!$imported) {
            jimport('joomproject.library');
            JLoader::register('JPtableComment', JPATH_ADMINISTRATOR . '/components/com_jpcomments/tables/comment.php');

            $imported = true;
        }

        $table = Table::getInstance('Comment', 'JPtable');

        if (!$table) return;

        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        // Get comments
        $query->select('id')
              ->from('#__jp_comments')
              ->where('context = ' . $db->quote($context))
              ->where('item_id = ' . (int) $id)
              ->where('level = 1');

        $db->setQuery($query);
        $pks = (array) $db->loadColumn();

        // Delete comments
        foreach ($pks AS $pk)
        {
            $table->delete((int) $pk);
        }
    }
}
