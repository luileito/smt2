<?php
require_once '../../config.php';

$warn_on = isset($_GET['nowarnings']);
$bypass_txt  = 'If you are sure that your server has all requisites, ';
$bypass_txt .= '<a href="?nowarnings">click here to supress warnings and install smt2 at your own risk</a>.';

/* check MySQL version -------------------------------------------------------*/
echo 'Checking MySQL version: ';
if ($warn_on) {
  echo '<strong>Bypass.</strong><br />';
} else {
  if (check_systemversion("mysql")) 
  {
    echo '<strong class="ok">Ok.</strong><br />';
  } 
  else 
  {
    $msg  = '<strong class="ko">Error:</strong> ';
    $msg .= 'Your server has MySQL '.mysql_get_client_info().' installed, ';
    $msg .= 'but at least version 5 is required to handle this system. ';
    $msg .= $bypass_txt;
    die($msg);
  }
}
/* check PHP version -------------------------------------------------------- */
echo 'Checking PHP version: ';
if ($warn_on) {
  echo '<strong>Bypass.</strong><br />';
} else {
  if (check_systemversion("php")) 
  {
    echo '<strong class="ok">Ok.</strong><br />';
  } 
  else 
  {
    $msg  = '<strong class="ko">Error:</strong> ';
    $msg .= 'Your server has PHP '.phpversion().' installed, ';
    $msg .= 'but at least version 5 is required to handle this system. ';
    $msg .= $bypass_txt; 
    die($msg);
  } 
}
/* check cURL library ------------------------------------------------------- */
echo 'Checking cURL library: ';
if ($warn_on) {
  echo '<strong>Bypass.</strong><br />';
} else {
  if (function_exists("curl_init")) 
  {
    echo '<strong class="ok">Ok.</strong><br />';
  } 
  else 
  {
    $msg  = '<strong class="ko">Error:</strong> ';
    $msg .= 'Please follow <a href="http://php.net/manual/en/curl.setup.php">these instructions</a>. ';
    $msg .= $bypass_txt; 
    die($msg);
  }
}
/* check JSON library ------------------------------------------------------- */
echo 'Checking JSON support: ';
if ($warn_on) {
  echo '<strong>Bypass.</strong><br />';
} else {
  if (function_exists("json_encode"))
  {
    echo '<strong class="ok">Ok.</strong><br />';
  }
  else
  {
    $msg  = '<strong class="ko">Error:</strong> ';
    $msg .= 'Please follow <a href="http://php.net/manual/en/json.setup.ph">these instructions</a>. ';
    $msg .= $bypass_txt; 
    die($msg);
  }
}
?>
