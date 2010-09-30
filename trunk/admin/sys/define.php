<?php
/** Installed (smt) version. */
define ('SMT_VERSION',  "2.0.2");

/** Title for CMS pages. */
define ('CMS_TITLE',   "simple mouse tracking " . SMT_VERSION);
/** Path to admin dir. This absolute URL is used to resolve links, redirections, an so on. */
define ('ADMIN_PATH',    ABS_PATH."admin/");
/** Path to CSS styles. */
define ('CSS_PATH',      ADMIN_PATH."css/");
/** Path to core ActionScript functions. */
define ('SWF_PATH',      ABS_PATH."core/swf/");
/** Path to core JavaScript functions. */
define ('JS_PATH',       ABS_PATH."core/js/");
/** smt2 record script. */
define ('SMT_RECORD',    JS_PATH."smt-record.min.js");
/** smt2 replay script. */
define ('SMT_REPLAY',    JS_PATH."smt-replay.min.js");
/** smt2 auxiliar functions. */
define ('SMT_AUX',       JS_PATH."smt-aux.min.js");
/** WZ JavaScript graphics libary. */
define ('WZ_JSGRAPHICS', JS_PATH."wz_jsgraphics.min.js");
/** JSON parser. */
define ('JSON_PARSER',   JS_PATH."json2.min.js");
/** JavaScript DOM selector library (Sizzle, Peppy, Selector, etc.). */
define ('JS_SELECTOR',    JS_PATH."selector.min.js");
/** SWFObject library. */
define ('SWFOBJECT',     ADMIN_PATH."js/swfobject.js");

/** HTML logs dir. Do not use absolute URLs because fopen wrappers could be disabled. */
define ('CACHE_DIR',     INC_PATH."cache/");
/** Path to system dir. */
define ('SYS_DIR',       INC_PATH."sys/");
/** Path to common includes (header, footer, and so on). */
define ('INC_DIR',       INC_PATH."inc/");

/** Table for storing smt2 records. */
define ('TBL_RECORDS',   "records");
/** Table for caching HTML logs. */
define ('TBL_CACHE',     "cache");
/** Table for storing browser names. */
define ('TBL_BROWSERS',  "browsers");
/** Table for storing operating system names. */
define ('TBL_OS',        "os");
/** Table for registered users. */
define ('TBL_USERS',     "users");
/** Table for managing user roles. */
define ('TBL_ROLES',     "roles");
/** Table for registering extension modules. */
define ('TBL_EXTS',      "exts");
/** Table for customizing CMS options. */
define ('TBL_CMS',       "cms");
/** Table for customizing JS replay options. */
define ('TBL_JSOPT',     "jsopt");
/** Form input type: User must enter some value (input text) */
define ('CMS_TYPE',      0);
/** Form input type: User must choose between 2 options (checkbox) */
define ('CMS_CHOICE',    1);
/** Form input type: User must choose between 3 or more options (radio button) */
define ('CMS_MULTIPLE',  2);
?>