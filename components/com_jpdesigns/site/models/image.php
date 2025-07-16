<?php
/**
 * @package      Joomproject Pro
 * @subpackage   Designs
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;

jimport('joomla.application.component.model');
jimport('joomla.filesystem.file');
jimport('joomproject.application.helper');


/**
 * Image resizing class for Designs
 *
 */
class JPdesignsModelImage extends BaseDatabaseModel
{
    protected $source;
    protected $cache_id;
    protected $quality;
    protected $width;
    protected $height;
    protected $target_width;
    protected $target_height;
    protected $crop;
    protected $canvas;
    protected $canvas_width;
    protected $canvas_height;
    protected $buffer;
    protected $author_name;


    public function __construct($config = array())
    {
        parent::__construct($config);

        // Set default values
        $this->cache_id = '';

        $this->width  = 0;
        $this->height = 0;
        $this->buffer = '';

        // Set image quality
        if (array_key_exists('quality', $config)) {
            $this->quality = (int) $config['quality'];

            if ($this->quality > 90) $this->quality = 90;
            if ($this->quality < 40) $this->quality = 40;
        }
        else {
            $this->quality = 60;
        }

        // Set desired thumb size
        if (array_key_exists('size', $config)) {
            list($w, $h) = explode('x', $config['size']);

            $this->target_width  = (int) $w;
            $this->target_height = (int) $h;
        }
        else {
            $this->target_width  = 300;
            $this->target_height = 200;
        }

        // Set whether to crop the image or not
        if (array_key_exists('crop', $config)) {
            $this->crop = (bool) $config['crop'];
        }
        else {
            $this->crop = true;
        }
    }


    /**
     * Method to set the path to the source file
     *
     * @param     string     $path    The path to the source file
     *
     * @return    boolean             True if the file exists, False if not
     */
    public function setSource($path)
    {
        if (!File::exists($path)) {
            $this->setError(Text::_('COM_JOOMPROJECT_ERROR_IMAGE_NOT_FOUND'));
            return false;
        }

        $this->source = $path;

        return true;
    }


    /**
     * Method to set the author name of the image.
     * This will be used in the default watermark string
     *
     * @param     string     $name    The name of the author
     *
     * @return    void
     */
    public function setAuthor($name)
    {
        $this->author_name = $name;
    }


    /**
     * Method to set the cache id for the image
     *
     * @param     string     $group      Can be "design" or "revision"
     * @param     integer    $project    The project id
     * @param     integer    $id         The design or revision id
     *
     * @return    boolean                True
     */
    public function setCacheId($group, $project, $id)
    {
        $this->cache_id  = $group;
        $this->cache_id .= ':' . (int) $project;
        $this->cache_id .= ':' . (int) $id;
        $this->cache_id .= ':' . (int) $this->target_width;
        $this->cache_id .= ':' . (int) $this->target_height;

        return true;
    }


    /**
     * Method the check whether an image is cached or not
     *
     * @return    boolean    True if it is, False if not
     */
    public function isCached()
    {
        if (empty($this->cache_id)) return false;

        return File::exists($this->getCachedFilePath());
    }


    /**
     * Method to get the physical path to the cached image on the server
     *
     * @return    mixed    The path if found, False not.
     */
    public function getCachedFilePath()
    {


        if (empty($this->cache_id)) return false;

        $base = JPPATH_CACHE;


        $file = md5($this->cache_id) . '.jpg';

        return $base . '/com_jpdesigns.images/' . $file;
    }


    /**
     * Method to get the URL to the cached image
     *
     * @return    mixed    The URL if found, False not.
     */
    public function getCachedURL()
    {
        if (empty($this->cache_id)) return false;

        $base = Uri::base(true) . '/cache';
        $file = md5($this->cache_id) . '.jpg';

        return $base . '/com_jpdesigns.images/' . $file;
    }


    public function save($source = null)
    {
        $config = ComponentHelper::getParams('com_jpdesigns', true);

        // Override source path?
        if ($source) {
            $this->setSource($source);
        }

        // Cancel if there's no source file
        if (!$this->source) {
            $this->drawError($this->getError());
            return false;
        }

        // Get the file extension
        $source_ext = strtolower(File::getExt($this->source));
        $source_img = null;

        // Try to increase the memory limit
        $mem_limit = (int) $config->get('raise_mem');

        if ($mem_limit) {
            $this->increaseMemory($mem_limit);
        }

        // Create image
        switch ($source_ext)
        {
            case 'gif':
                $source_img = imagecreatefromgif ($this->source);
                break;

            case 'jpg':
            case 'jpeg':
                @ini_set('gd.jpeg_ignore_warning', 1);
                $source_img = imagecreatefromjpeg($this->source);
                break;

            case 'png':
                $source_img = imagecreatefrompng($this->source);
                break;
            case 'pdf':
                // we already have pdf preview image stored let's use it
                $this->source = str_replace('.pdf','.png',$this->source);
                $source_ext = 'png';
                $source_img = imagecreatefrompng($this->source);
                break;

        }

        // Unsupported file type
        if (is_null($source_img)) {
            $this->setError(Text::_('COM_JOOMPROJECT_ERROR_FILE_FORMAT_NOT_SUPPORTED'));
            $this->drawError($this->getError());
            return false;
        }

        // for pdf preview
        // Get original image size
        $this->width  = imagesx($source_img);
        $this->height = imagesy($source_img);



        // Calculate the new image size
        $new_width  = $this->target_width;
        $new_height = $this->target_height;

        // Don't stretch the image
        if ($this->target_width > $this->width)   $new_width  = $this->width;
        if ($this->target_height > $this->height) $new_height = $this->height;

        if (!$this->crop) {
            if ($this->width > $this->height) {
                $percentage = ($new_width / $this->width);
            } else {
                $percentage = ($new_height / $this->height);
            }

            $new_width  = round($this->width  * $percentage);
            $new_height = round($this->height * $percentage);
        }

        // Create a new true color image
        $this->canvas_width  = $new_width;
        $this->canvas_height = $new_height;
        $this->canvas        = imagecreatetruecolor($new_width, $new_height);

        // Fill the canvas
        $color = imagecolorallocate($this->canvas, 255, 255, 255);
        imagefill($this->canvas, 0, 0, $color);

        // Crop?
        if ($this->crop) {
            $src_x = $src_y = 0;
            $src_w = $this->width;
            $src_h = $this->height;
            $cmp_x = $this->width / $this->canvas_width;
            $cmp_y = $this->height / $this->canvas_height;

            // Calculate x or y coordinate and width or height of source
            if ($cmp_x > $cmp_y) {
                $src_w = round(($this->width / $cmp_x) * $cmp_y);
                $src_x = round(($this->width - ( ($this->width / $cmp_x) * $cmp_y ) ) / 2);
            }
            elseif ($cmp_y > $cmp_x) {
                $src_h = round(($this->height / $cmp_y) * $cmp_x);
                $src_y = round(( $this->height - ( ($this->height / $cmp_y) * $cmp_x ) ) / 2);
            }
            imagecopyresampled($this->canvas, $source_img, 0, 0, $src_x, $src_y, $this->canvas_width, $this->canvas_height, $src_w, $src_h);
        }
        else {
            imagecopyresampled($this->canvas, $source_img, 0, 0, 0, 0, $this->canvas_width, $this->canvas_height, $this->width, $this->height);
        }

        // Add watermark
        if ($config->get('watermark_txt') == '1') {
            $text = trim($config->get('watermark_string'));

            if (!$text) {
                $author = (empty($this->author_name) ? Factory::getConfig()->get('sitename') : $this->author_name);
                $text   = "Copyright " . date('Y') . " " . $author;
            }

            $this->addWatermarkText($text);
        }

        // Buffer the image output
        ob_start();
        ob_implicit_flush(false);

        imagejpeg($this->canvas, NULL, $this->quality);

        $this->buffer = ob_get_clean();

        // Generate file name and location
        $base = JPPATH_CACHE;

        $file = md5($this->cache_id) . '.jpg';

        // Store the image
        if (!File::write($base . '/com_jpdesigns.images/' . $file, $this->buffer)) {
            return false;
        }

        return true;
    }


    public function getBuffer()
    {
        return $this->buffer;
    }


    protected function addWatermarkText($text)
    {
        $config   = ComponentHelper::getParams('com_jpdesigns', true);
        $text_pos = $config->get('watermark_txt_pos', 'center');

        $font = 5;

        $letter_width  = imagefontwidth($font);
        $letter_height = imagefontheight($font);
        $text_lines    = array();
        $text_words    = array_reverse(explode(' ', $text));

        $line = '';
        $i = 0;

        $text_lines[$i]           = array();
        $text_lines[$i]['text']   = array();
        $text_lines[$i]['length'] = 0;
        $text_lines[$i]['pos_x']  = 0;

        if ($text_pos != 'center') {
            list($location_y, $location_x) = explode('-', $text_pos, 2);
        }
        else {
            $location_x = null;
            $location_y = null;
        }

        while(($word = array_pop($text_words)) !== null)
        {
            if ($word == '') $word = ' ';

            $word_length = strlen($word);
            $line_length = ($text_lines[$i]['length'] + $word_length) + 1;

            // Calculate the X coordinate of the text on the canvas
            if ($text_pos == 'center' || $location_x == 'center') {
                $pos_x = ceil(($this->canvas_width / 2) - (($letter_width * $line_length) / 2));
            }
            else {
                switch ($location_x)
                {
                    case 'left':
                        $pos_x = $letter_width;
                        break;

                    case 'right':
                        $pos_x = $this->canvas_width - (($letter_width * $line_length) + $letter_width);
                        break;
                }
            }

            // Check if the text is overflowing
            if (($pos_x + ($line_length * $letter_width)) > $this->canvas_width || $pos_x < 0) {
                // We need a new line
                $i++;
                $text_lines[$i]           = array();
                $text_lines[$i]['text']   = array();
                $text_lines[$i]['length'] = 0;
                $text_lines[$i]['pos_x']  = 0;
            }
            else {
                $text_lines[$i]['pos_x'] = $pos_x;
            }

            $text_lines[$i]['text'][] = $word;
            $text_lines[$i]['length'] = strlen(implode(' ', $text_lines[$i]['text']));
        }

        $total_lines = count($text_lines);
        $y_offset    = $total_lines * $letter_height;

        // Calculate the Y starting position of the text on the canvas
        if ($text_pos == 'center') {
            $pos_y = (round($this->canvas_height / 2)  - ceil($y_offset / 2)) - $letter_height;
        }
        else {
            switch ($location_y)
            {
                case 'top':
                    $pos_y = $letter_height;
                    break;

                case 'bottom':
                    $pos_y = ($this->canvas_height - $y_offset) - $letter_height;
                    break;
            }
        }

        imagesavealpha($this->canvas, true);
        $white = imagecolorallocatealpha($this->canvas, 255, 255, 255, 80);
        $black = imagecolorallocatealpha($this->canvas, 0, 0, 0, 80);

        $x = 0;
        foreach ($text_lines AS $i => $line)
        {
            imagestring($this->canvas, $font, $line['pos_x'] + 1, ($pos_y + $x) + 1, implode(' ', $line['text']), $black);
            imagestring($this->canvas, $font, $line['pos_x'], ($pos_y + $x), implode(' ', $line['text']), $white);
            $x += $letter_height;
        }
    }


    /**
     * Method to increase the memory limit
     *
     * @param     integer    $limit    The new limit
     *
     * @return    boolean              True on success, False on error
     */
    protected function increaseMemory($limit)
    {
        $current = $this->getMemoryLimit();

        if ($current) {
            $current = round(($current / 1024) / 1024);
        }

        if ($current > $limit) {
            return true;
        }

        if (ini_set('memory_limit', $limit . 'M') === false) {
            return false;
        }

        return true;
    }


    /**
     * Method to get the memory limit
     *
     * @return    integer    $mem    The limit in bytes
     */
    protected function getMemoryLimit()
    {
        $mem = ini_get('memory_limit');

        if (empty($mem)) return false;

        $mem   = trim($mem);
        $short = strtolower($mem[strlen($mem)-1]);

        switch($short)
        {
            case 'g':
                $mem *= 1024;

            case 'm':
                $mem *= 1024;

            case 'k':
                $mem *= 1024;
                break;
        }

        return $mem;
    }


    /**
     * Method to draw an error image
     *
     * @param     string    $txt    The text to draw
     *
     * @return    void
     */
    protected function drawError($txt)
    {
        $canvas    = imagecreatetruecolor($this->target_width, $this->target_height);
        $bg_color  = imagecolorallocate($canvas, 255, 255, 255);
        $txt_color = imagecolorallocate($canvas, 255, 0, 0);
        $font      = 1;

        imagefill($canvas, 0, 0, $bg_color);

        // Calculate the left position of the text
        $pos_x = round(($this->target_width - imagefontwidth($font) * strlen($txt)) / 2);
        $pos_y = ($this->target_height - round($this->target_height / 2));

        imagestring($canvas, $font, $pos_x, $pos_y, $txt, $txt_color);

        // Buffer the image output
        ob_start();
        ob_implicit_flush(false);

        imagejpeg($canvas, NULL, 90);

        $this->buffer = ob_get_contents();
        ob_end_clean();

        // Generate file name and location
        $base = JPPATH_CACHE;
        $file = md5($this->cache_id) . '.jpg';

        // Store the image
        File::write($base . '/com_jpdesigns.images/' . $file, $this->buffer);
    }
}
