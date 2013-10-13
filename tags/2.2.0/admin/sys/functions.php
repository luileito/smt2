<?php
/**
 * smt2 CMS core functions.
 * @date 27/March/2009  
 * @rev 30/September/2010
 */
unregister_GLOBALS();

// ignore PHP strict notice if time zone has not been set in php.ini
$defaultTimeZone = @date_default_timezone_get();

if ($defaultTimeZone) {
  $location = $defaultTimeZone;
} else if (ini_get('date.timezone')) {
  $location = ini_get('date.timezone');
} else {
  $location = 'UTC';
}
// set date
date_default_timezone_set($location);

// load base files
define('REQUIRED', dirname(__FILE__));

// ------------------------------------------------------------ database API ---
require REQUIRED.'/define.db.php';
require REQUIRED.'/functions.db.php';
// ---------------------------------------------------------- smt2 constants ---
require REQUIRED.'/define.php';
require REQUIRED.'/messages.php';
// --------------------------------------------------------------- utilities ---
require REQUIRED.'/class.domutil.php';
require REQUIRED.'/class.browser.php';
require REQUIRED.'/class.kmeans.php';
require REQUIRED.'/class.mousefeat.php';
require REQUIRED.'/class.hypernote.php';
// ------------------------------------------------------------------ others ---
require REQUIRED.'/functions.array.php';
require REQUIRED.'/functions.url.php';
//require_once realpath(REQUIRED.'/../../').'/core/functions.php';
require_once BASE_PATH.'/core/functions.php';

if (@db_option(TBL_PREFIX.TBL_CMS, "enableDebugging")) {
  error_reporting(E_ALL | E_STRICT);
} else {
  error_reporting(E_ERROR);
}

/** 
 * Additional head tags. Enable inserting custom tags on page head.
 * @global array $_headAdded
 */
$_headAdded = array();

/** 
 * Checks if server is ready to work with smt2 by comparing the server's $type version.
 * At least are required both PHP 5 and MySQL 5.
 * @param   string    $type       "php" or "mysql", by now
 * @param   string    $minReqVer  minimun system version (default: 5.0.0) 
 * @return  boolean               TRUE on sucess, or FALSE on failure 
 */
function check_systemversion($type, $minReqVer = "5.0.0") 
{
  switch (strtolower($type)) {
    case 'mysql':
      // mysqli_get_client_info() doesn't require connection
      $ver = mysql_get_client_info();
      break;
    case 'php':
      $ver = phpversion();
      break;
    default:
      break;
  }
  // $ver must be >= $minReqVer
  $status = version_compare($ver, $minReqVer, ">=");
  
  return $status;
}

/** 
 * Checks if a new smt2 version is released via (smt) website.
 * @return  int   Server response: 1 (up to date), 2 (new version found), 3 (minor build released), 0 (connection error), -1 (parsing error) 
 */
function get_smt_releases()
{
  // connect to Web Service
  $ws = get_remote_webpage("http://smt.speedzinemedia.com/versioncheck.php?v=".SMT_VERSION);
  
  return $ws['content'];
}

/** 
 * Displays a message about the installed smt2 version.
 * @return  string   Message 
 */
function check_smt_releases()
{
  global $_displayType;
  
  $dwnurl = "http://smt.speedzinemedia.com/downloads.php";
  
  $code = get_smt_releases();
  
  switch ($code) {
    case -1:  // parsing/reading error
      $type = $_displayType["ERROR"];
      $text = 'Error while retrieving new (smt)<sup>2</sup> releases.';
      break;
    case 0:   // connection error
    default:
      $type = $_displayType["ERROR"];
      $text = 'Could not find new (smt)<sup>2</sup> releases. If your Internet connection is OK, maybe the server is temporarily down.';
      break;
    case 1:   // up to date
      $type = $_displayType["SUCCESS"];
      $text = 'You are using the latest (smt)<sup>2</sup> version: '.SMT_VERSION;
      break;
    case 2:   // new version found
      $type = $_displayType["WARNING"];
      $text = 'A new (smt)<sup>2</sup> version is available. <a href="'.$dwnurl.'">Please upgrade</a>.';
      break;
    case 3:   // minor build released
      $type = $_displayType["WARNING"];
      $text = 'It seems that there is a <a href="'.$dwnurl.'">new (smt)<sup>2</sup> build available</a>.';
      break;
  }
  
  return display_text($type, $text);
}

/**
 * Displays a text paragraph on page.
 * @param   string    $type "warning", "error" or "success"
 * @param   string    $msg  message to display
 * @param   string    $elem DOM element to enclose message (default: p)
 */
function display_text($type, $msg, $elem = 'p')
{
  return '<'.$elem.' class="'.$type.'">'.$msg.'</'.$elem.'>';
}

/**
 * Redirects the browser to a specified anchor on the index.php page that sent a form from a CMS section.
 * @param   string    $id         HTML element id
 * @param   boolean   $success    no errors to display
 * @param   string    $customErr  if $success is false, type here your own custom message  
 */
function notify_request($id, $success, $customErr = "")
{
  global $_displayType, $_notifyMsg;
  
  $errorMessage = $_notifyMsg["ERROR"];
  if (!$success && !empty($customErr)) {
    $errorMessage = $customErr;
  }
  
  $_SESSION[ $id ] = ($success) ? 
                     display_text($_displayType["SUCCESS"], $_notifyMsg["SAVED"])
                     :
                     display_text($_displayType["ERROR"],   $errorMessage);

  url_redirect( dirname($_SERVER['SCRIPT_NAME'])."/#".$id );
}

/**
 * Displays the message saved on current PHP session. Then the $_SESSION text is unset.
 * @param   string  $name   session variable name 
 */
function check_notified_request($name) 
{
  // get $name text from session
  if (isset($_SESSION[$name])) {
    echo $_SESSION[$name];
  }
  // unset session var
  unset($_SESSION[$name]);
}

/** 
 * Shows only the first $words of a text, plus a [...] symbol. 
 * @param   string  $text   text to trim
 * @param   int     $words  number of words to display (default: 5)
 * @return  string          The trimmed text   
 */
function trim_text($text, $words = 5) 
{
  $space = " ";  
  $text = explode($space, $text);
  
  $show = "";
  foreach ($text as $i => $str) {
    if ($i < $words) { 
      $show .= $str.$space; 
    }
  }
  // references of $i and the last array element remain 
  if ($i >= $words) {
    // add [...] if word count is indeed larger than $words
    $show .= $space."[...]"; 
  }

  return $show;
}

/** 
 * Shows only the first $chars of a text, plus a [...] symbol. 
 * @param   string  $text   text to trim
 * @param   int     $chars  number of chars to display (default: 20)
 * @return  string          The trimmed text
 */
function trim_chars($text, $chars = 20) 
{
  $trimmed = substr($text, 0, $chars);
  if (strlen($trimmed) >= $chars) {
    $trimmed .= "[...]";
  }
  
  return $trimmed;
}

/**
 * Gets the client IP.
 * @return  string
 */
function get_ip()
{
   if( !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ) {
      $final_ip =
         ( !empty($_SERVER['REMOTE_ADDR']) ) ?
            $_SERVER['REMOTE_ADDR'] :
            ( ( !empty($_ENV['REMOTE_ADDR']) ) ?
               $_ENV['REMOTE_ADDR'] :
               "unknown" );
      $entries = split('[, ]', $_SERVER['HTTP_X_FORWARDED_FOR']);
      reset($entries);
      while (list(, $entry) = each($entries)) {
         $entry = trim($entry);
         if ( preg_match("/^([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/", $entry, $ip_list) ) {
            // see http://www.faqs.org/rfcs/rfc1918.html
            $private_ip = array(
                                '/^0\./',
                                '/^127\.0\.0\.1/',
                                '/^192\.168\..*/',
                                '/^172\.((1[6-9])|(2[0-9])|(3[0-1]))\..*/',
                                '/^10\..*/'
                               );

            $found_ip = preg_replace($private_ip, $final_ip, $ip_list[1]);

            if ($final_ip != $found_ip) {
               $final_ip = $found_ip;
               break;
            }
         }
      }
   } else {
      $final_ip =
         ( !empty($_SERVER['REMOTE_ADDR']) ) ?
            $_SERVER['REMOTE_ADDR'] :
            ( ( !empty($_ENV['REMOTE_ADDR']) ) ?
               $_ENV['REMOTE_ADDR'] :
               "unknown" );
   }

   return $final_ip;


}

/** 
 * Masks a given client ID string, just for pretty reading.
 * @param  string   $hash   client ID
 * @return string           Pretty-formatted client ID
 */
function mask_client($hash)
{
  $len = strlen($hash) / 4;
  
  return substr($hash, 0, $len);
}

/** 
 * Displays a default error page.
 * Used when a cached page is deleted, as well as when cURL cannot fetch a remote page. 
 * @param  string   $bodyText additional info to display on page body
 * @return string             The error page 
 */

function error_webpage($bodyText = "") 
{  
  $webpage = '<html><head><title>Error</title></head><body>'.$bodyText.'</body></html>';
  
  return $webpage; 
} 

/** 
 * Merges vertical and horizontal coordinates in a bidimensional point array.
 * Stops coordinates (hesitations) are removed. 
 * @param   array  $xcoords        horizontal coordinates
 * @param   array  $ycoords        vertical coordinates
 * @param   array  $getDistances   if TRUE, the result array contains euclidean distances
 * @return  array                  2D points or euclidean distances array
 */
function convert_points($xcoords, $ycoords, $getDistances = false) 
{
  // initialize points array
  $pointArray = array();
  // check for illegal offsets on $coords
  $maxCount = count($xcoords) - 1;
  // transform arrays in a single points array
  foreach ($xcoords as $i => $value) 
  {
    $p = array($value, $ycoords[$i]); 
    // check if next point exists 
    if ($i >= $maxCount) { break; }
    
    $q = array($xcoords[$i + 1], $ycoords[$i + 1]);
    $distance = KMeans::getDistance($p, $q);
    // check
    if ($getDistances) {
      $pointArray[] = $distance;
    } else {
      // append point to the points array, discarding null distances
      if ($distance > 0) { $pointArray[] = $p; }
    }
  }
  
  return $pointArray;
}

/**
 * Counts the number of mouse clicks.
 * Drag and drop traces are removed.
 * @param   array  $clicks   array of click types (from Motorola protocol)
 * @return  int              number of clicks
 */
function get_click_coordinates($coords_x, $coords_y, $clicks)
{ 
  // check
  if (!is_array($coords_x)) { $coords_x = explode(",", $coords_x); }
  if (!is_array($coords_y)) { $coords_y = explode(",", $coords_y); }
  if (!is_array($clicks)) { $clicks = explode(",", $clicks); }
  
  $clickCoords = array( 
                        "x" => array(), 
                        "y" => array()
                      );
  foreach ($clicks as $i => $value)
  {
    $currClickX = $value > 0 ? $coords_x[$i] : 0;
    $currClickY = $value > 0 ? $coords_y[$i] : 0;
    $clickCoords['x'][] = $currClickX;
    $clickCoords['y'][] = $currClickY;
  }

  return $clickCoords;
}

/**
 * Counts the number of mouse clicks.
 * Drag and drop traces are removed.
 * @param   array  $clicks   array of click types (from Motorola protocol)
 * @return  int              number of clicks
 */
function count_clicks($clicks)
{
  $numClicks = 0;
  
  if (!is_array($clicks)) { $clicks = explode(",", $clicks); }
  
  $maxCount = count($clicks) - 1;
  foreach ($clicks as $i => $value)
  {
    // check if next point exists
    if ($i >= $maxCount) { break; }

    $next = $clicks[$i + 1];
    if ($value > 0 && $value != $next) {
      $numClicks++;
    }
  }

  return $numClicks;
}
      
/** 
 * Gets installed extensions priorities.
 * @return  array   Array with keys: dir name (string) => order priority (int)
 */
function get_exts_order() 
{
  $exts = db_select_all(TBL_PREFIX.TBL_EXTS, "*", "1");
  
  foreach ($exts as $ext) {
    $priority[ $ext['dir'] ] = (int) $ext['priority'];
  }
  // sort maintaining index association
  if (max($priority) > 0) { asort($priority); }

  return $priority;
}

/** 
 * Gets all available CMS sections.
 * @return  array   Array of strings (sections)
 */
function ext_available() 
{
  $dir = INC_PATH.'ext';
  $ext = array();
  if ($handle = opendir($dir)) 
  {
    while (false !== ($file = readdir($handle))) {
      // look for available module extensions
      if (!str_startswith($file, ".") && is_dir($dir.'/'.$file)) {
        $ext[] = $file;
      }
    }
    closedir($handle);
  }
  
  return $ext;
}

/** 
 * Gives format to CMS sections. 
 * @return  string  Formatted output list (LI elements)
 */
function ext_format()
{
  if (!isset($_SESSION['allowed'])) return false;
  
  $current = ext_name();
  // check priority
  $prioritized = get_exts_order();
  // loop through available sections
  $list = "";
  foreach ($prioritized as $dir => $priority) 
  { 
    if (!in_array($dir, $_SESSION['allowed'])) { continue; }
    
    $css = ($current == $dir) ? ' class="current"' : null;
    $href = ADMIN_PATH.'ext/'.$dir.'/';
    $list .= '<li'.$css.'><a href="'.$href.'">'.ucfirst(filename_to_str($dir)).'</a></li>';
  }
  
  return $list;
}

/** 
 * Gets the current CMS extension name. 
 * Sub-extensions are allowed as long as parent extension are allowed.
 * @return  string    Section name
 */
function ext_name() 
{
  $url = dirname($_SERVER['PHP_SELF']);
  $tok = "/ext/";
  
  if (strpos($url, $tok) === false) {
    $ext = explode("/", $url);
    return $ext[ count($ext) - 1 ];
  }
  
  list($admin, $ext) = explode($tok, $url);
  $subext = explode("/", $ext);
  if (empty($subext[0])) {
    $ext = explode("/", $url);
    return $ext[ count($ext) - 1 ];
  } else {
    return $subext[0];
  }
}

/** 
 * Assigns a valid filename to a given string: only alphanumeric chars. Spaces are converted to dashes.
 * @param   string  $string   input string 
 * @return  string            Normalized String
 */
function str_to_filename($string) 
{
  // remove non alphanumeric chars
  $string = preg_replace('/[^a-z0-9A-Z\s]+/', '', strtolower($string));
  // now convert spaces to dashes
  $string = str_replace(" ", "-", $string);
  
  return $string;
}

/** 
 * Reverse function for str_to_filename. Dashes are converted to spaces.
 * @param   string  $string   normalized String 
 * @return  string            Output String
 */
function filename_to_str($string) 
{
  // now convert dashes to spaces 
  $string = str_replace("-", " ", $string);
  
  return $string;
}

/** 
 * Adds $element tags to all CMS extensions header.
 * @param   mixed   $element  HTML code to insert in the HEAD of any CMS section (<style>, <script>, etc.). Can be a single string or an Array
 * @global  array   $_headAdded
 */
function add_head($element) 
{
  global $_headAdded;
  
  if (!$element) return;

  if (is_array($element)) {
    foreach ($element as $value) {
      $_headAdded[] = $value;
    }
  } else {
    $_headAdded[] = $element;
  }
}

/** 
 * Displays a <noscript> warning message. Useful for those extensions that require JavaScript functionality.
 * @param   string  $msg    custom warning message. Default: "Please enable JavaScript in order to work on this section."
 * @return  string          Message wrapped in a <noscript> tag   
 */
function check_noscript($msg = "") 
{
  global $_displayType, $_notifyMsg;
  
  if (empty($msg)) $msg = $_notifyMsg["NOSCRIPT"];
  
  return '<noscript>'.display_text($_displayType["WARNING"], $msg).'</noscript>';
}

/** 
 * Count files in a dir. This function skip directories, and it is not recursive.
 * By now it is only used to check the cache logs. 
 * @param   string  $dir    the directory to read files from
 * @return  int             Number of files
 */
function count_dir_files($dir) 
{  
  $count = 0;
  if ($handle = opendir($dir)) {
    while (false !== ($file = readdir($handle))) {
      if (!str_startswith($file, ".") && is_file($dir.'/'.$file)) {
        $count++;
      }
    }
    closedir($handle);
  }
  
  return $count;
}

/** 
 * Verifies that current login has admin privileges.
 * Note that various admin users can coexist on the CMS.
 * @return  boolean   TRUE on sucess, or FALSE on failure
 */
function is_admin() 
{
  if (!isset($_SESSION['login'])) return false;
  
  // get admin role_id
  $user = db_select(TBL_PREFIX.TBL_USERS, "role_id", "login = '".$_SESSION['login']."'");
  return ( (int) $user['role_id'] === 1 );
}

/** 
 * Verifies that current login is the superadmin user.
 * @return  boolean   TRUE on sucess, or FALSE on failure
 */
function is_root() 
{
  if (!isset($_SESSION['login'])) return false;

  
  // get root role_id
  $user = db_select(TBL_PREFIX.TBL_USERS, "id", "login = '".$_SESSION['login']."'");
  return ( (int) $user['id'] === 1 );
}

/** 
 * Gets all allowed CMS extensions for the current user.
 * @return  array   Array of strings (sections)
 */
function is_allowed() 
{
  // check current user's role
  if ($_SESSION['role_id'] > 0)
  {
    $user = db_select(TBL_PREFIX.TBL_USERS, "role_id", "login = '".$_SESSION['login']."'");
    if ( (int) $user['role_id'] !== 1 ) 
    {
      $current = ext_name();
      // check if current section is allowed
      return (strpos($current, $_SESSION['ext_allowed']) !== false);
    } else {
      return true;
    }
  }
  else 
  {
    return false;
  }
}
   
/**
 * Random Password Generator.
 * @autor Charlie
 * @link http://snippets.dzone.com/user/Charlie 
 * @version 0.1.0 - 2006-02-14 
 */
function generate_password()
{
  $pwd = array(
  	"C" => array('chars' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 'min' => 4, 'max' => 6),
  	"S" => array('chars' => "!@()-_=+?*^&", 'min' => 1, 'max' => 2),
  	"N" => array('chars' => '1234567890', 'min' => 2, 'max' => 2)
  );
	// Create the meta-password
	$meta = "";
	foreach ($pwd as $cToken => $seed) {
    $meta .= str_repeat($cToken, rand($seed['min'], $seed['max']));
  }
	$meta = str_shuffle($meta);
	// Create the real password
	$buffer = array();
	for ($i = 0; $i < strlen($meta); ++$i) {
    $buffer[] = $pwd[(string)$meta[$i]]['chars'][rand(0, strlen($pwd[$meta[$i]]['chars']) - 1)];
  }

	return implode("", $buffer);
}

/**
 * Checks if an email address is valid. 
 * The chars # $ % & ' * + / = ? ^ ` { | } ~ are theoretically allowed on the local part,
 * but in practice they are discarded.  
 * @param   string    $email  email to check 
 * @return  boolean           TRUE on sucess, or FALSE on failure   
 * @link    http://tools.ietf.org/html/rfc5321
 * @link    http://tools.ietf.org/html/rfc5322  
 */
function is_email($email)
{       
  return eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$", $email);
}

/**
 * Searches DNS for MX records corresponding to user's email account hostname. 
 * @param   string    $email  user email 
 * @return  boolean           TRUE on sucess, or FALSE on failure   
 */
function email_exists($email)
{
  if (!is_email($email)) { return false; }
  
  list($user, $domain) = split("@", $email);
  if (function_exists('getmxrr') && getmxrr($domain, $MXHost)) {
    return true;
  } else {
    return (fsockopen($domain, 80, $errno, $errstr, 30));
  }
}

/**
 * Stops executing a PHP script, displaying a reason for the error.
 * @param   string    $text  message
 */
function die_msg($text = "")
{
  if (!empty($text)) { $text = ": ".$text; }
  
  die("<strong>Error</strong>".$text);
}

/**
 * Pad with zeros a number.
 * @param int $num      input number
 * @param int $numZeros number of zeros
 * @return string
 */
function pad_number($num, $numZeros)
{
  return sprintf("%0".$numZeros."d", $num);
}

/** Emulates register_globals off. */
function unregister_GLOBALS()
{
    if (!ini_get('register_globals')) {
        return;
    }

    // might want to change this perhaps to a nicer error
    if (isset($_REQUEST['GLOBALS']) || isset($_FILES['GLOBALS'])) {
        die('GLOBALS overwrite attempt detected');
    }

    // variables that shouldn't be unset
    $noUnset = array('GLOBALS',  '_GET',
                     '_POST',    '_COOKIE',
                     '_REQUEST', '_SERVER',
                     '_ENV',     '_FILES');

    $input = array_merge($_GET,    $_POST,
                         $_COOKIE, $_SERVER,
                         $_ENV,    $_FILES,
                         isset($_SESSION) && is_array($_SESSION) ? $_SESSION : array());

    foreach ($input as $k => $v) {
        if (!in_array($k, $noUnset) && isset($GLOBALS[$k])) {
            unset($GLOBALS[$k]);
        }
    }
}

/**
 * Gets a SQL-like string with all cache IDs that are related to the same URL.
 * @param   int     $pageId   Log cache ID
 * @return  string  SQL query
 */
function get_cache_common_url($pageId)
{
  $common = db_select(TBL_PREFIX.TBL_CACHE, "url", "id = '".$pageId."'");
  $moreId = db_select_all(TBL_PREFIX.TBL_CACHE, "id", "url = '".$common['url']."'");
  // merge values
  $moreId = array_flatten($moreId);
  // set query
  $merge = "";
  foreach ($moreId as $k => $value) {
    if ($value != $pageId)
      $merge .= " OR cache_id = '".$value."' ";
  }

  return $merge;
}

/**
 * Checks if a string starts with a certain prefix.
 * @param   $string string  source string
 * @param   $prefix string  prefix to find
 * @return          boolean TRUE on success or FALSE on failure
 */
function str_startswith($str, $prefix) 
{
   return strncmp($str, $prefix, strlen($prefix)) == 0;
}

/**
 * Converts Boolean value to string.
 * @param   $string boolean value
 * @return          string  Either "true" or "false"
 */
function strbool($value)
{
  return $value ? 'true' : 'false';
}
?>
