<?php
// check data first (exclude the root user)
if (empty($_POST) || isset($_COOKIE['smt-root'])) exit;

require 'functions.php';
// add client id to POST data (the local server has the user cookies)
$_POST['client'] = get_client_id();

$file = ($_POST['action'] == "store") ? "store.php" : "append.php";
if (!empty($_POST['remote'])) 
{
  // forward request to smt2 server
  $request = get_remote_webpage(
                                  $_POST['remote'].$file,
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