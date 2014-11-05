<?php
// check data first (exclude registered users)
if (empty($_POST) || isset($_COOKIE['smt-usr'])) die(":(");

// Since v2.2.0, raw cross-domain POSTs work (only for fairly modern browsers)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: OPTIONS, POST');
header('Access-Control-Allow-Headers: X-Requested-With');
header('Access-Control-Max-Age: 86400');
if (strtolower($_SERVER['REQUEST_METHOD']) == 'options') exit;

if (isset($HTTP_RAW_POST_DATA)) {
  $data = explode('&', $HTTP_RAW_POST_DATA);
  foreach ($data as $val) {
    if (!empty($val)) {
      list($key, $value) = explode('=', $val);   
      $_POST[$key] = urldecode($value);
    }
  }
}
          
require 'functions.php';

if ($_POST['compressed']) {
  require 'class.lzw.php';
  $_POST['xcoords'] = LZW::decompress($_POST['xcoords']);
  $_POST['ycoords'] = LZW::decompress($_POST['ycoords']);
  $_POST['clicks']  = LZW::decompress($_POST['clicks']);
  $_POST['elhovered'] = LZW::decompress($_POST['elhovered']);
  $_POST['elclicked'] = LZW::decompress($_POST['elclicked']);
}

/*
// add client id to POST data (the local server has the user cookies)
$_POST['client'] = get_client_id();
*/

$file = ($_POST['action'] == "store") ? "store.php" : "append.php";
if (!empty($_POST['remote']) && $_POST['remote'] != "null") 
{
  // forward request to smt2 server
  $request = get_remote_webpage(
                                  $_POST['remote'].'/core/'.$file,
                                  array( 
                                          CURLOPT_COOKIE => $_POST['cookies'],
                                          CURLOPT_POST => true,
                                          CURLOPT_POSTFIELDS => $_POST 
                                       )
                               );
  // at this point the remote server should return the DB log id
  echo $request['content'];
}
else 
{
  require '../config.php';
  include $file;
}
?>
