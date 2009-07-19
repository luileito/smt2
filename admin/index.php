<?php
// server settings are required
require '../config.php';
require INC_PATH.'sys/logincheck.php';
include INC_PATH.'inc/header.php';

// check for access errors
if (isset($_SESSION['error'][NOT_ALLOWED])) { 
  echo '<p class="error">Your user account cannot access that section.</p>';
  unset($_SESSION['error']);
}

// sanitize installed/removed extensions
if (is_root()) {
  $prioritized = get_exts_order();
  // is there a new extension installed? 
  $newext = array_flip($_SESSION['allowed']);
  $diff = array_diff_key($newext, $prioritized);
  if ($diff > 1) {
    foreach ($diff as $dir => $priority) {
      if ($dir != "admin") {
        db_insert(TBL_PREFIX.TBL_EXTS, "dir,priority", "'".$dir."', '".(max($prioritized) + 1)."'");
        $warn = true;
      }
    }
    if ($warn) {
      echo '<p class="warning">New extensions have been installed. Please reload this page.</p>';
    }
  }
  // should be removed from DB a previously installed extension? 
  $installed = ext_available();
  foreach ($prioritized as $dir => $priority) {
    if (!in_array($dir, $installed)) {
      db_delete(TBL_PREFIX.TBL_EXTS, "dir = '".$dir."'");
    }
  }
}
  
// display header title
echo '<h1>Admin panel</h1>';
// check for new releases
echo check_smt_releases();

/* connection settings ------------------------------------------------------ */
error_reporting(0);
// check if (smt) is installed properly
if (!db_check()) {
  $error = true;
  echo '<div class="warning">
          <h2>Seems that you need to setup your database</h2>
          <p>
           Did you edit your <em>config.php</em> file? 
           If server data are correct, go <a href="./sys/install.php">install (smt)</a>.
          </p>
        </div>';
} 
// now enable default error reporting
error_reporting(E_ALL ^ E_NOTICE);


/* version checking --------------------------------------------------------- */
if (!check_systemversion("mysql")) {
  $error = true;
  echo '<div class="warning">
          <h2>MySQL version test failed</h2>
          <p>
           You have MySQL '.mysql_get_client_info().' installed, but at least version 5 is required to work with (smt).
          </p>
        </div>';
}
// check also PHP version
if (!check_systemversion("php")) {
  $error = true;
  echo '<div class="warning">
          <h2>PHP version test failed</h2>
          <p>
           You have PHP '.phpversion().' installed, but at least PHP 5 is required to handle the tracking logs.
          </p>
        </div>';
}

/* check cURL library ------------------------------------------------------- */
if ( !function_exists("curl_init") ) 
{
  $error = true;
  echo '<p class="error">Your server does not have the cURL library installed. Please follow <a href="http://php.net/manual/en/curl.setup.php">these instructions</a>.</p>';
} 

// if no errors are found, display a success message
if (!$error) {
  echo '<p class="success">The MySQL server is up and running properly.</p>';
}

if (!is_dir(CACHE)) {
  echo '<p class="error">The cache dir does not exist.</p>';
} else {
  $cache = count_dir_files(CACHE);
  if (!$cache) { 
    echo '<p class="warning">The log cache is empty.</p>';
  }
  $dblog = db_records();
  if ($cache > $dblog) {
    echo '<p class="warning">There are '.$cache.' logs in cache dir, but there are '.$dblog.' in database, which is something weird :/</p>';
  }
}

// include footer file
include INC_PATH.'inc/footer.php';
?>