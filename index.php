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
      print_r($_GET);
      echo "GET RECEIVED\n";
      break;
    case 'POST':
      /**
       * Lets see if we have a file
       */ 
      if($_FILES['image']) {
        $result = handleFileUpload($_FILES['image']);
        if($result['error']) {
          echo $result['error'].PHP_EOL;
          //Error code
          return;
        }
        echo $result['success'].PHP_EOL;
      }
      else {
        echo "Please upload a file using the form name 'image'".PHP_EOL;
      }
      break;
    default:
      echo "Sorry, not supported\n";
      break;
  }
  
  function handleFileUpload($file) {
    global $CONFIG;
    $result = array();
    if(!initializeDirectories()) {
      $result['error'] = "Cannot create image directories";
      return $result;
    }
    // Lets dig into the file.
    $name = $file['name'];
    $size = $file['size'];
    $type = $file['type']; //Shouldn't be trusted, but use it if we can.
    $tmp = $file['tmp_name'];
    if(!testFileType($name, $type)) {
      $final = join($CONFIG['EXTENSIONS'], ', ');
      $result['error'] = "We only accept files of the following types: $final";
    }
    $result = moveUploadedFile($tmp, $name);
    return $result;
  }
  
  function moveUploadedFile($tmp, $name) {
    //TODO: Don't use the filename, create a hash, to avoid collisions.
    global $CONFIG;
    $result = array();
    //Ok, the file is fine, lets write it to the filesystem.
    $success = move_uploaded_file($tmp, $CONFIG['UPLOAD_DIR'] . '/' . $name);
    if (!$success) {
      $result['error'] = "There was a problem writing $tmp to $name.".PHP_EOL;
    }
    else {
      $result['success'] = "File can be retrieved at ./images/".$name;
    }
    return $result;
    
  }
  
  function testFileType($name, $type) {
    global $CONFIG;
    if ($type && in_array($type, $CONFIG['MIME_TYPES'])) {
      return true;
    }
    $extenstion = end(explode('.', $name));
    return in_array($extenstion, $CONFIG['EXTENSIONS']);
  }
  
  
  function initializeDirectories() {
    global $CONFIG;
    $dir = $CONFIG['UPLOAD_DIR'];
    if(!file_exists($dir)) {
      echo getcwd().$dir.PHP_EOL;
      return mkdir($dir);
    }
    return true;
  }


?>