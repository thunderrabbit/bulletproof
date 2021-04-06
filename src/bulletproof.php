<?php
/**
 * BULLETPROOF.
 *
 * A single-class PHP library to upload images securely.
 *
 * PHP support 5.3+
 *
 * @version     5.0.0
 * @author      https://twitter.com/_samayo
 * @link        https://github.com/samayo/bulletproof
 * @license     MIT
 */
namespace Bulletproof;

class Image implements \ArrayAccess
{
    /**
     * @var string The new image name, to be provided or will be generated
     */
    protected $name;

    /**
     * @var int The image width in pixels
     */
    protected $width;

    /**
     * @var int The image height in pixels
     */
    protected $height;

    /**
     * @var string The image mime type (extension)
     */
    protected $mime;

    /**
     * @var string The full image path (dir + image + mime)
     */
    protected $path;

    /**
     * @var string The folder or image storage storage
     */
    protected $storage;

    /**
     * @var array The min and max image size allowed for upload (in bytes)
     */
    protected $size = array(100, 5000000);

    /**
     * @var array The max height and width image allowed
     */
    protected $dimensions = array(5000, 5000);

    /**
     * @var array The mime types allowed for upload
     */
    protected $mimeTypes = array('jpeg', 'png', 'gif', 'jpg');

    /**
     * @var array list of known image types
     */
    protected $acceptedMimes = array(
      1 => 'gif', 'jpeg', 'png', 'swf', 'psd',
      'bmp', 'tiff', 'tiff', 'jpc', 'jp2', 'jpx',
      'jb2', 'swc', 'iff', 'wbmp', 'xbm', 'ico'
    );

    /**
     * @var array error messages strings
     */
    protected $commonErrors = array(
        UPLOAD_ERR_OK => '',
        UPLOAD_ERR_INI_SIZE => 'Image is larger than the specified amount set by the server',
        UPLOAD_ERR_FORM_SIZE => 'Image is larger than the specified amount specified by browser',
        UPLOAD_ERR_PARTIAL => 'Image could not be fully uploaded. Please try again later',
        UPLOAD_ERR_NO_FILE => 'Image is not found',
        UPLOAD_ERR_NO_TMP_DIR => 'Can\'t write to disk, due to server configuration ( No tmp dir found )',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk. Please check you file permissions',
        UPLOAD_ERR_EXTENSION => 'A PHP extension has halted this file upload process'
    );

    /**
     * @var array storage for the global array
     */
    private $_files = array();
    private $_file = array();

    /**
     * @var string storage for any errors
     */
    private $error = '';

    /**
     * @param array $_files represents the $_FILES array passed as dependency
     */
    public function __construct(array $_files = array())
    {

        /* check if php_exif is enabled */
        if (!function_exists('exif_imagetype')) {
          $this->error = 'Function \'exif_imagetype\' Not found. Please enable \'php_exif\' in your PHP.ini';
      }

      $this->_files = $_files;
    }

    /**
     * \ArrayAccess unused method
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value){}

    /**
     * \ArrayAccess unused method
     *
     * @param mixed $offset
     */
    public function offsetExists($offset){}

    /**
     * \ArrayAccess unused method
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset){}

    /**
     * \ArrayAccess - DOES NOT get array value from object
     *
     * This returns true/false based on the existence of an object at $offset.
     * It then does an ugly side effect of setting $this->_file to the object,
     * which affects the return value of other functions (getSize(), etc)
     *
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetGet($offset)
    {
        /* return false if $image['key'] isn't found */
        if (!isset($this->_files[$offset])) {
          $this->error = sprintf('No file input found with name: (%s)', $offset);

          return false;
        }

      $this->_file = $this->_files[$offset];

       /* check for common upload errors */
      if (isset($this->_file['error'])) {
        $this->error = $this->commonErrors[$this->_file['error']];
      }

      return $this->error ? false : true;
    }

    /**
     * Sets max image height and width limit.
     *
     * @param int $maxWidth max width value
     * @param int $maxHeight max height value
     *
     * @return $this
     */
    public function setDimension($maxWidth, $maxHeight)
    {
      $this->dimensions = array($maxWidth, $maxHeight);

      return $this;
    }

    /**
     * Returns the full path of the image ex 'storage/image.mime'.
     *
     * @return string
     */
    public function getPath()
    {
      return $this->path = $this->getStorage() . '/' . $this->getName() . '.' . $this->getMime();
    }

    /**
     * Returns the image size in bytes.
     *
     * @return int
     */
    public function getSize()
    {
      return (int) $this->_file['size'];
    }

    /**
     * Define a min and max image size for uploading.
     *
     * @param int $min minimum value in bytes
     * @param int $max maximum value in bytes
     *
     * @return $this
     */
    public function setSize($min, $max)
    {
      $this->size = array($min, $max);
      return $this;
    }

    /**
     * Returns a JSON format of the image width, height, name, mime ...
     *
     * @return string
     */
    public function getJson()
    {
      return json_encode(
        array(
          'name' => $this->name,
          'mime' => $this->mime,
          'height' => $this->height,
          'width' => $this->width,
          'size' => $this->_file['size'],
          'storage' => $this->storage,
          'path' => $this->path,
        )
      );
    }

    /**
     * Returns the image mime type.
     *
     * @return null|string
     */
    public function getMime()
    {
      if (!$this->mime) {
        $this->mime = $this->getImageMime($this->_file['tmp_name']);
      }

      return $this->mime;
    }

    /**
     * Define a mime type for uploading.
     *
     * @param array $fileTypes
     *
     * @return $this
     */
    public function setMime(array $fileTypes)
    {
      $this->mimeTypes = $fileTypes;
      return $this;
    }

    /**
     * Gets the real image mime type.
     *
     * @param string $tmp_name The upload tmp directory
     *
     * @return null|string
     */
    protected function getImageMime($tmp_name)
    {
      $this->mime = @$this->acceptedMimes[exif_imagetype($tmp_name)];
      if (!$this->mime) {
        return null;
      }

      return $this->mime;
    }

    /**
     * Returns error string
     *
     * @return string
     */
    public function getError()
    {
      return $this->error;
    }

    /**
     * Returns the image name.
     *
     * @return string
     */
    public function getName()
    {
      return $this->name;
    }

    /**
     * Provide image name if not provided.
     *
     * @param string $isNameProvided
     *
     * @return $this
     */
    public function setName($isNameProvided = null)
    {

      if ($isNameProvided) {
        $this->name = filter_var($isNameProvided, FILTER_SANITIZE_STRING);
      }else{
        $this->name = uniqid('', true) . '_' . str_shuffle(implode(range('e', 'q')));
      }

      return $this;
    }

    /**
     * Returns the image width.
     *
     * @return int
     */
    public function getWidth()
    {
      if ($this->width != null) {
        return $this->width;
      }

      list($width) = getimagesize($this->_file['tmp_name']);

      return $width;
    }

    /**
     * Returns the image height in pixels.
     *
     * @return int
     */
    public function getHeight()
    {
      if ($this->height != null) {
        return $this->height;
      }

      list(, $height) = getimagesize($this->_file['tmp_name']);

      return $height;
    }

    /**
     * Returns the storage / folder name.
     *
     * @return string
     */
    public function getStorage()
    {
      if (!$this->storage) {
        $this->setStorage();
      }

      return $this->storage;
    }

    /**
     * Validate directory/permission before creating a folder.
     *
     * @param string $dir the folder name to check
     *
     * @return bool
     */
    private function isDirectoryValid($dir)
    {
      return !file_exists($dir) && !is_dir($dir) || is_writable($dir);
    }

    /**
     * Sets storage for upload storage.
     *
     * @param string $dir the folder name to create
     * @param int $permission chmod permission
     *
     * @return $this
     */
    public function setStorage($dir = 'uploads', $permission = 0666)
    {
      $this->storage = $this->createStorage($dir, $permission);
      return $this;
    }

    /**
     * Creates a storage for upload storage.
     *
     * @param string $dir the folder name to create
     * @param int $permission chmod permission
     *
     * @return string?
     */
    public function createStorage($dir = 'uploads', $permission = 0666)
    {
      $isDirectoryValid = $this->isDirectoryValid($dir);

      if (!$isDirectoryValid) {
        $this->error = 'Can not create a directory  \''.$dir.'\', please check write permission';
        return false;
      }

      $create = !is_dir($dir) ? @mkdir('' . $dir, (int) $permission, true) : true;

      if (!$create) {
        $this->error = 'Error! directory \'' . $dir . '\' could not be created';
        return false;
      }

      return $dir;
    }


    public function validateMime() {
      $this->getImageMime($this->_file['tmp_name']);

      if(!in_array($this->mime, $this->mimeTypes)) {
        $this->error = sprintf('Invalid File! Only (%s) image types are allowed', implode(', ', $this->mimeTypes));
        return false;
      }

      return true;
    }

    public function validateSize() {
      list($minSize, $maxSize) = $this->size;
      if ($this->_file['size'] < $minSize || $this->_file['size'] > $maxSize) {
        $min = $minSize.' bytes ('.intval($minSize / 1000).' kb)';
        $max = $maxSize.' bytes ('.intval($maxSize / 1000).' kb)';
        $this->error = 'Image size should be minimum '.$min.', upto maximum '.$max;
        return false;
      }

      return true;
    }

    public function validateDimension() {
      /* check image dimension */
      list($maxWidth, $maxHeight) = $this->dimensions;
      $this->width = $this->getWidth();
      $this->height = $this->getHeight();

      if($this->height > $maxHeight) {
        $this->error = sprintf('Image height should be smaller than (%s) pixels', $maxHeight);

        return false;
      }

      if($this->width > $maxWidth) {
        $this->error = sprintf('Image width should be smaller than (%s) pixels', $maxHeight);

        return false;
      }

      return true;
    }

    public function isValid() {

      return $this->validateMime() && $this->validateSize() && $this->validateDimension();
    }


    /**
     * Validate and save (upload) file
     *
     * @return false|Image
     */
    public function upload()
    {

      if ($this->error !== '') {
        return false;
      }

      $this->getName();


      $isSuccess = $this->isValid() && $this->isSaved($this->_file['tmp_name'], $this->getPath());

      return $isSuccess ? $this : false;
    }

    /**
     * Final upload method to be called, isolated for testing purposes.
     *
     * @param string $tmp_name the temporary storage of the image file
     * @param string $destination upload destination
     *
     * @return bool
     */
    protected function isSaved($tmp_name, $destination)
    {
      return move_uploaded_file($tmp_name, $destination);
    }
}
