<?php
// load (smt)2 system files
$base = realpath(dirname(__FILE__).'/../../');
require_once $base.'/config.php';

// Config file for the PHP Web Proxy
define('INDEX_FILE_NAME', 'index.php'); // Name of the default file (set on initial form)
define('URL_PARAM_NAME', 'url');  // The parameter name used for the proxy URL.

/* === LOGGING PROPERTIES === */
define('LOG_FILE',         dirname(__FILE__) . '/../logs/proxy.log'); // The path to the log file.
define('LINE_BREAK',       "\n"); // Which line break character should be used.
define('DATE_FORMAT',      'd-m-Y H:i:s'); // The date format for the log entry
define('LOG_LEVEL',        0); // 0 = Debug (Lowest), 4 = Fatal (Highest)
define('LOG_MAX_SIZE',     100); // Maximum log file size in KB. Set to 0 for unlimited.
define('MAX_LOG_BACKUPS',  5); // The number of log files to keep.

/* === Access Control === */

// White list and black list are based on domain name.
// 'domain.com' will match *exactly* domain.com
// '.domain.com' will match domain.com *and* all subdomains
// if your expression starts with a '/' it is treated as a regular expression - and will be checked against the WHOLE URL
// 
$WHITE_LIST = array();
$BLACK_LIST = array();
$BAN_LIST = array(); // an array of Regular Expressions matching IP addresses. For example. 10\.0\.0\..* would match all addresses coming from 10.0.0.0 subnet
?>
