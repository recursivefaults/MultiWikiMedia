<?php
/**
 * Let's only accept two types of requests.
 * 1) A post of a file
 * 2) A GET of that file which yields a thumbnail with specified parameters
 */
 
 $m = $_SERVER['REQUEST_METHOD'];
 switch ($m) {
   case 'GET':
     echo "GET RECEIVED\n";
     break;
   case 'POST':
     echo "POST RECEIVED\n";
     break;
   default:
     echo "Sorry, not supported\n";
     break;
 }


?>