<?php
/**
 * @package      Joomproject Pro
 * @subpackage   Designs
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */



defined('_JEXEC') or die();

use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Filesystem\File;


jimport('joomproject.framework');
if (JPApplicationHelper::exists('com_jprepo')) {
    JLoader::register('JPrepoHelper', JPATH_ADMINISTRATOR . '/components/com_jprepo/helpers/jprepo.php');
}

/**
 * Item Model for a design form.
 *
 */
class JPdesignsModelDesign extends AdminModel
{
    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     */
    protected $text_prefix = 'COM_JOOMPROJECT_DESIGN';


    /**
     * Returns a Table object, always creating it.
     *
     * @param     string    The table type to instantiate
     * @param     string    A prefix for the table class name. Optional.
     * @param     array     Configuration array for model. Optional.
     *
     * @return    jtable    A database object
     */
    public function getTable($type = 'Design', $prefix = 'JPtable', $config = array())
    {
        return Table::getInstance($type, $prefix, $config);
    }


    /**
     * Method to get a single record.
     *
     * @param     integer    The id of the primary key.
     *
     * @return    mixed      Object on success, false on failure.
     */
    public function getItem($pk = null)
    {
        if ($item = parent::getItem($pk)) {
            // Convert the params field to an array.
            $registry = new Registry;
            $registry->loadString((string)$item->attribs);
            $item->attribs = $registry->toArray();

            // Get the labels
            $labels = $this->getInstance('Labels', 'JPModel');
            $item->labels = $labels->getConnections('com_jpdesigns.design', $item->id);
        }

        return $item;
    }


    /**
     * Method to get the record form.
     *
     * @param     array      Data for the form.
     * @param     boolean    True if the form is to load its own data (default case), false if not.
     *
     * @return    mixed      A JForm object on success, false on failure
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_jpdesigns.design', 'design', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) return false;

        $jinput = Factory::getApplication()->input;
        $user   = Factory::getApplication()->getIdentity();
        $id     = (int) $jinput->get('id', 0);
        $task   = $jinput->get('task');

        // Check for existing item.
        // Modify the form based on Edit State access controls.
        if ($id != 0 && (!$user->authorise('core.edit.state', 'com_jpdesigns.design.' . $id)) || ($id == 0 && !$user->authorise('core.edit.state', 'com_jpdesigns')))
        {
            // Disable fields for display.
            $form->setFieldAttribute('state', 'disabled', 'true');

            // Disable fields while saving.
            $form->setFieldAttribute('state', 'filter', 'unset');
        }

        // Always disable these fields while saving
        $form->setFieldAttribute('alias', 'filter', 'unset');

        // Disable these fields if not an admin
        if (!$user->authorise('core.admin', 'com_jpdesigns')) {
            $form->setFieldAttribute('access', 'disabled', 'true');
            $form->setFieldAttribute('access', 'filter', 'unset');

            $form->setFieldAttribute('rules', 'disabled', 'true');
            $form->setFieldAttribute('rules', 'filter', 'unset');
        }

        // Disable these fields when updating
        if ($id) {
            $form->setFieldAttribute('project_id', 'readonly', 'true');
            $form->setFieldAttribute('project_id', 'required', 'false');

            // Media file is not required when updating
            $form->setFieldAttribute('file', 'required', 'false');

            if ($task != 'save2copy') {
                $form->setFieldAttribute('project_id', 'disabled', 'true');
                $form->setFieldAttribute('project_id', 'filter', 'unset');
            }

            // We still need to inject the project id when reloading the form
            if (!isset($data['project_id'])) {
                $db    = Factory::getDbo();
                $query = $db->getQuery(true);

                $query->select('project_id')
                      ->from('#__jp_designs')
                      ->where('id = ' . $db->quote($id));

                $db->setQuery($query);
                $form->setValue('project_id', null, (int) $db->loadResult());
            }
        }
        else {
            // Media file required for new records
            $form->setFieldAttribute('file', 'required', 'true');
        }

        return $form;
    }


    /**
     * A protected method to get a set of ordering conditions.
     *
     * @param     object    A record object.
     *
     * @return    array     An array of conditions to add to add to ordering queries.
     */
    protected function getReorderConditions($table)
    {
        $condition = array();

        $condition[] = 'project_id = ' . (int) $table->project_id;
        $condition[] = 'album_id = ' . (int) $table->album_id;

        return array(implode(' AND ', $condition));
    }


    /**
     * Prepare and sanitise the table data prior to saving.
     *
     * @param     jtable    A JTable object.
     *
     * @return    void
     */
    protected function prepareTable($table)
    {
        $condition = array();

        $condition[] = 'project_id = ' . (int) $table->project_id;
        $condition[] = 'album_id = ' . (int) $table->album_id;

        $condition = implode(' AND ', $condition);

        // Reorder the items within the category so the new item is first
        if (empty($table->id)) {
            $table->reorder($condition);
        }
    }


    /**
     * Method to auto-populate the model state.
     * Note: Calling getState in this method will result in recursion.
     *
     * @return    void
     */
    protected function populateState()
    {
        // Initialise variables.
        $app   = Factory::getApplication();
        $table = $this->getTable();
        $key   = $table->getKeyName();

        // Get the pk of the record from the request.
        $pk = \Joomla\CMS\Factory::getApplication()->input->getInt($key);
        $this->setState($this->getName() . '.id', $pk);

        if ($pk) {
            $table = $this->getTable();

            if ($table->load($pk)) {
                $project = (int) $table->project_id;
                $this->setState($this->getName() . '.project', $project);
                JPApplicationHelper::setActiveProject($project);
            }
        }
        else {
            $project = JPApplicationHelper::getActiveProjectId('filter_project');
            $album   = \Joomla\CMS\Factory::getApplication()->input->getUInt('filter_album', 0);

            if ($project) {
                $this->setState($this->getName() . '.project', $project);
            }

            if ($album) {
                $this->setState($this->getName() . '.album', $album);
            }
        }

        // Load the parameters.
        $value = ComponentHelper::getParams($this->option);
        $this->setState('params', $value);
    }


    /**
     * Method for uploading a thumbnail
     *
     * @param     array      $file       The thumb information
     * @param     integer    $project    The project id
     * @param     boolean    $stream     If set to true, use data stream
     *
     * @return    mixed                  Array with file info on success, otherwise False
     */
    public function uploadThumb($file = NULL, $project = 0, $stream = false)
    {
        $uploadpath = JPdesignsHelper::getBasePath($project);

        // user didn't upload any thumb
        if (!is_array($file)) {
            return true;
        }

        if (!isset($file['tmp_name'])) {
            return true;
        }


        // Try to create the upload path destination
        if (!\Joomla\CMS\Filesystem\Folder::exists($uploadpath)) {
            if (!\Joomla\CMS\Filesystem\Folder::create($uploadpath)) {
                $this->setError(Text::_('Error creating upload path'));
                return false;
            }
        }

        $errnum = (int) $file['error'];

        if ($errnum > 0) {
            $errmsg = JPdesignsHelper::getFileErrorMsg($errnum, $file['name']);
            $this->setError($errmsg);

            return false;
        }

        $name = $this->generateNewFileName($uploadpath, $file['name']);
        $ext  = strtolower(File::getExt($name));

        // Check file extension
        $allowedExtensions = ['jpeg', 'jpg', 'png', 'gif','webp'];

        if (!in_array($ext, $allowedExtensions)) {
            $this->setError(Text::_('COM_JOOMPROJECT_ERROR_FILE_FORMAT_NOT_SUPPORTED'));
            return false;
        }

        // Check mime type
        $info = getimagesize($file['tmp_name']);

        if (!$info) {
            $this->setError(Text::_('COM_JOOMPROJECT_ERROR_FILE_FORMAT_NOT_SUPPORTED'));
            return false;
        }

        if (!in_array($info[2], array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG,IMAGETYPE_WEBP))) {
            $this->setError(Text::_('COM_JOOMPROJECT_ERROR_FILE_FORMAT_NOT_SUPPORTED'));
            return false;
        }



        if (File::upload($file['tmp_name'], $uploadpath . '/thumbnails/' . $name, $stream) === true) {


            return $name;
        }

        return false;
    }


    /**
     * Method for uploading a file
     *
     * @param     array      $file       The file information
     * @param     integer    $project    The project id
     * @param     boolean    $stream     If set to true, use data stream
     *
     * @return    mixed                  Array with file info on success, otherwise False
     */
    public function upload($file = NULL, $project = 0, $stream = false)
    {
        $uploadpath = JPdesignsHelper::getBasePath($project);


        if (!is_array($file)) {
            $this->setError(Text::_('COM_JOOMPROJECT_WARNING_NO_FILE_SELECTED'));
            return false;
        }

        if (!isset($file['tmp_name'])) {
            $this->setError(Text::_('COM_JOOMPROJECT_WARNING_NO_FILE_SELECTED'));
            return false;
        }


        // Try to create the upload path destination
        if (!\Joomla\CMS\Filesystem\Folder::exists($uploadpath)) {
            if (!\Joomla\CMS\Filesystem\Folder::create($uploadpath)) {
                return false;
            }
        }

        $errnum = (int) $file['error'];

        if ($errnum > 0) {
            $errmsg = JPdesignsHelper::getFileErrorMsg($errnum, $file['name'], $file['size']);
            $this->setError($errmsg);

            return false;
        }

        $name = $this->generateNewFileName($uploadpath, $file['name']);
        $ext  = strtolower(File::getExt($name));

        // Check file extension
        $allowedExtensions = ['jpeg', 'jpg', 'png', 'gif','webp'];

        // if pdf support activated add pdf to allowed exts array
        $supportPDF = ComponentHelper::getParams('com_jpdesigns')->get('support_pdf',0);
        if($supportPDF){
            $allowedExtensions[] = 'pdf';
        }

        if (!in_array($ext, $allowedExtensions)) {
            $this->setError(Text::_('COM_JOOMPROJECT_ERROR_FILE_FORMAT_NOT_SUPPORTED'));
            return false;
        }

        // Check mime type for pdf
        if( $ext == 'pdf'){

            $mimeArray = array('text/plain', 'application/pdf');
            $mime = mime_content_type($file['tmp_name']);

            if (!in_array($mime, $mimeArray)) {
                $this->setError(Text::_('COM_JOOMPROJECT_ERROR_FILE_FORMAT_NOT_SUPPORTED'));
                return false;
            }


        }else{ // check mime type for images
            $info = getimagesize($file['tmp_name']);

            if (!$info) {
                $this->setError(Text::_('COM_JOOMPROJECT_ERROR_FILE_FORMAT_NOT_SUPPORTED'));
                return false;
            }

            if (!in_array($info[2], array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG,IMAGETYPE_WEBP))) {
                $this->setError(Text::_('COM_JOOMPROJECT_ERROR_FILE_FORMAT_NOT_SUPPORTED'));
                return false;
            }
        }



        if (File::upload($file['tmp_name'], $uploadpath . '/' . $name, $stream) === true) {



            // if upload successfull AND is PDF then upload preview image
            if($supportPDF){
                $pdfPreviewData = Factory::getApplication()->input->get('pdfpreview','','string');

                if(!empty($pdfPreviewData) && (strpos($pdfPreviewData, 'data:image/png;base64') !== false)){
                    $pdfPreviewData = str_replace('data:image/png;base64,', '', $pdfPreviewData);
                    $pdfPreviewData = str_replace(' ', '+', $pdfPreviewData);
                    $pdfPreviewData = base64_decode($pdfPreviewData);
                    $pdfpreviewPath =  str_replace('.pdf','.png',$uploadpath . '/' . $name);
                    $success = file_put_contents($pdfpreviewPath, $pdfPreviewData);
                }
            }

            return array('name' => $name, 'size' => $file['size'], 'extension' => $ext);
        }

        return false;
    }


    /**
     * Method to delete a file
     *
     * @param     string     $name       The file name
     * @param     integer    $project    The project id to which the file belongs to
     * @param     integer    $id         The design id
     *
     * @return    boolean                True on success, otherwise False
     */
    public function deleteFile($name, $project = 0, $id = 0)
    {
        $uploadpath = JPdesignsHelper::getBasePath($project);

        if (File::exists($uploadpath . '/' . $name)) {
            if (File::delete($uploadpath . '/' . $name) !== true) {
                return false;
            }
            else {
                // Delete the cache files too
                $size_preview = ComponentHelper::getParams('com_jpdesigns', true)->get('img_preview_size', '400x300');
                $size_full    = ComponentHelper::getParams('com_jpdesigns', true)->get('img_full_size', '1280x720');
                $size_cover   = ComponentHelper::getParams('com_jpdesigns', true)->get('img_cover_size', '770x300');

                list($width_preview, $height_preview) = explode('x', $size_preview);
                list($width_full,    $height_full)    = explode('x', $size_full);
                list($width_cover,   $height_cover)   = explode('x', $size_cover);

                $file_preview = md5('design:' . $project . ':' . $id . ':' . $width_preview . ':' . $height_preview) . '.jpg';
                $file_full    = md5('design:' . $project . ':' . $id . ':' . $width_full . ':' . $height_full) . '.jpg';
                $file_cover   = md5('design:' . $project . ':' . $id . ':' . $width_cover . ':' . $height_cover) . '.jpg';

                $base = JPPATH_CACHE;

                if (File::exists($base . '/com_jpdesigns.images/' . $file_preview)) {
                    File::delete($base . '/com_jpdesigns.images/' . $file_preview);
                }

                if (File::exists($base . '/com_jpdesigns.images/' . $file_full)) {
                    File::delete($base . '/com_jpdesigns.images/' . $file_full);
                }

                if (File::exists($base . '/com_jpdesigns.images/' . $file_cover)) {
                    File::delete($base . '/com_jpdesigns.images/' . $file_cover);
                }

                return true;
            }
        }
        else {
            return false;
        }
    }


    /**
     * Method to change the file name.
     *
     * @param     string    $dest    The target destination folder
     * @param     string    $name    The file name
     *
     * @return    string             Contains the new name
     */
    protected function generateNewFileName($dest, $name)
    {
        $name = File::makeSafe($name);
        $ext  = strtolower(File::getExt($name));
        $name = substr($name, 0 , (strlen($name) - (strlen($ext) + 1)));

        if ($name == '') {
            $name = File::makeSafe(Factory::getDate()->format('Y-m-d-H-i-s'));
        }

        $exists = true;
        $files  = \Joomla\CMS\Filesystem\Folder::files($dest);

        if (!is_array($files)) {
            return $name . '.' . $ext;
        }

        if (!count($files)) {
            return $name . '.' . $ext;
        }

        if (!in_array($name . '.' . $ext, $files)) {
            return $name . '.' . $ext;
        }

        while ($exists == true)
        {
            $m = null;

            if (preg_match('#-(\d+)$#', $name, $m)) {
                $name   = preg_replace('#-(\d+)$#', '-'.($m[1] + 1).'', $name);
                $exists = File::exists($dest . '/' . $name . '.' . $ext);
            }
            else {
                $name  .= '-2';
                $exists = File::exists($dest . '/' . $name . '.' . $ext);
            }
        }

        return $name . '.' . $ext;
    }


    /**
     * Method to save the form data.
     *
     * @param     array      The form data
     *
     * @return    boolean    True on success
     */
    public function save($data)
    {
        $table  = $this->getTable();
        $key    = $table->getKeyName();
        $pk     = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
        $is_new = true;
        $old    = null;

        // Include the content plugins for the on save events.
        PluginHelper::importPlugin('content');
        $dispatcher = \Joomla\CMS\Factory::getApplication();

        // Allow an exception to be thrown.
        try {
            // Load the row if saving an existing record.
            if ($pk > 0) {
                if ($table->load($pk)) {
                    $is_new = false;
                    $old    = clone $table;
                }
            }

            if (!$is_new) {
                $data['project_id'] = $table->project_id;
            }

            // Delete the old file if a new one is uploaded
            if (!$is_new && isset($data['file']['name'])) {
                $this->deleteFile($table->file_name, $table->project_id, $table->id);
            }

            // Use the file name as title if empty
            if (!isset($data['title']) || ($data['title'] == '' && isset($data['file']['name']))) {
                $data['title'] = File::stripExt($data['file']['name']);
            }

            // Get the other file properties
            if (isset($data['file']['name'])) {
                $data['file_name'] = $data['file']['name'];
            }

            if (isset($data['file']['extension'])) {
                $data['file_extension'] = $data['file']['extension'];
            }

            if (isset($data['file']['size'])) {
                $data['file_size'] = ($data['file']['size'] > 0 ? round($data['file']['size'] / 1024) : 0);
            }

            // Make sure the title and alias are always unique
            $data['alias'] = '';
            list($title, $alias) = $this->generateNewTitle($data['title'], $data['project_id'], $data['album_id'], $pk);

            $data['title'] = $title;
            $data['alias'] = $alias;

            // Handle permissions and access level
            if (isset($data['rules'])) {
                $prev_access = ($is_new ? 0 : $table->access);
                $access = JPAccessHelper::getViewLevelFromRules($data['rules'], $prev_access);

                if ($access) {
                    $data['access'] = $access;
                }
            }
            else {
                if ($is_new) {
                    // Let the table class find the correct access level
                    $data['access'] = 0;
                }
                else {
                    // Keep the existing access in the table
                    if (isset($data['access'])) {
                        unset($data['access']);
                    }
                }
            }

            // Make item published by default if new
            if (!isset($data['state']) && $is_new) {
                $data['state'] = 1;
            }

            // Bind the data.
            if (!$table->bind($data)) {
                $this->setError($table->getError());
                return false;
            }

            // Prepare the row for saving
            $this->prepareTable($table);

            // Check the data.
            if (!$table->check()) {
                $this->setError($table->getError());
                return false;
            }

            // Trigger the onContentBeforeSave event.
            $result = $dispatcher->triggerEvent($this->event_before_save, array($this->option . '.' . $this->name, &$table, $is_new, $data));

            if (in_array(false, $result, true)) {
                $this->setError($table->getError());
                return false;
            }

            // Store the data.
            if (!$table->store()) {
                $this->setError($table->getError());
                return false;
            }

            $pk_name = $table->getKeyName();

            if (isset($table->$pk_name)) {
                $this->setState($this->getName() . '.id', $table->$pk_name);
            }

            $this->setState($this->getName() . '.new', $is_new);

            $id = $this->getState($this->getName() . '.id');

            // Add to watch list
            if ($is_new) {
                $cid = array($id);

                if (!$this->watch($cid, 1)) {
                   return false;
                }
            }

            // Store the labels
            if (isset($data['labels'])) {
                $labels = $this->getInstance('Labels', 'JPModel');

                if ((int) $labels->getState('item.project') == 0) {
                    $labels->setState('item.project', $table->project_id);
                }

                $labels->setState('item.type', 'com_jpdesigns.design');
                $labels->setState('item.id', $id);

                if (!$labels->saveRefs($data['labels'])) {
                    return false;
                }
            }

            // Clean the cache.
            $this->cleanCache();

            // Trigger the onContentAfterSave event.
            $dispatcher->triggerEvent($this->event_after_save, array($this->option . '.' . $this->name, &$table, $is_new));
        }
        catch (Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }

        return true;
    }


    public function import($data)
    {
        $project = (int) $data['project_id'];

        if (!$project) {
            $this->setError(Text::_('COM_JOOMPROJECT_DESIGNS_ERROR_IMPORT_NO_PROJECT'));
            return false;
        }

        $base_path   = JPdesignsHelper::getBasePath($project);
        $source_path = $base_path . '/' . $data['source'] . '/' . $data['file_name'];

        if (!File::exists($source_path)) {
            $this->setError(Text::_('COM_JOOMPROJECT_ERROR_IMAGE_NOT_FOUND'));
            return false;
        }

        $file_name = $this->generateNewFileName($base_path, $data['file_name']);

        if (!File::copy($source_path, $base_path . '/' . $file_name)) {
            $this->setError(Text::_('COM_JOOMPROJECT_ERROR_IMPORT_COPY_FAILED'));
            return false;
        }

        $this->setState($this->getName() . '.id', 0);

        $item = array();
        $item['id']         = 0;
        $item['project_id'] = (int) $data['project_id'];
        $item['album_id']   = (int) (isset($data['album_id']) ? $data['album_id'] : 0);
        $item['title']      = $data['title'];
        $data['rules']      = null;

        $item['file']              = array();
        $item['file']['name']      = $file_name;
        $item['file']['size']      = filesize($base_path . '/' . $file_name);
        $item['file']['extension'] = strtolower(File::getExt($file_name));

        if (!$this->save($item)) {
            File::delete($source_path, $base_path . '/' . $file_name);
            return false;
        }

        if (!File::delete($source_path)) {
            $this->setError(Text::_('COM_JOOMPROJECT_ERROR_IMPORT_DELETE_FAILED'));

            $cid = array($this->getState($this->getName() . '.id'));
            $this->delete($cid);
            return false;
        }

        return true;
    }


    /**
     * Method to set the approval state of a design record
     *
     * @param     integer    $id       The design id
     * @param     integer    $id       The user id
     * @param     integer    $state    The approval state
     *
     * @return    boolean              True on success, False on error
     */
    public function approve($id, $user_id, $state = 0)
    {
        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        // Check if a record exists
        $query->select('created')
              ->from('#__jp_designs_approved')
              ->where('id = ' . $db->quote((int) $id))
              ->where('revision_id = ' . $db->quote(0))
              ->where('created_by = ' . $db->quote((int) $user_id));

        $db->setQuery($query);


        try
        {
            $exists = $db->loadResult();
        }
        catch (RuntimeException $e)
        {
            $this->setError($e->getMessage());
            return false;
        }


        if (!$exists) {
            $obj = new stdClass();

            $obj->id          = (int) $id;
            $obj->revision_id = 0;
            $obj->created_by  = (int) $user_id;
            $obj->created     = Factory::getDate()->toSql();
            $obj->state       = (int) $state;

            try
            {
                $db->insertObject('#__jp_designs_approved', $obj);
            }
            catch (RuntimeException $e)
            {
                $this->setError($e->getMessage());
                return false;
            }

        }
        else {
            $query->clear();
            $query->update('#__jp_designs_approved')
                  ->set('state = ' . $db->quote((int) $state))
                  ->set('created = ' . $db->quote(Factory::getDate()->toSql()))
                  ->where('id = ' . $db->quote((int) $id))
                  ->where('revision_id = ' . $db->quote(0))
                  ->where('created_by = ' . $db->quote((int) $user_id));

            $db->setQuery($query);
            try
            {
                $db->execute();
            }
            catch (RuntimeException $e)
            {
                $this->setError($e->getMessage());
                return false;
            }

        }

        $table = $this->getTable();
        $table->load($id);
        $table->approved = $state;

        // Include the content plugins for the on save events.
        PluginHelper::importPlugin('content');
        $dispatcher = \Joomla\CMS\Factory::getApplication();

        // Trigger the onContentBeforeSave event.
        $result = $dispatcher->triggerEvent($this->event_before_save, array($this->option . '.' . $this->name, &$table, false,[]));

        // Trigger the onContentAfterSave event.
        $dispatcher->triggerEvent($this->event_after_save, array($this->option . '.' . $this->name, &$table, false));

        return true;
    }


    /**
     * Method to watch an item
     *
     * @param    array      $pks      The items to watch
     * @param    integer    $value    1 to watch, 0 to unwatch
     * @param    integer    $uid      The user id to watch the item
     */
    public function watch(&$pks, $value = 1, $uid = null)
    {
        $user  = Factory::getUser($uid);
        $table = $this->getTable();
        $pks   = (array) $pks;

        $is_admin = $user->authorise('core.admin', $this->option);
        $levels   = $user->getAuthorisedViewLevels();
        $projects = array();

        $item_type = 'com_jpdesigns.design';

        // Access checks.
        foreach ($pks as $i => $pk)
        {
            $table->reset();

            if ($table->load($pk)) {
                if (!$is_admin && !in_array($table->access, $levels)) {
                    unset($pks[$i]);
                    \Joomla\CMS\Factory::getApplication()->enqueueMessage( Text::_('JERROR_ALERTNOAUTHOR'),'warning');
                    $this->setError(Text::_('JERROR_ALERTNOAUTHOR'));
                    return false;
                }

                $projects[$pk] = (int) $table->project_id;
            }
            else {
                unset($pks[$i]);
            }
        }

        // Attempt to watch/unwatch the selected items
        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        foreach ($pks AS $i => $pk)
        {
            $query->clear();

            if ($value == 0) {
                $query->delete('#__jp_ref_observer')
                      ->where('item_type = ' . $db->quote($item_type) )
                      ->where('item_id = ' . $db->quote((int) $pk))
                      ->where('user_id = ' . $db->quote((int) $user->get('id')));

                $db->setQuery($query);

                try
                {
                    $db->execute();
                }
                catch (RuntimeException $e)
                {
                    $this->setError($e->getMessage());
                    return false;
                }

            }
            else {
                $query->select('COUNT(*)')
                      ->from('#__jp_ref_observer')
                      ->where('item_type = ' . $db->quote($item_type) )
                      ->where('item_id = ' . $db->quote((int) $pk))
                      ->where('user_id = ' . $db->quote((int) $user->get('id')));

                $db->setQuery($query);
                $count = (int) $db->loadResult();

                if (!$count) {
                    $data = new stdClass;

                    $data->user_id   = (int) $user->get('id');
                    $data->item_type = $item_type;
                    $data->item_id   = (int) $pk;
                    $data->project_id= (int) $projects[$pk];



                    try
                    {
                       $db->insertObject('#__jp_ref_observer', $data);
                    }
                    catch (RuntimeException $e)
                    {
                        $this->setError($e->getMessage());
                        return false;
                    }
                }
            }
        }

        // Clear the component's cache
        $this->cleanCache();

        return true;
    }


    /**
     * Custom clean the cache
     *
     */
    protected function cleanCache($group = 'com_jpdesigns', $client_id = 0)
    {
        parent::cleanCache($group);
    }


    /**
     * Method to change the title & alias.
     * Overloaded from JModelAdmin class
     *
     * @param     string     The title
     * @param     integer    The project id
     * @param     integer    The album id
     * @param     integer    The item id
     *
     * @return    array      Contains the modified title and alias
     */
    protected function generateNewTitle($title, $project, $album = 0, $id = 0)
    {
        $table = $this->getTable();
        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        $alias   = Joomla\CMS\Application\ApplicationHelper::stringURLSafe($title);
        $project = (int) $project;
        $album   = (int) $album;

        if (trim(str_replace('-', '', $alias)) == '') {
            $alias = Joomla\CMS\Application\ApplicationHelper::stringURLSafe(Factory::getDate()->format('Y-m-d-H-i-s'));
        }

        $query->select('COUNT(id)')
              ->from($table->getTableName())
              ->where('project_id = ' . $db->quote($project))
              ->where('album_id = '  . $db->quote($album))
              ->where('alias = ' . $db->quote($alias));

        if ($id) {
            $query->where('id != ' . intval($id));
        }

        $db->setQuery((string) $query);
        $count = (int) $db->loadResult();

        if ($id > 0 && $count == 0) {
            return array($title, $alias);
        }
        elseif ($id == 0 && $count == 0) {
            return array($title, $alias);
        }
        else {
            while ($table->load(array('album_id' => $album, 'project_id' => $project, 'alias' => $alias)))
            {
                $m = null;

                if (preg_match('#-(\d+)$#', $alias, $m)) {
                    $alias = preg_replace('#-(\d+)$#', '-'.($m[1] + 1).'', $alias);
                }
                else {
                    $alias .= '-2';
                }

                if (preg_match('#\((\d+)\)$#', $title, $m)) {
                    $title = preg_replace('#\(\d+\)$#', '('.($m[1] + 1).')', $title);
                }
                else {
                    $title .= ' (2)';
                }
            }
        }

        return array($title, $alias);
    }


    /**
     * Method to test whether a record can be deleted.
     * Defaults to the permission set in the component.
     *
     * @param     object     A record object.
     *
     * @return    boolean    True if allowed to delete the record.
     */
    protected function canDelete($record)
    {
        if (!empty($record->id)) {
            if ($record->state != -2) return false;

            $user  = Factory::getApplication()->getIdentity();
            $asset = 'com_jpdesigns.design.' . (int) $record->id;

            return $user->authorise('core.delete', $asset);
        }

        return parent::canDelete($record);
    }


    /**
     * Method to test whether a record can have its state edited.
     * Defaults to the permission set in the component.
     *
     * @param     object     A record object.
     *
     * @return    boolean    True if allowed to edit the state of the record.
     */
    protected function canEditState($record)
    {
        if (!empty($record->id)) {
            $user  = Factory::getApplication()->getIdentity();
            $asset = 'com_jpdesigns.design.' . (int) $record->id;

            return $user->authorise('core.edit.state', $asset);
        }

        return parent::canEditState($record);
    }


    /**
     * Method to test whether a record can be edited.
     * Defaults to the permission set in the component.
     *
     * @param     object     A record object.
     *
     * @return    boolean    True if allowed to edit the record.
     */
    protected function canEdit($record)
    {
        $user = Factory::getApplication()->getIdentity();

        // Check for existing item.
        if (!empty($record->id)) {
            $asset  = 'com_jpdesigns.design.' . (int) $record->id;

            return ($user->authorise('core.edit', $asset) || ($access->get('core.edit.own', $asset) && $record->created_by == $user->id));
        }

        return $user->authorise('core.edit', 'com_jpdesigns');
    }


    /**
     * Method to get the data that should be injected in the form.
     *
     * @return    mixed    The data for the form.
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = Factory::getApplication()->getUserState('com_jpdesigns.edit.' . $this->getName() . '.data', array());

        if (empty($data)) {
            $data = $this->getItem();

            // Set default values
            if ($this->getState($this->getName() . '.id') == 0) {
                $active_id = JPApplicationHelper::getActiveProjectId();

                $data->set('project_id', $active_id);
            }
        }

        return $data;
    }
}
