<?php
/** Installed (smt) version. */
define ('SMT_VERSION',  "2.2.0");

/** Title for CMS pages. */
define ('CMS_TITLE',   "simple mouse tracking " . SMT_VERSION);
/** Path to admin dir. This absolute URL is used to resolve links, redirections, an so on. */
define ('ADMIN_PATH',    ABS_PATH."admin/");
/** Path to CSS styles. */
define ('CSS_PATH',      ADMIN_PATH."css/");
/** Path to core ActionScript functions. */
define ('SWF_PATH',      ABS_PATH."core/swf/");
// check if debugging is active
$jspath = ABS_PATH."core/js/";
$jsext = ".min.js";
if (@db_option(TBL_PREFIX.TBL_CMS, "enableDebugging")) {
  $jspath .= "src/";
  $jsext = ".js";
}
/** Path to core JavaScript functions. */
define ('JS_PATH',       $jspath);
/** smt2 record script. */
define ('SMT_RECORD',    JS_PATH."smt-record".$jsext);
/** smt2 replay script. */
define ('SMT_REPLAY',    JS_PATH."smt-replay".$jsext);
/** smt2 auxiliar functions. */
define ('SMT_AUX',       JS_PATH."smt-aux".$jsext);
/** WZ JavaScript graphics libary. */
define ('WZ_JSGRAPHICS', JS_PATH."wz_jsgraphics".$jsext);
/** JSON parser. */
define ('JSON_PARSER',   JS_PATH."json2".$jsext);
/** JavaScript DOM selector library (Sizzle, Peppy, Selector, etc.). */
define ('JS_SELECTOR',   JS_PATH."selector".$jsext);
/** SWFObject library. */
define ('SWFOBJECT',     ADMIN_PATH."js/swfobject.js"); // it's already minified

/** HTML logs dir. Do not use absolute URLs because fopen wrappers could be disabled. */
define ('CACHE_DIR',     BASE_PATH."/cache/");
/** Path to system dir. */
define ('SYS_DIR',       INC_PATH."sys/");
/** Path to common includes (header, footer, and so on). */
define ('INC_DIR',       INC_PATH."inc/");

/** Form input type: User must enter some value (input text) */
define ('CMS_TYPE',      0);
/** Form input type: User must choose between 2 options (checkbox) */
define ('CMS_CHOICE',    1);
/** Form input type: User must choose between 3 or more options (radio button) */
define ('CMS_MULTIPLE',  2);
?>
