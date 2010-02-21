<?php
require_once '../../config.php';

/* check MySQL version -------------------------------------------------------*/
echo 'Checking MySQL version: ';
if ( check_systemversion("mysql") ) 
{
  echo '<strong class="ok">Ok.</strong><br />';
} 
else 
{
  die('<strong class="ko">Error:</strong> Your server has MySQL '.mysql_get_client_info().' installed, but at least version 5 is required to handle this system.');
}

/* check PHP version -------------------------------------------------------- */
echo 'Checking PHP version: ';
if ( check_systemversion("php") ) 
{
  echo '<strong class="ok">Ok.</strong><br />';
} 
else 
{
  die('<strong class="ko">Error:</strong> Your server has PHP '.phpversion().' installed, but at least PHP 5 is required to handle this system.');
} 

/* check cURL library ------------------------------------------------------- */
echo 'Checking cURL library: ';
if ( function_exists("curl_init") ) 
{
  echo '<strong class="ok">Ok.</strong><br />';
} 
else 
{
  die('<strong class="ko">Error:</strong> Please follow <a href="http://php.net/manual/en/curl.setup.php">these instructions</a>.');
} 
?>