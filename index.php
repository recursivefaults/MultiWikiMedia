<?php
/**
 * Constants, should really be configuration driven
 */
 $UPLOAD_DIR = './images';  //Where we store the files on the FS.
 $EXTENSIONS = array('png', 'svg', 'jpg', 'jpeg'); // What extenstion of images we support.
 $MIME_TYPES = array('image/png', 'image/jpg', 'image/jpeg', 'image/svg');

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
    global $EXTENSIONS, $UPLOAD_DIR;
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
      $final = join($EXTENSIONS, ', ');
      $result['error'] = "We only accept files of the following types: $final";
    }
    
    //Ok, the file is fine, lets write it to the filesystem.
    $success = move_uploaded_file($tmp, $UPLOAD_DIR . '/' . $name);
    if (!$success) {
      $result['error'] = "There was a problem writing $tmp to $name.".PHP_EOL;
    }
    else {
      $result['success'] = "File can be retrieved at ./images/".$name;
    }
    return $result;
  }
  
  function testFileType($name, $type) {
    global $MIME_TYPES;
    global $EXTENSIONS;
    if ($type && in_array($type, $MIME_TYPES)) {
      return true;
    }
    $extenstion = end(explode('.', $name));
    return in_array($extenstion, $EXTENSIONS);
  }
  
  
  function initializeDirectories() {
    global $UPLOAD_DIR;
    if(!file_exists($UPLOAD_DIR)) {
      echo getcwd().$UPLOAD_DIR.PHP_EOL;
      return mkdir($UPLOAD_DIR);
    }
    return true;
  }


?>