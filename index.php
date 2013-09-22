<?php
/**
 * Constants, should really be configuration driven
 */
 $CONFIG = array(
  'UPLOAD_DIR' => './images',
  'EXTENSIONS' => array('png', 'svg', 'jpg', 'jpeg'),
  'MIME_TYPES' => array('image/png', 'image/jpg', 'image/jpeg', 'image/svg')
  
 );
/**
 * Let's only accept two types of requests.
 * 1) A post of a file
 * 2) A GET of that file which yields a thumbnail with specified parameters
 */
 
  $m = $_SERVER['REQUEST_METHOD'];
  switch ($m) {
    case 'GET':
      $helper = new ThumbnailHelper($_GET, $CONFIG);
      if(!$helper->isValid()) {
        echo $helper->getError().PHP_EOL;
        return;
      }
      $file = $helper->createThumbnail();
      if($file) {
        echo file_get_contents($file);
        return;
      }
      echo "We couldn't serve your request at this time.".PHP_EOL;
      
      break;
    case 'POST':
      /**
       * Lets see if we have a file
       */
      if(sizeof($_FILES) == 0) {
        echo "Please post an image with a form to continue!".PHP_EOL;
        return;
      }
      $results = array();
      foreach ($_FILES as $key => $value) {
        $fileHelper = new FileUpload($value, $CONFIG);
        $success = false;
        if(!$fileHelper->isInitialized()) {
          $fileHelper->initializeDirectories();
        }
        if(!$fileHelper->isUploadValid()) {
          $results[$key] = "We don't recognize the file you've uploaded.".PHP_EOL;
          continue;
        }
        $success = $fileHelper->writeFile(new ShaNameBuilder($value));
        if($success) {
          $results[$key] = "File successfully uploaded as $success".PHP_EOL;
          continue;
        }
        $results[$key] = "We were unable to upload your file at this time.".PHP_EOL;
      }
      $final = "Reults of uploads: \n";
      foreach ($results as $key => $value) {
        $final .= "\t$key: $value";
      }
      echo $final.PHP_EOL;
      break;
    default:
      echo "Sorry, not supported\n";
      break;
  }
  
  /**
  * A simple class to manage thumbnail creation
  */
  class ThumbnailHelper
  {
    private $options = array(); //These are the $_GET options specified
    private $formats = array('jpg', 'png');
    private $error = NULL;
    function __construct($options, $config)
    {
      $this->options = $options;
      if(!is_null($config)) {
        $this->config = $config;
      }
    }
    
    /**
     * Tells us if the options that have been specified are valid
     * for creating a thumbnail.
     */
    public function isValid()
    {
      $opts = $this->options;
      if(!isset($opts['size']) && !isset($opts['name']) && !isset($opts['format'])) {
        $this->error = "Please specify the following parameters: size, format, and name.";
        return false;
      }
      if(!in_array($opts['format'], $this->formats)) {
        $t = join($this->formats, ', ');
        $this->error = "Format should be one of these: $t";
        return false;
      }
      if(!file_exists($this->config['UPLOAD_DIR'] . '/'. $opts['name'])) {
        $this->error = "The file you specified doesn't exist.";
        return false;
      }
      if(preg_match("/\d+x\d+/", $opts['size']) == 0) {
        $this->error = "The size should be specified as {width}x{height}";
        return false;
      }
      return true;
    }
    
    public function getError() {
      $err = $this->error;
      $this->error = NULL;
      return $err;
    }
    
    public function createThumbnail()
    {
      $name = $this->options['name'];
      $size = $this->options['size'];
      $dir = $this->config['UPLOAD_DIR'];
      $format = $this->options['format'];
      // Lets go for it. Image Magick time!
      $f = explode('.', $name);
      $base = $dir . '/';
      $file_base = $f[0];
      $file = $base . $name;
      $output_file = $base.$file_base."-thumb.".$format;
      shell_exec("convert $file -thumbnail $size"."^ $output_file");
      if(!file_exists($output_file)) {
        return false;
      }
      return $output_file;
    }
  }
  
  /**
  * A simple class to encapsulate a file upload.
  *
  * It accepts a file object as it comes in from $_FILES[item].
  * It also accepts a configuration object that looks like this:
  * 'UPLOAD_DIR' => directory for uploads
  * 'EXTENSIONS' => the extensions that are accepted (eg. jpg, gif, png)
  * 'MIME_TYPES' => if the form specified a mime type, this will help check too.
  */
  class FileUpload
  {
    private $file = NULL;                 // The file object as it exists in $_FILES
    private $config = array();            // Configuration options
    function __construct($file, $config)
    {
      $this->file = $file;
      $this->config = $config;
    }
    
    /**
     * Return true if the upload directory exists and is ready
     * return false otherwise.
     */
    public function isInitialized()
    {
      return file_exists($this->config['UPLOAD_DIR']);
    }
    
    /**
     * Create the upload directory as specified in the configuration
     * object. Return if it was successful or not
     */
    public function initializeDirectories()
    {
      if(!$this->isInitialized()) {
        return mkdir($dir);
      }
      return true;
    }
    
    /**
     * Tests the file that was passed in against
     * the mime type (If it exists), and the extension.
     * 
     * The valid options can be specified in the config
     * object.
     */
    public function isUploadValid()
    {
      $type = $this->file['type'];
      $name = $this->file['name'];
      if ($type && in_array($type, $this->config['MIME_TYPES'])) {
        return true;
      }
      $extenstion = end(explode('.', $name));
      return in_array($extenstion, $this->config['EXTENSIONS']);
    }
    
    /**
     * Writes the file to the specified upload location
     *
     * Takes an optional name building class that will
     * take responsibility for naming the file.
     *
     * Returns the name of the file if uploaded, and 
     * false if it failed to upload.
     */
    public function writeFile($nameBuilder)
    {
      $name = $this->file['name'];
      if($nameBuilder) {
        $name = $nameBuilder->buildName();
      }
      $tmp = $this->file['tmp_name'];
      //Ok, the file is fine, lets write it to the filesystem.
      if(move_uploaded_file($tmp, $this->config['UPLOAD_DIR'] . '/' . $name)) {
        return $name;
      }
      return false;
    }
  }
  
  /**
  * A basic name builder for files.
  */
  class BasicNameBuilder
  {
    protected $file = NULL;
    function __construct($file)
    {
      $this->file = $file;
    }
    
    /**
     * Return the name of the file that was uploaded
     */
    public function buildName() {
      return $this->file['name'];
    }
  }
  
  /**
   * A name builder that creates a hash of the name and
   * creation date.
   *
   * This will allow people to upload things wtih similar
   * names without over-writing each other.
   * 
   * The hash is created from the incoming name, and 
   * the temporary file's creation date.
   */
  class ShaNameBuilder extends BasicNameBuilder {
    public function buildName() {
      //We want to sha the creation date and name.
      $time = filemtime($this->file['tmp_name']);
      $tmp = end(explode(DIRECTORY_SEPARATOR, $this->file['tmp_name']));
      $name = $this->file['name'];
      $extension = end(explode('.', $name));
      return sha1($name.$tmp.$time).".".$extension;
    }
  }
  
  


?>