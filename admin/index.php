<?php
// server settings are required
require '../config.php';
require SYS_DIR.'/logincheck.php';

// now render page
include INC_DIR.'header.php';

// check for access errors
if (isset($_SESSION['error']["NOT_ALLOWED"])) {
  echo display_text($_displayType["ERROR"], 'Your user account cannot access that section.');
  unset($_SESSION['error']);
}

// sanitize installed/removed extensions
if (is_root()) {
  $prioritized = get_exts_order();
  // is there a new extension installed?
  $newext = array_flip($_SESSION['allowed']);
  $diff = array_diff_key($newext, $prioritized);
  $warn = false;
  if ($diff > 1) {
    foreach ($diff as $dir => $priority) {
      if ($dir != "admin") {
        db_insert(TBL_PREFIX.TBL_EXTS, "dir,priority", "'".$dir."', '".(max($prioritized) + 1)."'");
        $warn = true;
      }
    }
    if ($warn) {
      echo display_text($_displayType["WARNING"], 'New extensions have been installed. Please reload this page.');
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

/* now check for new releases
    servers in safe_mode or with open_basedir set will throw a CURL_FOLLOW_LOCATION error
*/
echo check_smt_releases();

/* connection settings ------------------------------------------------------ */
error_reporting(0);
// check if (smt) is installed properly
if (!db_check()) {
  $dberror = true;

  $msg  = '<h2>Seems that you need to setup your database</h2>';
  $msg .= '<p>';
  $msg .= 'Did you edit your <em>config.php</em> file?'.PHP_EOL;
  $msg .= 'If server data are correct, go <a href="'.SYS_DIR.'install.php">install (smt)</a>.';
  $msg .= '</p>';

  echo display_text($_displayType["WARNING"], $msg, 'div');
}
// now enable default error reporting
error_reporting(E_ALL ^ E_NOTICE);

/* JSON checking ------------------------------------------------------------ */
if (!function_exists('json_encode'))
{
  $msg  = '<h2>JSON library not found</h2>';
  $msg .= '<p>';
  $msg .= 'Your server does not have the JavaScript Object Notation extension installed.'.PHP_EOL;
  $msg .= 'Please follow <a rel="external" href="http://es2.php.net/manual/en/json.setup.php">these instructions</a>.';
  $msg .= '</p>';

  echo display_text($_displayType["ERROR"], $msg, 'div');
}

/* cURL checking ------------------------------------------------------------ */
if (!function_exists('curl_init'))
{
  $msg  = '<h2>cURL library not found</h2>';
  $msg .= '<p>';
  $msg .= 'Your server does not have the cURL library installed.'.PHP_EOL;
  $msg .= 'Please follow <a rel="external" href="http://php.net/manual/en/curl.setup.php">these instructions</a>.';
  $msg .= '</p>';

  echo display_text($_displayType["ERROR"], $msg, 'div');
}

/* security checking --------------------------------------------------------- */
if (ini_get('safe_mode'))
{
  echo display_text($_displayType["WARNING"], 'PHP safe mode is <strong>on</strong>: cURL won\'t work as expected!');
}

if (ini_get('open_basedir'))
{
  $msg  = 'Your PHP open base dir restriction could interfere with cURL.';
  $msg .= 'Fortunately, there exist <a rel="external" href="http://www.php.net/manual/en/function.curl-setopt.php#71313">some</a>';
  $msg .= ' <a rel="external" href="http://www.php.net/manual/en/function.curl-setopt.php#79787">workarounds</a>.';

  echo display_text($_displayType["WARNING"], $msg);
}

/* version checking --------------------------------------------------------- */
if (!check_systemversion("mysql", 5))
{
  $dberror = true;

  $msg  = '<h2>MySQL version test failed</h2>';
  $msg .= '<p>';
  $msg .= 'You have MySQL <code>'.mysql_get_client_info().'</code> installed, ';
  $msg .= 'but at least version <strong>5</strong> is required. ';
  $msg .= '<em>You can ignore this message if the system is already working.</em>';
  $msg .= '</p>';

  echo display_text($_displayType["ERROR"], $msg, 'div');
}

if (!check_systemversion("php", 5))
{
  $dberror = true;

  $msg  = '<h2>PHP version test failed</h2>';
  $msg .= '<p>';
  $msg .= 'You have PHP <code>'.phpversion().'</code> installed, ';
  $msg .= 'but at least PHP <strong>5</strong> is required to handle the tracking logs. ';
  $msg .= '<em>You can ignore this warning if the system is already working.</em>';
  $msg .= '</p>';

  echo display_text($_displayType["ERROR"], $msg, 'div');
}

/*
// if no errors are found, display a success message
if (!$dberror) {
  echo '<p class="success">The MySQL server is up and running properly.</p>';
}
*/

if (!is_dir(CACHE_DIR) && !mkdir(CACHE_DIR)) {
  echo display_text($_displayType["ERROR"], 'Cannot create cache dir. You must do it manually.');
}

$cache = count_dir_files(CACHE_DIR);
if (!$cache) {
  echo display_text($_displayType["WARNING"], 'The log cache is empty.');
}
$dblog = db_records();
if ($cache > $dblog) {
  echo display_text($_displayType["WARNING"],
        'There are '.$cache.' logs in cache dir, but there are '.$dblog.' in database, which is something weird :/');
}

// ensure that logs can be written
if (!is_writeable(CACHE_DIR)) {
  $perms = substr(decoct( fileperms(CACHE_DIR) ), 2);
  if (($perms != "775" || $perms != "777") && !chmod(CACHE_DIR, 0775))
  {
    echo display_text($_displayType["ERROR"],
                      'Settings permissions to <strong>'.CACHE_DIR.'</strong> failed.
                       You must set write permissions to that directory manually.'
                     );
  }
}

if (is_admin()) {
?>

<h3>Tracking Code</h3>
<p>Put this snippet in the pages you'd wish to track:</p>
<code><pre><?php
  //$code  = '<script type="text/javascript" src="'.ABS_PATH.'core/js/smt-aux.min.js"></script>'    . PHP_EOL;
  //$code .= '<script type="text/javascript" src="'.ABS_PATH.'core/js/smt-record.min.js"></script>' . PHP_EOL;
  $code  = '<script type="text/javascript" src="'.ABS_PATH.'core/js/smt2e.min.js"></script>' . PHP_EOL;
  $code .= '<script type="text/javascript">' . PHP_EOL;
  $code .= 'try { '             . PHP_EOL;
  $code .= '  smt2.record();'   . PHP_EOL;
  $code .= '} catch(err) {} '   . PHP_EOL;
  $code .= '</script>'          . PHP_EOL;
  echo htmlentities($code);
?></pre></code>
<br/>
<p>For more info, go to <a href="https://code.google.com/p/smt2/w/list">the Wiki pages</a>.</p>

<?php
}

// include footer file
include INC_DIR.'footer.php';
?>
