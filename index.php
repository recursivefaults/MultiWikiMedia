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
      if(!isset($_GET['name']) && !isset($_GET['size']) && !isset($_GET['format'])) {
        echo "Please provide the following parameters: name, size, and format.".PHP_EOL;
        return;
      }
      $name = $_GET['name'];
      $size = $_GET['size'];
      $format = $_GET['format'];
      if(!in_array($format, array('png', 'jpg'))) {
        echo "Format must be of type jpg or png".PHP_EOL;
        return;
      }
      $dir = $CONFIG['UPLOAD_DIR'];
      $file = $dir.'/'.$name;
      if(!file_exists($file)) {
        echo "Sorry, that file doesn't exist, try uploading it!".PHP_EOL;
        return;
      }
      
      // Lets go for it. Image Magick time!
      $f = explode('.', $name);
      $file_base = $f[0];
      $output_file = $dir.'/'.$file_base."-thumb.".$format;
      echo shell_exec("convert $file -thumbnail $size"."^ $output_file");
      echo file_get_contents($output_file);
      break;
    case 'POST':
      /**
       * Lets see if we have a file
       */ 
      if($_FILES['image']) {
        $fileHelper = new FileUpload($_FILES['image'], $CONFIG);
        $success = false;
        if(!$fileHelper->isInitialized()) {
          $fileHelper->initializeDirectories();
        }
        if(!$fileHelper->isUploadValid()) {
          echo "We don't recognize the file you've uploaded.".PHP_EOL;
          return;
        }
        $success = $fileHelper->writeFile(new ShaNameBuilder($_FILES['image']));
        if($success) {
          echo "File successfully uploaded as $success".PHP_EOL;
          return;
        }
        echo "We were unable to upload the file at this time.".PHP_EOL;
      }
      else {
        echo "Please upload a file using the form name 'image'".PHP_EOL;
      }
      break;
    default:
      echo "Sorry, not supported\n";
      break;
  }
  

  /**
  * A simple class to encapsulate a file upload.
  */
  class FileUpload
  {
    private $file = NULL;
    private $config = array();
    function __construct($file, $config)
    {
      $this->file = $file;
      $this->config = $config;
    }
    public function isInitialized()
    {
      return file_exists($this->config['UPLOAD_DIR']);
    }
    
    public function initializeDirectories()
    {
      if(!$this->isInitialized()) {
        return mkdir($dir);
      }
      return true;
    }
    
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
    
    public function buildName() {
      return $this->file['name'];
    }
  }
  
  class ShaNameBuilder extends BasicNameBuilder {
    public function buildName() {
      //We want to sha the creation date and name.
      $time = filemtime($this->file['tmp_name']);
      $name = $this->file['name'];
      $extension = end(explode('.', $name));
      return sha1($name.$time).".".$extension;
    }
  }
  
  


?>